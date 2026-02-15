<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\TagString;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class TagStringTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [['tag_input' => 'valid1'], 'valid1'];
        yield 'allowed characters' => [['tag_input' => "tag-name_123'quoted"], "tag-name_123'quoted"];
        yield 'min length' => [['tag_input' => '1234'], '1234'];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\TagString
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedValue): void
    {
        $paramTest = TagStringFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'too short' => [['tag_input' => '123'], Messages::STRING_TOO_SHORT];
        yield 'disallowed characters' => [['tag_input' => 'tag with spaces'], Messages::STRING_FOUND_INVALID_CHAR];
        yield 'null value' => [['tag_input' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\TagString
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            TagStringFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/tag_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\TagString
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new TagString('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class TagStringFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[TagString('tag_input')]
        public readonly string $value,
    ) {
    }
}
