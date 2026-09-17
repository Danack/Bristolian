<?php

namespace Functions;

use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversFunction;

/**
 * @TODO - these tests could really do with some assertions.
 */

#[CoversFunction('createFooterHtml')]
#[CoversFunction('createPageHeaderHtml')]
#[CoversFunction('createPageHtml')]
#[CoversFunction('getPageLayoutHtml')]
#[CoversFunction('share_this_page')]

class SiteHtmlFunctionsTest extends BaseTestCase
{
    public function test_createPageHeaderHtml()
    {
        $result = createPageHeaderHtml();
    }

    public function test_createFooterHtml()
    {
        $result = createFooterHtml();
    }

    public function test_getPageLayoutHtml()
    {
        $extraAssets = new \Bristolian\SiteHtml\ExtraAssets();
        $result = getPageLayoutHtml($extraAssets);
    }

    public function test_createPageHtml()
    {
        $assetLinkConfig = new \Bristolian\Config\HardCodedAssetLinkConfig(true, "abdefg");
        $assetLinkEmitter = new \Bristolian\SiteHtml\AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new \Bristolian\SiteHtml\ExtraAssets();

        $html = "<div>I am great webpage.</div>";

        $result = createPageHtml($assetLinkEmitter, $extraAssets, $html);

        $this->assertStringContainsString('data-widgety-debug-allowed="1"', $result);
    }

    public function test_createPageHtml_disables_widgety_debug_in_production()
    {
        $assetLinkConfig = new \Bristolian\Config\HardCodedAssetLinkConfig(true, "abdefg", true);
        $assetLinkEmitter = new \Bristolian\SiteHtml\AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new \Bristolian\SiteHtml\ExtraAssets();

        $result = createPageHtml($assetLinkEmitter, $extraAssets, "<div>content</div>");

        $this->assertStringContainsString('data-widgety-debug-allowed="0"', $result);
    }

    public function test_share_this_page()
    {
        $_SERVER['HTTP_HOST']  = "www.example.com";
        $_SERVER['REQUEST_URI'] = "/hello";
        $result = share_this_page();
    }
}
