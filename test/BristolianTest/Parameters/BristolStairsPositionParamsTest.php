<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\BristolStairsPositionParams;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\BristolStairsPositionParams
 */
class BristolStairsPositionParamsTest extends BaseTestCase
{
    public function testWorks()
    {
        $bristol_stair_info_id = 'stairs_123';
        $latitude = 51.4545;
        $longitude = -2.5879;

        $params = [
            'bristol_stair_info_id' => $bristol_stair_info_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        $bristolStairsPositionParams = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($bristol_stair_info_id, $bristolStairsPositionParams->bristol_stair_info_id);
        $this->assertSame($latitude, $bristolStairsPositionParams->latitude);
        $this->assertSame($longitude, $bristolStairsPositionParams->longitude);
    }

    public function testFailsWithMissingBristolStairInfoId()
    {
        try {
            $params = [
                'latitude' => 51.4545,
                'longitude' => -2.5879,
            ];

            BristolStairsPositionParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/bristol_stair_info_id' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingLatitude()
    {
        try {
            $params = [
                'bristol_stair_info_id' => 'stairs_123',
                'longitude' => -2.5879,
            ];

            BristolStairsPositionParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/latitude' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingLongitude()
    {
        try {
            $params = [
                'bristol_stair_info_id' => 'stairs_123',
                'latitude' => 51.4545,
            ];

            BristolStairsPositionParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/longitude' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithInvalidDataTypes()
    {
        try {
            $params = [
                'bristol_stair_info_id' => 123,
                'latitude' => 'invalid',
                'longitude' => 'invalid',
            ];

            BristolStairsPositionParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $validationProblems = $ve->getValidationProblems();
            $this->assertGreaterThan(0, count($validationProblems));
        }
    }

    public function testImplementsDataTypeInterface()
    {
        $params = [
            'bristol_stair_info_id' => 'stairs_123',
            'latitude' => 51.4545,
            'longitude' => -2.5879,
        ];

        $bristolStairsPositionParams = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertInstanceOf(\DataType\DataType::class, $bristolStairsPositionParams);
    }
}
