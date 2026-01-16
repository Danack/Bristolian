<?php

namespace BristolianTest\Service\TinnedFish;

use Bristolian\Model\TinnedFish\Product;
use BristolianTest\BaseTestCase;

use function normalizeOpenFoodFactsData;

/**
 * Tests for tinned fish normalization functions
 *
 * @covers \normalizeOpenFoodFactsData
 * @covers \parseTinnedFishWeight
 * @covers \extractTinnedFishSpecies
 */
class ProductNormalizerTest extends BaseTestCase
{
    public static function provides_normalizes_basic_product_data(): \Generator
    {
        yield 'complete product data' => [
            '3017620422003',
            [
                'status' => 1,
                'product' => [
                    'product_name' => 'Sardines in Olive Oil',
                    'brands' => 'Test Brand',
                    'quantity' => '125 g',
                    'image_url' => 'https://example.com/image.jpg',
                ]
            ],
            [
                'barcode' => '3017620422003',
                'name' => 'Sardines in Olive Oil',
                'brand' => 'Test Brand',
                'weight' => 125.0,
                'image_url' => 'https://example.com/image.jpg',
                'product_code' => null,
            ]
        ];
    }

    /**
     * @covers \normalizeOpenFoodFactsData
     * @dataProvider provides_normalizes_basic_product_data
     */
    public function test_normalizes_basic_product_data(
        string $barcode,
        array $rawData,
        array $expected
    ): void {
        $product = normalizeOpenFoodFactsData($barcode, $rawData);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertSame($expected['barcode'], $product->barcode);
        $this->assertSame($expected['name'], $product->name);
        $this->assertSame($expected['brand'], $product->brand);
        $this->assertSame($expected['weight'], $product->weight);
        $this->assertSame($expected['image_url'], $product->image_url);
        $this->assertSame($expected['product_code'], $product->product_code);
        $this->assertSame($rawData, $product->raw_data);
    }

    public static function provides_parses_weight_with_drained_weight(): \Generator
    {
        yield 'French format (égoutté)' => [
            '125 g (égoutté: 90g)',
            125.0,
            90.0,
        ];
        yield 'English format (drained)' => [
            '125 g (drained: 90g)',
            125.0,
            90.0,
        ];
        yield 'French format without colon' => [
            '150 g (égoutté 100g)',
            150.0,
            100.0,
        ];
    }

    /**
     * @covers \normalizeOpenFoodFactsData
     * @dataProvider provides_parses_weight_with_drained_weight
     */
    public function test_parses_weight_with_drained_weight(
        string $quantity,
        float $expectedWeight,
        float $expectedWeightDrained
    ): void {
        $rawData = [
            'status' => 1,
            'product' => [
                'product_name' => 'Sardines',
                'brands' => 'Brand',
                'quantity' => $quantity,
            ]
        ];

        $product = normalizeOpenFoodFactsData('1234567890123', $rawData);

        $this->assertSame($expectedWeight, $product->weight);
        $this->assertSame($expectedWeightDrained, $product->weight_drained);
    }

    public static function provides_parses_weight_formats(): \Generator
    {
        yield 'with space' => ['125 g', 125.0];
        yield 'without space' => ['125g', 125.0];
        yield 'decimal with dot' => ['125.5 g', 125.5];
        yield 'decimal with comma' => ['125,5 g', 125.5];
        yield 'large weight' => ['500 g', 500.0];
        yield 'small weight' => ['50 g', 50.0];
    }

    /**
     * @covers \normalizeOpenFoodFactsData
     * @dataProvider provides_parses_weight_formats
     */
    public function test_parses_weight_formats(string $quantity, float $expectedWeight): void
    {
        $rawData = [
            'status' => 1,
            'product' => [
                'product_name' => 'Sardines',
                'brands' => 'Brand',
                'quantity' => $quantity,
            ]
        ];

        $product = normalizeOpenFoodFactsData('1234567890123', $rawData);

        $this->assertSame($expectedWeight, $product->weight);
    }

    public static function provides_extracts_species_from_product_name(): \Generator
    {
        yield 'sardines' => ['Premium Sardines in Oil', 'Sardines'];
        yield 'tuna' => ['Wild Caught Tuna', 'Tuna'];
        yield 'salmon' => ['Atlantic Salmon Fillet', 'Salmon'];
        yield 'mackerel' => ['Smoked Mackerel', 'Mackerel'];
        yield 'anchovies' => ['Anchovies in Oil', 'Anchovies'];
        yield 'herring' => ['Pickled Herring', 'Herring'];
        yield 'cod' => ['Cod Liver Oil', 'Cod'];
        yield 'trout' => ['Rainbow Trout', 'Trout'];
        yield 'sprats' => ['Smoked Sprats', 'Sprats'];
        yield 'pilchards' => ['Cornish Pilchards', 'Pilchards'];
        yield 'French thon (tuna)' => ['Thon à l\'huile', 'Tuna'];
        yield 'French saumon (salmon)' => ['Saumon fumé', 'Salmon'];
        yield 'French maquereau (mackerel)' => ['Filets de maquereau', 'Mackerel'];
    }

