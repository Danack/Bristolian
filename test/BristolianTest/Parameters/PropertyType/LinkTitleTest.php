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
 * @coversNothing
 */
class LinkTitleTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            ['title_input' => 'This is a valid title that meets the minimum length requirement'],
            'This is a valid title that meets the minimum length requirement',
        ];
        yield 'missing' => [[], null];
        yield 'empty string' => [['title_input' => ''], null];
        yield 'whitespace only' => [['title_input' => '   '], null];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\LinkTitle
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $paramTest = LinkTitleFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'null value' => [['title_input' => null], Messages::STRING_EXPECTED];
        yield 'too short' => [['title_input' => 'short'], Messages::STRING_TOO_SHORT];
        yield 'too long' => [['title_input' => str_repeat('a', 3000)], Messages::STRING_TOO_LONG];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\LinkTitle
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            LinkTitleFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/title_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\LinkTitle
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new LinkTitle('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
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
