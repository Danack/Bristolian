<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\ProcessRule;


use BristolianTest\BaseTestCase;
use DataType\DataStorage\TestArrayDataStorage;
use DataType\Exception\DataTypeLogicException;
use DataType\ExtractRule\GetStringOrDefault;
use DataType\InputType;
use DataType\ProcessedValue;
use DataType\ProcessedValues;
use Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime;


/**
 * @coversNothing
 */
class ClipEndTimeAfterStartTimeTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime::process
     * @covers \Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime::__construct
     */
    public function test_process_works(): void
    {
        $inputParameter = new InputType(
            'start',
            new GetStringOrDefault('red')
        );

        $inputParameter->setTargetParameterName('start');
        $processedValue = new ProcessedValue($inputParameter, 50);
        $processedValues = ProcessedValues::fromArray([$processedValue]);

        $values = [
            'start' => 50,
            'end' => 60,
        ];

        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue($values);

        $rule = new ClipEndTimeAfterStartTime('start');
        $result = $rule->process(60, $processedValues, $dataStorage);

        $this->assertFalse($result->isFinalResult());
        $this->assertSame(60, $result->getValue());
    }

    /**
     * @covers \Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime::process
     */
    public function test_process_returns_error_missing_previous(): void
    {
        $rule = new ClipEndTimeAfterStartTime('start');
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process('blah', $processedValues, $dataStorage);

        $this->assertTrue($result->isFinalResult());
        $problems = $result->getValidationProblems();
        $this->assertCount(1, $problems);
        $this->assertStringMatchesTemplateString(
            \DataType\Messages::ERROR_NO_PREVIOUS_PARAMETER,
            $problems[0]->getProblemMessage()
        );
    }


    /**
     * @covers \Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime::process
     * @covers \Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime::__construct
     */
    public function test_process_errors(): void
    {
        $inputParameter = new InputType(
            'start',
            new GetStringOrDefault('red')
        );

        $inputParameter->setTargetParameterName('start');
        $processedValue = new ProcessedValue($inputParameter, 50);
        $processedValues = ProcessedValues::fromArray([$processedValue]);

        $values = [
            'start' => 50,
            'end' => 40,
        ];

        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue($values);

        $rule = new ClipEndTimeAfterStartTime('start');
        $result = $rule->process(40, $processedValues, $dataStorage);

        $this->assertTrue($result->isFinalResult());
        $problems = $result->getValidationProblems();
        $this->assertCount(1, $problems);

        $this->assertStringMatchesTemplateString(
            ClipEndTimeAfterStartTime::ERROR_END_NOT_AFTER_START,
            $problems[0]->getProblemMessage()
        );
    }

    /**
     * @covers \Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime::process
     */
    public function test_process_invalid_start_not_integer(): void
    {
        $inputParameter = new InputType(
            'start',
            new GetStringOrDefault('red')
        );
        $inputParameter->setTargetParameterName('start');
        $processedValue = new ProcessedValue($inputParameter, "foo");
        $processedValues = ProcessedValues::fromArray([$processedValue]);

        $values = [
            'start' => 'foo',
            'end' => 60,
        ];
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue($values);
        $rule = new ClipEndTimeAfterStartTime('start');

        $this->expectException(DataTypeLogicException::class);
        $result = $rule->process(60, $processedValues, $dataStorage);
    }





    /**
     * @covers \Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime::process
     */
    public function test_process_invalid_end_not_integer(): void
    {
        $inputParameter = new InputType(
            'start',
            new GetStringOrDefault('red')
        );
        $inputParameter->setTargetParameterName('start');
        $processedValue = new ProcessedValue($inputParameter, 50);
        $processedValues = ProcessedValues::fromArray([$processedValue]);

        $values = [
            'start' => 50,
            'end' => 60,
        ];
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue($values);
        $rule = new ClipEndTimeAfterStartTime('start');

        $this->expectException(DataTypeLogicException::class);
        $result = $rule->process("foo", $processedValues, $dataStorage);

    }





    /**
     * @covers \Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime::updateParamDescription
     */
    public function test_updateParamDescription_sets_description(): void
    {
        $rule = new ClipEndTimeAfterStartTime('start');
        $paramDescription = new \DataType\OpenApi\OpenApiV300ParamDescription('test');

        $rule->updateParamDescription($paramDescription);

        $this->assertSame(
            ClipEndTimeAfterStartTime::DESCRIPTION_TEXT,
            $paramDescription->getDescription()
        );
    }
}
