<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Config\Config;
use Bristolian\Exception\BristolianException;
use PDO;

function generate_table_strings($sorted_column_names)
{
    $separator = "";
    $columns_separated_by_comma_new_line = "";
    $values_names_separated_by_comma_new_line = "";

    foreach ($sorted_column_names as $column_name) {
//        if (strcasecmp($column_name, 'created_at') === 0) {
//            continue;
//        }
//        if (strcasecmp($column_name, 'updated_at') === 0) {
//            continue;
//        }

        $columns_separated_by_comma_new_line .= $separator . "    " . $column_name;
        $values_names_separated_by_comma_new_line .= $separator . "    :" . $column_name;
        $separator = ",\n";
    }

    $columns_separated_by_comma_new_line .= "\n";
    $values_names_separated_by_comma_new_line .= "\n";

    return [$columns_separated_by_comma_new_line, $values_names_separated_by_comma_new_line];
}


function generate_table_update_strings($sorted_column_names)
{
    $separator = "";
    $update_strings = "";

    foreach ($sorted_column_names as $column_name) {
        $update_strings .= $separator . "  $column_name = :" . $column_name ."";
        $separator = ",\n";
    }

    return $update_strings;
}

/**
 * Convert table name (snake_case) to class name (PascalCase)
 *
 * @codeCoverageIgnore
 */
function table_name_to_class_name(string $tableName): string
{
    $parts = explode('_', $tableName);
    $className = '';
    foreach ($parts as $part) {
        $className .= ucfirst($part);
    }
    return $className;
}

/**
 * Map MySQL column type to PHP type
 *
 * @codeCoverageIgnore
 * @param array<string, mixed> $column
 * @return string PHP type (e.g., 'string', 'int', 'DateTimeInterface')
 */
function map_column_to_php_type(array $column): string
{
    $dataType = strtolower($column['DATA_TYPE']);
    $isNullable = $column['IS_NULLABLE'] === 'YES';
    $columnName = strtolower($column['COLUMN_NAME']);

    // Check for datetime columns
    $datetimeColumns = ['created_at', 'updated_at', 'start_time', 'end_time'];
    if (in_array($columnName, $datetimeColumns, true)) {
        return $isNullable ? '?\DateTimeInterface' : '\DateTimeInterface';
    }

    // Map MySQL types to PHP types
    switch ($dataType) {
        case 'tinyint':
        case 'smallint':
        case 'mediumint':
        case 'int':
        case 'integer':
        case 'bigint':
            return $isNullable ? '?int' : 'int';
        
        case 'decimal':
        case 'numeric':
        case 'float':
        case 'double':
        case 'real':
            return $isNullable ? '?float' : 'float';
        
        case 'char':
        case 'varchar':
        case 'text':
        case 'tinytext':
        case 'mediumtext':
        case 'longtext':
        case 'enum':
        case 'set':
        case 'binary':
        case 'varbinary':
        case 'blob':
        case 'tinyblob':
        case 'mediumblob':
        case 'longblob':
            return $isNullable ? '?string' : 'string';
        
        case 'date':
        case 'time':
        case 'datetime':
        case 'timestamp':
        case 'year':
            return $isNullable ? '?\DateTimeInterface' : '\DateTimeInterface';
        
        case 'json':
            return $isNullable ? '?string' : 'string'; // JSON stored as string, can be decoded later
        
        case 'bit':
            return $isNullable ? '?bool' : 'bool';
        
        default:
            // Default to string for unknown types
            return $isNullable ? '?string' : 'string';
    }
}

/**
 * Generate a model class from database table schema
 *
 * @codeCoverageIgnore
 * @param string $tableName
 * @param array<array<string, mixed>> $columns_info
 * @param string $output_directory
 * @return void
 * @throws \Safe\Exceptions\FilesystemException
 */
function generate_model_class(string $tableName, array $columns_info, string $output_directory): void
{
    $className = table_name_to_class_name($tableName);
    $output_filename = $output_directory . "/" . $className . ".php";

    $contents = "<?php\n\n";
    $contents .= "declare(strict_types = 1);\n\n";
    $contents .= "// Auto-generated file do not edit\n\n";
    $contents .= "// generated with 'php cli.php generate:model_classes'\n\n";
    $contents .= "namespace Bristolian\\Model\\Generated;\n\n";
    $contents .= "use Bristolian\\FromArray;\n";
    $contents .= "use Bristolian\\ToString;\n\n";
    $contents .= "class $className\n";
    $contents .= "{\n";
    $contents .= "    use FromArray;\n";
    $contents .= "    use ToString;\n\n";

    // Generate constructor parameters
    $constructorParams = [];
    foreach ($columns_info as $column) {
        $columnName = $column['COLUMN_NAME'];
        $phpType = map_column_to_php_type($column);
        $constructorParams[] = [
            'type' => $phpType,
            'name' => $columnName,
        ];
    }

    // Generate constructor
    $contents .= "    public function __construct(\n";
    $paramCount = count($constructorParams);
    foreach ($constructorParams as $index => $param) {
        $comma = ($index < $paramCount - 1) ? ',' : '';
        $contents .= "        public readonly {$param['type']} \${$param['name']}$comma\n";
    }
    $contents .= "    ) {\n";
    $contents .= "    }\n";
    $contents .= "}\n";

    \Safe\file_put_contents($output_filename, $contents);
}







/**
 *
 * @codeCoverageIgnore
 * @param string $tableName
 * @param array $columns_info
 * @return void
 * @throws \Safe\Exceptions\FilesystemException
 */
