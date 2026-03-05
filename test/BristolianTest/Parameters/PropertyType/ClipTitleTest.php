<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\ClipTitle;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class ClipTitleTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            ['title_input' => 'A valid clip title'],
            'A valid clip title',
        ];
        yield 'single char' => [['title_input' => 'x'], 'x'];
        yield 'max length' => [['title_input' => str_repeat('a', ClipTitle::TITLE_MAXIMUM_LENGTH)], str_repeat('a', ClipTitle::TITLE_MAXIMUM_LENGTH)];
        yield 'empty string' => [['title_input' => ''], null];
        yield 'whitespace only' => [['title_input' => '   '], null];
        yield 'null value' => [['title_input' => null], null];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipTitle
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $paramTest = ClipTitleFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing' => [[], Messages::VALUE_NOT_SET];
        yield 'too long' => [['title_input' => str_repeat('a', ClipTitle::TITLE_MAXIMUM_LENGTH + 1)], Messages::STRING_TOO_LONG];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipTitle
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            ClipTitleFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/title_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipTitle
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new ClipTitle('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class ClipTitleFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[ClipTitle('title_input')]
        public readonly ?string $value,
    ) {
    }
}
