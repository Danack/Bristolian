<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\ProcessRule;


use BristolianTest\BaseTestCase;
use DataType\DataStorage\TestArrayDataStorage;
use DataType\ProcessedValues;
use Bristolian\Parameters\ProcessRule\ParseClipTimestampToSeconds;


/**
 * @coversNothing
 */
class ParseClipTimestampToSecondsTest extends BaseTestCase
{
    public static function provides_valid_time_inputs(): \Generator
    {
        yield 'just seconds' => ["30", 30];
        yield 'one colon form' => ['1:15', 75];
        yield 'hms' => ['1:00:00', 3600];
    }

    /**
     * @covers \Bristolian\Parameters\ProcessRule\ParseClipTimestampToSeconds::process
     * @dataProvider provides_valid_time_inputs
     */
    public function test_process_works(string $input, int $expected_seconds): void
    {
        $rule = new ParseClipTimestampToSeconds();
        $processedValues = new ProcessedValues();

        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process($input, $processedValues, $dataStorage);

        $this->assertFalse($result->isFinalResult());
        $this->assertSame($expected_seconds, $result->getValue());
    }

    /**
     * @covers \Bristolian\Parameters\ProcessRule\ParseClipTimestampToSeconds::process
     */
    public function test_process_returns_error_for_empty_string(): void
    {
        $rule = new ParseClipTimestampToSeconds();
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process('blah', $processedValues, $dataStorage);

        $this->assertTrue($result->isFinalResult());
        $problems = $result->getValidationProblems();
        $this->assertCount(1, $problems);
        $this->assertStringContainsString(
            ParseClipTimestampToSeconds::ERROR_INVALID_TIMESTAMP,
            $problems[0]->getProblemMessage()
        );
    }

    /**
     * @covers \Bristolian\Parameters\ProcessRule\ParseClipTimestampToSeconds::updateParamDescription
     */
    public function test_updateParamDescription_sets_description(): void
    {
        $rule = new ParseClipTimestampToSeconds();
        $paramDescription = new \DataType\OpenApi\OpenApiV300ParamDescription('test');

        $rule->updateParamDescription($paramDescription);

        $this->assertSame(
            ParseClipTimestampToSeconds::DESCRIPTION_TEXT,
            $paramDescription->getDescription()
        );
    }
}
