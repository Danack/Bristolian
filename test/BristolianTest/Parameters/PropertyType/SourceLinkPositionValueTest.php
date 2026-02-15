<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\SourceLinkPositionValue;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class SourceLinkPositionValueTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, int}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'integer' => [['position_input' => 100], 100];
        yield 'string integer' => [['position_input' => '500'], 500];
        yield 'zero' => [['position_input' => 0], 0];
        yield 'max value' => [['position_input' => 10000], 10000];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkPositionValue
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, int $expectedValue): void
    {
        $paramTest = SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'negative' => [['position_input' => -1], Messages::INT_TOO_SMALL];
        yield 'too high' => [['position_input' => 10001], Messages::INT_TOO_LARGE];
        yield 'invalid type' => [['position_input' => 'not a number'], Messages::INT_REQUIRED_FOUND_NON_DIGITS2];
        yield 'null value' => [['position_input' => null], Messages::INT_REQUIRED_UNSUPPORTED_TYPE];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkPositionValue
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/position_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkPositionValue
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new SourceLinkPositionValue('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class SourceLinkPositionValueFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[SourceLinkPositionValue('position_input')]
        public readonly int $value,
    ) {
    }
}
