<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\BasicFloat;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\GetInputTypesFromAttributes;
use VarMap\ArrayVarMap;

class BasicFloatTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Parameters\PropertyType\BasicFloat
     */
    public function testWorks()
    {
        $value = 1.234;
        $data = ['float_input' => $value];

        $floatParamTest = BasicFloatFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $floatParamTest->value);
    }
}