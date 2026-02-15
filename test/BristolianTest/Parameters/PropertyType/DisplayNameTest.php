<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\DisplayName;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class DisplayNameTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [['display_name_input' => 'ValidName'], 'ValidName'];
        yield 'min length' => [['display_name_input' => 'abcd'], 'abcd'];
        yield 'max length' => [['display_name_input' => str_repeat('a', 32)], str_repeat('a', 32)];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\DisplayName
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedValue): void
    {
        $paramTest = DisplayNameFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'too short' => [['display_name_input' => 'abc'], Messages::STRING_TOO_SHORT];
        yield 'too long' => [['display_name_input' => str_repeat('a', 33)], Messages::STRING_TOO_LONG];
        yield 'null value' => [['display_name_input' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\DisplayName
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            DisplayNameFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/display_name_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\DisplayName
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new DisplayName('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class DisplayNameFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[DisplayName('display_name_input')]
        public readonly string $value,
    ) {
    }
}
