<?php

namespace Bristolian\CliController;

use Bristolian\Exception\BristolianException;
use OpenApi\OpenApiGenerator;
use Seld\JsonLint\JsonParser;
use VarMap\VarMap;

/**
 * CLI controller for OpenAPI/Swagger operations.
 *
 * @codeCoverageIgnore
 */
class OpenApi
{
    
    /**
     * Generate OpenAPI spec from PHP generator
     */
    public function generate(): void
    {
        $generator = new OpenApiGenerator();
        $apiData = $generator->getApiData();
        
        // Convert to JSON with pretty printing
        $jsonContent = json_encode($apiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if ($jsonContent === false) {
            throw new BristolianException("Failed to encode OpenAPI data to JSON");
        }

        echo $jsonContent . "\n";
    }
    
    /**
     * Validate an OpenAPI JSON file
     */
    public function validate(VarMap $varMap): void
    {
        $filePath = $varMap->get('file_path');
        $errors = $this->validateOpenApiFile($filePath);
        $this->printValidationResults($filePath, $errors);
        
        if (!empty($errors)) {
            exit(1);
        }
    }
    
    /**
     * Generate and validate OpenAPI spec
     */
    public function generateAndValidate(): void
    {
        // Generate the spec
        $generator = new OpenApiGenerator();
        $apiData = $generator->getApiData();
        $jsonContent = json_encode($apiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        // Write to temporary file
        $tempFile = sys_get_temp_dir() . '/bristolian_openapi_' . uniqid() . '.json';
        file_put_contents($tempFile, $jsonContent);
        
        try {
            echo "Generated OpenAPI specification to temporary file: $tempFile\n\n";
            
            // Validate the generated file
            $errors = $this->validateOpenApiFile($tempFile);
            $this->printValidationResults($tempFile, $errors);
            
            if (empty($errors)) {
                echo "\nGenerated JSON:\n";
                echo str_repeat('-', 50) . "\n";
                echo $jsonContent . "\n";
            }
        } finally {
            // Clean up temp file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
        
        if (!empty($errors)) {
            exit(1);
        }
    }
    
    /**
     * Validate OpenAPI file and return errors
     * @return array<string>
     */
    private function validateOpenApiFile(string $filePath): array
    {
        $errors = [];
        
        // 1. Check if file exists
        if (!file_exists($filePath)) {
            $errors[] = "File does not exist: $filePath";
            return $errors;
        }
        
        // 2. Validate JSON syntax
        try {
            $jsonContent = file_get_contents($filePath);
            $jsonParser = new JsonParser();
            $data = $jsonParser->parse($jsonContent);
        } catch (\Exception $e) {
            $errors[] = "JSON syntax error: " . $e->getMessage();
            return $errors;
        }
        
        // 3. Check if it's an OpenAPI document
        if (!isset($data->openapi) && !isset($data->swagger)) {
            $errors[] = "Not an OpenAPI document: missing 'openapi' or 'swagger' field";
        }
        
        // 4. Basic OpenAPI structure validation
        $requiredFields = ['info', 'paths'];
        foreach ($requiredFields as $field) {
            if (!isset($data->$field)) {
                $errors[] = "Missing required field: $field";
            }
        }
        
        // 5. Validate info section
        if (isset($data->info)) {
            $infoRequired = ['title', 'version'];
            foreach ($infoRequired as $field) {
                if (!isset($data->info->$field)) {
                    $errors[] = "Missing required info field: $field";
                }
            }
        }
        
        // 6. Validate paths section
        if (isset($data->paths)) {
            $this->validatePaths($data->paths, $errors);
        }
        
        // 7. Validate OpenAPI version
        if (isset($data->openapi)) {
            $version = $data->openapi;
            if (!preg_match('/^3\.\d+\.\d+$/', $version)) {
                $errors[] = "Invalid OpenAPI version format: $version";
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate paths section of OpenAPI spec
     * @param array<string> $errors
     */
    private function validatePaths(mixed $paths, array &$errors): void
    {
        foreach ($paths as $path => $pathItem) {
            if (!is_string($path) || !str_starts_with($path, '/')) {
                $errors[] = "Invalid path format: $path";
            }
            
            $httpMethods = ['get', 'post', 'put', 'delete', 'patch', 'head', 'options', 'trace'];
            foreach ($pathItem as $method => $operation) {
                if (!in_array(strtolower($method), $httpMethods)) {
                    $errors[] = "Invalid HTTP method '$method' in path '$path'";
                }
                
                // Check operation has required fields
                if (!isset($operation->responses)) {
                    $errors[] = "Operation '$method $path' missing required 'responses' field";
                }
            }
        }
    }
    
    /**
     * Print validation results
     * @param array<string> $errors
     */
    private function printValidationResults(string $filePath, array $errors): void
    {
        echo "Validating OpenAPI file: $filePath\n";
        echo str_repeat('=', 50) . "\n";
        
        if (empty($errors)) {
            echo "✅ VALIDATION PASSED\n";
            echo "The OpenAPI file is valid!\n";
        }
        else {
            echo "❌ VALIDATION FAILED\n";
            echo "Found " . count($errors) . " error(s):\n\n";
            foreach ($errors as $i => $error) {
                echo ($i + 1) . ". $error\n";
            }
        }
    }
}
