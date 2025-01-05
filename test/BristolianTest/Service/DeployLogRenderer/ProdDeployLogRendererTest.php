<?php

namespace BristolianTest\Service\DeployLogRenderer;

use Bristolian\Service\DeployLogRenderer\ProdDeployLogRenderer;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Service\DeployLogRenderer\ProdDeployLogRenderer
 */
class ProdDeployLogRendererTest extends BaseTestCase
{
    public function testWorks()
    {
        $renderer = new ProdDeployLogRenderer();
        $result = $renderer->render();
        $this->assertIsString($result);
    }
}
