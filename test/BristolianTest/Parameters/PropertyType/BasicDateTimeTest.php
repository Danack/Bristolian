<?php

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\BasicDateTime;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class BasicDateTimeTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     * Expected value is the formatted date string (Y-m-d H:i:s)
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [['datetime_input' => '2023-12-25 14:30:00'], '2023-12-25 14:30:00'];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\BasicDateTime
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedFormat): void
    {
        $paramTest = BasicDateTimeFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertInstanceOf(\DateTimeInterface::class, $paramTest->value);
        $this->assertSame($expectedFormat, $paramTest->value->format('Y-m-d H:i:s'));
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'invalid format' => [['datetime_input' => 'invalid date format'], Messages::ERROR_INVALID_DATETIME];
        yield 'null value' => [['datetime_input' => null], Messages::ERROR_DATETIME_MUST_START_AS_STRING];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\BasicDateTime
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            BasicDateTimeFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/datetime_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\BasicDateTime
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new BasicDateTime('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class BasicDateTimeFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicDateTime('datetime_input')]
        public readonly \DateTimeInterface $value,
    ) {
    }
}
