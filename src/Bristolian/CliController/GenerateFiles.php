<?php

namespace Bristolian\CliController;

use Bristolian\Config\Config;
use Bristolian\BristolianException;
use PDO;

function generate_table_helper_class($tableName, $columns)
{
    $output_filename = __DIR__ . "/../../Bristolian/Database/" . $tableName . ".php";

    $contents = "<?php\n\n";
    $contents .= "// Auto-generated file do not edit\n\n";
    $contents .= "namespace Bristolian\\Database;\n\n";

    $columns_separated_by_comma_new_line = "";
    $values_names_separated_by_comma_new_line = "";
    $separator = "";
    foreach ($columns as $column) {
        if (strcasecmp($column['COLUMN_NAME'], 'created_at') === 0) {
            continue;
        }
        if (strcasecmp($column['COLUMN_NAME'], 'updated_at') === 0) {
            continue;
        }

        $columns_separated_by_comma_new_line .= $separator . "    " . $column['COLUMN_NAME'];
        $values_names_separated_by_comma_new_line .= $separator . "    :" . $column['COLUMN_NAME'];
        $separator = ",\n";
    }
    $columns_separated_by_comma_new_line .= "\n";
    $values_names_separated_by_comma_new_line .= "\n";

    $contents .= "class $tableName\n";
    $contents .= "{\n";
    $contents .= "    const INSERT = <<< SQL\n";
    $contents .= "insert into $tableName (\n";
    $contents .= $columns_separated_by_comma_new_line;
    $contents .= ")\n";
    $contents .= "values (\n";
    $contents .= $values_names_separated_by_comma_new_line;
    $contents .= ")\n";
    $contents .= "SQL;\n\n";


    $contents .= "    const SELECT = <<< SQL\n";
    $contents .= "select  \n";
    $contents .= $columns_separated_by_comma_new_line;
    $contents .= "from\n  $tableName\n";

    $contents .= "SQL;\n";

    $contents .= "}\n";











    \Safe\file_put_contents($output_filename, $contents);
}







class GenerateFiles
{
    /**
     * This generates a TypeScript file that contains constants that need to be shared
     * between the front and backend e.g. the name for the field on a form that uploads
     * a file.
     */
    public function generateJavaScriptConstants()
    {
        $output_filename = __DIR__ . "/../../../app/public/tsx/generated/constants.tsx";

        $constants = [
            'MEME_FILE_UPLOAD_FORM_NAME' => \Bristolian\AppController\MemeUpload::MEME_FILE_UPLOAD_FORM_NAME,
            'ROOM_FILE_UPLOAD_FORM_NAME' => \Bristolian\AppController\Rooms::ROOM_FILE_UPLOAD_FORM_NAME,
        ];

        $template = <<< TEMPLATE
export const :js_name: string = ":js_value";\n
TEMPLATE;


        $content = "// This is an auto-generated file\n";
        $content .= "// DO NOT EDIT\n\n";
        $content .= "// You'll need to bounce the docker boxes to regenerate.\n\n";
        // TODO - add command name

        foreach ($constants as $constant_name => $constant_value) {
            $params = [
                ':js_name' => $constant_name,
                // Technically this is wrong. The escaping needed is
                // "string escape within JS". But JavaScript escaping is probably
                // safe.
                ':js_value' => $constant_value
            ];
            $content .= esprintf($template, $params);
        }

        $result = file_put_contents($output_filename, $content);
        if ($result === false) {
            throw new BristolianException("Something ");
        }
    }

    /**
     * This generates a
     */
    public function generateTableHelperClasses(
        Config $config,
        PDO $pdo
    ) {
        $schema = $config->getDatabaseSchema();

        $table_query = "SELECT table_name FROM information_schema.tables
        WHERE table_schema = '$schema'";

        $statement = $pdo->query($table_query);



        if ($statement === false) {
            echo "Query failed.";
            exit(-1);
        }

        $rows = $statement->fetchAll();



        $column_query = <<< SQL
SELECT COLUMN_NAME
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = '$schema' AND TABLE_NAME = '%s';
SQL;

        foreach ($rows as $row) {
            $tableName = $row['TABLE_NAME'];
            $query = sprintf($column_query, $tableName);
            // SHOW COLUMNS FROM my_table;
            $columns_statement = $pdo->query($query);

            $columns = $columns_statement->fetchAll();

            generate_table_helper_class($tableName, $columns);
        }
    }
}
