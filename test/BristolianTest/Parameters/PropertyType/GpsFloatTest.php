<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\GpsFloat;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\GpsFloat
 */
class GpsFloatTest extends BaseTestCase
{
    public function testWorksWithValue()
    {
        $value = 51.4545;
        $data = ['gps_input' => $value];

        $gpsParamTest = GpsFloatFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $gpsParamTest->value);
    }

    public function testFailsWithNull()
    {
        try {
            $data = ['gps_input' => null];

            GpsFloatFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/gps_input' => Messages::FLOAT_REQUIRED_WRONG_TYPE]
            );
        }
    }

    public function testWorksWithMissingValue()
    {
        $data = [];

        $gpsParamTest = GpsFloatFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertNull($gpsParamTest->value);
    }

    public function testWorksWithStringFloat()
    {
        $value = '51.4545';
        $data = ['gps_input' => $value];

        $gpsParamTest = GpsFloatFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame(51.4545, $gpsParamTest->value);
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new GpsFloat('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new GpsFloat('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class GpsFloatFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[GpsFloat('gps_input')]
        public readonly ?float $value,
    ) {
    }
}