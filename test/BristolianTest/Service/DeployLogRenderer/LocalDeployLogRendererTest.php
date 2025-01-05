<?php

namespace BristolianTest\Service\DeployLogRenderer;

use Bristolian\Service\DeployLogRenderer\LocalDeployLogRenderer;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Service\DeployLogRenderer\LocalDeployLogRenderer
 */
class LocalDeployLogRendererTest extends BaseTestCase
{
    public function testWorks()
    {
        $renderer = new LocalDeployLogRenderer();

        $result = $renderer->render();
        $this->assertIsString($result);
        $this->assertStringContainsString("there is no log", $result);
    }
}
