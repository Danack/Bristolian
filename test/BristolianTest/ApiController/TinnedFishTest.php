<?php

declare(strict_types=1);

namespace BristolianTest\ApiController;

use Bristolian\ApiController\TinnedFish;
use Bristolian\Model\TinnedFish\Product;
use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Parameters\TinnedFish\BarcodeLookupParams;
use Bristolian\Parameters\TinnedFish\GenerateApiTokenParams;
use Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo;
use Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo;
use Bristolian\Response\TinnedFish\ExternalApiErrorResponse;
use Bristolian\Response\TinnedFish\GenerateApiTokenResponse;
use Bristolian\Response\TinnedFish\GetAllProductsResponse;
use Bristolian\Response\TinnedFish\InvalidBarcodeResponse;
use Bristolian\Response\TinnedFish\ProductLookupResponse;
use Bristolian\Response\TinnedFish\ProductNotFoundResponse;
use Bristolian\Service\ApiToken\ApiTokenGenerator;
use Bristolian\Service\TinnedFish\FakeOpenFoodFactsFetcher;
use Bristolian\Service\TinnedFish\OpenFoodFactsApiException;
use Bristolian\Session\FakeUserSession;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\ApiController\TinnedFish::getAllProducts
 * @covers \Bristolian\ApiController\TinnedFish::getProductByBarcode
 * @covers \Bristolian\ApiController\TinnedFish::generateApiToken
 */
