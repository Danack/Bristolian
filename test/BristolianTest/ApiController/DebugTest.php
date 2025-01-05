<?php

namespace BristolianTest\ApiController;

use Bristolian\Exception\DebuggingCaughtException;
use Bristolian\Exception\DebuggingUncaughtException;
use BristolianTest\BaseTestCase;
use Bristolian\ApiController\Debug;
use SlimDispatcher\Response\JsonResponse;

/**
 * @covers \Bristolian\ApiController\Debug
 */
class DebugTest extends BaseTestCase
{
    public function testWorks()
    {
        $debug = new Debug();
        $this->expectException(\Bristolian\Exception\DebuggingCaughtException::class);
        $debug->testCaughtException();
    }


    public function testUncaughtException(): never
    {
        $debug = new Debug();
        $this->expectException(\Bristolian\Exception\DebuggingUncaughtException::class);
        $debug->testUncaughtException();
    }

    /**
     * @group xdebug
     */
    public function testXdebugWorking(): void
    {
        $debug = new Debug();
        $result = $debug->testXdebugWorking();

        $this->assertInstanceOf(JsonResponse::class, $result);
        $json = $result->getBody();
        $data = json_decode($json, true);
        if (function_exists('xdebug_break') === true) {
            $this->assertSame(['status' => 'ok'], $data);
        }
        else {
            $expected = [
                'status' => "xdebug_break isn't a function. Are you on the xdebug port?"
            ];
            $this->assertSame($expected, $data);
        }
    }
}
