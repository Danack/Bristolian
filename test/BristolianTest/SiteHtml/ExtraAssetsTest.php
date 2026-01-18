<?php

namespace BristolianTest\SiteHtml;

use Bristolian\SiteHtml\ExtraAssets;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\SiteHtml\ExtraAssets
 */
class ExtraAssetsTest extends BaseTestCase
{
    public function testAddCSS()
    {
        $extraAssets = new ExtraAssets();
        $cssFile = '/css/style.css';
        
        $extraAssets->addCSS($cssFile);
        
        $html = $extraAssets->getHTML();
        $this->assertStringContainsString($cssFile, $html);
        $this->assertStringContainsString("<link rel='stylesheet' href='/css/style.css' />", $html);
    }

    public function testAddJS()
    {
        $extraAssets = new ExtraAssets();
        $jsFile = '/js/app.js';
        
        $extraAssets->addJS($jsFile);
        
        $html = $extraAssets->getHTML();
        $this->assertStringContainsString($jsFile, $html);
        $this->assertStringContainsString("<script src='/js/app.js'></script>", $html);
    }

    public function testGetHTMLReturnsEmptyStringWhenNoAssets()
    {
        $extraAssets = new ExtraAssets();
        
        $html = $extraAssets->getHTML();
        
        $this->assertSame('', $html);
    }

    public function testAddMultipleCSS()
    {
        $extraAssets = new ExtraAssets();
        
        $extraAssets->addCSS('/css/style1.css');
        $extraAssets->addCSS('/css/style2.css');
        $extraAssets->addCSS('/css/style3.css');
        
        $html = $extraAssets->getHTML();
        
        $this->assertStringContainsString("<link rel='stylesheet' href='/css/style1.css' />", $html);
        $this->assertStringContainsString("<link rel='stylesheet' href='/css/style2.css' />", $html);
        $this->assertStringContainsString("<link rel='stylesheet' href='/css/style3.css' />", $html);
    }

    public function testAddMultipleJS()
    {
        $extraAssets = new ExtraAssets();
        
        $extraAssets->addJS('/js/app1.js');
        $extraAssets->addJS('/js/app2.js');
        $extraAssets->addJS('/js/app3.js');
        
        $html = $extraAssets->getHTML();
        
        $this->assertStringContainsString("<script src='/js/app1.js'></script>", $html);
        $this->assertStringContainsString("<script src='/js/app2.js'></script>", $html);
        $this->assertStringContainsString("<script src='/js/app3.js'></script>", $html);
    }

    public function testGetHTMLReturnsCSSThenJS()
    {
        $extraAssets = new ExtraAssets();
        
        $extraAssets->addCSS('/css/style.css');
        $extraAssets->addJS('/js/app.js');
        
        $html = $extraAssets->getHTML();
        
        // CSS should appear before JS
        $cssPosition = strpos($html, "<link rel='stylesheet' href='/css/style.css' />");
        $jsPosition = strpos($html, "<script src='/js/app.js'></script>");
        
        $this->assertNotFalse($cssPosition);
        $this->assertNotFalse($jsPosition);
        $this->assertLessThan($jsPosition, $cssPosition, 'CSS should appear before JS in output');
    }

    public function testGetHTMLMaintainsOrderOfMultipleAssets()
    {
        $extraAssets = new ExtraAssets();
        
        $extraAssets->addCSS('/css/reset.css');
        $extraAssets->addCSS('/css/main.css');
        $extraAssets->addJS('/js/vendor.js');
        $extraAssets->addJS('/js/app.js');
        
        $html = $extraAssets->getHTML();
        
        // Check CSS order
        $reset = strpos($html, "/css/reset.css");
        $main = strpos($html, "/css/main.css");
        $this->assertLessThan($main, $reset, 'CSS files should maintain insertion order');
        
        // Check JS order
        $vendor = strpos($html, "/js/vendor.js");
        $app = strpos($html, "/js/app.js");
        $this->assertLessThan($app, $vendor, 'JS files should maintain insertion order');
    }

    public function testGetHTMLWithSpecialCharactersInPaths()
    {
        $extraAssets = new ExtraAssets();
        
        $extraAssets->addCSS('/css/style.css?v=1.0.0');
        $extraAssets->addJS('/js/app.js?v=2.0.0&debug=true');
        
        $html = $extraAssets->getHTML();
        
        $this->assertStringContainsString("/css/style.css?v=1.0.0", $html);
        $this->assertStringContainsString("/js/app.js?v=2.0.0&debug=true", $html);
    }

    public function testGetHTMLWithAbsoluteUrls()
    {
        $extraAssets = new ExtraAssets();
        
        $extraAssets->addCSS('https://cdn.example.com/style.css');
        $extraAssets->addJS('https://cdn.example.com/app.js');
        
        $html = $extraAssets->getHTML();
        
        $this->assertStringContainsString("<link rel='stylesheet' href='https://cdn.example.com/style.css' />", $html);
        $this->assertStringContainsString("<script src='https://cdn.example.com/app.js'></script>", $html);
    }

    public function testGetHTMLCanBeCalledMultipleTimes()
    {
        $extraAssets = new ExtraAssets();
        
        $extraAssets->addCSS('/css/style.css');
        $extraAssets->addJS('/js/app.js');
        
        $html1 = $extraAssets->getHTML();
        $html2 = $extraAssets->getHTML();
        
        $this->assertSame($html1, $html2, 'Multiple calls to getHTML should return the same result');
    }

    public function testGetHTMLEndsLinesWithNewline()
    {
        $extraAssets = new ExtraAssets();
        
        $extraAssets->addCSS('/css/style.css');
        $extraAssets->addJS('/js/app.js');
        
        $html = $extraAssets->getHTML();
        
        // Each line should end with a newline
        $this->assertStringContainsString("/>\n", $html);
        $this->assertStringContainsString("</script>\n", $html);
    }
}
