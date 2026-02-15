<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\GpsFloat;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class GpsFloatTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, float|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'float' => [['gps_input' => 51.4545], 51.4545];
        yield 'missing' => [[], null];
        yield 'string float' => [['gps_input' => '51.4545'], 51.4545];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\GpsFloat
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?float $expectedValue): void
    {
        $paramTest = GpsFloatFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'null value' => [['gps_input' => null], Messages::FLOAT_REQUIRED_WRONG_TYPE];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\GpsFloat
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            GpsFloatFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/gps_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\GpsFloat
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new GpsFloat('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class GpsFloatFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[GpsFloat('gps_input')]
        public readonly ?float $value,
    ) {
    }
}
