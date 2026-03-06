<?php

declare(strict_types=1);

namespace BristolianTest\Service\TinnedFish;

use Bristolian\Service\HttpFetcher\HttpFetcher;
use Bristolian\Service\TinnedFish\OpenFoodFactsApiException;
use Bristolian\Service\TinnedFish\OpenFoodFactsFetcher;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class OpenFoodFactsFetcherTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\TinnedFish\OpenFoodFactsFetcher::__construct
     * @covers \Bristolian\Service\TinnedFish\OpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_returns_data_when_status_200_and_status_one(): void
    {
        $barcode = '5012345678900';
        $url = 'https://world.openfoodfacts.org/api/v0/product/5012345678900.json';
        $body = '{"status":1,"product":{"product_name":"Test"}}';
        $httpFetcher = new MapHttpFetcher([$url => [200, $body, []]]);
        $fetcher = new OpenFoodFactsFetcher($httpFetcher);

        $result = $fetcher->fetchProduct($barcode);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['status']);
        $this->assertSame('Test', $result['product']['product_name']);
    }

    /**
     * @covers \Bristolian\Service\TinnedFish\OpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_returns_null_when_http_404(): void
    {
        $barcode = '404barcode';
        $url = 'https://world.openfoodfacts.org/api/v0/product/404barcode.json';
        $httpFetcher = new MapHttpFetcher([$url => [404, '', []]]);
        $fetcher = new OpenFoodFactsFetcher($httpFetcher);

        $this->assertNull($fetcher->fetchProduct($barcode));
    }

    /**
     * @covers \Bristolian\Service\TinnedFish\OpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_returns_null_when_status_zero(): void
    {
        $barcode = '000000';
        $url = 'https://world.openfoodfacts.org/api/v0/product/000000.json';
        $body = '{"status":0}';
        $httpFetcher = new MapHttpFetcher([$url => [200, $body, []]]);
        $fetcher = new OpenFoodFactsFetcher($httpFetcher);

        $this->assertNull($fetcher->fetchProduct($barcode));
    }

    /**
     * @covers \Bristolian\Service\TinnedFish\OpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_returns_null_when_status_key_missing(): void
    {
        $barcode = 'nostatus';
        $url = 'https://world.openfoodfacts.org/api/v0/product/nostatus.json';
        $body = '{"product":{}}';
        $httpFetcher = new MapHttpFetcher([$url => [200, $body, []]]);
        $fetcher = new OpenFoodFactsFetcher($httpFetcher);

        $this->assertNull($fetcher->fetchProduct($barcode));
    }

    /**
     * @covers \Bristolian\Service\TinnedFish\OpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_throws_when_non_200_non_404(): void
    {
        $barcode = '500barcode';
        $url = 'https://world.openfoodfacts.org/api/v0/product/500barcode.json';
        $httpFetcher = new MapHttpFetcher([$url => [500, 'Server error', []]]);
        $fetcher = new OpenFoodFactsFetcher($httpFetcher);

        $this->expectException(OpenFoodFactsApiException::class);
        $this->expectExceptionMessage('OpenFoodFacts API returned status code: 500');
        $fetcher->fetchProduct($barcode);
    }

    /**
     * @covers \Bristolian\Service\TinnedFish\OpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_throws_when_response_not_valid_json(): void
    {
        $barcode = 'badjson';
        $url = 'https://world.openfoodfacts.org/api/v0/product/badjson.json';
        $httpFetcher = new MapHttpFetcher([$url => [200, 'not json', []]]);
        $fetcher = new OpenFoodFactsFetcher($httpFetcher);

        $this->expectException(OpenFoodFactsApiException::class);
        $this->expectExceptionMessage('Failed to parse OpenFoodFacts API response');
        $fetcher->fetchProduct($barcode);
    }

    /**
     * @covers \Bristolian\Service\TinnedFish\OpenFoodFactsFetcher::fetchProduct
     */
    public function test_fetchProduct_urlencodes_barcode(): void
    {
        $barcode = '123 456';
        $url = 'https://world.openfoodfacts.org/api/v0/product/123+456.json';
        $body = '{"status":1}';
        $httpFetcher = new MapHttpFetcher([$url => [200, $body, []]]);
        $fetcher = new OpenFoodFactsFetcher($httpFetcher);

        $result = $fetcher->fetchProduct($barcode);
        $this->assertIsArray($result);
    }
}

/**
 * HttpFetcher that returns fixed [statusCode, body, headers] per URL.
 * Used to test OpenFoodFactsFetcher without network.
 *
 * @internal
 */
final class MapHttpFetcher implements HttpFetcher
{
    /** @var array<string, array{0: int, 1: string, 2: mixed[]}> */
    private array $urlToResponse;

    /** @param array<string, array{0: int, 1: string, 2: mixed[]}> $urlToResponse */
    public function __construct(array $urlToResponse)
    {
        $this->urlToResponse = $urlToResponse;
    }

    public function fetch(
        string $uri,
        string $method = 'GET',
        array $queryParams = [],
        string|null $body = null,
        array $headers = []
    ): array {
        if (!isset($this->urlToResponse[$uri])) {
            return [404, '', []];
        }
        return $this->urlToResponse[$uri];
    }
}
