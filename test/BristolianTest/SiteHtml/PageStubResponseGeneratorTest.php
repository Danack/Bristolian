<?php

namespace BristolianTest\SiteHtml;

use Bristolian\Config\HardCodedAssetLinkConfig;
use Bristolian\SiteHtml\AssetLinkEmitter;
use Bristolian\SiteHtml\ExtraAssets;
use Bristolian\SiteHtml\PageStubResponseGenerator;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\HtmlResponse;

/**
 * @covers \Bristolian\SiteHtml\PageStubResponseGenerator
 */
class PageStubResponseGeneratorTest extends BaseTestCase
{
    public function testCreate404Page()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageStubResponseGenerator($assetLinkEmitter);
        
        $path = '/some/missing/path';
        $response = $generator->create404Page($extraAssets, $path);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(404, $response->getStatus());
        $this->assertSame('text/html', $response->getHeaders()['Content-Type']);
    }

    public function testCreate404PageContainsPathInformation()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageStubResponseGenerator($assetLinkEmitter);
        
        $path = '/missing/resource';
        $response = $generator->create404Page($extraAssets, $path);
        
        $html = $response->getBody();
        
        // Should contain the path that was not found
        $this->assertStringContainsString($path, $html);
        $this->assertStringContainsString("couldn't find it", $html);
    }

    public function testCreate404PageContains404Message()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageStubResponseGenerator($assetLinkEmitter);
        
        $path = '/test/path';
        $response = $generator->create404Page($extraAssets, $path);
        
        $html = $response->getBody();
        
        // Should contain 404 message
        $this->assertStringContainsString('This is a 404 page', $html);
    }

    public function testCreate404PageIncludesFullHtmlStructure()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageStubResponseGenerator($assetLinkEmitter);
        
        $path = '/test/path';
        $response = $generator->create404Page($extraAssets, $path);
        
        $html = $response->getBody();
        
        // Should be a complete HTML page
        $this->assertStringContainsString('<html', $html);
        $this->assertStringContainsString('</html>', $html);
        $this->assertStringContainsString('<title>', $html);
    }

    public function testCreate404PageWithExtraAssets()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        // Add extra assets
        $extraAssets->addCSS('/css/error-page.css');
        $extraAssets->addJS('/js/error-tracking.js');
        
        $generator = new PageStubResponseGenerator($assetLinkEmitter);
        
        $path = '/test/path';
        $response = $generator->create404Page($extraAssets, $path);
        
        $html = $response->getBody();
        
        // Should include the extra assets
        $this->assertStringContainsString('/css/error-page.css', $html);
        $this->assertStringContainsString('/js/error-tracking.js', $html);
    }

    public function testCreate404PageWithAssetVersioning()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'version456');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageStubResponseGenerator($assetLinkEmitter);
        
        $path = '/test/path';
        $response = $generator->create404Page($extraAssets, $path);
        
        $html = $response->getBody();
        
        // Should contain versioned asset links
        $this->assertStringContainsString('?version=version456', $html);
        $this->assertStringContainsString('/css/site.css', $html);
        $this->assertStringContainsString('/js/app.bundle.js', $html);
    }

    public function testCreate404PageWithSpecialCharactersInPath()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageStubResponseGenerator($assetLinkEmitter);
        
        $path = '/path/with?query=string&special=chars';
        $response = $generator->create404Page($extraAssets, $path);
        
        $html = $response->getBody();
        
        // Path should be present in the output
        $this->assertStringContainsString($path, $html);
    }

    public function testCreate404PageWithEmptyPath()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageStubResponseGenerator($assetLinkEmitter);
        
        $path = '';
        $response = $generator->create404Page($extraAssets, $path);
        
        $this->assertSame(404, $response->getStatus());
        $html = $response->getBody();
        
        // Should still generate a valid 404 page
        $this->assertStringContainsString('This is a 404 page', $html);
    }

    public function testCreate404PageWithRootPath()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageStubResponseGenerator($assetLinkEmitter);
        
        $path = '/';
        $response = $generator->create404Page($extraAssets, $path);
        
        $html = $response->getBody();
        
        $this->assertStringContainsString($path, $html);
        $this->assertSame(404, $response->getStatus());
    }

    public function testCreate404PageWithLongPath()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageStubResponseGenerator($assetLinkEmitter);
        
        $path = '/this/is/a/very/long/path/with/many/segments/that/does/not/exist';
        $response = $generator->create404Page($extraAssets, $path);
        
        $html = $response->getBody();
        
        $this->assertStringContainsString($path, $html);
        $this->assertSame(404, $response->getStatus());
    }
}