    /**
     * @covers \normalizeOpenFoodFactsData
     * @dataProvider provides_extracts_species_from_product_name
     */
    public function test_extracts_species_from_product_name(
        string $productName,
        string $expectedSpecies
    ): void {
        $rawData = [
            'status' => 1,
            'product' => [
                'product_name' => $productName,
                'brands' => 'Brand',
            ]
        ];

        $product = normalizeOpenFoodFactsData('1234567890123', $rawData);

        $this->assertSame($expectedSpecies, $product->species);
    }

    public static function provides_extracts_species_from_categories(): \Generator
    {
        yield 'mackerel in categories' => [
            'Fish in Oil',
            'Canned fish, Mackerel products',
            'Mackerel',
        ];
        yield 'sardines in categories' => [
            'Conserve de poisson',
            'Canned sardines, Fish products',
            'Sardines',
        ];
        yield 'tuna in categories' => [
            'Canned fish',
            'Seafood, Tuna products',
            'Tuna',
        ];
    }

    /**
     * @covers \normalizeOpenFoodFactsData
     * @dataProvider provides_extracts_species_from_categories
     */
    public function test_extracts_species_from_categories(
        string $productName,
        string $categories,
        string $expectedSpecies
    ): void {
        $rawData = [
            'status' => 1,
            'product' => [
                'product_name' => $productName,
                'brands' => 'Brand',
                'categories' => $categories,
            ]
        ];

        $product = normalizeOpenFoodFactsData('1234567890123', $rawData);

        $this->assertSame($expectedSpecies, $product->species);
    }

    public static function provides_returns_null_species_when_not_found(): \Generator
    {
        yield 'generic fish product' => ['Unknown Fish Product'];
        yield 'non-fish product' => ['Vegetable Soup'];
        yield 'empty name' => [''];
    }

    /**
     * @covers \normalizeOpenFoodFactsData
     * @dataProvider provides_returns_null_species_when_not_found
     */
    public function test_returns_null_species_when_not_found(string $productName): void
    {
        $rawData = [
            'status' => 1,
            'product' => [
                'product_name' => $productName,
                'brands' => 'Brand',
            ]
        ];

        $product = normalizeOpenFoodFactsData('1234567890123', $rawData);

        $this->assertNull($product->species);
    }

    public static function provides_handles_missing_product_data(): \Generator
    {
        yield 'empty product array' => [
            '1234567890123',
            ['status' => 1, 'product' => []],
            [
                'barcode' => '1234567890123',
                'name' => 'Unknown',
                'brand' => 'Unknown',
                'weight' => null,
                'weight_drained' => null,
                'species' => null,
                'image_url' => null,
            ]
        ];
        yield 'missing product key' => [
            '9876543210987',
            ['status' => 1],
            [
                'barcode' => '9876543210987',
                'name' => 'Unknown',
                'brand' => 'Unknown',
                'weight' => null,
                'weight_drained' => null,
                'species' => null,
                'image_url' => null,
            ]
        ];
    }

    /**
     * @covers \normalizeOpenFoodFactsData
     * @dataProvider provides_handles_missing_product_data
     */
    public function test_handles_missing_product_data(
        string $barcode,
        array $rawData,
        array $expected
    ): void {
        $product = normalizeOpenFoodFactsData($barcode, $rawData);

        $this->assertSame($expected['barcode'], $product->barcode);
        $this->assertSame($expected['name'], $product->name);
        $this->assertSame($expected['brand'], $product->brand);
        $this->assertSame($expected['weight'], $product->weight);
        $this->assertSame($expected['weight_drained'], $product->weight_drained);
        $this->assertSame($expected['species'], $product->species);
        $this->assertSame($expected['image_url'], $product->image_url);
    }

    public static function provides_uses_fallback_fields(): \Generator
    {
        yield 'English product name fallback' => [
            [
                'status' => 1,
                'product' => [
                    'product_name_en' => 'English Product Name',
                    'brands' => 'Brand',
                ]
            ],
            'name',
            'English Product Name',
        ];
        yield 'Front image URL fallback' => [
            [
                'status' => 1,
                'product' => [
                    'product_name' => 'Product',
                    'brands' => 'Brand',
                    'image_front_url' => 'https://example.com/front.jpg',
                ]
            ],
            'image_url',
            'https://example.com/front.jpg',
        ];
        yield 'Primary image URL takes precedence' => [
            [
                'status' => 1,
                'product' => [
                    'product_name' => 'Product',
                    'brands' => 'Brand',
                    'image_url' => 'https://example.com/main.jpg',
                    'image_front_url' => 'https://example.com/front.jpg',
                ]
            ],
            'image_url',
            'https://example.com/main.jpg',
        ];
        yield 'Primary product name takes precedence' => [
            [
                'status' => 1,
                'product' => [
                    'product_name' => 'Primary Name',
                    'product_name_en' => 'English Name',
                    'brands' => 'Brand',
                ]
            ],
            'name',
            'Primary Name',
        ];
    }

    /**
     * @covers \normalizeOpenFoodFactsData
     * @dataProvider provides_uses_fallback_fields
     */
    public function test_uses_fallback_fields(
        array $rawData,
        string $fieldToCheck,
        string $expectedValue
    ): void {
        $product = normalizeOpenFoodFactsData('1234567890123', $rawData);

        $this->assertSame($expectedValue, $product->$fieldToCheck);
    }
}
