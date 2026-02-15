<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters\TinnedFish;

use Bristolian\Parameters\TinnedFish\GenerateApiTokenParams;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class GenerateApiTokenParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'simple name' => [['name' => 'My API Token'], 'My API Token'];
        yield 'single char' => [['name' => 'x'], 'x'];
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\GenerateApiTokenParams
     * @covers \Bristolian\Parameters\PropertyType\BasicString
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedName): void
    {
        $params = GenerateApiTokenParams::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedName, $params->name);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing name' => [[], Messages::VALUE_NOT_SET];
        yield 'null name' => [['name' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\GenerateApiTokenParams
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(
        array $input,
        string $expectedErrorMessage
    ): void {
        try {
            GenerateApiTokenParams::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/name' => $expectedErrorMessage]
            );
        }
    }
}
