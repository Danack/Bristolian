<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\SourceLinkHighlightParam;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class SourceLinkHighlightParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, int, int, int, int, int}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            [
                'page' => 1,
                'left' => 100,
                'top' => 200,
                'right' => 300,
                'bottom' => 400,
            ],
            1,
            100,
            200,
            300,
            400,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\SourceLinkHighlightParam
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        int $expectedPage,
        int $expectedLeft,
        int $expectedTop,
        int $expectedRight,
        int $expectedBottom
    ): void {
        $params = SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedPage, $params->page);
        $this->assertSame($expectedLeft, $params->left);
        $this->assertSame($expectedTop, $params->top);
        $this->assertSame($expectedRight, $params->right);
        $this->assertSame($expectedBottom, $params->bottom);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, array<string, string>}>
     */
    public static function provides_invalid_input_and_expected_errors(): \Generator
    {
        yield 'missing page' => [
            ['left' => 100, 'top' => 200, 'right' => 300, 'bottom' => 400],
            ['/page' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing left' => [
            ['page' => 1, 'top' => 200, 'right' => 300, 'bottom' => 400],
            ['/left' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing top' => [
            ['page' => 1, 'left' => 100, 'right' => 300, 'bottom' => 400],
            ['/top' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing right' => [
            ['page' => 1, 'left' => 100, 'top' => 200, 'bottom' => 400],
            ['/right' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing bottom' => [
            ['page' => 1, 'left' => 100, 'top' => 200, 'right' => 300],
            ['/bottom' => Messages::VALUE_NOT_SET],
        ];
        yield 'all missing' => [
            [],
            [
                '/page' => Messages::VALUE_NOT_SET,
                '/left' => Messages::VALUE_NOT_SET,
                '/top' => Messages::VALUE_NOT_SET,
                '/right' => Messages::VALUE_NOT_SET,
                '/bottom' => Messages::VALUE_NOT_SET,
            ],
        ];
        yield 'invalid types' => [
            ['page' => 'invalid', 'left' => 'invalid', 'top' => 'invalid', 'right' => 'invalid', 'bottom' => 'invalid'],
            [
                '/page' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
                '/left' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
                '/top' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
                '/right' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
                '/bottom' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
            ],
        ];
        yield 'null values' => [
            ['page' => null, 'left' => null, 'top' => null, 'right' => null, 'bottom' => null],
            [
                '/page' => Messages::INT_REQUIRED_UNSUPPORTED_TYPE,
                '/left' => Messages::INT_REQUIRED_UNSUPPORTED_TYPE,
                '/top' => Messages::INT_REQUIRED_UNSUPPORTED_TYPE,
                '/right' => Messages::INT_REQUIRED_UNSUPPORTED_TYPE,
                '/bottom' => Messages::INT_REQUIRED_UNSUPPORTED_TYPE,
            ],
        ];
    }

    /**
     * @covers \Bristolian\Parameters\SourceLinkHighlightParam
     * @dataProvider provides_invalid_input_and_expected_errors
     * @param array<string, mixed> $input
     * @param array<string, string> $expectedProblems
     */
    public function test_rejects_invalid_input_with_expected_errors(array $input, array $expectedProblems): void
    {
        try {
            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems($ve->getValidationProblems(), $expectedProblems);
        }
    }
}
