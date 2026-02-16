<?php

namespace BristolianTest;

use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;

/**
 * @coversNothing
 */
class FunctionsBccTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{string}>
     */
    public static function provides_parse_tros_from_html_returns_empty(): \Generator
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => ["   \n  "];
        yield 'no TROs in HTML' => ['<html><body><h1>No TROs here</h1><p>Just some content</p></body></html>'];
    }

    /**
     * @covers \parseTrosFromHtml
     * @dataProvider provides_parse_tros_from_html_returns_empty
     */
    public function testParseTrosFromHtmlReturnsEmptyForInput(string $html): void
    {
        $tros = \parseTrosFromHtml($html);

        $this->assertCount(0, $tros);
    }

    /**
     * @covers \parseTrosFromHtml
     */
    public function testParseTrosFromHtmlParsesExampleFile(): void
    {
        $exampleFile = __DIR__ . '/Service/BccTroFetcher/example_1.html';
        $htmlContent = file_get_contents($exampleFile);
        if ($htmlContent === false) {
            $this->fail("Could not read example file: $exampleFile");
        }

        $tros = \parseTrosFromHtml($htmlContent);

        $this->assertCount(1, $tros);
        $tro = $tros[0];
        $this->assertInstanceOf(BccTro::class, $tro);
        $this->assertSame(
            'Proposed parallel crossign and zebra crossing: Hengrove Promenade, Hengrove',
            $tro->title
        );
        $this->assertSame('Ref PX-DJR-25-031', $tro->reference_code);
        $this->assertSame('(1) Statement of Reasons Hengrove Promenade', $tro->statement_of_reasons->title);
        $this->assertSame('10060', $tro->statement_of_reasons->id);
        $this->assertSame('(2) Notice Hengrove Promenade Parallel and Zebra crossings', $tro->notice_of_proposal->title);
        $this->assertSame('(3) Plan Hengrove Promenade Parallel and Zebra', $tro->proposed_plan->title);
    }

    /**
     * @covers \parseTrosFromHtml
     */
    public function testParseTrosFromHtmlSkipsH3WithoutColon(): void
    {
        $html = '<html><body>'
            . '<h3>Not a TRO title</h3><h4>Ref X</h4><ul><li><a href="/f/1">Link</a></li></ul>'
            . '<h3>Valid TRO: With colon</h3><h4>Ref ABC-123</h4><ul>'
            . '<li><a href="/files/100-statement" data-id="100" data-title="Statement">Statement of Reasons</a></li>'
            . '<li><a href="/files/101-notice" data-id="101" data-title="Notice">Notice</a></li>'
            . '<li><a href="/files/102-plan" data-id="102" data-title="Plan">Plan</a></li>'
            . '</ul></body></html>';

        $tros = \parseTrosFromHtml($html);

        $this->assertCount(1, $tros);
        $this->assertSame('Valid TRO: With colon', $tros[0]->title);
        $this->assertSame('Ref ABC-123', $tros[0]->reference_code);
    }

    /**
     * @covers \parseTrosFromHtml
     */
    public function testParseTrosFromHtmlWithH3H4ButNoUlReturnsTroWithEmptyDocuments(): void
    {
        $html = '<html><body><h3>Proposed thing: Somewhere</h3><h4>Ref XYZ-99</h4><p>No document list here</p></body></html>';

        $tros = \parseTrosFromHtml($html);

        $this->assertCount(1, $tros);
        $this->assertSame('Proposed thing: Somewhere', $tros[0]->title);
        $this->assertSame('Ref XYZ-99', $tros[0]->reference_code);
        $this->assertSame('', $tros[0]->statement_of_reasons->title);
        $this->assertSame('', $tros[0]->statement_of_reasons->href);
        $this->assertSame('', $tros[0]->statement_of_reasons->id);
    }

    /**
     * @covers \parseTrosFromHtml
     * @covers \extractDocumentLinksFromUl
     */
    public function testParseTrosFromHtmlExtractsIdFromHrefWhenDataIdMissing(): void
    {
        $html = '<html><body><h3>TRO: Title</h3><h4>Ref X</h4><ul>'
            . '<li><a href="/files/999-statement-of-reasons">Statement of Reasons</a></li>'
            . '</ul></body></html>';

        $tros = \parseTrosFromHtml($html);

        $this->assertCount(1, $tros);
        $this->assertSame('999', $tros[0]->statement_of_reasons->id);
        $this->assertSame('/files/999-statement-of-reasons', $tros[0]->statement_of_reasons->href);
        $this->assertSame('Statement of Reasons', $tros[0]->statement_of_reasons->title);
    }

    /**
     * @covers \extractDocumentLinksFromUl
     */
    public function testExtractDocumentLinksFromUlReturnsAllThreeDocumentTypes(): void
    {
        $html = '<ul>'
            . '<li><a href="/sor" data-id="1" data-title="SOR">Statement of Reasons</a></li>'
            . '<li><a href="/notice" data-id="2" data-title="Notice">Notice</a></li>'
            . '<li><a href="/plan" data-id="3" data-title="Plan">Plan</a></li>'
            . '</ul>';
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $ul = $xpath->query('//ul')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $ul);

        $documents = \extractDocumentLinksFromUl($xpath, $ul);

        $this->assertArrayHasKey('statement_of_reasons', $documents);
        $this->assertArrayHasKey('notice_of_proposal', $documents);
        $this->assertArrayHasKey('proposed_plan', $documents);
        $this->assertSame('SOR', $documents['statement_of_reasons']->title);
        $this->assertSame('1', $documents['statement_of_reasons']->id);
        $this->assertSame('Notice', $documents['notice_of_proposal']->title);
        $this->assertSame('Plan', $documents['proposed_plan']->title);
    }

    /**
     * @covers \extractDocumentLinksFromUl
     */
    public function testExtractDocumentLinksFromUlUsesLinkTextWhenDataTitleMissing(): void
    {
        $html = '<ul><li><a href="/files/42-foo">Statement of Reasons</a></li></ul>';
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $ul = $xpath->query('//ul')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $ul);

        $documents = \extractDocumentLinksFromUl($xpath, $ul);

        $this->assertSame('Statement of Reasons', $documents['statement_of_reasons']->title);
        $this->assertSame('42', $documents['statement_of_reasons']->id);
    }
}
