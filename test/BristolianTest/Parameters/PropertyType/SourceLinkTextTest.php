<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\SourceLinkText;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\SourceLinkText
 */
class SourceLinkTextTest extends BaseTestCase
{
    public function testWorks()
    {
        $value = 'This is some source link text content';
        $data = ['text_input' => $value];

        $textParamTest = SourceLinkTextFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $textParamTest->value);
    }

    public function testWorksWithEmptyString()
    {
        $value = '';
        $data = ['text_input' => $value];

        $textParamTest = SourceLinkTextFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $textParamTest->value);
    }

    public function testWorksWithMaximumLength()
    {
        $value = str_repeat('a', 16384); // Exactly at the limit
        $data = ['text_input' => $value];

        $textParamTest = SourceLinkTextFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $textParamTest->value);
    }

    public function testFailsWithMissingRequiredParameter()
    {
        try {
            $data = [];

            SourceLinkTextFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/text_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithTooLong()
    {
        try {
            $data = ['text_input' => str_repeat('a', 17000)]; // Exceeds 16KB limit

            SourceLinkTextFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/text_input' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function testFailsWithInvalidDataType()
    {
        try {
            $data = ['text_input' => 123];

            SourceLinkTextFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/text_input' => Messages::STRING_EXPECTED]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $data = ['text_input' => null];

            SourceLinkTextFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/text_input' => Messages::STRING_REQUIRED_FOUND_NULL]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new SourceLinkText('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new SourceLinkText('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class SourceLinkTextFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[SourceLinkText('text_input')]
        public readonly string $value,
    ) {
    }
}
