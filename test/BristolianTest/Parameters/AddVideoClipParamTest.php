<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\AddVideoClipParam;
use Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime;
use Bristolian\Parameters\ProcessRule\ParseClipTimestampToSeconds;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\AddVideoClipParam
 */
class AddVideoClipParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string, int, int, string|null, string|null}>
     */
    public static function provides_valid_input(): \Generator
    {
        yield 'clip with title and description' => [
            [
                'url' => 'https://www.youtube.com/watch?v=q84psZX6MbA',
                'start_time' => '1:15',
                'end_time' => '4:15',
                'title' => 'Clip title',
                'description' => 'Clip description',
            ],
            'q84psZX6MbA',
            75,
            255,
            'Clip title',
            'Clip description',
        ];

        yield 'times only without title or description' => [
            [
                'url' => 'https://www.youtube.com/watch?v=q84psZX6MbA',
                'start_time' => '0',
                'end_time' => '60',
            ],
            'q84psZX6MbA',
            0,
            60,
            null,
            null,
        ];
    }

    /**
     * @dataProvider provides_valid_input
     * @param array<string, mixed> $input
     */
    public function test_add_video_clip_param_parses_valid_input(
        array $input,
        string $expected_youtube_video_id,
        int $expected_start_seconds,
        int $expected_end_seconds,
        ?string $expected_title,
        ?string $expected_description
    ): void {
        $param = AddVideoClipParam::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expected_youtube_video_id, $param->youtube_video_id);
        $this->assertSame($expected_start_seconds, $param->start_seconds);
        $this->assertSame($expected_end_seconds, $param->end_seconds);
        $this->assertSame($expected_title, $param->title);
        $this->assertSame($expected_description, $param->description);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input(): \Generator
    {
        yield 'invalid start timestamp' => [
            [
                'url' => 'https://www.youtube.com/watch?v=q84psZX6MbA',
                'start_time' => 'not-a-time',
                'end_time' => '60',
            ],
            '/start_time',
            ParseClipTimestampToSeconds::ERROR_INVALID_TIMESTAMP,
        ];

        yield 'end time not after start time' => [
            [
                'url' => 'https://www.youtube.com/watch?v=q84psZX6MbA',
                'start_time' => '4:15',
                'end_time' => '1:15',
            ],
            '/end_time',
            ClipEndTimeAfterStartTime::ERROR_END_NOT_AFTER_START,
        ];

        yield 'missing url' => [
            [
                'start_time' => '0',
                'end_time' => '60',
            ],
            '/url',
            Messages::VALUE_NOT_SET,
        ];
    }

    /**
     * @dataProvider provides_invalid_input
     * @param array<string, mixed> $input
     */
    public function test_add_video_clip_param_rejects_invalid_input(
        array $input,
        string $expected_error_path,
        string $expected_error_message
    ): void {
        try {
            AddVideoClipParam::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException');
        } catch (\DataType\Exception\ValidationException $exception) {
            $this->assertValidationProblems(
                $exception->getValidationProblems(),
                [$expected_error_path => $expected_error_message]
            );
        }
    }
}
