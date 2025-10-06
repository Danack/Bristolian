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
 * @covers \Bristolian\Parameters\PropertyType\SourceLinkPositionValue
 */
class SourceLinkPositionValueTest extends BaseTestCase
{
    public function testWorks()
    {
        $value = 100;
        $data = ['position_input' => $value];

        $positionParamTest = SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $positionParamTest->value);
    }

    public function testWorksWithStringInteger()
    {
        $value = '500';
        $data = ['position_input' => $value];

        $positionParamTest = SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame(500, $positionParamTest->value);
    }

    public function testWorksWithZero()
    {
        $value = 0;
        $data = ['position_input' => $value];

        $positionParamTest = SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $positionParamTest->value);
    }

    public function testWorksWithMaximumValue()
    {
        $value = 10000;
        $data = ['position_input' => $value];

        $positionParamTest = SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $positionParamTest->value);
    }

    public function testFailsWithMissingRequiredParameter()
    {
        try {
            $data = [];

            SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/position_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithNegativeValue()
    {
        try {
            $data = ['position_input' => -1];

            SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/position_input' => Messages::INT_TOO_SMALL]
            );
        }
    }

    public function testFailsWithTooHighValue()
    {
        try {
            $data = ['position_input' => 10001];

            SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/position_input' => Messages::INT_TOO_LARGE]
            );
        }
    }

    public function testFailsWithInvalidDataType()
    {
        try {
            $data = ['position_input' => 'not a number'];

            SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/position_input' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $data = ['position_input' => null];

            SourceLinkPositionValueFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/position_input' => Messages::INT_REQUIRED_UNSUPPORTED_TYPE]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new SourceLinkPositionValue('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new SourceLinkPositionValue('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
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
