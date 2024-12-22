<?php
declare(strict_types=1);

namespace BristolianTest\DataType\ProcessRule;

use BristolianTest\BaseTestCase;
use Bristolian\DataType\ProcessRule\RandomPasswordIfNullOrEmpty;
use DataType\DataStorage\TestArrayDataStorage;
use DataType\ProcessedValues;

/**
 * @coversNothing
 */
class RandomPasswordIfNullOrEmptyTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\DataType\ProcessRule\RandomPasswordIfNullOrEmpty
     */
    public function testValidationWorks()
    {
        $testValue = 'password12345';

        $rule = new RandomPasswordIfNullOrEmpty(8);
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);
        $processedValues = new ProcessedValues();
        $validationResult = $rule->process(
            'password12345', $processedValues, $dataStorage
        );
        $this->assertNoProblems($validationResult);
        $this->assertSame($validationResult->getValue(), $testValue);


        $validationResult = $rule->process(
            null, $processedValues, $dataStorage
        );
        $this->assertNoProblems($validationResult);
        $this->assertGreaterThanOrEqual(8, strlen($validationResult->getValue()));

        $validationResult = $rule->process(
            '', $processedValues, $dataStorage
        );
        $this->assertNoProblems($validationResult);
        $this->assertGreaterThanOrEqual(8, strlen($validationResult->getValue()));
    }


    /**
     * @covers \Bristolian\DataType\ProcessRule\RandomPasswordIfNullOrEmpty
     */
    public function testDescription()
    {
        $rule = new RandomPasswordIfNullOrEmpty(8);
        $description = $this->applyRuleToDescription($rule);
    }
}
