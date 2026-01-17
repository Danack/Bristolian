<?php
declare(strict_types=1);

namespace BristolianTest\DataType\ProcessRule;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\ProcessRule\PhpEnum;
use DataType\DataStorage\TestArrayDataStorage;
use DataType\Messages;
use DataType\ProcessedValues;

/**
 * @coversNothing
 */
class PhpEnumTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Parameters\ProcessRule\PhpEnum
     */
    public function testValidationWorks()
    {
        $testEnum = FixtureEnum::APPLES;

        $rule = new PhpEnum(FixtureEnum::class);

        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);
        $processedValues = new ProcessedValues();
        $validationResult = $rule->process(
            FixtureEnum::APPLES->value, $processedValues, $dataStorage
        );
        $this->assertNoProblems($validationResult);
        $this->assertSame($validationResult->getValue(), $testEnum);

        $invalid_case = 'not valid';

        $validationResult = $rule->process(
            $invalid_case, $processedValues, $dataStorage
        );

        $this->assertValidationProblems(
            $validationResult->getValidationProblems(),
            ['/' => Messages::ENUM_MAP_UNRECOGNISED_VALUE_SINGLE]
        );

        $this->assertValidationProblemContains(
            '/',
            $invalid_case,
            $validationResult->getValidationProblems()
        );

        $enum_values = getEnumCaseValues(FixtureEnum::class);
        foreach ($enum_values as $enum_value) {
            $this->assertValidationProblemContains(
                '/',
                $enum_value,
                $validationResult->getValidationProblems()
            );
        }
    }


    /**
     * @covers \Bristolian\Parameters\ProcessRule\PhpEnum
     */
    public function testDescription()
    {
        $rule = new PhpEnum(FixtureEnum::class);
        $description = $this->applyRuleToDescription($rule);
    }
}
