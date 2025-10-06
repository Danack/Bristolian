<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\SourceLinkTitle;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\SourceLinkTitle
 */
class SourceLinkTitleTest extends BaseTestCase
{
    public function testWorks()
    {
        $value = 'This is a valid source link title that meets the minimum length requirement';
        $data = ['title_input' => $value];

        $titleParamTest = SourceLinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $titleParamTest->value);
    }

    public function testFailsWithMissingRequiredParameter()
    {
        try {
            $data = [];

            SourceLinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/title_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithTooShort()
    {
        try {
            $data = ['title_input' => 'short'];

            SourceLinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/title_input' => Messages::STRING_TOO_SHORT]
            );
        }
    }

    public function testFailsWithTooLong()
    {
        try {
            $data = ['title_input' => str_repeat('a', 2000)];

            SourceLinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/title_input' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function testFailsWithInvalidDataType()
    {
        try {
            $data = ['title_input' => 123];

            SourceLinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/title_input' => Messages::STRING_EXPECTED]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $data = ['title_input' => null];

            SourceLinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/title_input' => Messages::STRING_REQUIRED_FOUND_NULL]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new SourceLinkTitle('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new SourceLinkTitle('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class SourceLinkTitleFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[SourceLinkTitle('title_input')]
        public readonly string $value,
    ) {
    }
}