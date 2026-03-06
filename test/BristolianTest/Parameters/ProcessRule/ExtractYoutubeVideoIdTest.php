<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\ProcessRule;

use Bristolian\Parameters\ProcessRule\ExtractYoutubeVideoId;
use BristolianTest\BaseTestCase;
use DataType\DataStorage\TestArrayDataStorage;
use DataType\ProcessedValues;

/**
 * @coversNothing
 */
class ExtractYoutubeVideoIdTest extends BaseTestCase
{
    public static function provides_valid_youtube_inputs(): \Generator
    {
        yield 'standard watch URL' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'];
        yield 'short URL' => ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'];
        yield 'embed URL' => ['https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'];
        yield 'raw 11-char ID' => ['dQw4w9WgXcQ', 'dQw4w9WgXcQ'];
    }

    /**
     * @covers \Bristolian\Parameters\ProcessRule\ExtractYoutubeVideoId::process
     * @dataProvider provides_valid_youtube_inputs
     */
    public function test_process_extracts_valid_youtube_id(string $input, string $expectedId): void
    {
        $rule = new ExtractYoutubeVideoId();
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process($input, $processedValues, $dataStorage);

        $this->assertFalse($result->isFinalResult());
        $this->assertSame($expectedId, $result->getValue());
    }

    /**
     * @covers \Bristolian\Parameters\ProcessRule\ExtractYoutubeVideoId::process
     */
    public function test_process_returns_error_for_empty_string(): void
    {
        $rule = new ExtractYoutubeVideoId();
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process('', $processedValues, $dataStorage);

        $this->assertTrue($result->isFinalResult());
        $problems = $result->getValidationProblems();
        $this->assertCount(1, $problems);
        $this->assertStringContainsString(
            ExtractYoutubeVideoId::ERROR_INVALID_YOUTUBE_URL,
            $problems[0]->getProblemMessage()
        );
    }

    /**
     * @covers \Bristolian\Parameters\ProcessRule\ExtractYoutubeVideoId::process
     */
    public function test_process_returns_error_for_invalid_url(): void
    {
        $rule = new ExtractYoutubeVideoId();
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process('https://example.com/not-youtube', $processedValues, $dataStorage);

        $this->assertTrue($result->isFinalResult());
        $problems = $result->getValidationProblems();
        $this->assertCount(1, $problems);
        $this->assertStringContainsString(
            ExtractYoutubeVideoId::ERROR_INVALID_YOUTUBE_URL,
            $problems[0]->getProblemMessage()
        );
    }

    /**
     * @covers \Bristolian\Parameters\ProcessRule\ExtractYoutubeVideoId::updateParamDescription
     */
    public function test_updateParamDescription_sets_description(): void
    {
        $rule = new ExtractYoutubeVideoId();
        $paramDescription = new \DataType\OpenApi\OpenApiV300ParamDescription('test');

        $rule->updateParamDescription($paramDescription);

        $this->assertSame('YouTube video URL or 11-character video ID', $paramDescription->getDescription());
    }
}
