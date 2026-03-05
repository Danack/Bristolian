<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\CreateClipParam;
use Bristolian\Parameters\PropertyType\ClipSeconds;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class CreateClipParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string, int, int, string|null, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'required only' => [
            [
                'room_video_id' => '550e8400-e29b-41d4-a716-446655440000',
                'start_seconds' => 0,
                'end_seconds' => 60,
                'title' => '',
                'description' => '',
            ],
            '550e8400-e29b-41d4-a716-446655440000',
            0,
            60,
            null,
            null,
        ];
        yield 'with title and description' => [
            [
                'room_video_id' => '550e8400-e29b-41d4-a716-446655440000',
                'start_seconds' => 10,
                'end_seconds' => 120,
                'title' => 'Clip title',
                'description' => 'Clip description',
            ],
            '550e8400-e29b-41d4-a716-446655440000',
            10,
            120,
            'Clip title',
            'Clip description',
        ];
        yield 'max seconds' => [
            [
                'room_video_id' => '550e8400-e29b-41d4-a716-446655440000',
                'start_seconds' => ClipSeconds::MAX_SECONDS,
                'end_seconds' => ClipSeconds::MAX_SECONDS,
                'title' => '',
                'description' => '',
            ],
            '550e8400-e29b-41d4-a716-446655440000',
            ClipSeconds::MAX_SECONDS,
            ClipSeconds::MAX_SECONDS,
            null,
            null,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\CreateClipParam
     * @covers \Bristolian\Parameters\CreateClipParam::__construct
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        string $expectedRoomVideoId,
        int $expectedStartSeconds,
        int $expectedEndSeconds,
        ?string $expectedTitle,
        ?string $expectedDescription
    ): void {
        $params = CreateClipParam::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedRoomVideoId, $params->room_video_id);
        $this->assertSame($expectedStartSeconds, $params->start_seconds);
        $this->assertSame($expectedEndSeconds, $params->end_seconds);
        $this->assertSame($expectedTitle, $params->title);
        $this->assertSame($expectedDescription, $params->description);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing room_video_id' => [
            ['start_seconds' => 0, 'end_seconds' => 60],
            '/room_video_id',
            Messages::VALUE_NOT_SET,
        ];
        yield 'empty room_video_id' => [
            ['room_video_id' => '', 'start_seconds' => 0, 'end_seconds' => 60],
            '/room_video_id',
            Messages::STRING_TOO_SHORT,
        ];
        yield 'missing start_seconds' => [
            ['room_video_id' => '550e8400-e29b-41d4-a716-446655440000', 'end_seconds' => 60],
            '/start_seconds',
            Messages::VALUE_NOT_SET,
        ];
        yield 'missing end_seconds' => [
            ['room_video_id' => '550e8400-e29b-41d4-a716-446655440000', 'start_seconds' => 0],
            '/end_seconds',
            Messages::VALUE_NOT_SET,
        ];
        yield 'start_seconds negative' => [
            [
                'room_video_id' => '550e8400-e29b-41d4-a716-446655440000',
                'start_seconds' => -1,
                'end_seconds' => 60,
            ],
            '/start_seconds',
            Messages::INT_TOO_SMALL,
        ];
        yield 'end_seconds over max' => [
            [
                'room_video_id' => '550e8400-e29b-41d4-a716-446655440000',
                'start_seconds' => 0,
                'end_seconds' => ClipSeconds::MAX_SECONDS + 1,
            ],
            '/end_seconds',
            Messages::INT_TOO_LARGE,
        ];
        yield 'invalid start_seconds type' => [
            [
                'room_video_id' => '550e8400-e29b-41d4-a716-446655440000',
                'start_seconds' => 'not-a-number',
                'end_seconds' => 60,
            ],
            '/start_seconds',
            Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\CreateClipParam
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(
        array $input,
        string $errorPath,
        string $expectedErrorMessage
    ): void {
        try {
            CreateClipParam::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [$errorPath => $expectedErrorMessage]
            );
        }
    }
}
