<?php

namespace BristolianTest\Parameters\TinnedFish;

use Bristolian\Parameters\TinnedFish\BarcodeLookupParams;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * Tests for BarcodeLookupParams
 *
 * @coversNothing
 */
class BarcodeLookupParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, bool}>
     */
    public static function provides_fetch_external_input_and_expected_output(): \Generator
    {
        yield 'missing key defaults to true' => [[], true];
        yield 'true string' => [['fetch_external' => 'true'], true];
        yield 'false string' => [['fetch_external' => 'false'], false];
        yield '1 string' => [['fetch_external' => '1'], true];
        yield '0 string' => [['fetch_external' => '0'], false];
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\BarcodeLookupParams
     * @covers \Bristolian\Parameters\PropertyType\OptionalBoolDefaultTrue
     * @dataProvider provides_fetch_external_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_fetch_external_parses_input_to_expected_output(
        array $input,
        bool $expectedFetchExternal
    ): void {
        $params = BarcodeLookupParams::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedFetchExternal, $params->fetch_external);
    }
}
