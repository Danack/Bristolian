<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\EmailAddress;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class EmailAddressTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [['email_input' => 'test@example.com'], 'test@example.com'];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\EmailAddress
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedValue): void
    {
        $paramTest = EmailAddressFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'invalid email' => [['email_input' => 'not-an-email'], Messages::ERROR_EMAIL_NO_AT_CHARACTER];
        yield 'too short' => [['email_input' => ''], Messages::STRING_TOO_SHORT];
        yield 'too long' => [['email_input' => str_repeat('a', 300) . '@example.com'], Messages::STRING_TOO_LONG];
        yield 'null value' => [['email_input' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\EmailAddress
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            EmailAddressFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/email_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\EmailAddress
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new EmailAddress('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class EmailAddressFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[EmailAddress('email_input')]
        public readonly string $value,
    ) {
    }
}