function generate_table_helper_class(string $tableName, array $columns_info): void
{
    $output_filename = __DIR__ . "/../../Bristolian/Database/" . $tableName . ".php";

    $contents = "<?php\n\n";
    $contents .= "// Auto-generated file do not edit\n\n";
    $contents .= "// generated with 'php cli.php generate:php_table_helper_classes'\n\n";
    $contents .= "// Generator: src/Bristolian/CliController/GenerateFiles.php :: generate_table_helper_class()";
    $contents .= "// invoked from GenerateFiles::generateTableHelperClasses)\n\n";
    $contents .= "namespace Bristolian\\Database;\n\n";

    $columns_separated_by_comma_new_line = "";
    $values_names_separated_by_comma_new_line = "";


    $column_names_select = [];
    $column_names_insert = [];
    $column_names_update = [];
    foreach ($columns_info as $column) {
        $column_names_select[] = $column['COLUMN_NAME'];
        if (str_contains($column['EXTRA'], 'auto_increment') === true) {
            // auto-increment columns are autogenerated, not provided on insert.
            continue;
        }
        if (strcasecmp($column['COLUMN_NAME'], 'created_at') === 0) {
            continue;
        }
        if (strcasecmp($column['COLUMN_NAME'], 'updated_at') === 0) {
            continue;
        }
        if (strcasecmp($column['COLUMN_NAME'], 'start_time') === 0) {
            continue;
        }



        $column_names_insert[] = $column['COLUMN_NAME'];

        if (str_ends_with($column['COLUMN_NAME'], 'id')) {
            continue;
        }

        $column_names_update[] = $column['COLUMN_NAME'];
    }

    // Insert
    $sorted_column_names_insert = customSort($column_names_insert);
    [$columns_separated_by_comma_new_line, $values_names_separated_by_comma_new_line]
        = generate_table_strings($sorted_column_names_insert);

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

    // Select
    $sorted_column_names_select = customSort($column_names_select);
    [$columns_separated_by_comma_new_line, $values_names_separated_by_comma_new_line]
        = generate_table_strings($sorted_column_names_select);

    $contents .= "    const SELECT = <<< SQL\n";
    $contents .= "select\n";
    $contents .= $columns_separated_by_comma_new_line;
    $contents .= "from\n  $tableName \n"; // trailing space to avoid errors
    $contents .= "SQL;\n\n";

    // Update
    $sorted_column_names_update = customSort($column_names_update);
    $update_string = generate_table_update_strings($sorted_column_names_update);
    $contents .= "    const UPDATE = <<< SQL\n";
    $contents .= "update\n";
    $contents .= "  $tableName\n";
    $contents .= "set\n";
    $contents .= $update_string . "\n";
    $contents .= "where\n";
    $contents .= "  id = :id\n";
    $contents .= "  limit 1\n"; // useless?
    $contents .= "SQL;\n";
    $contents .= "}\n";



    \Safe\file_put_contents($output_filename, $contents);
}

function getTypeDocDescription(\ReflectionClass $rc)
{
    $description = '';

    $doc = $rc->getDocComment();

    // TODO - extract this to a tested function.
    if ($doc === false) {
        return 'no description available';
    }

    // Remove /** */ and leading * characters
    $clean = preg_replace('/^\s*\/\*\*|\*\/\s*$/', '', $doc);
    $clean = preg_replace('/^\s*\*\s?/m', '', $clean);

    // If there's an @description tag, use that
    if (preg_match('/@description\s+(.*)/i', $clean, $m)) {
        $description = trim($m[1]);
    }
    else {
        // Otherwise take the first non-empty line as a summary
        foreach (explode("\n", $clean) as $line) {
            $line = trim($line);
            if ($line !== '' && str_starts_with($line, '@') === false) {
                $description = $line;
                break;
            }
        }
    }

    return $description;
}

/**
 * Function to generate TypeScript definition of an interface for a PHP
 * class, so that data transferred from PHP to the front-end can be typed.
 *
 * Code is not unit tested as just not worth it currently.
 *
 * @codeCoverageIgnore
 * @param class-string $type
 * @return array{string, string[]} Returns the interface content and array of date field names
 */
function generateInterfaceForClass(string $type): array
{

    // TODO - this is a hack. It would almost certainly be better to
    // use https://www.npmjs.com/package/openapi-typescript but as our
    // Open API spec isn't generating, this will work for the time being.

    $content = '';
    $dateFields = [];

    $rc = new \ReflectionClass($type);

    $content .= "// " . getTypeDocDescription($rc) . "\n";
    ;
    $content .= "// Source type is $type\n";

    $name = $rc->getShortName();
    $content .= "export interface $name {\n";

    foreach ($rc->getProperties() as $property) {
        $nullable = false;

        $php_type = $property->getType();
        
        // Handle union types (e.g., "Type|null")
        if ($php_type instanceof \ReflectionUnionType) {
            $types = $php_type->getTypes();
            $hasNull = false;
            $nonNullTypes = [];
            
            foreach ($types as $unionType) {
                if ((string)$unionType === 'null') {
                    $hasNull = true;
                }
                else {
                    $nonNullTypes[] = $unionType;
                }
            }
            
            if (count($nonNullTypes) === 1) {
                $php_type = $nonNullTypes[0];
                $nullable = $hasNull;
            }
            else {
                // Multiple non-null types - use 'any' for now
                $php_type_str = 'any';
                if ($hasNull) {
                    $php_type_str .= "|null";
                }
                $content .= "    " . $property->getName() . ": " . $php_type_str . ";\n";
                continue;
            }
        }
        
        // Handle ReflectionNamedType (PHP 7.0+)
        if ($php_type instanceof \ReflectionNamedType) {
            $nullable = $php_type->allowsNull();
            $php_type_str = $php_type->getName();
        }
        else {
            $php_type_str = (string)$php_type;
        }

        $typescript_type_string = null;

        // Handle DateTime types FIRST (before checking if it's a class/interface)
        if (strcasecmp($php_type_str, 'DateTimeImmutable') === 0 ||
            strcasecmp($php_type_str, 'DateTimeInterface') === 0 ||
            strcasecmp($php_type_str, 'DateTime') === 0) {
            $php_type_str = "Date";
            $typescript_type_string = "Date";
            $dateFields[] = $property->getName();
        }
        elseif (strcasecmp($php_type_str, 'int') === 0) {
            $php_type_str = "number";
            $typescript_type_string = "number";
        }
        elseif (strcasecmp($php_type_str, 'float') === 0) {
            $php_type_str = "number";
            $typescript_type_string = "number";
        }
        elseif (strcasecmp($php_type_str, 'bool') === 0) {
            $php_type_str = "boolean";
            $typescript_type_string = "boolean";
        }
        elseif (strcasecmp($php_type_str, 'string') === 0) {
            $php_type_str = "string";
            $typescript_type_string = "string";
        }
        elseif (strcasecmp($php_type_str, 'array') === 0) {
//            $php_type_str = "array";
            $typescript_type_string = "Object";
        }
        elseif (class_exists($php_type_str) || interface_exists($php_type_str)) {
            // Check if it's a class type (but not DateTime types which we already handled)
            $type_rc = new \ReflectionClass($php_type_str);
            $php_type_str = $type_rc->getShortName();
            $typescript_type_string = $type_rc->getShortName();
        }

        if ($typescript_type_string === null) {
            echo "Failed to find typescript_type_string - php_type_str was $php_type_str\n";
            exit(-1);
        }

        if ($nullable === true) {
            $php_type_str .= "|null";
            $typescript_type_string .= "|null";
        }

        $content .= "    " . $property->getName() . ": " . $typescript_type_string . ";\n";
    }

    $content .= "}\n";

    return [$content, $dateFields];
}

