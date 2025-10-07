<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\BasicInteger;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\BasicInteger
 */
class BasicIntegerTest extends BaseTestCase
{
    public function testWorks()
    {
        $value = 42;
        $data = ['integer_input' => $value];

        $integerParamTest = BasicIntegerFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $integerParamTest->value);
    }

    public function testWorksWithStringInteger()
    {
        $value = '42';
        $data = ['integer_input' => $value];

        $integerParamTest = BasicIntegerFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame(42, $integerParamTest->value);
    }

    public function testFailsWithMissingRequiredParameter()
    {
        try {
            $data = [];

            BasicIntegerFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/integer_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithInvalidDataType()
    {
        try {
            $data = ['integer_input' => 'not a number'];

            BasicIntegerFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/integer_input' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $data = ['integer_input' => null];

            BasicIntegerFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/integer_input' => Messages::INT_REQUIRED_UNSUPPORTED_TYPE]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new BasicInteger('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new BasicInteger('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class BasicIntegerFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicInteger('integer_input')]
        public readonly int $value,
    ) {
    }
}
