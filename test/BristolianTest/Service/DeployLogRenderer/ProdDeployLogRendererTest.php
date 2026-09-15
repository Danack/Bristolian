<?php

namespace BristolianTest\Service\DeployLogRenderer;

use Bristolian\Service\DeployLogRenderer\ProdDeployLogRenderer;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Service\DeployLogRenderer\ProdDeployLogRenderer::class)]
class ProdDeployLogRendererTest extends BaseTestCase
{
    public function testWorks()
    {
        $renderer = new ProdDeployLogRenderer();
        $result = $renderer->render();

        /* @phpstan-ignore method.alreadyNarrowedType */
        $this->assertIsString($result);
    }
}
