<?php

namespace BristolianTest\SiteHtml;

use Bristolian\Config\HardCodedAssetLinkConfig;
use Bristolian\SiteHtml\AssetLinkEmitter;
use Bristolian\SiteHtml\ExtraAssets;
use Bristolian\SiteHtml\PageResponseGenerator;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ResponseFactory;

/**
 * @covers \Bristolian\SiteHtml\PageResponseGenerator
 */
class PageResponseGeneratorTest extends BaseTestCase
{
    public function testCreatePageWithStatusCode200()
    {
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageResponseGenerator(
            $responseFactory,
            $assetLinkEmitter,
            $extraAssets
        );
        
        $contentHtml = '<div>Test content</div>';
        $response = $generator->createPageWithStatusCode($contentHtml, 200);
        
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['text/html'], $response->getHeader('Content-Type'));
        
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();
        
        $this->assertStringContainsString($contentHtml, $html);
        $this->assertStringContainsString('<html', $html);
        $this->assertStringContainsString('</html>', $html);
    }

    public function testCreatePageWithStatusCode404()
    {
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'xyz789');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageResponseGenerator(
            $responseFactory,
            $assetLinkEmitter,
            $extraAssets
        );
        
        $contentHtml = '<p>Page not found</p>';
        $response = $generator->createPageWithStatusCode($contentHtml, 404);
        
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(['text/html'], $response->getHeader('Content-Type'));
        
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();
        
        $this->assertStringContainsString($contentHtml, $html);
    }

    public function testCreatePageWithStatusCode500()
    {
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'def456');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageResponseGenerator(
            $responseFactory,
            $assetLinkEmitter,
            $extraAssets
        );
        
        $contentHtml = '<p>Internal server error</p>';
        $response = $generator->createPageWithStatusCode($contentHtml, 500);
        
        $this->assertSame(500, $response->getStatusCode());
    }

    public function testCreatePageIncludesAssetLinks()
    {
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'version123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageResponseGenerator(
            $responseFactory,
            $assetLinkEmitter,
            $extraAssets
        );
        
        $contentHtml = '<div>Content</div>';
        $response = $generator->createPageWithStatusCode($contentHtml, 200);
        
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();
        
        // Should contain versioned asset links
        $this->assertStringContainsString('?version=version123', $html);
        $this->assertStringContainsString('/css/site.css', $html);
        $this->assertStringContainsString('/js/app.bundle.js', $html);
    }

    public function testCreatePageWithForceAssetRefresh()
    {
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(true, 'ignored_sha');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageResponseGenerator(
            $responseFactory,
            $assetLinkEmitter,
            $extraAssets
        );
        
        $contentHtml = '<div>Content</div>';
        $response = $generator->createPageWithStatusCode($contentHtml, 200);
        
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();
        
        // Should contain time-based cache busting
        $this->assertStringContainsString('?time=', $html);
    }

    public function testCreatePageWithExtraAssets()
    {
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        // Add extra CSS and JS
        $extraAssets->addCSS('/css/custom.css');
        $extraAssets->addJS('/js/custom.js');
        
        $generator = new PageResponseGenerator(
            $responseFactory,
            $assetLinkEmitter,
            $extraAssets
        );
        
        $contentHtml = '<div>Content with extra assets</div>';
        $response = $generator->createPageWithStatusCode($contentHtml, 200);
        
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();
        
        // Should contain extra assets
        $this->assertStringContainsString('/css/custom.css', $html);
        $this->assertStringContainsString('/js/custom.js', $html);
    }

    public function testCreatePageWithEmptyContent()
    {
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageResponseGenerator(
            $responseFactory,
            $assetLinkEmitter,
            $extraAssets
        );
        
        $response = $generator->createPageWithStatusCode('', 200);
        
        $this->assertSame(200, $response->getStatusCode());
        
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();
        
        // Should still have HTML structure even with empty content
        $this->assertStringContainsString('<html', $html);
        $this->assertStringContainsString('</html>', $html);
    }

    public function testCreatePageContainsPageTitle()
    {
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageResponseGenerator(
            $responseFactory,
            $assetLinkEmitter,
            $extraAssets
        );
        
        $contentHtml = '<div>Test</div>';
        $response = $generator->createPageWithStatusCode($contentHtml, 200);
        
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();
        
        // Should contain page title
        $this->assertStringContainsString('Bristolian', $html);
        $this->assertStringContainsString('<title>', $html);
    }

    public function testCreatePageWithSpecialCharacters()
    {
        $responseFactory = new ResponseFactory();
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        
        $generator = new PageResponseGenerator(
            $responseFactory,
            $assetLinkEmitter,
            $extraAssets
        );
        
        $contentHtml = '<div>Test &amp; special chars: &lt;script&gt;</div>';
        $response = $generator->createPageWithStatusCode($contentHtml, 200);
        
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();
        
        $this->assertStringContainsString($contentHtml, $html);
    }
}

