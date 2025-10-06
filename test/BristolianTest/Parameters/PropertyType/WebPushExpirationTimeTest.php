<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\WebPushExpirationTime;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\WebPushExpirationTime
 */
class WebPushExpirationTimeTest extends BaseTestCase
{
    public function testWorksWithValue()
    {
        $value = '2023-12-25T14:30:00Z';
        $data = ['expiration_input' => $value];

        $expirationParamTest = WebPushExpirationTimeFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $expirationParamTest->value);
    }

    public function testWorksWithNull()
    {
        $data = ['expiration_input' => null];

        $expirationParamTest = WebPushExpirationTimeFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertNull($expirationParamTest->value);
    }

    public function testFailsWithMissingValue()
    {
        try {
            $data = [];

            WebPushExpirationTimeFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/expiration_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testWorksWithEmptyString()
    {
        $data = ['expiration_input' => ''];

        $expirationParamTest = WebPushExpirationTimeFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame('', $expirationParamTest->value);
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new WebPushExpirationTime('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new WebPushExpirationTime('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class WebPushExpirationTimeFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[WebPushExpirationTime('expiration_input')]
        public readonly ?string $value,
    ) {
    }
}
