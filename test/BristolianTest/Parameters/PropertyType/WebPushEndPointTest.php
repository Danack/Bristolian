<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\WebPushEndPoint;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\WebPushEndPoint
 */
class WebPushEndPointTest extends BaseTestCase
{
    public function testWorks()
    {
        $value = 'https://fcm.googleapis.com/fcm/send/example-token';
        $data = ['endpoint_input' => $value];

        $endpointParamTest = WebPushEndPointFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $endpointParamTest->value);
    }

    public function testFailsWithMissingRequiredParameter()
    {
        try {
            $data = [];

            WebPushEndPointFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/endpoint_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithEmptyString()
    {
        try {
            $data = ['endpoint_input' => ''];

            WebPushEndPointFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/endpoint_input' => Messages::STRING_TOO_SHORT]
            );
        }
    }

    public function testFailsWithTooLong()
    {
        try {
            $data = ['endpoint_input' => 'https://example.com/' . str_repeat('a', 600)];

            WebPushEndPointFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/endpoint_input' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function testFailsWithNonHttpsUrl()
    {
        try {
            $data = ['endpoint_input' => 'http://example.com/endpoint'];

            WebPushEndPointFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/endpoint_input' => Messages::STRING_REQUIRES_PREFIX]
            );
        }
    }

    public function testFailsWithInvalidDataType()
    {
        try {
            $data = ['endpoint_input' => 123];

            WebPushEndPointFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/endpoint_input' => Messages::STRING_EXPECTED]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $data = ['endpoint_input' => null];

            WebPushEndPointFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/endpoint_input' => Messages::STRING_REQUIRED_FOUND_NULL]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new WebPushEndPoint('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new WebPushEndPoint('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class WebPushEndPointFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[WebPushEndPoint('endpoint_input')]
        public readonly string $value,
    ) {
    }
}
