<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\SourceLinkText;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class SourceLinkTextTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [['text_input' => 'This is some source link text content'], 'This is some source link text content'];
        yield 'empty string' => [['text_input' => ''], ''];
        yield 'max length' => [['text_input' => str_repeat('a', 16384)], str_repeat('a', 16384)];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkText
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedValue): void
    {
        $paramTest = SourceLinkTextFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'too long' => [['text_input' => str_repeat('a', 17000)], Messages::STRING_TOO_LONG];
        yield 'invalid type' => [['text_input' => 123], Messages::STRING_EXPECTED];
        yield 'null value' => [['text_input' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkText
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            SourceLinkTextFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/text_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkText
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new SourceLinkText('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class SourceLinkTextFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[SourceLinkText('text_input')]
        public readonly string $value,
    ) {
    }
}
