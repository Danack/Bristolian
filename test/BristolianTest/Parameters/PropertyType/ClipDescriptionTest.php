<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\ClipDescription;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class ClipDescriptionTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            ['description_input' => 'A valid clip description'],
            'A valid clip description',
        ];
        yield 'short allowed min 0' => [['description_input' => 'x'], 'x'];
        yield 'max length' => [['description_input' => str_repeat('a', ClipDescription::DESCRIPTION_MAXIMUM_LENGTH)], str_repeat('a', ClipDescription::DESCRIPTION_MAXIMUM_LENGTH)];
        yield 'empty string' => [['description_input' => ''], null];
        yield 'whitespace only' => [['description_input' => '   '], null];
        yield 'null value' => [['description_input' => null], null];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipDescription
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $paramTest = ClipDescriptionFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing' => [[], Messages::VALUE_NOT_SET];

        yield 'too long' => [['description_input' => str_repeat('a', ClipDescription::DESCRIPTION_MAXIMUM_LENGTH + 1)], Messages::STRING_TOO_LONG];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipDescription
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            ClipDescriptionFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/description_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipDescription
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new ClipDescription('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class ClipDescriptionFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[ClipDescription('description_input')]
        public readonly ?string $value,
    ) {
    }
}
