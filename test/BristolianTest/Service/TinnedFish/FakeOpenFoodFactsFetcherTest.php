<?php

declare(strict_types=1);

namespace BristolianTest\Service\TinnedFish;

use Bristolian\Service\TinnedFish\FakeOpenFoodFactsFetcher;
use Bristolian\Service\TinnedFish\OpenFoodFactsApiException;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeOpenFoodFactsFetcherTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\TinnedFish\FakeOpenFoodFactsFetcher::setResponse
     * @covers \Bristolian\Service\TinnedFish\FakeOpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_returns_set_response(): void
    {
        $fetcher = new FakeOpenFoodFactsFetcher();
        $data = ['product_name' => 'Test Product', 'barcode' => '123'];
        $fetcher->setResponse('123', $data);

        $this->assertSame($data, $fetcher->fetchProduct('123'));
    }

    /**
     * @covers \Bristolian\Service\TinnedFish\FakeOpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_returns_null_when_no_response_set(): void
    {
        $fetcher = new FakeOpenFoodFactsFetcher();
        $this->assertNull($fetcher->fetchProduct('unknown'));
    }

    /**
     * @covers \Bristolian\Service\TinnedFish\FakeOpenFoodFactsFetcher::setResponse
     * @covers \Bristolian\Service\TinnedFish\FakeOpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_returns_null_when_response_set_to_null(): void
    {
        $fetcher = new FakeOpenFoodFactsFetcher();
        $fetcher->setResponse('456', null);
        $this->assertNull($fetcher->fetchProduct('456'));
    }

    /**
     * @covers \Bristolian\Service\TinnedFish\FakeOpenFoodFactsFetcher::setException
     * @covers \Bristolian\Service\TinnedFish\FakeOpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_throws_when_exception_set(): void
    {
        $fetcher = new FakeOpenFoodFactsFetcher();
        $exception = new OpenFoodFactsApiException('API error');
        $fetcher->setException('789', $exception);

        $this->expectException(OpenFoodFactsApiException::class);
        $this->expectExceptionMessage('API error');
        $fetcher->fetchProduct('789');
    }
}
