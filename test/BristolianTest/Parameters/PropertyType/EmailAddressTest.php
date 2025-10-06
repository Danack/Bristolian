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
 * @covers \Bristolian\Parameters\PropertyType\EmailAddress
 */
class EmailAddressTest extends BaseTestCase
{
    public function testWorks()
    {
        $value = 'test@example.com';
        $data = ['email_input' => $value];

        $emailParamTest = EmailAddressFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $emailParamTest->value);
    }

    public function testFailsWithMissingRequiredParameter()
    {
        try {
            $data = [];

            EmailAddressFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/email_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithInvalidEmail()
    {
        try {
            $data = ['email_input' => 'not-an-email'];

            EmailAddressFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/email_input' => Messages::ERROR_EMAIL_NO_AT_CHARACTER]
            );
        }
    }

    public function testFailsWithTooShort()
    {
        try {
            $data = ['email_input' => ''];

            EmailAddressFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/email_input' => Messages::STRING_TOO_SHORT]
            );
        }
    }

    public function testFailsWithTooLong()
    {
        try {
            $data = ['email_input' => str_repeat('a', 300) . '@example.com'];

            EmailAddressFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/email_input' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $data = ['email_input' => null];

            EmailAddressFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/email_input' => Messages::STRING_REQUIRED_FOUND_NULL]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new EmailAddress('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new EmailAddress('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
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