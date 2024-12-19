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
    function test_createPageHeaderHtml()
    {
        $result = createPageHeaderHtml();
    }

    /**
     * @covers ::createFooterHtml
     */
    function test_createFooterHtml()
    {
        $result = createFooterHtml();
    }

    /**
     * @covers ::getPageLayoutHtml
     */
    function test_getPageLayoutHtml()
    {
        $result = getPageLayoutHtml();
    }

    /**
     * @covers ::createPageHtml
     */
    function test_createPageHtml()
    {
        $assetLinkConfig = new \Bristolian\Config\HardCodedAssetLinkConfig(true, "abdefg");
        $assetLinkEmitter = new \Bristolian\AssetLinkEmitter($assetLinkConfig);

        $html = "<div>I am great webpage.</div>";

        $result = createPageHtml($assetLinkEmitter, $html);
    }


    /**
     * @covers ::share_this_page
     */
    function test_share_this_page()
    {
        $_SERVER['HTTP_HOST']  = "www.example.com";
        $_SERVER['REQUEST_URI'] = "/hello";
        $result = share_this_page();
    }
}
