<?php

namespace BristolianTest;

use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use PHPUnit\Framework\Attributes\DataProvider;

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
     */
    #[DataProvider('provides_parse_tros_from_html_returns_empty')]
    public function testParseTrosFromHtmlReturnsEmptyForInput(string $html): void
    {
        $tros = \parseTrosFromHtml($html);

        $this->assertCount(0, $tros);
    }




    public static function provides_ParseTrosFromHtmlParsesExampleFile() {

        $statement_of_reasons_1 = new BccTroDocument(
            '(1) Statement of Reasons Hengrove Promenade',
            '/files/documents/10060-1-statement-of-reasons-hengrove-promenade',
            '10060'
        );
        $notice_of_proposal_1 = new BccTroDocument(
            '(2) Notice Hengrove Promenade Parallel and Zebra crossings',
            '/files/documents/10061-2-notice-hengrove-promenade-parallel-and-zebra-crossings',
            '10061'
        );
        $proposed_plan_1 = new BccTroDocument(
            '(3) Plan Hengrove Promenade Parallel and Zebra',
            '/files/documents/10062-3-plan-hengrove-promenade-parallel-and-zebra',
            '10062',
        );

        $bccTro_1 = new BccTro(
            $title = 'Proposed parallel crossign and zebra crossing: Hengrove Promenade, Hengrove',
            $reference_code = 'Ref PX-DJR-25-031',
            $statement_of_reasons_1,
            $notice_of_proposal_1,
            $proposed_plan_1
        );

        yield [__DIR__ . '/Service/BccTroFetcher/example_1.html', $bccTro_1];



        $statement_of_reasons_2 = new BccTroDocument(
            'Statement of Reasons, PX DJR 26 004',
            'https://www.bristol.gov.uk/files/documents/11286-statement-of-reasons-px-djr-26-004',
            '11286'
        );
        $notice_of_proposal_2 = new BccTroDocument(
            'Notice of Proposal, PX DJR 26 004',
            'https://www.bristol.gov.uk/files/documents/11287-notice-of-proposal-px-djr-26-004',
            '11287'
        );
        $proposed_plan_2 = new BccTroDocument(
            'Proposed Plan   Zebra crossing, PX DJR 26 004',
            'https://www.bristol.gov.uk/files/documents/11285-proposed-plan-zebra-crossing-px-djr-26-004',
            '11285'
        );


        $bccTro_2 = new BccTro(
            $title = 'Proposed Zebra Crossing: Marsh Street, City Centre (Central ward): ref PX-DJR-26-004',
            $reference_code = 'PX-DJR-26-004',
            $statement_of_reasons_2,
            $notice_of_proposal_2,
            $proposed_plan_2
        );

        yield [__DIR__ . '/Service/BccTroFetcher/example_2.html', $bccTro_2];
    }



    /**
     * @covers \parseTrosFromHtml
     * @group tro_wip
     */
    #[DataProvider('provides_ParseTrosFromHtmlParsesExampleFile')]
    public function testParseTrosFromHtmlParsesExampleFile(
        string $html_input_file,
        BccTro $expected_bcc_tro

    ): void
    {
        $exampleFile = $html_input_file;
        $htmlContent = file_get_contents($exampleFile);
        if ($htmlContent === false) {
            $this->fail("Could not read example file: $exampleFile");
        }

        $tros = \parseTrosFromHtml($htmlContent);

        $this->assertCount(1, $tros);


        $this->assertCount(1, $tros);
        $tro = $tros[0];
        $this->assertInstanceOf(BccTro::class, $tro);

        $this->assertEquals($expected_bcc_tro, $tro);


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
