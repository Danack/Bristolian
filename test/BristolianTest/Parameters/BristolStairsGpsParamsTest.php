<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\BristolStairsGpsParams;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class BristolStairsGpsParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, float|null, float|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'both' => [
            ['gps_latitude' => 51.4545, 'gps_longitude' => -2.5879],
            51.4545,
            -2.5879,
        ];
        yield 'no optional' => [[], null, null];
//        yield 'only latitude' => [['gps_latitude' => 51.4545], 51.4545, null];
//        yield 'only longitude' => [['gps_longitude' => -2.5879], null, -2.5879];
    }

    /**
     * @covers \Bristolian\Parameters\BristolStairsGpsParams
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        ?float $expectedLatitude,
        ?float $expectedLongitude
    ): void {
        $params = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedLatitude, $params->latitude);
        $this->assertSame($expectedLongitude, $params->longitude);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, array<string, string>}>
     */
    public static function provides_invalid_input_and_expected_errors(): \Generator
    {
        yield 'null values' => [
            ['gps_latitude' => null, 'gps_longitude' => null],
            [
                '/gps_latitude' => Messages::FLOAT_REQUIRED_WRONG_TYPE,
                '/gps_longitude' => Messages::FLOAT_REQUIRED_WRONG_TYPE,
            ],
        ];
        yield 'invalid types' => [
            ['gps_latitude' => 'invalid', 'gps_longitude' => 'invalid'],
            [
                '/gps_latitude' => Messages::FLOAT_REQUIRED,
                '/gps_longitude' => Messages::FLOAT_REQUIRED,
            ],
        ];
    }

    /**
     * @covers \Bristolian\Parameters\BristolStairsGpsParams
     * @dataProvider provides_invalid_input_and_expected_errors
     * @param array<string, mixed> $input
     * @param array<string, string> $expectedProblems
     */
    public function test_rejects_invalid_input_with_expected_errors(array $input, array $expectedProblems): void
    {
        try {
            BristolStairsGpsParams::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems($ve->getValidationProblems(), $expectedProblems);
        }
    }
}
