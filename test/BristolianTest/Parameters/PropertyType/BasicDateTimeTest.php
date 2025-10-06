<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\BasicDateTime;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\BasicDateTime
 */
class BasicDateTimeTest extends BaseTestCase
{
    public function testWorks()
    {
        $value = '2023-12-25 14:30:00';
        $data = ['datetime_input' => $value];

        $datetimeParamTest = BasicDateTimeFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertInstanceOf(\DateTimeInterface::class, $datetimeParamTest->value);
        $this->assertSame('2023-12-25 14:30:00', $datetimeParamTest->value->format('Y-m-d H:i:s'));
    }

    public function testFailsWithMissingRequiredParameter()
    {
        try {
            $data = [];

            BasicDateTimeFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/datetime_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithInvalidDateFormat()
    {
        try {
            $data = ['datetime_input' => 'invalid date format'];

            BasicDateTimeFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/datetime_input' => Messages::ERROR_INVALID_DATETIME]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $data = ['datetime_input' => null];

            BasicDateTimeFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/datetime_input' => Messages::ERROR_DATETIME_MUST_START_AS_STRING]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new BasicDateTime('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new BasicDateTime('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class BasicDateTimeFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicDateTime('datetime_input')]
        public readonly \DateTimeInterface $value,
    ) {
    }
}