<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\BasicFloat;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\GetInputTypesFromAttributes;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class BasicFloatTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, float}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [['float_input' => 1.234], 1.234];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\BasicFloat
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, float $expectedValue): void
    {
        $paramTest = BasicFloatFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }
}
