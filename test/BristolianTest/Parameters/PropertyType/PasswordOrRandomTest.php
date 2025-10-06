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
 * @covers \Bristolian\Parameters\PropertyType\PasswordOrRandom
 */
class PasswordOrRandomTest extends BaseTestCase
{
    public function testWorksWithProvidedPassword()
    {
        $value = 'user-provided-password';
        $data = ['password_input' => $value];

        $passwordParamTest = PasswordOrRandomFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $passwordParamTest->value);
    }

    public function testWorksWithEmptyString()
    {
        $data = ['password_input' => ''];

        $passwordParamTest = PasswordOrRandomFixture::createFromVarMap(new ArrayVarMap($data));
        // Should generate a random password of 16 characters
        $this->assertIsString($passwordParamTest->value);
        $this->assertGreaterThanOrEqual(16, strlen($passwordParamTest->value));
    }

    public function testFailsWithNull()
    {
        try {
            $data = ['password_input' => null];

            PasswordOrRandomFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/password_input' => Messages::STRING_REQUIRED_FOUND_NULL]
            );
        }
    }

    public function testFailsWithMissingValue()
    {
        try {
            $data = [];

            PasswordOrRandomFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/password_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithTooLong()
    {
        try {
            $data = ['password_input' => str_repeat('a', 300)];

            PasswordOrRandomFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/password_input' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new PasswordOrRandom('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new PasswordOrRandom('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
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