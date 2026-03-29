<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\RoomFileNote;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class RoomFileNoteTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'present' => [['note_input' => 'Longer explanation text'], 'Longer explanation text'];
        yield 'explicit null' => [['note_input' => null], null];
        yield 'empty string' => [['note_input' => ''], null];
        yield 'whitespace only' => [['note_input' => '   '], null];
        yield 'max length' => [
            ['note_input' => str_repeat('n', RoomFileNote::MAXIMUM_LENGTH)],
            str_repeat('n', RoomFileNote::MAXIMUM_LENGTH),
        ];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\RoomFileNote::__construct
     * @covers \Bristolian\Parameters\PropertyType\RoomFileNote::getInputType
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $fixture = RoomFileNoteFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $fixture->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing key' => [[], Messages::VALUE_NOT_SET];
        yield 'too long' => [
            ['note_input' => str_repeat('n', RoomFileNote::MAXIMUM_LENGTH + 1)],
            Messages::STRING_TOO_LONG,
        ];
        yield 'wrong type' => [['note_input' => 99.5], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\RoomFileNote::getInputType
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            RoomFileNoteFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $exception) {
            $this->assertValidationProblems(
                $exception->getValidationProblems(),
                ['/note_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\RoomFileNote::getInputType
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new RoomFileNote('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class RoomFileNoteFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[RoomFileNote('note_input')]
        public readonly ?string $value,
    ) {
    }
}
