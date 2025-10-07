<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\ProcessorRunRecordTypeParam;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\ProcessorRunRecordTypeParam
 */
class ProcessorRunRecordTypeParamTest extends BaseTestCase
{
    public function testWorks()
    {
        $task_type = 'email_send';

        $params = [
            'task_type' => $task_type,
        ];

        $processorRunRecordTypeParam = ProcessorRunRecordTypeParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($task_type, $processorRunRecordTypeParam->task_type);
    }

    public function testWorksWithNoOptionalParameters()
    {
        $params = [];

        $processorRunRecordTypeParam = ProcessorRunRecordTypeParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertNull($processorRunRecordTypeParam->task_type);
    }


    public function testFailsWithInvalidEnumValue()
    {
        try {
            $value = "This isn't valid";

            $params = [
                'task_type' => $value,
            ];

            ProcessorRunRecordTypeParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/task_type' => Messages::ENUM_MAP_UNRECOGNISED_VALUE_SINGLE]
            );
        }
    }

    public function testFailsWithNullValue()
    {
        try {
            $params = [
                'task_type' => null,
            ];

            ProcessorRunRecordTypeParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/task_type' => Messages::STRING_EXPECTED]
            );
        }
    }

    public function testImplementsDataTypeInterface()
    {
        $params = [];

        $processorRunRecordTypeParam = ProcessorRunRecordTypeParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertInstanceOf(\DataType\DataType::class, $processorRunRecordTypeParam);
    }
}
