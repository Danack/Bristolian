<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\OpenmapNearbyParams;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class OpenmapNearbyParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, float, float}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'bristol coordinates' => [
            ['latitude' => '51.4545', 'longitude' => '-2.5879'],
            51.4545, -2.5879,
        ];
        yield 'zero coordinates' => [
            ['latitude' => '0', 'longitude' => '0'],
            0.0, 0.0,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\OpenmapNearbyParams
     * @covers \Bristolian\Parameters\PropertyType\BasicFloat
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        float $expectedLatitude,
        float $expectedLongitude
    ): void {
        $params = OpenmapNearbyParams::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedLatitude, $params->latitude);
        $this->assertSame($expectedLongitude, $params->longitude);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing latitude' => [
            ['longitude' => '-2.5879'],
            '/latitude',
            Messages::VALUE_NOT_SET,
        ];
        yield 'missing longitude' => [
            ['latitude' => '51.4545'],
            '/longitude',
            Messages::VALUE_NOT_SET,
        ];
        yield 'invalid latitude' => [
            ['latitude' => 'not-a-number', 'longitude' => '-2.5879'],
            '/latitude',
            Messages::FLOAT_REQUIRED,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\OpenmapNearbyParams
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(
        array $input,
        string $errorPath,
        string $expectedErrorMessage
    ): void {
        try {
            OpenmapNearbyParams::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [$errorPath => $expectedErrorMessage]
            );
        }
    }
}
