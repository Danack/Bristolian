<?php

namespace BristolianTest\Service\BccTroFetcher;

use BristolianTest\BaseTestCase;
use Bristolian\Service\BccTroFetcher\StandardBccTroFetcher;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Model\BccTro;
use Bristolian\Model\BccTroDocument;

/**
 * @covers \Bristolian\Service\BccTroFetcher\StandardBccTroFetcher
 * @group wiptro
 */
class StandardBccTroFetcherTest extends BaseTestCase
{
    public function testParseTrosFromExampleHtml(): void
    {
        // Create a filesystem manually for testing
        $tempDir = __DIR__ . "/../../temp/";
        $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($tempDir);
        $filesystem = new LocalCacheFilesystem($adapter, $tempDir);

        // Create the fetcher with the filesystem
        $fetcher = new StandardBccTroFetcher(/*$filesystem*/);

        // Load the example HTML content
        $exampleFile = __DIR__ . '/example_1.html';
        $htmlContent = file_get_contents($exampleFile);

        if ($htmlContent === false) {
            $this->fail("Could not read example file: $exampleFile");
        }

        $tros = $fetcher->parseTrosFromHtml($htmlContent);

        // Verify we found exactly 1 TRO
        $this->assertCount(1, $tros);

        // Verify the TRO data matches expectations
        $tro = $tros[0];
        $this->assertInstanceOf(BccTro::class, $tro);
        $this->assertEquals(
            'Proposed parallel crossign and zebra crossing: Hengrove Promenade, Hengrove',
            $tro->title
        );
        $this->assertEquals('Ref PX-DJR-25-031', $tro->reference_code);

        // Verify document links
        $this->assertInstanceOf(BccTroDocument::class, $tro->statement_of_reasons);
        $this->assertEquals(
            '(1) Statement of Reasons Hengrove Promenade',
            $tro->statement_of_reasons->title
        );
        $this->assertEquals(
            '/files/documents/10060-1-statement-of-reasons-hengrove-promenade',
            $tro->statement_of_reasons->href
        );
        $this->assertEquals('10060', $tro->statement_of_reasons->id);

        $this->assertInstanceOf(BccTroDocument::class, $tro->notice_of_proposal);
        $this->assertEquals(
            '(2) Notice Hengrove Promenade Parallel and Zebra crossings',
            $tro->notice_of_proposal->title
        );
        $this->assertEquals(
            '/files/documents/10061-2-notice-hengrove-promenade-parallel-and-zebra-crossings',
            $tro->notice_of_proposal->href
        );
        $this->assertEquals('10061', $tro->notice_of_proposal->id);

        $this->assertInstanceOf(BccTroDocument::class, $tro->proposed_plan);
        $this->assertEquals(
            '(3) Plan Hengrove Promenade Parallel and Zebra',
            $tro->proposed_plan->title
        );
        $this->assertEquals(
            '/files/documents/10062-3-plan-hengrove-promenade-parallel-and-zebra',
            $tro->proposed_plan->href
        );
        $this->assertEquals('10062', $tro->proposed_plan->id);
    }

    public function testParseTrosFromEmptyHtml(): void
    {
        // Create a filesystem manually for testing
        $tempDir = __DIR__ . "/../../temp/";
        $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($tempDir);
        $filesystem = new LocalCacheFilesystem($adapter, $tempDir);

        $fetcher = new StandardBccTroFetcher(/*$filesystem*/);

        $tros = $fetcher->parseTrosFromHtml("");



        $this->assertCount(0, $tros);
    }

    public function testParseTrosFromHtmlWithNoTros(): void
    {
        // Create a filesystem manually for testing
        $tempDir = __DIR__ . "/../../temp/";
        $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($tempDir);
        $filesystem = new LocalCacheFilesystem($adapter, $tempDir);

        $fetcher = new StandardBccTroFetcher(/*$filesystem*/);

        // Test with HTML that has no TRO structure
        $htmlWithoutTros = '<html><body><h1>No TROs here</h1><p>Just some content</p></body></html>';

        $reflection = new \ReflectionClass(StandardBccTroFetcher::class);
        $parseMethod = $reflection->getMethod('parseTrosFromHtml');
        $parseMethod->setAccessible(true);

        $tros = $parseMethod->invokeArgs($fetcher, [$htmlWithoutTros]);

        $this->assertCount(0, $tros);
    }
}
