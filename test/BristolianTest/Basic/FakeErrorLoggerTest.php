<?php

namespace BristolianTest\Basic;

use Bristolian\Basic\FakeErrorLogger;

use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Basic\FakeErrorLogger::class)]

class FakeErrorLoggerTest extends BaseTestCase
{
    public function testWorks()
    {
        $message1 = "Hello world!";
        $message2 = "Hello again world!";

        $errorLogger = new FakeErrorLogger();
        $errorLogger->log($message1);
        $errorLogger->log($message2);
        $logLines = $errorLogger->getLogLines();
        $this->assertCount(2, $logLines);

        $this->assertSame($message1, $logLines[0]);
        $this->assertSame($message2, $logLines[1]);
    }
}
