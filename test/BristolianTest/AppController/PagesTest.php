<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Pages;
use Bristolian\MarkdownRenderer\CommonMarkRenderer;
use Bristolian\MarkdownRenderer\MarkdownRenderer;
use Bristolian\SiteHtml\AssetLinkEmitter;
use Bristolian\SiteHtml\ExtraAssets;
use Bristolian\SiteHtml\PageStubResponseGenerator;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ServerRequestInterface as Request;
use SlimDispatcher\Response\StubResponse;

/**
 * @coversNothing
 */
class PagesTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(MarkdownRenderer::class, CommonMarkRenderer::class);
    }

    /**
     * @covers \Bristolian\AppController\Pages::index
     */
    public function test_index(): void
    {
        $result = $this->injector->execute([Pages::class, 'index']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Absolute alpha', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::homepage
     */
    public function test_homepage(): void
    {
        $result = $this->injector->execute([Pages::class, 'homepage']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::get404Page
     */
    public function test_get404Page(): void
    {
        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: 'https://example.com/not/found/path',
            method: 'GET',
        );
        $extraAssets = new ExtraAssets();
        $assetLinkEmitter = new AssetLinkEmitter(new \Bristolian\Config\HardCodedAssetLinkConfig(false, 'test'));
        $pageStubResponseGenerator = new PageStubResponseGenerator($assetLinkEmitter);

        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);
        $this->injector->share($extraAssets);
        $this->injector->share($pageStubResponseGenerator);

        $result = $this->injector->execute([Pages::class, 'get404Page']);

        $this->assertInstanceOf(StubResponse::class, $result);
        $this->assertStringContainsString('/not/found/path', $result->getBody());
        $this->assertSame(404, $result->getStatus());
    }

    /**
     * @covers \Bristolian\AppController\Pages::about
     */
    public function test_about(): void
    {
        $result = $this->injector->execute([Pages::class, 'about']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::experimental
     */
    public function test_experimental(): void
    {
        $result = $this->injector->execute([Pages::class, 'experimental']);
        $this->assertIsString($result);
        $this->assertStringContainsString('notification_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::experimental_debug_param
     */
    public function test_experimental_debug_param(): void
    {
        $params = \Bristolian\Parameters\DebugParams::createFromVarMap(
            new \VarMap\ArrayVarMap([
                'message' => 'test message',
                'detail' => 'some detail',
            ])
        );
        $this->injector->share($params);

        $result = $this->injector->execute([Pages::class, 'experimental_debug_param']);
        $this->assertIsString($result);
        $this->assertStringContainsString('test message', $result);
    }
}
