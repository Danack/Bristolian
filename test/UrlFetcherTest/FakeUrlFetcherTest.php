<?php

declare(strict_types = 1);

namespace UrlFetcherTest;

use BristolianTest\BaseTestCase;
use UrlFetcher\FakeUrlFetcher;
use PHPUnit\Framework\Attributes\Group;

/**
 * @coversNothing
 */
class FakeUrlFetcherTest extends BaseTestCase
{
    /**
     * @covers \UrlFetcher\FakeUrlFetcher
     */
    #[Group('wip')]
    public function testBasic(): void
    {
        $data = 'John';
        $urlFetcher = new FakeUrlFetcher($data);
        $result = $urlFetcher->getUrl('http://www.example.com');
        $this->assertSame(
            $data,
            $result
        );

        $hits = $urlFetcher->getHits();
        $this->assertSame(1, $hits);
    }
}
