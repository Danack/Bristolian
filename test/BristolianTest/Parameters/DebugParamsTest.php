<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\DebugParams;
use DataType\Messages;
use VarMap\ArrayVarMap;
use DataType\Exception\ValidationException;

/**
 * @covers \Bristolian\Parameters\DebugParams
 */
class DebugParamsTest extends BaseTestCase
{
    public function testWorks()
    {
        $message = 'Hello there.';
        $detail = 'this is some detail';

        $params = [
            'message' => $message,
            'detail' => $detail,
        ];

        $debugParam = DebugParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($message, $debugParam->message);
        $this->assertSame($detail, $debugParam->detail);
    }

    public function testFailsWithMissingMessage()
    {
        try {
            $params = [
                'detail' => 'some detail',
            ];

            DebugParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/message' => \DataType\Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingDetail()
    {
        try {
            $params = [
                'message' => 'some message',
            ];

            DebugParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/detail' => \DataType\Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithBothMissing()
    {
        try {
            $params = [];

            DebugParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [
                    '/message' => \DataType\Messages::VALUE_NOT_SET,
                    '/detail' => \DataType\Messages::VALUE_NOT_SET
                ]
            );
        }
    }

    public function testFailsWithInvalidDataTypes()
    {
        try {
            $params = [
                'message' => 123,
                'detail' => 456,
            ];

            DebugParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [
                    '/message' => \DataType\Messages::STRING_EXPECTED,
                    '/detail' => \DataType\Messages::STRING_EXPECTED
                ]
            );
        }
    }

    public function testFailsWithNullValues()
    {
        try {
            $params = [
                'message' => null,
                'detail' => null,
            ];

            DebugParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [
                    '/message' => \DataType\Messages::STRING_EXPECTED,
                    '/detail' => \DataType\Messages::STRING_EXPECTED
                ]
            );
        }
    }

    public function testImplementsDataTypeInterface()
    {
        $message = 'test message';
        $detail = 'test detail';

        $params = [
            'message' => $message,
            'detail' => $detail,
        ];

        $debugParam = DebugParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertInstanceOf(\DataType\DataType::class, $debugParam);
    }

    public function testImplementsStaticFactoryInterface()
    {
        $message = 'test message';
        $detail = 'test detail';

        $params = [
            'message' => $message,
            'detail' => $detail,
        ];

        $debugParam = DebugParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertInstanceOf(\Bristolian\StaticFactory::class, $debugParam);
    }
}
