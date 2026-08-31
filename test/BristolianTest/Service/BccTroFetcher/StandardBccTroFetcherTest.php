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


    public static function provides_FetchTrosReturnsParsedTrosWhenHttpReturns200WithExampleHtml() {

       $statement_of_reasons = new BccTroDocument(
           '(1) Statement of Reasons Hengrove Promenade',
           '/files/documents/10060-1-statement-of-reasons-hengrove-promenade',
           '10060'
       );
       $notice_of_proposal = new BccTroDocument(
           '(2) Notice Hengrove Promenade Parallel and Zebra crossings',
           '/files/documents/10061-2-notice-hengrove-promenade-parallel-and-zebra-crossings',
           '10061'
       );
       $proposed_plan = new BccTroDocument(
           '(3) Plan Hengrove Promenade Parallel and Zebra',
           '/files/documents/10062-3-plan-hengrove-promenade-parallel-and-zebra',
           '10062',
       );

       $bccTro = new BccTro(
           $title = 'Proposed parallel crossign and zebra crossing: Hengrove Promenade, Hengrove',
           $reference_code = 'Ref PX-DJR-25-031',
           $statement_of_reasons,
           $notice_of_proposal,
           $proposed_plan
       );

       yield [__DIR__ . '/example_1.html', $bccTro];
    }


    /**

     * @dataProvider provides_FetchTrosReturnsParsedTrosWhenHttpReturns200WithExampleHtml
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::fetchTros
     * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::fetchHtmlContent
     */
    public function testFetchTrosReturnsParsedTrosWhenHttpReturns200WithExampleHtml(
        string $html_input_file,
        BccTro $expected_bcc_tro
    ): void
    {

        $htmlContent = file_get_contents($html_input_file);
        if ($htmlContent === false) {
            $this->fail("Could not read example file: $html_input_file");
        }

        $httpFetcher = new FakeHttpFetcherWithFixedResponse(200, $htmlContent);
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $tros = $fetcher->fetchTros();

        $this->assertCount(1, $tros);
        $tro = $tros[0];
        $this->assertInstanceOf(BccTro::class, $tro);

        $this->assertEquals($expected_bcc_tro, $tro);
    }
}
