<?php

namespace BristolianTest\Parameters\TinnedFish;

use Bristolian\Parameters\TinnedFish\BarcodeLookupParams;
use BristolianTest\BaseTestCase;
use DataType\DataType;
use Bristolian\StaticFactory;
use VarMap\ArrayVarMap;

/**
 * Tests for BarcodeLookupParams
 *
 * @covers \Bristolian\Parameters\TinnedFish\BarcodeLookupParams
 */
class BarcodeLookupParamsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Parameters\TinnedFish\BarcodeLookupParams
     */
    public function test_implements_required_interfaces(): void
    {
        $params = BarcodeLookupParams::createFromVarMap(new ArrayVarMap([]));

        $this->assertInstanceOf(DataType::class, $params);
        $this->assertInstanceOf(StaticFactory::class, $params);
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\BarcodeLookupParams
     */
    public function test_defaults_fetch_external_to_true(): void
    {
        $params = BarcodeLookupParams::createFromVarMap(new ArrayVarMap([]));

        $this->assertTrue($params->fetch_external);
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\BarcodeLookupParams
     */
    public function test_fetch_external_true_string(): void
    {
        $params = BarcodeLookupParams::createFromVarMap(new ArrayVarMap([
            'fetch_external' => 'true'
        ]));

        $this->assertTrue($params->fetch_external);
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\BarcodeLookupParams
     */
    public function test_fetch_external_false_string(): void
    {
        $params = BarcodeLookupParams::createFromVarMap(new ArrayVarMap([
            'fetch_external' => 'false'
        ]));

        $this->assertFalse($params->fetch_external);
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\BarcodeLookupParams
     */
    public function test_fetch_external_1_string(): void
    {
        $params = BarcodeLookupParams::createFromVarMap(new ArrayVarMap([
            'fetch_external' => '1'
        ]));

        $this->assertTrue($params->fetch_external);
    }

    /**
     * @covers \Bristolian\Parameters\TinnedFish\BarcodeLookupParams
     */
    public function test_fetch_external_0_string(): void
    {
        $params = BarcodeLookupParams::createFromVarMap(new ArrayVarMap([
            'fetch_external' => '0'
        ]));

        $this->assertFalse($params->fetch_external);
    }
}
