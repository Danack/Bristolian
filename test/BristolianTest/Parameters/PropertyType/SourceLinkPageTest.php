<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\SourceLinkPage;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\SourceLinkPage
 */
class SourceLinkPageTest extends BaseTestCase
{
    public function testWorks()
    {
        $value = 5;
        $data = ['page_input' => $value];

        $pageParamTest = SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $pageParamTest->value);
    }

    public function testWorksWithStringInteger()
    {
        $value = '10';
        $data = ['page_input' => $value];

        $pageParamTest = SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame(10, $pageParamTest->value);
    }

    public function testWorksWithZero()
    {
        $value = 0;
        $data = ['page_input' => $value];

        $pageParamTest = SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $pageParamTest->value);
    }

    public function testWorksWithMaximumValue()
    {
        $value = 1000;
        $data = ['page_input' => $value];

        $pageParamTest = SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $pageParamTest->value);
    }

    public function testFailsWithMissingRequiredParameter()
    {
        try {
            $data = [];

            SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/page_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithNegativeValue()
    {
        try {
            $data = ['page_input' => -1];

            SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/page_input' => Messages::INT_TOO_SMALL]
            );
        }
    }

    public function testFailsWithTooHighValue()
    {
        try {
            $data = ['page_input' => 1001];

            SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/page_input' => Messages::INT_TOO_LARGE]
            );
        }
    }

    public function testFailsWithInvalidDataType()
    {
        try {
            $data = ['page_input' => 'not a number'];

            SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/page_input' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $data = ['page_input' => null];

            SourceLinkPageFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/page_input' => Messages::INT_REQUIRED_UNSUPPORTED_TYPE]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new SourceLinkPage('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new SourceLinkPage('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class SourceLinkPageFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[SourceLinkPage('page_input')]
        public readonly int $value,
    ) {
    }
}