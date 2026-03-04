<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\RoomVideoId;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class RoomVideoIdTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'uuid' => [['room_video_id_input' => '550e8400-e29b-41d4-a716-446655440000'], '550e8400-e29b-41d4-a716-446655440000'];
        yield 'max length 36' => [['room_video_id_input' => str_repeat('a', RoomVideoId::LENGTH)], str_repeat('a', RoomVideoId::LENGTH)];
        yield 'single char' => [['room_video_id_input' => 'x'], 'x'];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\RoomVideoId
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedValue): void
    {
        $paramTest = RoomVideoIdFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], Messages::VALUE_NOT_SET];
        yield 'empty string' => [['room_video_id_input' => ''], Messages::STRING_TOO_SHORT];
        yield 'too long' => [['room_video_id_input' => str_repeat('a', RoomVideoId::LENGTH + 1)], Messages::STRING_TOO_LONG];
        yield 'null value' => [['room_video_id_input' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\RoomVideoId
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            RoomVideoIdFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/room_video_id_input' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\RoomVideoId
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new RoomVideoId('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class RoomVideoIdFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[RoomVideoId('room_video_id_input')]
        public readonly string $value,
    ) {
    }
}
