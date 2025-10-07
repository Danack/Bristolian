<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\Url;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\Url
 */
class UrlTest extends BaseTestCase
{
    public function testWorks()
    {
        $value = 'https://example.com';
        $data = ['url_input' => $value];

        $urlParamTest = UrlFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $urlParamTest->value);
    }

    public function testFailsWithMissingRequiredParameter()
    {
        try {
            $data = [];

            UrlFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/url_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithInvalidUrl()
    {
        try {
            $data = ['url_input' => 'not-a-url'];

            UrlFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/url_input' => Messages::ERROR_INVALID_URL]
            );
        }
    }

    public function testFailsWithTooShort()
    {
        try {
            $data = ['url_input' => ''];

            UrlFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/url_input' => Messages::STRING_TOO_SHORT]
            );
        }
    }

    public function testFailsWithTooLong()
    {
        try {
            $data = ['url_input' => 'https://example.com/' . str_repeat('a', 3000)];

            UrlFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/url_input' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $data = ['url_input' => null];

            UrlFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/url_input' => Messages::STRING_REQUIRED_FOUND_NULL]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new Url('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new Url('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class UrlFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[Url('url_input')]
        public readonly string $value,
    ) {
    }
}
