<?php

namespace BristolianTest\Config;

use BristolianTest\BaseTestCase;
use Bristolian\Config\RedisConfig;

/**
 * @coversNothing
 */
class RedisConfigTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Config\RedisConfig
     */
    public function test_works()
    {
        $host = "1.2.3.4";
        $password = 'password';
        $port = 1234;

        $config = new RedisConfig(
            $host,
            $password,
            $port
        );

        $this->assertSame($host, $config->host);
        $this->assertSame($password, $config->password);
        $this->assertSame($port, $config->port);
    }
}
