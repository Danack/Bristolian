<?php

namespace Bristolian\CliController;

// Output the extracted information
/**
 * @param $comment
 * @return string[]
 */
function formatComment($comment)
{
    // Clean up and format the comment
    $lines = explode("\n", $comment);

    $lines_formatted = [];

    foreach ($lines as $line) {
        if (str_starts_with($line, '/*') === true) {
            continue;
        }

        if (str_starts_with($line, '*/') === true) {
            continue;
        }

        $line = trim($line, ' */');
        $line = trim($line, ' */');
        $line = trim($line, ' */');

        if (str_starts_with($line, '@param') === true) {
            $line = substr($line, strlen('@param'));
        }
        $line = trim($line, ' */');


        $lines_formatted[] = $line;
    }
    return $lines_formatted;
}

class CodeGen
{
    public function analyze_datatypes(): void
    {
        $filename = __DIR__ . "/../../../vendor/danack/datatype/src/DataType/ExtractRule/GetIntOrDefault.php";

        // Load the PHP file

        if (!file_exists($filename)) {
            die("File not found: $filename\n");
        }

        // Use Reflection to extract comments
        try {
            // Get class reflection
            $className = 'DataType\\ExtractRule\\GetIntOrDefault'; // Fully qualified class name
            $reflectionClass = new \ReflectionClass($className);

            // Get class comment
            $classComment = $reflectionClass->getDocComment() ?: 'No class comment found.';

            // Get constructor comment
            $constructor = $reflectionClass->getConstructor();
            $constructorComment = $constructor ? ($constructor->getDocComment() ?: 'No constructor comment found.') : 'No constructor found.';

            echo "Class Comment:\n";
            echo implode("\n", formatComment($classComment)) . "\n\n";

            echo "Constructor Comment:\n";
            echo implode("\n", formatComment($constructorComment)) . "\n";
        } catch (\ReflectionException $e) {
            echo "Reflection error: " . $e->getMessage() . "\n";
        }
    }
}
