<?php

namespace BristolianTest\Config;

use Bristolian\Config\HardCodedEnvironmentName;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Config\HardCodedEnvironmentName::class)]
class HardCodedEnvironmentNameTest extends BaseTestCase
{
    public function test_returns_environment_name_for_email_subject(): void
    {
        $envName = 'staging';
        $config = new HardCodedEnvironmentName($envName);

        $this->assertSame($envName, $config->getEnvironmentNameForEmailSubject());
    }

    public function test_constructor_stores_env_name(): void
    {
        $envName = 'production';
        $config = new HardCodedEnvironmentName($envName);

        $this->assertSame($envName, $config->env_name);
    }
}
