<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\ClipTitle;
use Bristolian\Parameters\PropertyType\LinkTitle;
use Bristolian\Parameters\PropertyType\OptionalAddVideoTitle;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class OptionalAddVideoTitleTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        $minLength = LinkTitle::TITLE_MINIMUM_LENGTH;
        yield 'present at minimum length' => [['title_input' => str_repeat('t', $minLength)], str_repeat('t', $minLength)];
        yield 'missing' => [[], null];
        yield 'empty string' => [['title_input' => ''], null];
        yield 'whitespace only' => [['title_input' => '   '], null];
        yield 'max length' => [
            ['title_input' => str_repeat('t', ClipTitle::TITLE_MAXIMUM_LENGTH)],
            str_repeat('t', ClipTitle::TITLE_MAXIMUM_LENGTH),
        ];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalAddVideoTitle::__construct
     * @covers \Bristolian\Parameters\PropertyType\OptionalAddVideoTitle::getInputType
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $fixture = OptionalAddVideoTitleFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $fixture->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        $belowMin = LinkTitle::TITLE_MINIMUM_LENGTH - 1;
        yield 'too short when present' => [['title_input' => str_repeat('t', $belowMin)], Messages::STRING_TOO_SHORT];
        yield 'too long' => [
            ['title_input' => str_repeat('t', ClipTitle::TITLE_MAXIMUM_LENGTH + 1)],
            Messages::STRING_TOO_LONG,
        ];
        yield 'wrong type' => [['title_input' => false], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalAddVideoTitle::getInputType
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            OptionalAddVideoTitleFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $exception) {
            $this->assertValidationProblems(
                $exception->getValidationProblems(),
                ['/title_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalAddVideoTitle::getInputType
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new OptionalAddVideoTitle('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class OptionalAddVideoTitleFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[OptionalAddVideoTitle('title_input')]
        public readonly ?string $value,
    ) {
    }
}
