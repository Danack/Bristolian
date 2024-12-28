<?php

namespace BristolianTest\DataType;

use BristolianTest\BaseTestCase;
use Bristolian\DataType\DebugParam;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\DataType\DebugParam
 */
class DebugParamTest extends BaseTestCase
{
    public function testWorks()
    {
        $message = 'Hello there.';
        $detail = 'this is some detail';

        $params = [
            'message' => $message,
            'detail' => $detail,
        ];

        $debugParam = DebugParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($message, $debugParam->message);
        $this->assertSame($detail, $debugParam->detail);
    }
}
