<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\BasicPhpEnumTypeOrNull;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class BasicPhpEnumTypeOrNullTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, TestEnum|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid enum' => [['enum_input' => 'VALUE1'], TestEnum::VALUE1];
        yield 'missing value' => [[], null];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\BasicPhpEnumTypeOrNull
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?TestEnum $expectedValue): void
    {
        $paramTest = BasicPhpEnumTypeOrNullFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'null value' => [['enum_input' => null], Messages::STRING_EXPECTED];
        yield 'invalid enum' => [['enum_input' => 'INVALID_VALUE'], Messages::ENUM_MAP_UNRECOGNISED_VALUE_SINGLE];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\BasicPhpEnumTypeOrNull
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            BasicPhpEnumTypeOrNullFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/enum_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\BasicPhpEnumTypeOrNull
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new BasicPhpEnumTypeOrNull('test_name', TestEnum::class);
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class BasicPhpEnumTypeOrNullFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicPhpEnumTypeOrNull('enum_input', TestEnum::class)]
        public readonly ?TestEnum $value,
    ) {
    }
}

enum TestEnum: string
{
    case VALUE1 = 'VALUE1';
    case VALUE2 = 'VALUE2';
    case VALUE3 = 'VALUE3';
}
