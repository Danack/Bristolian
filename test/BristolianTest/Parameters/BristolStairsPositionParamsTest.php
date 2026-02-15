<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\BristolStairsPositionParams;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class BristolStairsPositionParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string, float, float}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            [
                'bristol_stair_info_id' => 'stairs_123',
                'latitude' => 51.4545,
                'longitude' => -2.5879,
            ],
            'stairs_123',
            51.4545,
            -2.5879,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\BristolStairsPositionParams
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        string $expectedId,
        float $expectedLatitude,
        float $expectedLongitude
    ): void {
        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedId, $params->bristol_stair_info_id);
        $this->assertSame($expectedLatitude, $params->latitude);
        $this->assertSame($expectedLongitude, $params->longitude);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, array<string, string>}>
     */
    public static function provides_invalid_input_and_expected_errors(): \Generator
    {
        yield 'missing bristol_stair_info_id' => [
            ['latitude' => 51.4545, 'longitude' => -2.5879],
            ['/bristol_stair_info_id' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing latitude' => [
            ['bristol_stair_info_id' => 'stairs_123', 'longitude' => -2.5879],
            ['/latitude' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing longitude' => [
            ['bristol_stair_info_id' => 'stairs_123', 'latitude' => 51.4545],
            ['/longitude' => Messages::VALUE_NOT_SET],
        ];
        yield 'invalid types' => [
            ['bristol_stair_info_id' => 123, 'latitude' => 'invalid', 'longitude' => 'invalid'],
            [
                '/bristol_stair_info_id' => Messages::STRING_EXPECTED,
                '/latitude' => Messages::FLOAT_REQUIRED,
                '/longitude' => Messages::FLOAT_REQUIRED,
            ],
        ];
    }

    /**
     * @covers \Bristolian\Parameters\BristolStairsPositionParams
     * @dataProvider provides_invalid_input_and_expected_errors
     * @param array<string, mixed> $input
     * @param array<string, string> $expectedProblems
     */
    public function test_rejects_invalid_input_with_expected_errors(array $input, array $expectedProblems): void
    {
        try {
            BristolStairsPositionParams::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems($ve->getValidationProblems(), $expectedProblems);
        }
    }
}
