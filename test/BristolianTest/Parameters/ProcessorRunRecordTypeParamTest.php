<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\ProcessorRunRecordTypeParam;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class ProcessorRunRecordTypeParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, ProcessType|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'with value' => [['task_type' => 'email_send'], ProcessType::email_send];
        yield 'no optional' => [[], null];
    }

    /**
     * @covers \Bristolian\Parameters\ProcessorRunRecordTypeParam
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, ?ProcessType $expectedTaskType): void
    {
        $params = ProcessorRunRecordTypeParam::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedTaskType, $params->task_type);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'invalid enum' => [['task_type' => "This isn't valid"], Messages::ENUM_MAP_UNRECOGNISED_VALUE_SINGLE];
        yield 'null value' => [['task_type' => null], Messages::STRING_EXPECTED];
    }

    /**
     * @covers \Bristolian\Parameters\ProcessorRunRecordTypeParam
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            ProcessorRunRecordTypeParam::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/task_type' => $expectedErrorMessage]
            );
        }
    }
}
