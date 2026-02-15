<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\SourceLinkParam;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class SourceLinkParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string, string, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            [
                'title' => 'This is a longer source title that meets the minimum length requirement',
                'highlights_json' => '{"highlights": []}',
                'text' => 'Source text content',
            ],
            'This is a longer source title that meets the minimum length requirement',
            '{"highlights": []}',
            'Source text content',
        ];
    }

    /**
     * @covers \Bristolian\Parameters\SourceLinkParam
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        string $expectedTitle,
        string $expectedHighlightsJson,
        string $expectedText
    ): void {
        $params = SourceLinkParam::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedTitle, $params->title);
        $this->assertSame($expectedHighlightsJson, $params->highlights_json);
        $this->assertSame($expectedText, $params->text);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, array<string, string>}>
     */
    public static function provides_invalid_input_and_expected_errors(): \Generator
    {
        yield 'missing title' => [
            ['highlights_json' => '{"highlights": []}', 'text' => 'Source text content'],
            ['/title' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing highlights_json' => [
            [
                'title' => 'This is a longer source title that meets the minimum length requirement',
                'text' => 'Source text content',
            ],
            ['/highlights_json' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing text' => [
            [
                'title' => 'This is a longer source title that meets the minimum length requirement',
                'highlights_json' => '{"highlights": []}',
            ],
            ['/text' => Messages::VALUE_NOT_SET],
        ];
        yield 'invalid types' => [
            ['title' => 123, 'highlights_json' => 456, 'text' => 789],
            [
                '/title' => Messages::STRING_EXPECTED,
                '/highlights_json' => Messages::STRING_EXPECTED,
                '/text' => Messages::STRING_EXPECTED,
            ],
        ];
    }

    /**
     * @covers \Bristolian\Parameters\SourceLinkParam
     * @dataProvider provides_invalid_input_and_expected_errors
     * @param array<string, mixed> $input
     * @param array<string, string> $expectedProblems
     */
    public function test_rejects_invalid_input_with_expected_errors(array $input, array $expectedProblems): void
    {
        try {
            SourceLinkParam::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems($ve->getValidationProblems(), $expectedProblems);
        }
    }
}
