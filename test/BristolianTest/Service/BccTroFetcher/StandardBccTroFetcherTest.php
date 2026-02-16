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
}
