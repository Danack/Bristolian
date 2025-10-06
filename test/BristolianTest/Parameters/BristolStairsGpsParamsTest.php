<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\BristolStairsGpsParams;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\BristolStairsGpsParams
 */
class BristolStairsGpsParamsTest extends BaseTestCase
{
    public function testWorks()
    {
        $latitude = 51.4545;
        $longitude = -2.5879;

        $params = [
            'gps_latitude' => $latitude,
            'gps_longitude' => $longitude,
        ];

        $bristolStairsGpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($latitude, $bristolStairsGpsParams->latitude);
        $this->assertSame($longitude, $bristolStairsGpsParams->longitude);
    }

    public function testWorksWithNoOptionalParameters()
    {
        $params = [];

        $bristolStairsGpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertNull($bristolStairsGpsParams->latitude);
        $this->assertNull($bristolStairsGpsParams->longitude);
    }

    public function testWorksWithAllOptionalParameters()
    {
        $latitude = 51.4545;
        $longitude = -2.5879;

        $params = [
            'gps_latitude' => $latitude,
            'gps_longitude' => $longitude,
        ];

        $bristolStairsGpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($latitude, $bristolStairsGpsParams->latitude);
        $this->assertSame($longitude, $bristolStairsGpsParams->longitude);
    }

    public function testFailsWithNullValues()
    {
        try {
            $params = [
                'gps_latitude' => null,
                'gps_longitude' => null,
            ];

            BristolStairsGpsParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [
                    '/gps_latitude' => Messages::FLOAT_REQUIRED_WRONG_TYPE,
                    '/gps_longitude' => Messages::FLOAT_REQUIRED_WRONG_TYPE,
                ]
            );
        }
    }

    public function testWorksWithOnlyLatitude()
    {
        $latitude = 51.4545;

        $params = [
            'gps_latitude' => $latitude,
        ];

        $bristolStairsGpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($latitude, $bristolStairsGpsParams->latitude);
        $this->assertNull($bristolStairsGpsParams->longitude);
    }

    public function testWorksWithOnlyLongitude()
    {
        $longitude = -2.5879;

        $params = [
            'gps_longitude' => $longitude,
        ];

        $bristolStairsGpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertNull($bristolStairsGpsParams->latitude);
        $this->assertSame($longitude, $bristolStairsGpsParams->longitude);
    }

    public function testFailsWithInvalidDataTypes()
    {
        try {
            $params = [
                'gps_latitude' => 'invalid',
                'gps_longitude' => 'invalid',
            ];

            BristolStairsGpsParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $validationProblems = $ve->getValidationProblems();
            $this->assertGreaterThan(0, count($validationProblems));
        }
    }

    public function testImplementsDataTypeInterface()
    {
        $params = [];

        $bristolStairsGpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertInstanceOf(\DataType\DataType::class, $bristolStairsGpsParams);
    }
}
