<?php

declare(strict_types = 1);

namespace Functions;

use BristolianTest\BaseTestCase;
use function get_image_gps;
use function getGps;
use function gps2Num;

/**
 * @coversNothing
 */
class FunctionsExifTest extends BaseTestCase
{
    private const STAIRS_WITH_GPS = __DIR__ . '/../fixtures/stairs/stairs_test_a_8.jpeg';

    private const STAIRS_WITHOUT_GPS = __DIR__ . '/../fixtures/stairs/stairs_test_c_7.jpeg';

    /**
     * @covers ::get_image_gps
     */
    public function test_get_image_gps_returns_coordinates_for_image_with_gps(): void
    {
        $result = get_image_gps(self::STAIRS_WITH_GPS);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        [$latitude, $longitude] = $result;
        $this->assertGreaterThan(51, $latitude);
        $this->assertLessThan(52, $latitude);
        $this->assertLessThan(-2, $longitude);
        $this->assertGreaterThan(-3, $longitude);
    }

    /**
     * @covers ::get_image_gps
     */
    public function test_get_image_gps_returns_null_for_image_without_gps(): void
    {
        $result = get_image_gps(self::STAIRS_WITHOUT_GPS);

        $this->assertNull($result);
    }

    /**
     * @covers ::get_image_gps
     */
    public function test_get_image_gps_returns_null_for_non_existent_file(): void
    {
        $result = get_image_gps('/nonexistent/path/image.jpeg');

        $this->assertNull($result);
    }

    /**
     * @return \Generator<string, array{array<string>, string, float|int}>
     */
    public static function provides_getGps_input_and_expected(): \Generator
    {
        yield 'Bristol latitude N' => [
            ['51/1', '29/1', '93/100'],
            'N',
            51.0 + 29 / 60 + 93 / 100 / 3600,
        ];
        yield 'Bristol longitude W' => [
            ['2/1', '38/1', '4076/100'],
            'W',
            -(2.0 + 38 / 60 + 4076 / 100 / 3600),
        ];
        yield 'zero coord' => [[], 'N', 0];
        yield 'S hemisphere negative' => [['1/1', '0/1', '0/1'], 'S', -1.0];
        yield 'E hemisphere positive' => [['1/1', '0/1', '0/1'], 'E', 1.0];
    }

    /**
     * @covers ::getGps
     * @dataProvider provides_getGps_input_and_expected
     * @param array<string> $exifCoord
     */
    public function test_getGps_converts_exif_coord_to_decimal(
        array $exifCoord,
        string $hemi,
        float|int $expected
    ): void {
        $result = getGps($exifCoord, $hemi);
        $this->assertEqualsWithDelta($expected, $result, 0.0001);
    }

    /**
     * @return \Generator<string, array{string, float}>
     */
    public static function provides_gps2Num_input_and_expected(): \Generator
    {
        yield 'simple fraction' => ['51/1', 51.0];
        yield 'decimal fraction' => ['4545/100', 45.45];
        yield 'whole number' => ['29', 29.0];
        yield 'degrees minutes seconds' => ['93/100', 0.93];
    }

    /**
     * @covers ::gps2Num
     * @dataProvider provides_gps2Num_input_and_expected
     */
    public function test_gps2Num_converts_coord_part_to_float(string $coordPart, float $expected): void
    {
        $result = gps2Num($coordPart);
        $this->assertEqualsWithDelta($expected, $result, 0.0001);
    }
}
