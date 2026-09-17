<?php

namespace BristolianTest\Parameters\ProcessRule;

use Bristolian\Parameters\ProcessRule\StringToBoolDefaultTrue;
use BristolianTest\BaseTestCase;
use DataType\DataStorage\TestArrayDataStorage;
use DataType\ProcessedValues;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Parameters\ProcessRule\StringToBoolDefaultTrue::class, 'process')]
#[CoversMethod(\Bristolian\Parameters\ProcessRule\StringToBoolDefaultTrue::class, 'updateParamDescription')]

class StringToBoolDefaultTrueTest extends BaseTestCase
{
    private function createProcessRule(): StringToBoolDefaultTrue
    {
        return new StringToBoolDefaultTrue();
    }

    public function test_null_returns_true(): void
    {
        $rule = $this->createProcessRule();
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process(null, $processedValues, $dataStorage);

        $this->assertFalse($result->isFinalResult());
        $this->assertSame(true, $result->getValue());
    }

    public function test_empty_string_returns_true(): void
    {
        $rule = $this->createProcessRule();
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process('', $processedValues, $dataStorage);

        $this->assertFalse($result->isFinalResult());
        $this->assertSame(true, $result->getValue());
    }

    public static function provides_truthy_values_return_true(): \Generator
    {
        yield 'true lowercase' => ['true'];
        yield 'TRUE uppercase' => ['TRUE'];
        yield 'True mixed case' => ['True'];
        yield '1' => ['1'];
        yield 'yes lowercase' => ['yes'];
        yield 'YES uppercase' => ['YES'];
        yield 'Yes mixed case' => ['Yes'];
    }

    #[DataProvider('provides_truthy_values_return_true')]
    public function test_truthy_values_return_true(string $value): void
    {
        $rule = $this->createProcessRule();
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process($value, $processedValues, $dataStorage);

        $this->assertFalse($result->isFinalResult());
        $this->assertSame(true, $result->getValue());
    }

    public static function provides_falsy_values_return_false(): \Generator
    {
        yield 'false lowercase' => ['false'];
        yield 'FALSE uppercase' => ['FALSE'];
        yield 'False mixed case' => ['False'];
        yield '0' => ['0'];
        yield 'no lowercase' => ['no'];
        yield 'NO uppercase' => ['NO'];
        yield 'No mixed case' => ['No'];
    }

    #[DataProvider('provides_falsy_values_return_false')]
    public function test_falsy_values_return_false(string $value): void
    {
        $rule = $this->createProcessRule();
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process($value, $processedValues, $dataStorage);

        $this->assertFalse($result->isFinalResult());
        $this->assertSame(false, $result->getValue());
    }

    public function test_invalid_value_returns_error(): void
    {
        $rule = $this->createProcessRule();
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process('invalid', $processedValues, $dataStorage);

        $this->assertTrue($result->isFinalResult());
        $problems = $result->getValidationProblems();
        $this->assertCount(1, $problems);
        $this->assertStringContainsString('boolean', $problems[0]->getProblemMessage());
    }

    public function test_whitespace_is_trimmed(): void
    {
        $rule = $this->createProcessRule();
        $processedValues = new ProcessedValues();
        $dataStorage = TestArrayDataStorage::fromArraySetFirstValue([]);

        $result = $rule->process('  true  ', $processedValues, $dataStorage);

        $this->assertFalse($result->isFinalResult());
        $this->assertSame(true, $result->getValue());
    }

    public function test_updateParamDescription_sets_type_and_default(): void
    {
        $rule = $this->createProcessRule();
        $paramDescription = new \DataType\OpenApi\OpenApiV300ParamDescription('test');

        $rule->updateParamDescription($paramDescription);

        $this->assertSame('boolean', $paramDescription->getType());
    }
}
