<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\ClipSeconds;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class ClipSecondsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, int}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'zero' => [['seconds_input' => 0], 0];
        yield 'integer' => [['seconds_input' => 100], 100];
        yield 'string integer' => [['seconds_input' => '42'], 42];
        yield 'max value 10 hours' => [['seconds_input' => ClipSeconds::MAX_SECONDS], ClipSeconds::MAX_SECONDS];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipSeconds
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, int $expectedValue): void
    {
        $paramTest = ClipSecondsFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'negative' => [['seconds_input' => -1], Messages::INT_TOO_SMALL];
        yield 'above max 10 hours' => [['seconds_input' => ClipSeconds::MAX_SECONDS + 1], Messages::INT_TOO_LARGE];
        yield 'invalid type' => [['seconds_input' => 'not a number'], Messages::INT_REQUIRED_FOUND_NON_DIGITS2];
        yield 'null value' => [['seconds_input' => null], Messages::INT_REQUIRED_UNSUPPORTED_TYPE];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipSeconds
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            ClipSecondsFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/seconds_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipSeconds
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new ClipSeconds('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class ClipSecondsFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[ClipSeconds('seconds_input')]
        public readonly int $value,
    ) {
    }
}
