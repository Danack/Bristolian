<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\AboutMeText;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class AboutMeTextTest extends BaseTestCase
{

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid text' => [['about_me_input' => 'This is my about me text.'], 'This is my about me text.'];
        yield 'empty string' => [['about_me_input' => ''], ''];
        yield 'max length' => [['about_me_input' => str_repeat('a', 4096)], str_repeat('a', 4096)];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\AboutMeText
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedValue): void
    {
        $paramTest = AboutMeTextFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'too long' => [['about_me_input' => str_repeat('a', 4097)], Messages::STRING_TOO_LONG];
        yield 'null value' => [['about_me_input' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\AboutMeText
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            AboutMeTextFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/about_me_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\AboutMeText
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new AboutMeText('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class AboutMeTextFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[AboutMeText('about_me_input')]
        public readonly string $value,
    ) {
    }
}
