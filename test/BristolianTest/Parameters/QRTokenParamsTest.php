<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\QRTokenParams;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class QRTokenParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'simple token' => [['token' => 'abc123'], 'abc123'];
        yield 'long token' => [['token' => str_repeat('x', 100)], str_repeat('x', 100)];
    }

    /**
     * @covers \Bristolian\Parameters\QRTokenParams
     * @covers \Bristolian\Parameters\PropertyType\BasicString
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedToken): void
    {
        $params = QRTokenParams::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedToken, $params->token);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing token' => [[], Messages::VALUE_NOT_SET];
        yield 'null token' => [['token' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\QRTokenParams
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(
        array $input,
        string $expectedErrorMessage
    ): void {
        try {
            QRTokenParams::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/token' => $expectedErrorMessage]
            );
        }
    }
}
