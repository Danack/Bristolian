<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\TableNumberOfRowsValue;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\TableNumberOfRowsValue
 */
class TableNumberOfRowsValueTest extends BaseTestCase
{
    public function testWorks()
    {
        $value = 5;
        $data = ['rows_input' => $value];

        $rowsParamTest = TableNumberOfRowsValueFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $rowsParamTest->value);
    }

    public function testWorksWithStringInteger()
    {
        $value = '10';
        $data = ['rows_input' => $value];

        $rowsParamTest = TableNumberOfRowsValueFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame(10, $rowsParamTest->value);
    }

    public function testWorksWithOne()
    {
        $value = 1;
        $data = ['rows_input' => $value];

        $rowsParamTest = TableNumberOfRowsValueFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $rowsParamTest->value);
    }

    public function testFailsWithMissingRequiredParameter()
    {
        try {
            $data = [];

            TableNumberOfRowsValueFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/rows_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testWorksWithZero()
    {
        $value = 0;
        $data = ['rows_input' => $value];

        $rowsParamTest = TableNumberOfRowsValueFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $rowsParamTest->value);
    }

    public function testWorksWithNegativeValue()
    {
        $value = -1;
        $data = ['rows_input' => $value];

        $rowsParamTest = TableNumberOfRowsValueFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $rowsParamTest->value);
    }

    public function testFailsWithInvalidDataType()
    {
        try {
            $data = ['rows_input' => 'not a number'];

            TableNumberOfRowsValueFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/rows_input' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $data = ['rows_input' => null];

            TableNumberOfRowsValueFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/rows_input' => Messages::INT_REQUIRED_UNSUPPORTED_TYPE]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new TableNumberOfRowsValue('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new TableNumberOfRowsValue('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class TableNumberOfRowsValueFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[TableNumberOfRowsValue('rows_input')]
        public readonly int $value,
    ) {
    }
}