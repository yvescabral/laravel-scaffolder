<?php

namespace LAIS\Scaffold\Console\Commands\Migration;

class CreateSchema
{

    private $schema = [];

    public function parse($tableName, $schema) {
        $this->schema[] = $this->schemaUp($tableName, $schema);
        $this->schema[] = $this->schemaDown($tableName);

        return $this->schema;
    }

    protected function schemaUp($tableName, $schema) {
        $schemaUp = "Schema::create('".$tableName."', function (Blueprint \$table) {\n";
        $schemaUp .= $this->createColumns($this->getFields($schema));
        $schemaUp .= "\t\t});";

        return $schemaUp;
    }

    protected function schemaDown($tableName) {
        return "Schema::drop('".$tableName."');";
    }

    protected function createColumns($fields) {
            $columns = "\t\t\t\$table->increments('id');\n";
            foreach ($fields as $field) {
                $columns .= sprintf("\t\t\t\$table->%s('%s')", $field->type, $field->name);
                if (property_exists($field, "arguments"))
                {
                  $columns = substr($columns, 0, -1) . ', ';
                  $columns .= implode(', ', $field->arguments) . ')';
                }
                foreach ($field->options as $option => $value)
                {
                  $columns.= sprintf("->%s(%s)", $option, $value === true ? '' : $value);
                }
                $columns .= ";\n";
            }

            $columns .= "\t\t\t\$table->timestamps();\n";

            return $columns;
    }

    public function getFields($schema) {
    $schemas = preg_split('/,\s?(?![^()]*\))/', $schema);
    $fields = array();

    foreach ($schemas as $schema) {
        $parts = explode(":", $schema);
        $field = new \stdClass();
        $field->name = array_shift($parts);
        $field->type = array_shift($parts);
        if (preg_match('/(.+?)\(([^)]+)\)/', $field->type, $matches))
        {
          $field->type = $matches[1];
          $field->arguments = explode(',', $matches[2]);
        }
        $field->options = $this->getOptions($parts);
        $fields[] = $field;
    }

    return $fields;
  }

    protected function getOptions($options)
    {
      if (empty($options)) return [];

      foreach ($options as $option)
      {
          if (str_contains($option, '(')) {
              preg_match('/([a-z]+)\(([^\)]+)\)/i', $option, $matches);
              $results[$matches[1]] = $matches[2];
          } else {
              $results[$option] = true;
          }
      }
        return $results;
    }


}


//--schema="title:string:default('Tweet #1'), body:text"

/*
 *      Schema::create('flights', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('airline');
            $table->timestamps();
        });
 */
