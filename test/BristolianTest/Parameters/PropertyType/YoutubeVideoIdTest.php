<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\ProcessRule\ExtractYoutubeVideoId;
use Bristolian\Parameters\PropertyType\YoutubeVideoId;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class YoutubeVideoIdTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'standard watch URL' => [
            ['video_id' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            'dQw4w9WgXcQ',
        ];
        yield 'short URL' => [
            ['video_id' => 'https://youtu.be/dQw4w9WgXcQ'],
            'dQw4w9WgXcQ',
        ];
        yield 'embed URL' => [
            ['video_id' => 'https://www.youtube.com/embed/dQw4w9WgXcQ'],
            'dQw4w9WgXcQ',
        ];
        yield 'raw 11-char ID' => [
            ['video_id' => 'dQw4w9WgXcQ'],
            'dQw4w9WgXcQ',
        ];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\YoutubeVideoId::__construct
     * @covers \Bristolian\Parameters\PropertyType\YoutubeVideoId::getInputType
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, string $expectedId): void
    {
        $params = YoutubeVideoIdFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedId, $params->video_id);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing required' => [[], '/video_id', Messages::VALUE_NOT_SET];
        yield 'null value' => [['video_id' => null], '/video_id', Messages::STRING_EXPECTED];
        yield 'empty string' => [['video_id' => ''], '/video_id', Messages::STRING_TOO_SHORT];
        yield 'invalid url' => [
            ['video_id' => 'https://example.com/not-youtube'],
            '/video_id',
            ExtractYoutubeVideoId::ERROR_INVALID_YOUTUBE_URL,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\YoutubeVideoId::__construct
     * @covers \Bristolian\Parameters\PropertyType\YoutubeVideoId::getInputType
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(
        array $input,
        string $errorPath,
        string $expectedErrorMessage
    ): void {
        try {
            YoutubeVideoIdFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [$errorPath => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\YoutubeVideoId::getInputType
     */
    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new YoutubeVideoId('test_name');
        $this->assertSame('test_name', $propertyType->getInputType()->getName());
    }
}

class YoutubeVideoIdFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[YoutubeVideoId('video_id')]
        public readonly string $video_id,
    ) {
    }
}
