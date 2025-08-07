<?php

namespace BristolianTest\DataType;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\DebugParams;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\DebugParams
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

        $debugParam = DebugParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($message, $debugParam->message);
        $this->assertSame($detail, $debugParam->detail);
    }
}