/**
 * Generate a TypeScript conversion function for a type that converts
 * string date fields to Date objects.
 *
 * @codeCoverageIgnore
 * @param class-string $type
 * @param string[] $dateFields
 * @return string
 */
function generateConversionFunctionForClass(string $type, array $dateFields): string
{
    if (count($dateFields) === 0) {
        return '';
    }

    $rc = new \ReflectionClass($type);
    $name = $rc->getShortName();

    $content = "// Conversion function for $type\n";
    $content .= "export function create$name(data: DateToString<$name>): $name {\n";
    $content .= "  return convertDatesFromStrings<$name>(\n";
    $content .= "    data,\n";
    $content .= "    [";
    
    $separator = "";
    foreach ($dateFields as $field) {
        $content .= $separator . "'$field'";
        $separator = ", ";
    }
    
    $content .= "]\n";
    $content .= "  );\n";
    $content .= "}\n";

    return $content;
}

/**
 * Function to generate TypeScript definition of an enum from a PHP
 * Backed Enum class, so that data transferred from PHP to the front-end can be typed.
 *
 * Code is not unit tested as just not worth it currently.
 *
 * @codeCoverageIgnore
 * @param class-string $type
 * @return string
 */
function generateEnumForClass(string $type): string
{
    $content = '';

    $rc = new \ReflectionClass($type);

    $cases = getEnumCases($type);

    $name = $rc->getShortName();


    $content .= "// " . getTypeDocDescription($rc) . "\n";
    $content .= "// Source PHP type: $type\n";

    $content .= "export enum $name {\n";

    // Iterate over all cases and print name and value
    foreach ($cases as $case) {
        if (is_string($case->value) === true) {
            $content .= sprintf(
                "  %s = \"%s\",\n",
                $case->name,
                $case->value
            );
        }
        else if (is_int($case->value) === true) {
            $content .= sprintf(
                "  %s = %s,\n",
                $case->name,
                $case->value
            );
        }
        else {
            throw new \Exception("Unsupported type (neither string nor int) '" . var_export($case, true) . "'.");
        }
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
        $content .= "// You'll need to bounce the docker boxes to regenerate.\n";
        $content .= "//\n";
        $content .= "// or run 'php cli.php generate:javascript_constants' \n";

        $content .= "// Code for generating this file is in \Bristolian\CliController\GenerateFiles::generateJavaScriptTypes \n\n";

        $content .= "import { DateToString, convertDatesFromStrings } from '../functions';\n\n";

        $types = [
            \Bristolian\Model\Generated\BristolStairInfo::class,
            \Bristolian\Model\Generated\ChatMessage::class,
            \Bristolian\Model\Generated\EmailIncoming::class,
            \Bristolian\Model\Generated\StoredMeme::class,
            \Bristolian\Model\Generated\MemeTag::class,
            \Bristolian\Model\Generated\ProcessorRunRecord::class,
            \Bristolian\Model\Generated\RoomLink::class,
            \Bristolian\Model\Generated\RoomSourcelink::class,
            \Bristolian\Model\Generated\RoomFileObjectInfo::class,
            \Bristolian\Model\Types\RoomSourceLinkView::class,
            \Bristolian\Model\Types\UserProfile::class,
            \Bristolian\Model\Types\UserDisplayName::class,
            \Bristolian\Model\Types\UserProfileWithDisplayName::class,
            \Bristolian\Model\TinnedFish\Product::class,
        ];

        $conversionFunctions = '';

        foreach ($types as $type) {
            [$interfaceContent, $dateFields] = generateInterfaceForClass($type);
            $content .= $interfaceContent;
            $content .= "\n";
            
            $conversionFunctions .= generateConversionFunctionForClass($type, $dateFields);
            $conversionFunctions .= "\n";
        }

        /**
         * @var $enums class-string[]
         */
        $enums = [
            \Bristolian\ChatMessage\ChatType::class,
            \Bristolian\Repo\ProcessorRepo\ProcessType::class,
            \Bristolian\Model\TinnedFish\ValidationStatus::class,
        ];


        foreach ($enums as $enum) {
            $content .= generateEnumForClass($enum);
            $content .= "\n";
        }

        // Add conversion functions at the end
        $content .= $conversionFunctions;

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

            'BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME' => \Bristolian\AppController\BristolStairs::BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME,

            'SOURCELINK_JSON_MINIMUM_LENGTH' => \Bristolian\Parameters\PropertyType\SourceLinkHighlightsJson::MINIMUM_LENGTH,
            'SOURCELINK_JSON_MAXIMUM_LENGTH' => \Bristolian\Parameters\PropertyType\SourceLinkHighlightsJson::MAXIMUM_LENGTH,

            'SOURCELINK_TITLE_MINIMUM_LENGTH' => \Bristolian\Parameters\PropertyType\SourceLinkTitle::MINIMUM_LENGTH,
            'SOURCELINK_TITLE_MAXIMUM_LENGTH' => \Bristolian\Parameters\PropertyType\SourceLinkTitle::MAXIMUM_LENGTH,

            'SOURCELINK_TEXT_MAXIMUM_LENGTH' => \Bristolian\Parameters\PropertyType\SourceLinkText::MAXIMUM_LENGTH,

            'SOURCE_LINK_MAX_PAGES' => \Bristolian\Parameters\PropertyType\SourceLinkPage::MAX_PAGES,

            'MINIMUM_DISPLAY_NAME_LENGTH' => \Bristolian\Parameters\PropertyType\DisplayName::MINIMUM_DISPLAY_NAME_LENGTH,
            'MAXIMUM_DISPLAY_NAME_LENGTH' => \Bristolian\Parameters\PropertyType\DisplayName::MAXIMUM_DISPLAY_NAME_LENGTH,

            'MINIMUM_ABOUT_ME_LENGTH' => \Bristolian\Parameters\PropertyType\AboutMeText::MINIMUM_ABOUT_ME_LENGTH,
            'MAXIMUM_ABOUT_ME_LENGTH' => \Bristolian\Parameters\PropertyType\AboutMeText::MAXIMUM_ABOUT_ME_LENGTH,

            'DUPLICATE_FILENAME' => \Bristolian\Service\MemeStorageProcessor\UploadError::DUPLICATE_FILENAME,

            'MEMES_DISPLAY_LIMIT' => \Bristolian\AppController\User::MEMES_DISPLAY_LIMIT,
        ];

        $string_template = <<< TEMPLATE
export const :js_name: string = ":js_value";\n
TEMPLATE;

        $int_template = <<< TEMPLATE
export const :js_name: number = :js_value;\n
TEMPLATE;

        $content = "// This is an auto-generated file\n";
        $content .= "// DO NOT EDIT\n";
        $content .= "//\n";
        $content .= "// You'll need to bounce the docker boxes to regenerate.\n";
        $content .= "//\n";
        $content .= "// or run 'php cli.php generate:javascript_constants' \n";

        $content .= "// Code for generating this file is in \Bristolian\CliController\GenerateFiles::generateJavaScriptConstants \n";


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
SELECT *
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = '$schema' AND TABLE_NAME = '%s';
SQL;

        foreach ($rows as $row) {
            $tableName = $row['TABLE_NAME'];
            $query = sprintf($column_query, $tableName);
            $columns_statement = $pdo->query($query);
            $columns_info = $columns_statement->fetchAll();

            generate_table_helper_class($tableName, $columns_info);
        }
    }

    /**
     * Generate PHP response type classes from API routes that have type information.
     *
     * @codeCoverageIgnore
     */
    public function generatePhpResponseTypes(): void
    {
        require_once __DIR__ . "/../../../api/src/api_routes.php";
        
        $routes = getAllApiRoutes();
        $output_directory = __DIR__ . "/../../Bristolian/Response/Typed";
        
        // Ensure the directory exists
        if (!is_dir($output_directory)) {
            mkdir($output_directory, 0755, true);
        }
        
        foreach ($routes as $route) {
            // Route format: [path, method, controller, type_info, setup_callable]
            if (count($route) < 4 || $route[3] === null) {
                continue; // Skip routes without type information
            }
            
            $path = $route[0];
            $method = $route[1];
            $type_info = $route[3];
            
            if (!is_array($type_info) || count($type_info) === 0) {
                continue; // Skip if type_info is not a valid array
            }
            
            // Generate class name from route path
            $className = $this->generateClassNameFromRoute($path, $method);
            $namespace = "Bristolian\\Response\\Typed";
            
            // Generate the PHP class content

            echo "$className\n";

            if ($className === "GetRoomsLinksResponse") {
                echo "here";
            }

            $content = $this->generateResponseClassContent($className, $namespace, $type_info, $path, $method);



            // Write the file
            $output_filename = $output_directory . "/" . $className . ".php";

//            echo "writing $output_filename";

            $result = file_put_contents($output_filename, $content);
            
            if ($result === false) {
                throw new BristolianException("Failed to write response type file: $output_filename");
            }
        }
    }
    
    /**
     * Convert a route path and method to a PHP class name.
     *
     * Example: '/api/rooms/{room_id:.*}/files' + 'GET' -> 'GetRoomsFilesResponse'
     * Example: '/api/bristol_stairs' + 'GET' -> 'GetBristolStairsResponse'
     */
    private function generateClassNameFromRoute(string $path, string $method): string
    {
        // Remove leading /api if present
        $path = preg_replace('#^/api#', '', $path);
        
        // Remove path parameters like {room_id:.*}
        $path = preg_replace('#\{[^}]+\}#', '', $path);
        
        // Remove leading/trailing slashes
        $path = trim($path, '/');
        
        // Split by slashes and capitalize each segment
        $parts = explode('/', $path);
        $className = '';
        
        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }
            // Convert snake_case to CamelCase, then capitalize first letter
            $part = $this->snakeCaseToCamelCase($part);
            $className .= ucfirst($part);
        }
        
        // Add method prefix for disambiguation (e.g., Get, Post, Put, Delete)
        $methodPrefix = ucfirst(strtolower($method));
        $className = $methodPrefix . $className;
        
        // Add Response suffix
        $className .= 'Response';
        
        // Ensure it's a valid PHP class name (remove any remaining invalid characters)
        $className = preg_replace('#[^a-zA-Z0-9]#', '', $className);
        
        // If empty, use a default
        if (empty($className)) {
            $className = 'ApiResponse';
        }
        
        return $className;
    }
    
    /**
     * Convert snake_case to CamelCase.
     *
     * Example: 'bristol_stairs' -> 'bristolStairs'
     * Example: 'processor_run_records' -> 'processorRunRecords'
     */
    private function snakeCaseToCamelCase(string $str): string
    {
        return preg_replace_callback(
            '/_([a-z])/',
            function ($matches) {
                return strtoupper($matches[1]);
            },
            $str
        );
    }
    
    /**
     * Generate PHP class content for a response type.
     *
     * @param string $className The generated class name
     * @param string $namespace The namespace for the class
     * @param array $type_info Array of [name, class, is_array] tuples
     * @param string $path The route path
     * @param string $method The HTTP method
     */
    private function generateResponseClassContent(string $className, string $namespace, array $type_info, string $path, string $method): string
    {
        $content = "<?php\n\n";
        $content .= "// Auto-generated file do not edit\n\n";
        $content .= "// generated with 'php cli.php generate:php_response_types'\n";
        $content .= "//\n";

        $content .= "//\n";
        $content .= "// Generator: src/Bristolian/CliController/GenerateFiles.php :: generateResponseClassContent()\n";
        $content .= "// invoked from GenerateFiles::generatePhpResponseTypes)\n";

        $content .= "// The information used to generate this file comes from:\n";
        $content .= "// api/src/api_routes.php - specifically from routes that have type information\n";
        $content .= "//\n";
        $content .= "// In api_routes.php, each route is an array with the format:\n";
        $content .= "// [path, method, controller, type_info, setup_callable]\n";
        $content .= "//\n";
        $content .= "// The type_info (at index 3) is an array of field definitions:\n";
        $content .= "// [\n";
        $content .= "//     ['field_name', ClassName::class, is_array],\n";
        $content .= "//     ...\n";
        $content .= "// ]\n";
        $content .= "//\n";
        $content .= "// Each field definition is: [field_name, fully_qualified_class_name, is_array]\n";
        $content .= "// - field_name: the name of the field in the JSON response\n";
        $content .= "// - fully_qualified_class_name: the model class (usually from Bristolian\\Model\\Generated)\n";
        $content .= "// - is_array: true for arrays of objects, false for single objects\n";
        $content .= "//\n";
        $content .= "// This response class is used by the route:\n";
        $content .= "//   Path: $path\n";
        $content .= "//   Method: $method\n";
        $content .= "//\n";
        $content .= "// The actual field definitions for this route are:\n";
        
        // Build the actual field definitions comment
        foreach ($type_info as $field_info) {
            if (!is_array($field_info) || count($field_info) < 2) {
                continue;
            }
            
            $field_name = $field_info[0];
            $field_type = $field_info[1];
            $is_array = isset($field_info[2]) && $field_info[2] === true;
            
            if (is_string($field_type)) {
                $is_array_str = $is_array ? 'true' : 'false';
                $content .= "//   ['$field_name', \\$field_type::class, $is_array_str]\n";
            }
        }

        $content .= "namespace $namespace;\n\n";
        
        // Collect all imports
        $imports = [
            'Bristolian\\Exception\\DataEncodingException',
            'SlimDispatcher\\Response\\StubResponse',
        ];
        
        $constructorParams = [];
        $dataFields = [];
        
        foreach ($type_info as $field_info) {
            if (!is_array($field_info) || count($field_info) < 2) {
                continue;
            }
            
            $field_name = $field_info[0];
            $field_type = $field_info[1];
            $is_array = isset($field_info[2]) && $field_info[2] === true;
            $scalar_type = isset($field_info[3]) ? $field_info[3] : null;
            $is_scalar = false;
            if ($field_type === null && $scalar_type === 'bool') {
                $is_scalar = true;
            }
            
            if ($is_scalar) {
                $constructorParams[] = [
                    'name' => $field_name,
                    'type' => 'bool',
                    'doc_type' => 'bool',
                    'short_name' => 'bool',
                    'is_array' => false,
                    'scalar' => true,
                ];
                $dataFields[] = [
                    'name' => $field_name,
                    'value' => '$' . $field_name,
                    'scalar' => true,
                ];
                continue;
            }
            
            // Add import for the type
            if (is_string($field_type)) {
                if (class_exists($field_type) === false) {
                    throw new BristolianException("Class '$field_type' does not exist, cannot generate code for $className response");
                }

                $imports[] = $field_type;
                $rc = new \ReflectionClass($field_type);
                $short_name = $rc->getShortName();
                
                // Build constructor parameter type
                if ($is_array) {
                    $param_type = "array";
                    $doc_type = $short_name . "[]";
                }
                else {
                    $param_type = $short_name;
                    $doc_type = $short_name;
                }
                
                $constructorParams[] = [
                    'name' => $field_name,
                    'type' => $param_type,
                    'doc_type' => $doc_type,
                    'short_name' => $short_name,
                    'is_array' => $is_array,
                ];
                
                $dataFields[] = [
                    'name' => $field_name,
                    'value' => '$' . $field_name,
                ];
            }
        }
        
        // Remove duplicates from imports
        $imports = array_unique($imports);
        sort($imports);
        
        // Add imports
        foreach ($imports as $import) {
            $content .= "use $import;\n";
        }
        
        $content .= "\n";
        $content .= "/**\n";
        $content .= " * Auto-generated class - do not edit manually\n";
        $content .= " * No need to test this class as it is auto-generated\n";
        $content .= " * @codeCoverageIgnore\n";
        $content .= " */\n";
        $content .= "class $className implements StubResponse\n";
        $content .= "{\n";
        $content .= "    private string \$body;\n\n";
//        $content .= "    private \$headers = [];\n\n";
//        $content .= "    private \$status;\n\n";
        
        // Constructor
        $param_doc = [];
        $param_list = [];
        foreach ($constructorParams as $param) {
            $param_doc[] = "     * @param {$param['doc_type']} \${$param['name']}";
            if ($param['is_array']) {
                $param_list[] = "array \${$param['name']}";
            }
            elseif (!empty($param['scalar'])) {
                $param_list[] = "bool \${$param['name']} = false";
            }
            else {
                $param_list[] = "{$param['short_name']} \${$param['name']}";
            }
        }
        
        $content .= "    /**\n";
        $content .= implode("\n", $param_doc) . "\n";
        $content .= "     */\n";
        $content .= "    public function __construct(" . implode(", ", $param_list) . ")\n";
        $content .= "    {\n";
        
        // Build the data array
        // Convert each field to value
        $content .= "        \$converted_data = [];\n";
        foreach ($dataFields as $field) {
            if (!empty($field['scalar'])) {
                $content .= "        \$converted_data['{$field['name']}'] = {$field['value']};\n";
            }
            else {
                $content .= "        [\$error, \$converted_{$field['name']}] = \\convertToValue({$field['value']});\n";
                $content .= "        if (\$error !== null) {\n";
                $content .= "            throw new DataEncodingException(\"Could not convert {$field['name']} to a value. \", \$error);\n";
                $content .= "        }\n";
                $content .= "        \$converted_data['{$field['name']}'] = \$converted_{$field['name']};\n";
            }
        }
        
        $content .= "\n";
        $content .= "        \$response_ok = [\n";
        $content .= "            'result' => 'success',\n";
        $content .= "            'data' => \$converted_data\n";
        $content .= "        ];\n\n";
        $content .= "        \$this->body = json_encode(\$response_ok, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);\n";
        $content .= "    }\n\n";
        
        // getStatus method
        $content .= "    public function getStatus(): int\n";
        $content .= "    {\n";
        $content .= "        return 200;\n";
        $content .= "    }\n\n";
        
        // getHeaders method


        $content .= "    /**\n";
        $content .= "     * @return array<string, string>\n";
        $content .= "     */\n";

        $content .= "    public function getHeaders(): array\n";
        $content .= "    {\n";
        $content .= "        return [\n";
        $content .= "            'Content-Type' => 'application/json'\n";
        $content .= "        ];\n";
        $content .= "    }\n\n";
        
        // getBody method
        $content .= "    public function getBody(): string\n";
        $content .= "    {\n";
        $content .= "        return \$this->body;\n";
        $content .= "    }\n";
        
        $content .= "}\n";
        
        return $content;
    }

    /**
     * Generate TypeScript file with API route endpoints and response types.
     *
     * @codeCoverageIgnore
     */
    public function generateTypeScriptApiRoutes(): void
    {
        require_once __DIR__ . "/../../../api/src/api_routes.php";
        
        $routes = getAllApiRoutes();
        $output_filename = __DIR__ . "/../../../app/public/tsx/generated/api_routes.tsx";
        
        $content = "// This is an auto-generated file\n";
        $content .= "// DO NOT EDIT\n\n";
        $content .= "// You'll need to bounce the docker boxes to regenerate.\n";
        $content .= "//\n";
        $content .= "// or run 'php cli.php generate:typescript_api_routes'\n";
        $content .= "// Code for generating this file is in \\Bristolian\\CliController\\GenerateFiles::generateTypeScriptApiRoutes\n\n";
        
        // Collect all unique model types that need to be imported
        $modelTypes = [];
        $routeData = [];
        
        foreach ($routes as $route) {
            // Route format: [path, method, controller, type_info, setup_callable]
            if (count($route) < 4 || $route[3] === null) {
                continue; // Skip routes without type information
            }
            
            $path = $route[0];
            $method = $route[1];
            $type_info = $route[3];
            
            if (!is_array($type_info) || count($type_info) === 0) {
                continue; // Skip if type_info is not a valid array
            }
            
            // Generate response type name
            $responseTypeName = $this->generateTypeScriptResponseTypeName($path, $method);
            
            // Collect model types for imports
            foreach ($type_info as $field_info) {
                if (!is_array($field_info) || count($field_info) < 2) {
                    continue;
                }
                
                $field_type = $field_info[1];
                if (is_string($field_type) && class_exists($field_type)) {
                    $modelTypes[$field_type] = true;
                }
            }
            
            // Extract parameters from path
            $params = $this->extractPathParameters($path);
            
            // Build nested path structure
            $pathStructure = $this->buildPathStructure($path);
            
            $routeData[] = [
                'path' => $path,
                'method' => $method,
                'response_type' => $responseTypeName,
                'type_info' => $type_info,
                'params' => $params,
                'path_structure' => $pathStructure,
            ];
        }
        
        // Check if any response types use DateToString (i.e., have model types with date fields)
        $needsDateToString = false;
        foreach ($routeData as $route) {
            foreach ($route['type_info'] as $field_info) {
                if (!is_array($field_info) || count($field_info) < 2) {
                    continue;
                }
                $field_type = $field_info[1];
                if (is_string($field_type) && class_exists($field_type)) {
                    [$interfaceContent, $dateFields] = generateInterfaceForClass($field_type);
                    if (count($dateFields) > 0) {
                        $needsDateToString = true;
                        break 2; // Break out of both loops
                    }
                }
            }
        }
        
        // Add imports for model types
        if (count($modelTypes) > 0 || $needsDateToString) {
            $content .= "import { DateToString, convertDatesFromStrings } from '../functions';\n";
        }
        
        if (count($modelTypes) > 0) {
            $content .= "import type { ";
            
            $importNames = [];
            foreach (array_keys($modelTypes) as $modelType) {
                $rc = new \ReflectionClass($modelType);
                $importNames[] = $rc->getShortName();
            }
            
            sort($importNames);
            $content .= implode(", ", $importNames);
            $content .= " } from './types';\n\n";
        }
        else if ($needsDateToString) {
            $content .= "\n";
        }
        
        // Add helper function for API calls
        $content .= "// Helper function for API calls\n";
        $content .= "function apiCall<T>(endpoint: string): Promise<T> {\n";
        $content .= "  return fetch(endpoint)\n";
        $content .= "    .then((response: Response) => {\n";
        $content .= "      if (response.status !== 200) {\n";
        $content .= "        throw new Error('Server failed to return an OK response.');\n";
        $content .= "      }\n";
        $content .= "      return response;\n";
        $content .= "    })\n";
        $content .= "    .then((response: Response) => response.json());\n";
        $content .= "}\n\n";
        
        // Generate Laravel-style API route helpers
        $content .= "// Laravel-style API route helpers\n";
        $content .= "export const api = {\n";
        $content .= $this->generateLaravelStyleRoutes($routeData);
        $content .= "} as const;\n";
        
        $content .= "\n";
        
        // Generate response type interfaces
        $content .= "// API Response Types\n";
        foreach ($routeData as $routeInfo) {
            $content .= $this->generateTypeScriptResponseInterface(
                $routeInfo['response_type'],
                $routeInfo['type_info']
            );
            $content .= "\n";
        }
        
        $result = file_put_contents($output_filename, $content);
        if ($result === false) {
            throw new BristolianException("Failed to write TypeScript API routes file: $output_filename");
        }
    }
    
    /**
     * Generate a TypeScript constant name from route path and method.
     *
     * Example: '/api/rooms/{room_id:.*}/files' + 'GET' -> 'API_ROOMS_FILES_GET'
     */
    private function generateEndpointConstantName(string $path, string $method): string
    {
        // Remove leading /api if present
        $path = preg_replace('#^/api#', '', $path);
        
        // Remove path parameters like {room_id:.*}
        $path = preg_replace('#\{[^}]+\}#', '', $path);
        
        // Remove leading/trailing slashes
        $path = trim($path, '/');
        
        // Replace slashes and special chars with underscores
        $constant = strtoupper(str_replace(['/', '-'], '_', $path));
        
        // Add method suffix
        $constant .= '_' . strtoupper($method);
        
        // Ensure it starts with API_
        if (!str_starts_with($constant, 'API_')) {
            $constant = 'API_' . $constant;
        }
        
        // Clean up multiple underscores
        $constant = preg_replace('#_+#', '_', $constant);
        
        // Remove trailing underscores
        $constant = rtrim($constant, '_');
        
        return $constant;
    }
    
    /**
     * Generate a TypeScript type name from route path and method.
     *
     * Example: '/api/rooms/{room_id:.*}/files' + 'GET' -> 'GetRoomsFilesResponse'
     * Example: '/api/bristol_stairs' + 'GET' -> 'GetBristolStairsResponse'
     */
    private function generateTypeScriptResponseTypeName(string $path, string $method): string
    {
        // Remove leading /api if present
        $path = preg_replace('#^/api#', '', $path);
        
        // Remove path parameters like {room_id:.*}
        $path = preg_replace('#\{[^}]+\}#', '', $path);
        
        // Remove leading/trailing slashes
        $path = trim($path, '/');
        
        // Split by slashes and capitalize each segment
        $parts = explode('/', $path);
        $typeName = '';
        
        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }
            // Convert snake_case to CamelCase, then capitalize first letter
            $part = $this->snakeCaseToCamelCase($part);
            $typeName .= ucfirst($part);
        }
        
        // Add method prefix
        $methodPrefix = ucfirst(strtolower($method));
        $typeName = $methodPrefix . $typeName;
        
        // Add Response suffix
        $typeName .= 'Response';
        
        // Ensure it's a valid TypeScript type name (remove any remaining invalid characters)
        $typeName = preg_replace('#[^a-zA-Z0-9]#', '', $typeName);
        
        // If empty, use a default
        if (empty($typeName)) {
            $typeName = 'ApiResponse';
        }
        
        return $typeName;
    }
    
    /**
     * Generate TypeScript interface for a response type.
     */
    private function generateTypeScriptResponseInterface(string $typeName, array $type_info): string
    {
        $content = "export interface {$typeName} {\n";
        $content .= "    result: 'success';\n";
        $content .= "    data: {\n";
        
        foreach ($type_info as $field_info) {
            if (!is_array($field_info) || count($field_info) < 2) {
                continue;
            }
            
            $field_name = $field_info[0];
            $field_type = $field_info[1];
            $is_array = isset($field_info[2]) && $field_info[2] === true;
            $scalar_type = isset($field_info[3]) ? $field_info[3] : null;
            $is_scalar = $field_type === null && $scalar_type === 'bool';
            
            if ($is_scalar) {
                $content .= "        {$field_name}?: boolean;\n";
                continue;
            }
            
            if (is_string($field_type) && class_exists($field_type)) {
                $rc = new \ReflectionClass($field_type);
                $short_name = $rc->getShortName();
                
                // Check if this model type has date fields by generating its interface
                // and checking if date fields are returned
                [$interfaceContent, $dateFields] = generateInterfaceForClass($field_type);
                $hasDateFields = count($dateFields) > 0;
                
                // Reference the type from types.tsx (which should already be generated)
                // If it's an array and has date fields, use DateToString<T>[] since API returns dates as strings
                if ($is_array) {
                    if ($hasDateFields) {
                        $ts_type = "DateToString<{$short_name}>[]";
                    }
                    else {
                        $ts_type = "{$short_name}[]";
                    }
                }
                else {
                    if ($hasDateFields) {
                        $ts_type = "DateToString<{$short_name}>";
                    }
                    else {
                        $ts_type = $short_name;
                    }
                }
                
                $content .= "        {$field_name}: {$ts_type};\n";
            }
            else {
                // Fallback for unknown types
                $ts_type = $is_array ? "any[]" : "any";
                $content .= "        {$field_name}: {$ts_type};\n";
            }
        }
        
        $content .= "    };\n";
        $content .= "}\n";
        
        return $content;
    }
    
    /**
     * Extract parameter names from a route path.
     * Example: '/api/rooms/{room_id:.*}/files' -> ['room_id']
     */
    private function extractPathParameters(string $path): array
    {
        $params = [];
        if (preg_match_all('#\{([^:}]+)(?::[^}]*)?\}#', $path, $matches)) {
            $params = $matches[1];
        }
        return $params;
    }
    
    /**
     * Build a nested path structure from a route path.
     * Example: '/api/rooms/{room_id:.*}/files' -> ['rooms', 'files']
     */
    private function buildPathStructure(string $path): array
    {
        // Remove leading /api if present
        $path = preg_replace('#^/api#', '', $path);
        
        // Remove path parameters like {room_id:.*}
        $path = preg_replace('#\{[^}]+\}#', '', $path);
        
        // Remove leading/trailing slashes
        $path = trim($path, '/');
        
        // Split by slashes
        $parts = explode('/', $path);
        
        // Filter out empty parts
        return array_filter($parts, function ($part) {
            return !empty($part);
        });
    }
    
    /**
     * Generate Laravel-style route helpers as a nested object structure.
     */
    private function generateLaravelStyleRoutes(array $routeData): string
    {
        // Organize routes into a nested structure
        $routesTree = [];
        
        foreach ($routeData as $route) {
            $structure = $route['path_structure'];
            $params = $route['params'];
            $path = $route['path'];
            $method = strtolower($route['method']);
            
            // Build nested structure
            $current = &$routesTree;
            $pathParts = array_values($structure);
            
            // Navigate/create nested structure
            for ($i = 0; $i < count($pathParts) - 1; $i++) {
                $part = $this->camelCase($pathParts[$i]);
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
            
            // Add the final route function
            $finalKey = $this->camelCase($pathParts[count($pathParts) - 1] ?? 'index');
            
            // Generate function parameters
            $paramList = [];
            $paramTypes = [];
            foreach ($params as $param) {
                $paramList[] = $param . ': string';
                $paramTypes[] = $param;
            }
            
            // Generate function body - build URL with template literals
            $urlParts = $this->buildUrlTemplate($path, $params);
            
            // Get response type name
            $responseTypeName = $route['response_type'];
            
            if (count($params) === 0) {
                // No parameters - simple function
                $current[$finalKey] = [
                    'type' => 'function',
                    'params' => [],
                    'url' => $path,
                    'response_type' => $responseTypeName,
                ];
            }
            else {
                // Has parameters - function with params
                $current[$finalKey] = [
                    'type' => 'function',
                    'params' => $paramTypes,
                    'url' => $urlParts,
                    'response_type' => $responseTypeName,
                ];
            }
        }
        
        // Generate TypeScript code from the tree
        return $this->generateTypeScriptFromTree($routesTree, 0);
    }
    
    /**
     * Convert a string to camelCase.
     */
    private function camelCase(string $str): string
    {
        // Handle snake_case and kebab-case
        $str = str_replace(['-', '_'], ' ', $str);
        $parts = explode(' ', $str);
        $result = lcfirst($parts[0]);
        for ($i = 1; $i < count($parts); $i++) {
            $result .= ucfirst($parts[$i]);
        }
        return $result;
    }
    
    /**
     * Build URL template parts for generating the function body.
     */
    private function buildUrlTemplate(string $path, array $params): array
    {
        // Split path into parts, replacing parameters with placeholders
        $parts = [];
        $currentPos = 0;
        
        // Find all parameter positions
        $paramPositions = [];
        foreach ($params as $param) {
            if (preg_match('#\{' . preg_quote($param, '#') . '(?::[^}]*)?\}#', $path, $matches, PREG_OFFSET_CAPTURE)) {
                $paramPositions[] = [
                    'param' => $param,
                    'start' => $matches[0][1],
                    'end' => $matches[0][1] + strlen($matches[0][0]),
                ];
            }
        }
        
        // Sort by position
        usort($paramPositions, function ($a, $b) {
            return $a['start'] <=> $b['start'];
        });
        
        // Build parts
        $lastPos = 0;
        foreach ($paramPositions as $pos) {
            // Add literal part before parameter
            if ($pos['start'] > $lastPos) {
                $literal = substr($path, $lastPos, $pos['start'] - $lastPos);
                if (!empty($literal)) {
                    $parts[] = ['type' => 'literal', 'value' => $literal];
                }
            }
            
            // Add parameter
            $parts[] = ['type' => 'param', 'value' => $pos['param']];
            
            $lastPos = $pos['end'];
        }
        
        // Add remaining literal part
        if ($lastPos < strlen($path)) {
            $literal = substr($path, $lastPos);
            if (!empty($literal)) {
                $parts[] = ['type' => 'literal', 'value' => $literal];
            }
        }
        
        return $parts;
    }
    
    /**
     * Generate TypeScript code from the routes tree structure.
     */
    private function generateTypeScriptFromTree(array $tree, int $indent): string
    {
        $indentStr = str_repeat('  ', $indent);
        $content = "";
        
        $keys = array_keys($tree);
        sort($keys);
        
        foreach ($keys as $key) {
            $value = $tree[$key];
            
            if (isset($value['type']) && $value['type'] === 'function') {
                // Generate function that returns Promise with fetch logic
                $params = $value['params'];
                $urlParts = $value['url'];
                $responseType = $value['response_type'];
                
                if (count($params) === 0) {
                    // No parameters
                    $content .= "{$indentStr}  {$key}: (): Promise<{$responseType}> => {\n";
                    $content .= "{$indentStr}    return apiCall<{$responseType}>(`{$urlParts}`);\n";
                    $content .= "{$indentStr}  },\n";
                }
                else {
                    // Has parameters
                    $paramList = implode(', ', array_map(function ($p) {
                        return "{$p}: string";
                    }, $params));
                    $content .= "{$indentStr}  {$key}: ({$paramList}): Promise<{$responseType}> => {\n";
                    
                    // Build template literal for URL
                    $urlTemplate = "{$indentStr}    const endpoint = `";
                    foreach ($urlParts as $part) {
                        if ($part['type'] === 'literal') {
                            $urlTemplate .= $part['value'];
                        }
                        else {
                            $urlTemplate .= '${' . $part['value'] . '}';
                        }
                    }
                    $urlTemplate .= "`;\n";
                    $content .= $urlTemplate;
                    $content .= "{$indentStr}    return apiCall<{$responseType}>(endpoint);\n";
                    $content .= "{$indentStr}  },\n";
                }
            }
            else {
                // Nested object
                $content .= "{$indentStr}  {$key}: {\n";
                $content .= $this->generateTypeScriptFromTree($value, $indent + 1);
                $content .= "{$indentStr}  },\n";
            }
        }
        
        return $content;
    }

    /**
     * Generate model classes from database schema.
     *
     * @codeCoverageIgnore
     */
    public function generateModelClasses(
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
SELECT *
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = '$schema' AND TABLE_NAME = '%s'
  ORDER BY ORDINAL_POSITION;
SQL;

        $output_directory = __DIR__ . "/../../Bristolian/Model/Generated";
        
        // Ensure the directory exists
        if (!is_dir($output_directory)) {
            mkdir($output_directory, 0755, true);
        }

        foreach ($rows as $row) {
            $tableName = $row['TABLE_NAME'];
            $query = sprintf($column_query, $tableName);
            $columns_statement = $pdo->query($query);
            $columns_info = $columns_statement->fetchAll();

            generate_model_class($tableName, $columns_info, $output_directory);
        }
    }
}
