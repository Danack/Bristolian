<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\LinkTitle;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\LinkTitle
 */
class LinkTitleTest extends BaseTestCase
{
    public function testWorksWithValidTitle()
    {
        $value = 'This is a valid title that meets the minimum length requirement';
        $data = ['title_input' => $value];

        $titleParamTest = LinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertSame($value, $titleParamTest->value);
    }

    public function testFailsWithNull()
    {
        try {
            $data = ['title_input' => null];

            LinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/title_input' => Messages::STRING_EXPECTED]
            );
        }
    }

    public function testWorksWithMissingValue()
    {
        $data = [];

        $titleParamTest = LinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertNull($titleParamTest->value);
    }

    public function testWorksWithEmptyString()
    {
        $data = ['title_input' => ''];

        $titleParamTest = LinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertNull($titleParamTest->value);
    }

    public function testWorksWithWhitespaceOnly()
    {
        $data = ['title_input' => '   '];

        $titleParamTest = LinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
        $this->assertNull($titleParamTest->value);
    }

    public function testFailsWithTooShort()
    {
        try {
            $data = ['title_input' => 'short'];

            LinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
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
            $data = ['title_input' => str_repeat('a', 3000)];

            LinkTitleFixture::createFromVarMap(new ArrayVarMap($data));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/title_input' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function testImplementsHasInputType()
    {
        $propertyType = new LinkTitle('test_name');
        $this->assertInstanceOf(\DataType\HasInputType::class, $propertyType);
    }

    public function testGetInputTypeReturnsCorrectType()
    {
        $propertyType = new LinkTitle('test_name');
        $inputType = $propertyType->getInputType();
        
        $this->assertInstanceOf(\DataType\InputType::class, $inputType);
        $this->assertSame('test_name', $inputType->getName());
    }
}

class LinkTitleFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[LinkTitle('title_input')]
        public readonly ?string $value,
    ) {
    }
}
