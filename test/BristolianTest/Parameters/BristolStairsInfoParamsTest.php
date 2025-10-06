<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\BristolStairsInfoParams;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\BristolStairsInfoParams
 */
class BristolStairsInfoParamsTest extends BaseTestCase
{
    public function testWorks()
    {
        $bristol_stair_info_id = 'stairs_123';
        $description = 'A nice set of stairs';
        $steps = '25';

        $params = [
            'bristol_stair_info_id' => $bristol_stair_info_id,
            'description' => $description,
            'steps' => $steps,
        ];

        $bristolStairsInfoParams = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($bristol_stair_info_id, $bristolStairsInfoParams->bristol_stair_info_id);
        $this->assertSame($description, $bristolStairsInfoParams->description);
        $this->assertSame($steps, $bristolStairsInfoParams->steps);
    }

    public function testFailsWithMissingBristolStairInfoId()
    {
        try {
            $params = [
                'description' => 'A nice set of stairs',
                'steps' => '25',
            ];

            BristolStairsInfoParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/bristol_stair_info_id' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingDescription()
    {
        try {
            $params = [
                'bristol_stair_info_id' => 'stairs_123',
                'steps' => '25',
            ];

            BristolStairsInfoParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/description' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingSteps()
    {
        try {
            $params = [
                'bristol_stair_info_id' => 'stairs_123',
                'description' => 'A nice set of stairs',
            ];

            BristolStairsInfoParams::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/steps' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithInvalidDataTypes()
    {
        try {
            $params = [
                'bristol_stair_info_id' => 123,
                'description' => 456,
                'steps' => 789,
            ];

            BristolStairsInfoParams::createFromVarMap(new ArrayVarMap($params));
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
            'description' => 'A nice set of stairs',
            'steps' => '25',
        ];

        $bristolStairsInfoParams = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap($params));

        $this->assertInstanceOf(\DataType\DataType::class, $bristolStairsInfoParams);
    }

}
