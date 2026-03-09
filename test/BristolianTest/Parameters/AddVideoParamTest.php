<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\AddVideoParam;
use Bristolian\Parameters\ProcessRule\ExtractYoutubeVideoId;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class AddVideoParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string, string|null, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        $videoId = 'dQw4w9WgXcQ';
//        yield 'url only' => [
//            [
//                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
//                'description' => null,
//                'title' => null,
//            ],
//            $videoId,
//            null,
//            null,
//        ];
        $title = 'A video title of enough chars';

        yield 'url with title and description' => [
            [
                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'title' => $title,
                'description' => 'Optional description',
            ],
            $videoId,
            $title,
            'Optional description',
        ];
        yield 'url with title only' => [
            [
                'url' => 'https://youtu.be/dQw4w9WgXcQ',
                'title' => $title,
                'description' => null,
            ],
            $videoId,
            $title,
            null,
        ];
//        yield 'raw video id' => [
//            [
//                'url' => 'dQw4w9WgXcQ',
//                'description' => null,
//                'title' => null,
//            ],
//            $videoId,
//            null,
//            null,
//        ];

//        yield 'a live url' => [
//            [
//                'url' => "https://www.youtube.com/live/bJFZlj9nnVU",
//                'description' => null,
//                'title' => null,
//            ],
//            'bJFZlj9nnVU',
//            null,
//            null,
//        ];
    }

    /**
     * @group wip
     * @covers \Bristolian\Parameters\AddVideoParam
     * @covers \Bristolian\Parameters\AddVideoParam::__construct
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        string $expectedYoutubeVideoId,
        ?string $expectedTitle,
        ?string $expectedDescription
    ): void {
        $params = AddVideoParam::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedYoutubeVideoId, $params->youtube_video_id);
        $this->assertSame($expectedTitle, $params->title);
        $this->assertSame($expectedDescription, $params->description);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing url' => [[], '/url', Messages::VALUE_NOT_SET];
        yield 'invalid youtube url' => [['url' => 'not-a-url'], '/url', ExtractYoutubeVideoId::ERROR_INVALID_YOUTUBE_URL];
        yield 'empty url' => [['url' => ''], '/url', Messages::STRING_TOO_SHORT];
        yield 'null url' => [['url' => null], '/url', Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\AddVideoParam
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(
        array $input,
        string $errorPath,
        string $expectedErrorMessage
    ): void {
        try {
            AddVideoParam::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [$errorPath => $expectedErrorMessage]
            );
        }
    }
}
