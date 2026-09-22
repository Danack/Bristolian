<?php

declare(strict_types=1);

namespace BristolianTest\Service\BccTroFetcher;

use Bristolian\Service\BccTroFetcher\FakeBccTroFetcher;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\BccTroFetcher\FakeBccTroFetcher::class, '__construct')]
#[CoversMethod(\Bristolian\Service\BccTroFetcher\FakeBccTroFetcher::class, 'fetchPage')]

class FakeBccTroFetcherTest extends BaseTestCase
{
    public function test_fetchPage_returns_configured_html(): void
    {
        $html = '<html>configured page</html>';
        $fetcher = new FakeBccTroFetcher($html);

        $this->assertSame($html, $fetcher->fetchPage());
    }

    public function test_fetchPage_rethrows_when_configured(): void
    {
        $fetcher = new FakeBccTroFetcher('', new \RuntimeException('boom'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('boom');

        $fetcher->fetchPage();
    }
}
