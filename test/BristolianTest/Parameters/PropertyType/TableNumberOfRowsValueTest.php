<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\TableNumberOfRowsValue;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class TableNumberOfRowsValueTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, int}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'integer' => [['rows_input' => 5], 5];
        yield 'string integer' => [['rows_input' => '10'], 10];
        yield 'one' => [['rows_input' => 1], 1];
        yield 'zero' => [['rows_input' => 0], 0];
        yield 'negative' => [['rows_input' => -1], -1];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\TableNumberOfRowsValue
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, int $expectedValue): void
    {
        $paramTest = TableNumberOfRowsValueFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'invalid type' => [['rows_input' => 'not a number'], Messages::INT_REQUIRED_FOUND_NON_DIGITS2];
        yield 'null value' => [['rows_input' => null], Messages::INT_REQUIRED_UNSUPPORTED_TYPE];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\TableNumberOfRowsValue
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            TableNumberOfRowsValueFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/rows_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\TableNumberOfRowsValue
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new TableNumberOfRowsValue('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class TableNumberOfRowsValueFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[TableNumberOfRowsValue('rows_input')]
        public readonly int $value,
    ) {
    }
}
