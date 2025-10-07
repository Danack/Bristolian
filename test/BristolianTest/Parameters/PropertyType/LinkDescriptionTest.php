<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\LinkDescription;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\LinkDescription
 */
class LinkDescriptionTest extends BaseTestCase
{
    public function testWorksWithValidDescription()
    {
        $value = 'This is a valid description that meets the minimum length requirement';
        $data = ['description_input' => $value];

        $descriptionParamTest = LinkDescriptionFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $descriptionParamTest->value);
    }

    public function testFailsWithNull()
    {
        try {
            $data = ['description_input' => null];

            LinkDescriptionFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/description_input' => Messages::STRING_EXPECTED]
            );
        }
    }

    public function testWorksWithMissingValue()
    {
        $data = [];

        $descriptionParamTest = LinkDescriptionFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertNull($descriptionParamTest->value);
    }

    public function testWorksWithEmptyString()
    {
        $data = ['description_input' => ''];

        $descriptionParamTest = LinkDescriptionFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertNull($descriptionParamTest->value);
    }

    public function testWorksWithWhitespaceOnly()
    {
        $data = ['description_input' => '   '];

        $descriptionParamTest = LinkDescriptionFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertNull($descriptionParamTest->value);
    }

    public function testFailsWithTooShort()
    {
        try {
            $data = ['description_input' => 'short'];

            LinkDescriptionFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/description_input' => Messages::STRING_TOO_SHORT]
            );
        }
    }

    public function testFailsWithTooLong()
    {
        try {
            $data = ['description_input' => str_repeat('a', 3000)];

            LinkDescriptionFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/description_input' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new LinkDescription('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new LinkDescription('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class LinkDescriptionFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[LinkDescription('description_input')]
        public readonly ?string $value,
    ) {
    }
}
