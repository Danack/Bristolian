<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\BasicPhpEnumTypeOrNull;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\BasicPhpEnumTypeOrNull
 */
class BasicPhpEnumTypeOrNullTest extends BaseTestCase
{
    public function testWorksWithValidEnumValue()
    {
        $value = 'VALUE1';
        $data = ['enum_input' => $value];

        $enumParamTest = BasicPhpEnumTypeOrNullFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame(TestEnum::VALUE1, $enumParamTest->value);
    }

    public function testFailsWithNull()
    {
        try {
            $data = ['enum_input' => null];

            BasicPhpEnumTypeOrNullFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/enum_input' => Messages::STRING_EXPECTED]
            );
        }
    }

    public function testWorksWithMissingValue()
    {
        $data = [];

        $enumParamTest = BasicPhpEnumTypeOrNullFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertNull($enumParamTest->value);
    }

    public function testFailsWithInvalidEnumValue()
    {
        try {
            $data = ['enum_input' => 'INVALID_VALUE'];

            BasicPhpEnumTypeOrNullFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/enum_input' => Messages::ENUM_MAP_UNRECOGNISED_VALUE_SINGLE]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new BasicPhpEnumTypeOrNull('test_name', TestEnum::class);
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new BasicPhpEnumTypeOrNull('test_name', TestEnum::class);
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class BasicPhpEnumTypeOrNullFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicPhpEnumTypeOrNull('enum_input', TestEnum::class)]
        public readonly ?TestEnum $value,
    ) {
    }
}

enum TestEnum: string
{
    case VALUE1 = 'VALUE1';
    case VALUE2 = 'VALUE2';
    case VALUE3 = 'VALUE3';
}
