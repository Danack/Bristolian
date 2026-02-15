<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\PasswordOrRandom;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class PasswordOrRandomTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     * null means "expect generated value with length >= 16"
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'with value' => [['password_input' => 'user-provided-password'], 'user-provided-password'];
        yield 'empty string generates random' => [['password_input' => ''], null];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\PasswordOrRandom
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $paramTest = PasswordOrRandomFixture::createFromVarMap(new ArrayVarMap($input));
        if ($expectedValue !== null) {
            $this->assertSame($expectedValue, $paramTest->value);
        }
        else {
            $this->assertGreaterThanOrEqual(16, strlen($paramTest->value));
        }
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'null value' => [['password_input' => null], Messages::STRING_EXPECTED];
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'too long' => [['password_input' => str_repeat('a', 300)], Messages::STRING_TOO_LONG];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\PasswordOrRandom
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            PasswordOrRandomFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/password_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\PasswordOrRandom
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new PasswordOrRandom('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class PasswordOrRandomFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[PasswordOrRandom('password_input')]
        public readonly string $value,
    ) {
    }
}
