<?php

namespace Elison\Talos\Console\Commands;

use Illuminate\Console\Command;
use File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GenerateRequest extends Command
{
    protected $signature = 'talos:generate-request {table_name}, {request_class_name}';

    protected $description = 'Generate Request based on column types of a table';

    public function handle()
    {
        $this->info('Generating request...');

        $tableName = $this->argument('table_name');
        $requestClassName = $this->argument('request_class_name');

        $rulesArray = $this->getRules($tableName);

        if (empty($rulesArray)) {
            return $this->error('Please provide a correct table name');
        }

        $this->generateFile($requestClassName, $rulesArray, $tableName);

        $this->info('Request Generated Successfully...');
    }

    private function getRules($tableName)
    {
        $columnNames = Schema::getColumnListing($tableName);

        $rulesArray = [];

        if (empty($columnNames)) {
            return [];
        }

        if ($columnNames) {
            foreach ($columnNames as $columnName) {
                $columnType = DB::getSchemaBuilder()->getColumnType($tableName, $columnName);

                $rulesArray[$columnName] = $columnType;
            }
        }

        return $this->filterRules($rulesArray);
    }

    private function generateFile($requestClassName, $rulesArray, $tableName)
    {
        $stub = file_get_contents(__DIR__ . ('/stubs/request.stub'));
        $stub = str_replace('{{ class }}', $requestClassName, $stub);

        $rulesString = $this->generateRuleString($rulesArray, $tableName);
        $stub = str_replace('{{ [] }}', $rulesString, $stub);

        File::put(app_path('Http/Requests/' . $requestClassName . '.php'), $stub);
    }

    private function filterRules($rules)
    {
        // TODO: ADD MORE CASES
        foreach ($rules as $key => $rule) {
            switch ($rule) {
                case 'datetime':
                    $rules[$key] = 'date';
                    break;
                case 'bigint':
                    $rules[$key] = 'integer';
                    break;
                case 'text':
                    $rules[$key] = 'string';
                    break;
            }
        }

        return $rules;
    }

    private function generateRuleString($rulesArray, $tableName)
    {
        $rulesString = '';

        foreach ($rulesArray as $columnName => $rule) {
            $columnConstraints = $this->getConstraintsForColumn($tableName, $columnName);

            $isPrimaryKey = $columnConstraints->COLUMN_KEY === 'PRI';

            if (! $isPrimaryKey) {
                $requiredOrNot = $columnConstraints->IS_NULLABLE === 'YES' ? 'nullable' : 'required';

                $rulesString .= "
            '" . $columnName . "'" . " => " . "['" . $rule . "','" . $requiredOrNot . "'],";
            }
        }

        return $rulesString;
    }

    private function getConstraintsForColumn($tableName, $columnName)
    {
        // TODO: GET MORE CONSTRAINTS
        return DB::table('INFORMATION_SCHEMA.columns')
            ->select('IS_NULLABLE', 'COLUMN_KEY')
            ->where([
                ['table_name', $tableName],
                ['column_name', $columnName]
            ])
            ->first();
    }
}
