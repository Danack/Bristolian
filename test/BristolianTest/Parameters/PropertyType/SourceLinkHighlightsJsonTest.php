<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\SourceLinkHighlightsJson;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\SourceLinkHighlightsJson
 */
class SourceLinkHighlightsJsonTest extends BaseTestCase
{
    public function testWorksWithValidJson()
    {
        $highlights_json = '{"highlights": [{"page": 1, "left": 100, "top": 200, "right": 300, "bottom": 400}]}';

        $params = [
            'highlights_json' => $highlights_json,
        ];

        // Test through a simple DataType class that uses this property type
        $this->testPropertyTypeThroughDataType($params, $highlights_json);
    }

    public function testWorksWithMinimumLength()
    {
        $highlights_json = '{"highlights": []}'; // Exactly 16 characters

        $params = [
            'highlights_json' => $highlights_json,
        ];

        $this->testPropertyTypeThroughDataType($params, $highlights_json);
    }

    public function testWorksWithMaximumLength()
    {
        // Create a JSON string that's close to but not exceeding the maximum length (16KB)
        $highlights = [];
        for ($i = 0; $i < 200; $i++) { // Reduced number of items
            $highlights[] = [
                'page' => $i,
                'left' => 100,
                'top' => 200,
                'right' => 300,
                'bottom' => 400,
                'text' => 'Highlight text'
            ];
        }
        $highlights_json = json_encode(['highlights' => $highlights]);
        
        // Ensure we don't exceed the maximum length
        if (strlen($highlights_json) > 16384) {
            $highlights_json = str_repeat('a', 16384); // Exactly at the limit
        }

        $params = [
            'highlights_json' => $highlights_json,
        ];

        $this->testPropertyTypeThroughDataType($params, $highlights_json);
    }

    public function testFailsWithTooShort()
    {
        try {
            $highlights_json = '{"h": []}'; // Too short

            $params = [
                'highlights_json' => $highlights_json,
            ];

            $this->testPropertyTypeThroughDataType($params, $highlights_json);
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/highlights_json' => Messages::STRING_TOO_SHORT]
            );
        }
    }

    public function testFailsWithTooLong()
    {
        try {
            // Create a string that's longer than the maximum (16KB)
            $highlights_json = str_repeat('a', 17 * 1024); // 17KB

            $params = [
                'highlights_json' => $highlights_json,
            ];

            $this->testPropertyTypeThroughDataType($params, $highlights_json);
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/highlights_json' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function testFailsWithInvalidDataType()
    {
        try {
            $params = [
                'highlights_json' => 123, // Not a string
            ];

            $this->testPropertyTypeThroughDataType($params, 123);
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/highlights_json' => Messages::STRING_EXPECTED]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $params = [
                'highlights_json' => null,
            ];

            $this->testPropertyTypeThroughDataType($params, null);
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/highlights_json' => Messages::STRING_REQUIRED_FOUND_NULL]
            );
        }
    }

    public function testFailsWithMissingValue()
    {
        try {
            $params = [];

            $this->testPropertyTypeThroughDataType($params, null);
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/highlights_json' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new SourceLinkHighlightsJson('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new SourceLinkHighlightsJson('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }

    /**
     * Helper method to test the property type through a DataType class
     * Since SourceLinkHighlightsJson is a property type, we test it through SourceLinkParam
     */
    private function testPropertyTypeThroughDataType(array $params, $expectedValue)
    {
        // Add required parameters for SourceLinkParam
        $fullParams = array_merge([
            'title' => 'This is a longer source title that meets the minimum length requirement',
            'text' => 'Source text content',
        ], $params);
        
        $sourceLinkParam = \Bristolian\Parameters\SourceLinkParam::createFromVarMap(new ArrayVarMap($fullParams));
        
        if ($expectedValue !== null) {
            $this->assertSame($expectedValue, $sourceLinkParam->highlights_json);
        }
    }
}
