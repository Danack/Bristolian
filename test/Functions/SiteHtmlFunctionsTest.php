<?php

namespace Functions;

use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 * @TODO - these tests could really do with some assertions.
 */
class SiteHtmlFunctionsTest extends BaseTestCase
{
    /**
     * @covers ::createPageHeaderHtml
     */
    public function test_createPageHeaderHtml()
    {
        $result = createPageHeaderHtml();
    }

    /**
     * @covers ::createFooterHtml
     */
    public function test_createFooterHtml()
    {
        $result = createFooterHtml();
    }

    /**
     * @covers ::getPageLayoutHtml
     */
    public function test_getPageLayoutHtml()
    {
        $extraAssets = new \Bristolian\SiteHtml\ExtraAssets();
        $result = getPageLayoutHtml($extraAssets);
    }

    /**
     * @covers ::createPageHtml
     */
    public function test_createPageHtml()
    {
        $assetLinkConfig = new \Bristolian\Config\HardCodedAssetLinkConfig(true, "abdefg");
        $assetLinkEmitter = new \Bristolian\SiteHtml\AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new \Bristolian\SiteHtml\ExtraAssets();

        $html = "<div>I am great webpage.</div>";

        $result = createPageHtml($assetLinkEmitter, $extraAssets, $html);

        $this->assertStringContainsString('data-widgety-debug-allowed="1"', $result);
    }

    /**
     * @covers ::createPageHtml
     */
    public function test_createPageHtml_disables_widgety_debug_in_production()
    {
        $assetLinkConfig = new \Bristolian\Config\HardCodedAssetLinkConfig(true, "abdefg", true);
        $assetLinkEmitter = new \Bristolian\SiteHtml\AssetLinkEmitter($assetLinkConfig);
        $extraAssets = new \Bristolian\SiteHtml\ExtraAssets();

        $result = createPageHtml($assetLinkEmitter, $extraAssets, "<div>content</div>");

        $this->assertStringContainsString('data-widgety-debug-allowed="0"', $result);
    }


    /**
     * @covers ::share_this_page
     */
    public function test_share_this_page()
    {
        $_SERVER['HTTP_HOST']  = "www.example.com";
        $_SERVER['REQUEST_URI'] = "/hello";
        $result = share_this_page();
    }
}
