<?php

namespace Bristolian\CliController;

use Bristolian\Exception\BristolianException;
use Bristolian\Config\Config;
use PDO;



/**
 *
 * @codeCoverageIgnore
 * @param string $tableName
 * @param array $columns
 * @return void
 * @throws \Safe\Exceptions\FilesystemException
 */
function generate_table_helper_class(string $tableName, array $columns): void
{
    $output_filename = __DIR__ . "/../../Bristolian/Database/" . $tableName . ".php";

    $contents = "<?php\n\n";
    $contents .= "// Auto-generated file do not edit\n\n";
    $contents .= "namespace Bristolian\\Database;\n\n";

    $columns_separated_by_comma_new_line = "";
    $values_names_separated_by_comma_new_line = "";
    $separator = "";

    // TODO - sort these columns by something, so that they are consistent
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
    $contents .= "select\n";
    $contents .= $columns_separated_by_comma_new_line;
    $contents .= "from\n  $tableName \n"; //trailing space to avoid errors

    $contents .= "SQL;\n";

    $contents .= "}\n";

    \Safe\file_put_contents($output_filename, $contents);
}


/**
 * Function to generate TypeScript definition of an interface for a PHP
 * class, so that data transferred from PHP to the front-end can be typed.
 *
 * Code is not unit tested as just not worth it currently.
 *
 * @codeCoverageIgnore
 * @param class-string $type
 * @return string
 */
function generateInterfaceForClass(string $type): string
{

    // TODO - this is a hack. It would almost certainly be better to
    // use https://www.npmjs.com/package/openapi-typescript but as our
    // Open API spec isn't generating, this will work for the time being.

    $content = '';

    $rc = new \ReflectionClass($type);

    $name = $rc->getShortName();
    $content .= "// $type\n";
    $content .= "export interface $name {\n";

    foreach ($rc->getProperties() as $property) {
        $php_type = $property->getType();
        if (str_starts_with($php_type, '?') === true) {
            $php_type = substr($php_type, 1) . "|null";
        }

        $content .= "    " . $property->getName() . ": " . $php_type . ";\n";
    }

    $content .= "}\n";

    return $content;
}


/**
 * Class to hold code that generates JavaScript helper code.
 * Not unit-tested as just currently not worth it.
 *
 * @codeCoverageIgnore
 */
class GenerateFiles
{
    public function generateAllJavaScriptFiles(): void
    {
        $this->generateJavaScriptConstants();
        $this->generateJavaScriptTypes();
    }


    public function generateJavaScriptTypes(): void
    {
        $output_filename = __DIR__ . "/../../../app/public/tsx/generated/types.tsx";

        $content = "// This is an auto-generated file\n";
        $content .= "// DO NOT EDIT\n\n";
        $content .= "// You'll need to bounce the docker boxes to regenerate.\n\n";

        $types = [
            \Bristolian\Model\RoomLink::class,
            \Bristolian\Model\RoomSourceLink::class,
        ];

        foreach ($types as $type) {
            $content .= generateInterfaceForClass($type);
            $content .= "\n";
        }

        $result = file_put_contents($output_filename, $content);
        if ($result === false) {
            throw new BristolianException("Something went wrong writing to file in generateJavaScriptTypes");
        }
    }




    /**
     * This generates a TypeScript file that contains constants that need to be shared
     * between the front and backend e.g. the name for the field on a form that uploads
     * a file.
     */
    public function generateJavaScriptConstants(): void
    {
        $output_filename = __DIR__ . "/../../../app/public/tsx/generated/constants.tsx";

        $constants = [
            'MEME_FILE_UPLOAD_FORM_NAME' => \Bristolian\AppController\MemeUpload::MEME_FILE_UPLOAD_FORM_NAME,
            'ROOM_FILE_UPLOAD_FORM_NAME' => \Bristolian\AppController\Rooms::ROOM_FILE_UPLOAD_FORM_NAME,

            'SOURCELINK_JSON_MINIMUM_LENGTH' => \Bristolian\DataType\SourceLinkHighlightsJson::MINIMUM_LENGTH,
            'SOURCELINK_JSON_MAXIMUM_LENGTH' => \Bristolian\DataType\SourceLinkHighlightsJson::MAXIMUM_LENGTH,

            'SOURCELINK_TITLE_MINIMUM_LENGTH' => \Bristolian\DataType\SourceLinkTitle::MINIMUM_LENGTH,
            'SOURCELINK_TITLE_MAXIMUM_LENGTH' => \Bristolian\DataType\SourceLinkTitle::MAXIMUM_LENGTH,

            'SOURCELINK_TEXT_MAXIMUM_LENGTH' => \Bristolian\DataType\SourceLinkText::MAXIMUM_LENGTH,

            'SOURCE_LINK_MAX_PAGES' => \Bristolian\DataType\SourceLinkPage::MAX_PAGES,
        ];

        $string_template = <<< TEMPLATE
export const :js_name: string = ":js_value";\n
TEMPLATE;

        $int_template = <<< TEMPLATE
export const :js_name: number = :js_value;\n
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
            $template = $string_template;
            if (is_int($constant_value) === true) {
                $template = $int_template;
            }
            $content .= esprintf($template, $params);
        }

        $result = file_put_contents($output_filename, $content);
        if ($result === false) {
            throw new BristolianException("Something went wrong writing to file in generateJavaScriptConstants");
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
