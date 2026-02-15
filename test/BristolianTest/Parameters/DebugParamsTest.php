<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\DebugParams;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class DebugParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            ['message' => 'Hello there.', 'detail' => 'this is some detail'],
            'Hello there.',
            'this is some detail',
        ];
    }

    /**
     * @covers \Bristolian\Parameters\DebugParams
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        string $expectedMessage,
        string $expectedDetail
    ): void {
        $params = DebugParams::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedMessage, $params->message);
        $this->assertSame($expectedDetail, $params->detail);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, array<string, string>}>
     */
    public static function provides_invalid_input_and_expected_errors(): \Generator
    {
        yield 'missing message' => [
            ['detail' => 'some detail'],
            ['/message' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing detail' => [
            ['message' => 'some message'],
            ['/detail' => Messages::VALUE_NOT_SET],
        ];
        yield 'both missing' => [
            [],
            [
                '/message' => Messages::VALUE_NOT_SET,
                '/detail' => Messages::VALUE_NOT_SET,
            ],
        ];
        yield 'invalid types' => [
            ['message' => 123, 'detail' => 456],
            [
                '/message' => Messages::STRING_EXPECTED,
                '/detail' => Messages::STRING_EXPECTED,
            ],
        ];
        yield 'null values' => [
            ['message' => null, 'detail' => null],
            [
                '/message' => Messages::STRING_EXPECTED,
                '/detail' => Messages::STRING_EXPECTED,
            ],
        ];
    }

    /**
     * @covers \Bristolian\Parameters\DebugParams
     * @dataProvider provides_invalid_input_and_expected_errors
     * @param array<string, mixed> $input
     * @param array<string, string> $expectedProblems
     */
    public function test_rejects_invalid_input_with_expected_errors(array $input, array $expectedProblems): void
    {
        try {
            DebugParams::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems($ve->getValidationProblems(), $expectedProblems);
        }
    }
}
