<?php

namespace BristolianTest;

use Bristolian\Config\HardCodedAssetLinkConfig;
use Bristolian\SiteHtml\AssetLinkEmitter;
use Bristolian\SiteHtml\ExtraAssets;
use Bristolian\StringToHtmlPageConverter;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @coversNothing
 */
class StringToHtmlPageConverterTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\StringToHtmlPageConverter
     */
    public function testWorks()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();

        $converter = new StringToHtmlPageConverter($assetLinkEmitter, $extraAssets);

        $content = '<div>Test HTML content</div>';
        $request = new Request();
        $response = new Response();

        $result = $converter->convertStringToHtmlResponse($content, $request, $response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(['text/html'], $result->getHeader('Content-Type'));

        $body = $result->getBody();
        $body->rewind();
        $html = $body->getContents();

        $this->assertStringContainsString($content, $html);
        $this->assertStringContainsString('<html', $html);
        $this->assertStringContainsString('</html>', $html);
    }

    /**
     * @covers \Bristolian\StringToHtmlPageConverter
     */
    public function testWorks_with_extra_assets()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();
        $extraAssets->addCSS('/css/custom.css');
        $extraAssets->addJS('/js/custom.js');

        $converter = new StringToHtmlPageConverter($assetLinkEmitter, $extraAssets);

        $content = '<div>Content with extra assets</div>';
        $request = new Request();
        $response = new Response();

        $result = $converter->convertStringToHtmlResponse($content, $request, $response);

        $body = $result->getBody();
        $body->rewind();
        $html = $body->getContents();

        $this->assertStringContainsString($content, $html);
        $this->assertStringContainsString('/css/custom.css', $html);
        $this->assertStringContainsString('/js/custom.js', $html);
    }

    /**
     * @covers \Bristolian\StringToHtmlPageConverter
     */
    public function testWorks_with_empty_content()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();

        $converter = new StringToHtmlPageConverter($assetLinkEmitter, $extraAssets);

        $content = '';
        $request = new Request();
        $response = new Response();

        $result = $converter->convertStringToHtmlResponse($content, $request, $response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(['text/html'], $result->getHeader('Content-Type'));

        $body = $result->getBody();
        $body->rewind();
        $html = $body->getContents();

        // Should still have HTML structure even with empty content
        $this->assertStringContainsString('<html', $html);
        $this->assertStringContainsString('</html>', $html);
    }

    /**
     * @covers \Bristolian\StringToHtmlPageConverter
     */
    public function testWorks_preserves_existing_response_properties()
    {
        $assetLinkConfig = new HardCodedAssetLinkConfig(false, 'abc123');
        $assetLinkEmitter = new AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new ExtraAssets();

        $converter = new StringToHtmlPageConverter($assetLinkEmitter, $extraAssets);

        $content = '<div>Test content</div>';
        $request = new Request();
        $response = new Response();
        $response = $response->withStatus(200);
        $response = $response->withHeader('X-Custom-Header', 'custom-value');

        $result = $converter->convertStringToHtmlResponse($content, $request, $response);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame(['custom-value'], $result->getHeader('X-Custom-Header'));
        $this->assertSame(['text/html'], $result->getHeader('Content-Type'));
    }
}
