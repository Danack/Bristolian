<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\SourceLinkHighlightsJson;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class SourceLinkHighlightsJsonTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        $validJson = '{"highlights": [{"page": 1, "left": 100, "top": 200, "right": 300, "bottom": 400}]}';
        yield 'valid json' => [['highlights_json' => $validJson], $validJson];
        yield 'min length' => [['highlights_json' => '{"highlights": []}'], '{"highlights": []}'];
        $highlights = [];
        for ($i = 0; $i < 200; $i++) {
            $highlights[] = [
                'page' => $i,
                'left' => 100,
                'top' => 200,
                'right' => 300,
                'bottom' => 400,
                'text' => 'Highlight text'
            ];
        }
        $maxJson = json_encode_safe(['highlights' => $highlights]);
        if (strlen($maxJson) > 16384) {
            $maxJson = str_repeat('a', 16384);
        }
        yield 'max length' => [['highlights_json' => $maxJson], $maxJson];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkHighlightsJson
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $params
     */
    public function test_parses_valid_input_to_expected_output(array $params, string $expectedValue): void
    {
        $fullParams = $this->fullSourceLinkParams($params);
        $sourceLinkParam = \Bristolian\Parameters\SourceLinkParam::createFromVarMap(new ArrayVarMap($fullParams));
        $this->assertSame($expectedValue, $sourceLinkParam->highlights_json);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'too short' => [['highlights_json' => '{"h": []}'], Messages::STRING_TOO_SHORT];
        yield 'too long' => [['highlights_json' => str_repeat('a', 17 * 1024)], Messages::STRING_TOO_LONG];
        yield 'invalid type' => [['highlights_json' => 123], Messages::STRING_EXPECTED];
        yield 'null value' => [['highlights_json' => null], Messages::STRING_EXPECTED];
        yield 'missing' => [[], Messages::VALUE_NOT_SET];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkHighlightsJson
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $params
     */
    public function test_rejects_invalid_input_with_expected_error(array $params, string $expectedErrorMessage): void
    {
        $fullParams = $this->fullSourceLinkParams($params);
        try {
            \Bristolian\Parameters\SourceLinkParam::createFromVarMap(new ArrayVarMap($fullParams));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/highlights_json' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkHighlightsJson
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new SourceLinkHighlightsJson('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function fullSourceLinkParams(array $params): array
    {
        return array_merge([
            'title' => 'This is a longer source title that meets the minimum length requirement',
            'text' => 'Source text content',
        ], $params);
    }
}
