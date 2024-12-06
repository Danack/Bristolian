<?php

namespace BristolianTest\AppErrorHandler;

use Bristolian\Basic\FakeErrorLogger;
use BristolianTest\BaseTestCase;
use Bristolian\AppErrorHandler\HtmlErrorHandlerForLocalDev;
use Bristolian\Config\HardCodedAssetLinkConfig;
use Bristolian\AssetLinkEmitter;

/**
 * @coversNothing
 */
class HtmlErrorHandlerForLocalDevTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Config\RedisConfig
     */
    public function test_works()
    {
        $config = new HardCodedAssetLinkConfig(false, "12345");
        $link_emitter = new AssetLinkEmitter($config);
        $errorLogger = new FakeErrorLogger();

        $error_handler = new HtmlErrorHandlerForLocalDev(
            $link_emitter,
            $errorLogger
        );

        $callable = $error_handler('foo');

        $this->markTestSkipped("This needs testing.");
        //  $callable($request, $response, $exception);
    }
}
