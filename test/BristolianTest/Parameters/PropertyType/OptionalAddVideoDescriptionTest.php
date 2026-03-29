<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\ClipDescription;
use Bristolian\Parameters\PropertyType\OptionalAddVideoDescription;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class OptionalAddVideoDescriptionTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'present' => [['description_input' => 'A valid clip description'], 'A valid clip description'];
        yield 'missing' => [[], null];
        yield 'empty string' => [['description_input' => ''], null];
        yield 'whitespace only' => [['description_input' => '   '], null];
        yield 'max length' => [
            ['description_input' => str_repeat('a', ClipDescription::DESCRIPTION_MAXIMUM_LENGTH)],
            str_repeat('a', ClipDescription::DESCRIPTION_MAXIMUM_LENGTH),
        ];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalAddVideoDescription::__construct
     * @covers \Bristolian\Parameters\PropertyType\OptionalAddVideoDescription::getInputType
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $fixture = OptionalAddVideoDescriptionFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $fixture->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'too long' => [
            ['description_input' => str_repeat('a', ClipDescription::DESCRIPTION_MAXIMUM_LENGTH + 1)],
            Messages::STRING_TOO_LONG,
        ];
        yield 'wrong type' => [['description_input' => 12345], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalAddVideoDescription::getInputType
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            OptionalAddVideoDescriptionFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $exception) {
            $this->assertValidationProblems(
                $exception->getValidationProblems(),
                ['/description_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalAddVideoDescription::getInputType
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new OptionalAddVideoDescription('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class OptionalAddVideoDescriptionFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[OptionalAddVideoDescription('description_input')]
        public readonly ?string $value,
    ) {
    }
}
