<?php

declare(strict_types = 1);

namespace Bristolian\Response\TinnedFish;

use Bristolian\Model\TinnedFish\Product;
use SlimDispatcher\Response\StubResponse;

/**
 * Response for getting all products from the canonical database.
 */
class GetAllProductsResponse implements StubResponse
{
    private string $body;

    /**
     * @param Product[] $products
     */
    public function __construct(array $products)
    {
        $productsData = [];
        foreach ($products as $product) {
            // Convert the product data to match the format expected by the frontend
            $productsData[] = [
                'barcode' => $product->barcode,
                'name' => $product->name,
                'brand' => $product->brand,
                'species' => $product->species,
                'weight' => $product->weight,
                'weight_drained' => $product->weight_drained,
                'product_code' => $product->product_code,
                'image_url' => $product->image_url,
                'validation_status' => $product->validation_status->value,
                'created_at' => $product->created_at?->format(\Bristolian\App::DATE_TIME_FORMAT) ?? '',
            ];
        }

        $response = [
            'success' => true,
            'products' => $productsData,
        ];

        $this->body = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }

    public function getStatus(): int
    {
        return 200;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json'
        ];
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
