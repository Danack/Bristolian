<?php

namespace BristolianTest\Service\BccTroFetcher;

use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use Bristolian\Service\BccTroFetcher\StandardBccTroFetcher;
use Bristolian\Service\HttpFetcher\FakeHttpFetcherReturning404;
use Bristolian\Service\HttpFetcher\FakeHttpFetcherWithFixedResponse;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class StandardBccTroFetcherTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::__construct
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::fetchTros
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::fetchHtmlContent
     */
    public function testFetchTrosThrowsWhenHttpReturns404(): void
    {
        $httpFetcher = new FakeHttpFetcherReturning404();
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch content from');
        $this->expectExceptionMessage('HTTP 404');

        $fetcher->fetchTros();
    }

    /**
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::fetchTros
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::fetchHtmlContent
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::parseTrosFromHtml
     */
    public function testFetchTrosReturnsParsedTrosWhenHttpReturns200WithExampleHtml(): void
    {
        $exampleFile = __DIR__ . '/example_1.html';
        $htmlContent = file_get_contents($exampleFile);
        if ($htmlContent === false) {
            $this->fail("Could not read example file: $exampleFile");
        }

        $httpFetcher = new FakeHttpFetcherWithFixedResponse(200, $htmlContent);
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $tros = $fetcher->fetchTros();

        $this->assertCount(1, $tros);
        $tro = $tros[0];
        $this->assertInstanceOf(BccTro::class, $tro);
        $this->assertSame(
            'Proposed parallel crossign and zebra crossing: Hengrove Promenade, Hengrove',
            $tro->title
        );
        $this->assertSame('Ref PX-DJR-25-031', $tro->reference_code);

        $this->assertInstanceOf(BccTroDocument::class, $tro->statement_of_reasons);
        $this->assertSame(
            '(1) Statement of Reasons Hengrove Promenade',
            $tro->statement_of_reasons->title
        );
        $this->assertSame(
            '/files/documents/10060-1-statement-of-reasons-hengrove-promenade',
            $tro->statement_of_reasons->href
        );
        $this->assertSame('10060', $tro->statement_of_reasons->id);

        $this->assertInstanceOf(BccTroDocument::class, $tro->notice_of_proposal);
        $this->assertSame(
            '(2) Notice Hengrove Promenade Parallel and Zebra crossings',
            $tro->notice_of_proposal->title
        );
        $this->assertSame(
            '/files/documents/10061-2-notice-hengrove-promenade-parallel-and-zebra-crossings',
            $tro->notice_of_proposal->href
        );
        $this->assertSame('10061', $tro->notice_of_proposal->id);

        $this->assertInstanceOf(BccTroDocument::class, $tro->proposed_plan);
        $this->assertSame(
            '(3) Plan Hengrove Promenade Parallel and Zebra',
            $tro->proposed_plan->title
        );
        $this->assertSame(
            '/files/documents/10062-3-plan-hengrove-promenade-parallel-and-zebra',
            $tro->proposed_plan->href
        );
        $this->assertSame('10062', $tro->proposed_plan->id);
    }

    /**
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::parseTrosFromHtml
     */
    public function testParseTrosFromExampleHtml(): void
    {
        $httpFetcher = new FakeHttpFetcherReturning404();
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $exampleFile = __DIR__ . '/example_1.html';
        $htmlContent = file_get_contents($exampleFile);
        if ($htmlContent === false) {
            $this->fail("Could not read example file: $exampleFile");
        }

        $tros = $fetcher->parseTrosFromHtml($htmlContent);

        $this->assertCount(1, $tros);
        $tro = $tros[0];
        $this->assertInstanceOf(BccTro::class, $tro);
        $this->assertSame(
            'Proposed parallel crossign and zebra crossing: Hengrove Promenade, Hengrove',
            $tro->title
        );
        $this->assertSame('Ref PX-DJR-25-031', $tro->reference_code);

        $this->assertInstanceOf(BccTroDocument::class, $tro->statement_of_reasons);
        $this->assertSame(
            '(1) Statement of Reasons Hengrove Promenade',
            $tro->statement_of_reasons->title
        );
        $this->assertSame(
            '/files/documents/10060-1-statement-of-reasons-hengrove-promenade',
            $tro->statement_of_reasons->href
        );
        $this->assertSame('10060', $tro->statement_of_reasons->id);

        $this->assertInstanceOf(BccTroDocument::class, $tro->notice_of_proposal);
        $this->assertSame(
            '(2) Notice Hengrove Promenade Parallel and Zebra crossings',
            $tro->notice_of_proposal->title
        );
        $this->assertSame(
            '/files/documents/10061-2-notice-hengrove-promenade-parallel-and-zebra-crossings',
            $tro->notice_of_proposal->href
        );
        $this->assertSame('10061', $tro->notice_of_proposal->id);

        $this->assertInstanceOf(BccTroDocument::class, $tro->proposed_plan);
        $this->assertSame(
            '(3) Plan Hengrove Promenade Parallel and Zebra',
            $tro->proposed_plan->title
        );
        $this->assertSame(
            '/files/documents/10062-3-plan-hengrove-promenade-parallel-and-zebra',
            $tro->proposed_plan->href
        );
        $this->assertSame('10062', $tro->proposed_plan->id);
    }

    /**
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::parseTrosFromHtml
     */
    public function testParseTrosFromEmptyHtml(): void
    {
        $httpFetcher = new FakeHttpFetcherReturning404();
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $tros = $fetcher->parseTrosFromHtml("");

        $this->assertCount(0, $tros);
    }

    /**
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::parseTrosFromHtml
     */
    public function testParseTrosFromHtmlWithNoTros(): void
    {
        $httpFetcher = new FakeHttpFetcherReturning404();
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $htmlWithoutTros = '<html><body><h1>No TROs here</h1><p>Just some content</p></body></html>';

        $tros = $fetcher->parseTrosFromHtml($htmlWithoutTros);

        $this->assertCount(0, $tros);
    }

    /**
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::parseTrosFromHtml
     */
    public function testParseTrosFromHtmlSkipsH3WithoutColon(): void
    {
        $httpFetcher = new FakeHttpFetcherReturning404();
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $html = '<html><body>'
            . '<h3>Not a TRO title</h3><h4>Ref X</h4><ul><li><a href="/f/1">Link</a></li></ul>'
            . '<h3>Valid TRO: With colon</h3><h4>Ref ABC-123</h4><ul>'
            . '<li><a href="/files/100-statement" data-id="100" data-title="Statement">Statement of Reasons</a></li>'
            . '<li><a href="/files/101-notice" data-id="101" data-title="Notice">Notice</a></li>'
            . '<li><a href="/files/102-plan" data-id="102" data-title="Plan">Plan</a></li>'
            . '</ul></body></html>';

        $tros = $fetcher->parseTrosFromHtml($html);

        $this->assertCount(1, $tros);
        $this->assertSame('Valid TRO: With colon', $tros[0]->title);
        $this->assertSame('Ref ABC-123', $tros[0]->reference_code);
    }

    /**
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::parseTrosFromHtml
     */
    public function testParseTrosFromHtmlWithH3H4ButNoUlReturnsTroWithEmptyDocuments(): void
    {
        $httpFetcher = new FakeHttpFetcherReturning404();
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $html = '<html><body><h3>Proposed thing: Somewhere</h3><h4>Ref XYZ-99</h4><p>No document list here</p></body></html>';

        $tros = $fetcher->parseTrosFromHtml($html);

        $this->assertCount(1, $tros);
        $this->assertSame('Proposed thing: Somewhere', $tros[0]->title);
        $this->assertSame('Ref XYZ-99', $tros[0]->reference_code);
        $this->assertSame('', $tros[0]->statement_of_reasons->title);
        $this->assertSame('', $tros[0]->statement_of_reasons->href);
        $this->assertSame('', $tros[0]->statement_of_reasons->id);
    }

    /**
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::parseTrosFromHtml
     */
    public function testParseTrosFromHtmlExtractsIdFromHrefWhenDataIdMissing(): void
    {
        $httpFetcher = new FakeHttpFetcherReturning404();
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $html = '<html><body><h3>TRO: Title</h3><h4>Ref X</h4><ul>'
            . '<li><a href="/files/999-statement-of-reasons">Statement of Reasons</a></li>'
            . '</ul></body></html>';

        $tros = $fetcher->parseTrosFromHtml($html);

        $this->assertCount(1, $tros);
        $this->assertSame('999', $tros[0]->statement_of_reasons->id);
        $this->assertSame('/files/999-statement-of-reasons', $tros[0]->statement_of_reasons->href);
        $this->assertSame('Statement of Reasons', $tros[0]->statement_of_reasons->title);
    }
}
