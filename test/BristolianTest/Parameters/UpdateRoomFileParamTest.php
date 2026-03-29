<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\PropertyType\RoomFileDescription;
use Bristolian\Parameters\PropertyType\RoomFileNote;
use Bristolian\Parameters\UpdateRoomFileParam;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class UpdateRoomFileParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null, string|null, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'all fields' => [
            [
                'description' => 'File label',
                'note' => 'Longer note text',
                'document_timestamp' => '2024-06-01T12:00:00Z',
            ],
            'File label',
            'Longer note text',
            '2024-06-01T12:00:00Z',
        ];
        yield 'description and note only' => [
            [
                'description' => 'Only description',
                'note' => null,
            ],
            'Only description',
            null,
            null,
        ];
        yield 'explicit nulls no timestamp key' => [
            [
                'description' => null,
                'note' => null,
            ],
            null,
            null,
            null,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomFileParam
     * @covers \Bristolian\Parameters\UpdateRoomFileParam::__construct
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_createFromArray_parses_to_expected_values(
        array $input,
        ?string $expectedDescription,
        ?string $expectedNote,
        ?string $expectedDocumentTimestamp
    ): void {
        $params = UpdateRoomFileParam::createFromArray($input);

        $this->assertSame($expectedDescription, $params->description);
        $this->assertSame($expectedNote, $params->note);
        $this->assertSame($expectedDocumentTimestamp, $params->document_timestamp);
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomFileParam
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_createFromVarMap_parses_to_expected_values(
        array $input,
        ?string $expectedDescription,
        ?string $expectedNote,
        ?string $expectedDocumentTimestamp
    ): void {
        $params = UpdateRoomFileParam::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedDescription, $params->description);
        $this->assertSame($expectedNote, $params->note);
        $this->assertSame($expectedDocumentTimestamp, $params->document_timestamp);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'description too long' => [
            [
                'description' => str_repeat('d', RoomFileDescription::MAXIMUM_LENGTH + 1),
                'note' => null,
            ],
            '/description',
            Messages::STRING_TOO_LONG,
        ];
        yield 'note too long' => [
            [
                'description' => null,
                'note' => str_repeat('n', RoomFileNote::MAXIMUM_LENGTH + 1),
            ],
            '/note',
            Messages::STRING_TOO_LONG,
        ];
        yield 'document_timestamp wrong type' => [
            [
                'description' => null,
                'note' => null,
                'document_timestamp' => false,
            ],
            '/document_timestamp',
            Messages::STRING_EXPECTED,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomFileParam
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_createFromArray_rejects_invalid_input(
        array $input,
        string $expectedPath,
        string $expectedMessage
    ): void {
        try {
            UpdateRoomFileParam::createFromArray($input);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $exception) {
            $this->assertValidationProblems(
                $exception->getValidationProblems(),
                [$expectedPath => $expectedMessage]
            );
        }
    }
}