class TinnedFishTest extends BaseTestCase
{
    public function test_getAllProducts_returns_empty_array_when_no_products(): void
    {
        $repo = new FakeTinnedFishProductRepo();
        $controller = new TinnedFish();

        $response = $controller->getAllProducts($repo);

        $this->assertInstanceOf(GetAllProductsResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('products', $data);
        $this->assertIsArray($data['products']);
        $this->assertCount(0, $data['products']);
    }

    public function test_getAllProducts_returns_all_products(): void
    {
        $now = new \DateTimeImmutable();
        $product1 = new Product(
            barcode: '1234567890123',
            name: 'Sardines in Olive Oil',
            brand: 'Test Brand',
            species: 'Sardines',
            weight: 125.0,
            weight_drained: 90.0,
            product_code: 'PROD-001',
            image_url: 'https://example.com/image1.jpg',
            validation_status: ValidationStatus::VALIDATED_IS_FISH,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $product2 = new Product(
            barcode: '9876543210987',
            name: 'Tuna in Brine',
            brand: 'Another Brand',
            species: 'Tuna',
            weight: 200.0,
            weight_drained: 150.0,
            product_code: 'PROD-002',
            image_url: 'https://example.com/image2.jpg',
            validation_status: ValidationStatus::NOT_VALIDATED,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $repo = new FakeTinnedFishProductRepo([$product1, $product2]);
        $controller = new TinnedFish();

        $response = $controller->getAllProducts($repo);

        $this->assertInstanceOf(GetAllProductsResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('products', $data);
        $this->assertIsArray($data['products']);
        $this->assertCount(2, $data['products']);

        // Check first product
        $firstProduct = $data['products'][0];
        $this->assertSame('1234567890123', $firstProduct['barcode']);
        $this->assertSame('Sardines in Olive Oil', $firstProduct['name']);
        $this->assertSame('Test Brand', $firstProduct['brand']);
        $this->assertSame('Sardines', $firstProduct['species']);
        $this->assertSame(125.0, $firstProduct['weight']);
        $this->assertSame(90.0, $firstProduct['weight_drained']);
        $this->assertSame('PROD-001', $firstProduct['product_code']);
        $this->assertSame('https://example.com/image1.jpg', $firstProduct['image_url']);
        $this->assertSame(ValidationStatus::VALIDATED_IS_FISH->value, $firstProduct['validation_status']);
        $this->assertNotEmpty($firstProduct['created_at']);

        // Check second product
        $secondProduct = $data['products'][1];
        $this->assertSame('9876543210987', $secondProduct['barcode']);
        $this->assertSame('Tuna in Brine', $secondProduct['name']);
        $this->assertSame('Another Brand', $secondProduct['brand']);
        $this->assertSame('Tuna', $secondProduct['species']);
        $this->assertSame(200.0, $secondProduct['weight']);
        $this->assertSame(150.0, $secondProduct['weight_drained']);
        $this->assertSame('PROD-002', $secondProduct['product_code']);
        $this->assertSame('https://example.com/image2.jpg', $secondProduct['image_url']);
        $this->assertSame(ValidationStatus::NOT_VALIDATED->value, $secondProduct['validation_status']);
        $this->assertNotEmpty($secondProduct['created_at']);
    }

    public function test_getProductByBarcode_returns_product_from_canonical_database(): void
    {
        $now = new \DateTimeImmutable();
        $product = new Product(
            barcode: '3107761210000',
            name: 'Test Product',
            brand: 'Test Brand',
            species: 'Sardines',
            weight: 125.0,
            weight_drained: 90.0,
            product_code: 'PROD-001',
            image_url: 'https://example.com/image.jpg',
            validation_status: ValidationStatus::VALIDATED_IS_FISH,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $repo = new FakeTinnedFishProductRepo([$product]);
        $fetcher = new FakeOpenFoodFactsFetcher();
        $controller = new TinnedFish();
        $params = new BarcodeLookupParams(fetch_external: true);

        $response = $controller->getProductByBarcode($params, '3107761210000', $repo, $fetcher);

        $this->assertInstanceOf(ProductLookupResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertSame('canonical', $data['source']);
        $this->assertArrayHasKey('product', $data);
        $this->assertSame('3107761210000', $data['product']['barcode']);
        $this->assertSame('Test Product', $data['product']['name']);
        $this->assertNull($data['copyright']);
    }

    public function test_getProductByBarcode_fetches_from_external_api_when_not_in_canonical(): void
    {
        $repo = new FakeTinnedFishProductRepo();
        $fetcher = new FakeOpenFoodFactsFetcher();
        
        // Set up fake external API response
        $externalData = [
            'status' => 1,
            'product' => [
                'product_name' => 'External Product',
                'brands' => 'External Brand',
                'categories' => 'Fish, Sardines',
                'quantity' => '125 g (égoutté: 90g)',
                'image_url' => 'https://example.com/external.jpg',
            ],
        ];
        $fetcher->setResponse('3107761210000', $externalData);

        $controller = new TinnedFish();
        $params = new BarcodeLookupParams(fetch_external: true);

        $response = $controller->getProductByBarcode($params, '3107761210000', $repo, $fetcher);

        $this->assertInstanceOf(ProductLookupResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertSame('external', $data['source']);
        $this->assertArrayHasKey('product', $data);
        $this->assertSame('3107761210000', $data['product']['barcode']);
        $this->assertArrayHasKey('copyright', $data);
        $this->assertNotNull($data['copyright']);

        // Verify the product was saved to the canonical database
        $savedProduct = $repo->getByBarcode('3107761210000');
        $this->assertNotNull($savedProduct);
        $this->assertSame('3107761210000', $savedProduct->barcode);
    }

    public function test_getProductByBarcode_returns_404_when_not_found_anywhere(): void
    {
        $repo = new FakeTinnedFishProductRepo();
        $fetcher = new FakeOpenFoodFactsFetcher();
        // Don't set any response, so fetcher returns null

        $controller = new TinnedFish();
        $params = new BarcodeLookupParams(fetch_external: true);

        $response = $controller->getProductByBarcode($params, '3107761210000', $repo, $fetcher);

        $this->assertInstanceOf(ProductNotFoundResponse::class, $response);
        $this->assertSame(404, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }

    public function test_getProductByBarcode_handles_external_api_exception(): void
    {
        $repo = new FakeTinnedFishProductRepo();
        $fetcher = new FakeOpenFoodFactsFetcher();
        
        $exception = new OpenFoodFactsApiException('API request failed');
        $fetcher->setException('3107761210000', $exception);

        $controller = new TinnedFish();
        $params = new BarcodeLookupParams(fetch_external: true);

        $response = $controller->getProductByBarcode($params, '3107761210000', $repo, $fetcher);

        $this->assertSame(502, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }

    public function test_getProductByBarcode_validates_barcode_format(): void
    {
        $repo = new FakeTinnedFishProductRepo();
        $fetcher = new FakeOpenFoodFactsFetcher();
        $controller = new TinnedFish();
        $params = new BarcodeLookupParams(fetch_external: true);

        // Test invalid barcode (too short)
        $response = $controller->getProductByBarcode($params, '12345', $repo, $fetcher);
        $this->assertInstanceOf(InvalidBarcodeResponse::class, $response);
        $this->assertSame(400, $response->getStatus());

        // Test invalid barcode (contains letters)
        $response = $controller->getProductByBarcode($params, 'ABC123456789', $repo, $fetcher);
        $this->assertInstanceOf(InvalidBarcodeResponse::class, $response);
        $this->assertSame(400, $response->getStatus());
    }

    public function test_getProductByBarcode_with_bearer_token_scenario(): void
    {
        // This test covers the scenario from the curl request:
        // curl -v "https://bristolian.org/api/tfd/v1/products/barcode/3107761210000?fetch_external=true"
        //   -H "Authorization: Bearer P2dtFU4MRiS2bn9vvJjx_wRggkQ4jtfLVwnWhclQFAA"
        
        // Note: Bearer token authentication is handled by middleware, not the controller.
        // This test verifies the controller method works correctly when called.
        
        $repo = new FakeTinnedFishProductRepo();
        $fetcher = new FakeOpenFoodFactsFetcher();
        
        // Simulate external API returning product data
        $externalData = [
            'status' => 1,
            'product' => [
                'product_name' => 'Test Tinned Fish Product',
                'brands' => 'Test Brand',
                'categories' => 'Fish, Sardines',
                'quantity' => '125 g (égoutté: 90g)',
                'image_url' => 'https://example.com/product.jpg',
            ],
        ];
        $fetcher->setResponse('3107761210000', $externalData);

        $controller = new TinnedFish();
        $params = new BarcodeLookupParams(fetch_external: true);

        $response = $controller->getProductByBarcode($params, '3107761210000', $repo, $fetcher);

        // Should return successful response with external source
        $this->assertInstanceOf(ProductLookupResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertSame('external', $data['source']);
        $this->assertArrayHasKey('product', $data);
        $this->assertSame('3107761210000', $data['product']['barcode']);
        $this->assertArrayHasKey('copyright', $data);
        $this->assertNotNull($data['copyright']);

        // Verify product was cached in canonical database
        $cachedProduct = $repo->getByBarcode('3107761210000');
        $this->assertNotNull($cachedProduct);
        $this->assertSame('3107761210000', $cachedProduct->barcode);
    }

    public function test_getProductByBarcode_returns_404_when_not_in_canonical_and_fetch_external_false(): void
    {
        $repo = new FakeTinnedFishProductRepo();
        $fetcher = new FakeOpenFoodFactsFetcher();

        $controller = new TinnedFish();
        $params = new BarcodeLookupParams(fetch_external: false);

        $response = $controller->getProductByBarcode($params, '3107761210000', $repo, $fetcher);

        $this->assertInstanceOf(ProductNotFoundResponse::class, $response);
        $this->assertSame(404, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }

    public function test_generateApiToken_creates_token_and_returns_response(): void
    {
        $tokenRepo = new FakeApiTokenRepo([]);
        $tokenGenerator = new ApiTokenGenerator();
        $userSession = new FakeUserSession(true, 'admin-user-id', 'admin@example.com');

        $controller = new TinnedFish();
        $params = new GenerateApiTokenParams(name: 'Test Token Name');

        $response = $controller->generateApiToken($params, $userSession, $tokenRepo, $tokenGenerator);

        $this->assertInstanceOf(GenerateApiTokenResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('qr_code_url', $data);
        $this->assertArrayHasKey('created_at', $data);

        $this->assertSame('Test Token Name', $data['name']);
        $this->assertNotEmpty($data['token']);
        $this->assertStringContainsString('token=', $data['qr_code_url']);
        $this->assertStringContainsString(urlencode($data['token']), $data['qr_code_url']);
    }
}
