<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\FoiRequests;
use Bristolian\Repo\FoiRequestRepo\FoiRequestRepo;
use Bristolian\Repo\FoiRequestRepo\FakeFoiRequestRepo;
use Bristolian\Parameters\FoiRequestParams;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\RedirectResponse;
use VarMap\ArrayVarMap;
use VarMap\VarMap;

/**
 * @coversNothing
 */
class FoiRequestsTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(FoiRequestRepo::class, FakeFoiRequestRepo::class);
        $this->injector->share(FakeFoiRequestRepo::class);
    }

    /**
     * @covers \Bristolian\AppController\FoiRequests::view
     */
    public function test_view_with_no_requests_returns_message(): void
    {
        $result = $this->injector->execute([FoiRequests::class, 'view']);
        $this->assertIsString($result);
        $this->assertSame('No FOI requests created on system yet.', $result);
    }

    /**
     * @covers \Bristolian\AppController\FoiRequests::view
     */
    public function test_view_with_requests(): void
    {
        $repo = $this->injector->make(FakeFoiRequestRepo::class);
        $param = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => 'Test FOI request',
            'url' => 'https://example.com/foi',
            'description' => 'A test request',
        ]));
        $repo->createFoiRequest($param);

        $result = $this->injector->execute([FoiRequests::class, 'view']);
        $this->assertIsString($result);
        $this->assertStringContainsString('FOI requests', $result);
        $this->assertStringContainsString('Test FOI request', $result);
    }

    /**
     * @covers \Bristolian\AppController\FoiRequests::process_add
     */
    public function test_process_add(): void
    {
        $varMap = new ArrayVarMap([
            'text' => 'New FOI request',
            'url' => 'https://example.com/new-foi',
            'description' => 'A new request',
        ]);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);

        $result = $this->injector->execute([FoiRequests::class, 'process_add']);
        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\FoiRequests::edit
     */
    public function test_edit(): void
    {
        $result = $this->injector->execute([FoiRequests::class, 'edit']);
        $this->assertIsString($result);
        $this->assertStringContainsString('FOI Request editing page', $result);
    }
}
