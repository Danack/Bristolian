<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BristolStairsRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use Bristolian\Repo\BristolStairsRepo\FakeBristolStairsRepo;
use Bristolian\Model\Generated\BristolStairInfo;
use VarMap\ArrayVarMap;

/**
 * Tests for FakeBristolStairsRepo
 *
 * @covers \Bristolian\Repo\BristolStairsRepo\FakeBristolStairsRepo
 * @group standard_repo
 * @coversNothing
 */
class FakeBristolStairsRepoTest extends BristolStairsRepoFixture
{
    /**
     * @return BristolStairsRepo
     */
    public function getTestInstance(): BristolStairsRepo
    {
        return new FakeBristolStairsRepo();
    }
    /**
     * Test that the repo is initialized with 3 fake stairs
     */
    public function test_constructor_initializes_with_fake_data(): void
    {
        $repo = new FakeBristolStairsRepo();
        $all_stairs = $repo->getAllStairsInfo();

        $this->assertCount(3, $all_stairs);
    }

    /**
     * Test get_total_number_of_steps with default data
     */
    public function test_get_total_number_of_steps_with_default_data(): void
    {
        $repo = new FakeBristolStairsRepo();
        [$flights_of_stairs, $total_steps] = $repo->get_total_number_of_steps();

        $this->assertSame(3, $flights_of_stairs);
        $this->assertSame(105, $total_steps); // 45 + 32 + 28
    }

    /**
     * Test getAllStairsInfo returns only non-deleted stairs
     */
    public function test_getAllStairsInfo_returns_non_deleted(): void
    {
        $repo = new FakeBristolStairsRepo();
        $all_stairs = $repo->getAllStairsInfo();

        $this->assertCount(3, $all_stairs);
        
        foreach ($all_stairs as $stair) {
            $this->assertInstanceOf(BristolStairInfo::class, $stair);
            $this->assertSame(0, $stair->is_deleted);
        }
    }

    /**
     * Test getAllStairsInfo returns stairs with expected properties
     */
    public function test_getAllStairsInfo_contains_expected_data(): void
    {
        $repo = new FakeBristolStairsRepo();
        $all_stairs = $repo->getAllStairsInfo();
        $stairs_array = array_values($all_stairs);

        // Check first stair
        $this->assertSame(1, $stairs_array[0]->id);
        $this->assertSame(51.4556, $stairs_array[0]->latitude);
        $this->assertSame(-2.5943, $stairs_array[0]->longitude);
        $this->assertSame('Steep stairs near Park Street', $stairs_array[0]->description);
        $this->assertSame('fake_image_1', $stairs_array[0]->stored_stair_image_file_id);
        $this->assertSame(45, $stairs_array[0]->steps);

        // Check second stair
        $this->assertSame(2, $stairs_array[1]->id);
        $this->assertSame(32, $stairs_array[1]->steps);

        // Check third stair
        $this->assertSame(3, $stairs_array[2]->id);
        $this->assertSame(28, $stairs_array[2]->steps);
    }

    /**
     * Test getStairInfoById returns correct stair
     */
    public function test_getStairInfoById_returns_correct_stair(): void
    {
        $repo = new FakeBristolStairsRepo();
        $stair = $repo->getStairInfoById(1);

        $this->assertInstanceOf(BristolStairInfo::class, $stair);
        $this->assertSame(1, $stair->id);
        $this->assertSame('Steep stairs near Park Street', $stair->description);
        $this->assertSame(45, $stair->steps);
    }

    /**
     * Test getStairInfoById with different IDs
     */
    public function test_getStairInfoById_with_different_ids(): void
    {
        $repo = new FakeBristolStairsRepo();

        $stair1 = $repo->getStairInfoById(1);
        $this->assertSame(1, $stair1->id);

        $stair2 = $repo->getStairInfoById(2);
        $this->assertSame(2, $stair2->id);

        $stair3 = $repo->getStairInfoById(3);
        $this->assertSame(3, $stair3->id);
    }

    /**
     * Test getStairInfoById throws exception for non-existent ID
     */
    public function test_getStairInfoById_throws_exception_for_nonexistent_id(): void
    {
        $repo = new FakeBristolStairsRepo();

        $this->expectException(ContentNotFoundException::class);
        $repo->getStairInfoById(999);
    }

    /**
     * Test store_stairs_info creates new stair
     */
    public function test_store_stairs_info_creates_new_stair(): void
    {
        $repo = new FakeBristolStairsRepo();

        $new_stair = $repo->store_stairs_info(
            'test_image_id',
            'Test stairs description',
            51.4600,
            -2.6000,
            50
        );

        $this->assertInstanceOf(BristolStairInfo::class, $new_stair);
        $this->assertSame(4, $new_stair->id); // Should be 4th item
        $this->assertSame(51.46, $new_stair->latitude);
        $this->assertSame(-2.6, $new_stair->longitude);
        $this->assertSame('Test stairs description', $new_stair->description);
        $this->assertSame('test_image_id', $new_stair->stored_stair_image_file_id);
        $this->assertSame(50, $new_stair->steps);
        $this->assertSame(0, $new_stair->is_deleted);
    }

    /**
     * Test store_stairs_info increments total count
     */
    public function test_store_stairs_info_increments_total_count(): void
    {
        $repo = new FakeBristolStairsRepo();

        // Initial count
        [$flights_before, $steps_before] = $repo->get_total_number_of_steps();
        $this->assertSame(3, $flights_before);
        $this->assertSame(105, $steps_before);

        // Add new stair
        $repo->store_stairs_info(
            'test_image_id',
            'Test stairs',
            51.4600,
            -2.6000,
            20
        );

        // Check updated count
        [$flights_after, $steps_after] = $repo->get_total_number_of_steps();
        $this->assertSame(4, $flights_after);
        $this->assertSame(125, $steps_after); // 105 + 20
    }

    /**
     * Test store_stairs_info adds to getAllStairsInfo result
     */
    public function test_store_stairs_info_adds_to_all_stairs(): void
    {
        $repo = new FakeBristolStairsRepo();

        $all_stairs_before = $repo->getAllStairsInfo();
        $this->assertCount(3, $all_stairs_before);

        $repo->store_stairs_info(
            'test_image_id',
            'Test stairs',
            51.4600,
            -2.6000,
            20
        );

        $all_stairs_after = $repo->getAllStairsInfo();
        $this->assertCount(4, $all_stairs_after);
    }

    /**
     * Test store_stairs_info sets timestamps
     */
    public function test_store_stairs_info_sets_timestamps(): void
    {
        $repo = new FakeBristolStairsRepo();

        $before_creation = new \DateTimeImmutable();
        $new_stair = $repo->store_stairs_info(
            'test_image_id',
            'Test stairs',
            51.4600,
            -2.6000,
            20
        );
        $after_creation = new \DateTimeImmutable();

        $this->assertInstanceOf(\DateTimeInterface::class, $new_stair->created_at);
        $this->assertInstanceOf(\DateTimeInterface::class, $new_stair->updated_at);
        
        // Check timestamps are between before and after
        $this->assertGreaterThanOrEqual($before_creation->getTimestamp(), $new_stair->created_at->getTimestamp());
        $this->assertLessThanOrEqual($after_creation->getTimestamp(), $new_stair->created_at->getTimestamp());
    }

    /**
     * Test updateStairInfo updates description and steps
     */
    public function test_updateStairInfo_updates_description_and_steps(): void
    {
        $repo = new FakeBristolStairsRepo();

        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'description' => 'Updated description',
            'steps' => '100'
        ]));

        $repo->updateStairInfo($params);

        $updated_stair = $repo->getStairInfoById(1);
        $this->assertSame('Updated description', $updated_stair->description);
        $this->assertSame(100, $updated_stair->steps);
    }

    /**
     * Test updateStairInfo preserves other fields
     */
    public function test_updateStairInfo_preserves_other_fields(): void
    {
        $repo = new FakeBristolStairsRepo();

        $original_stair = $repo->getStairInfoById(1);
        $original_latitude = $original_stair->latitude;
        $original_longitude = $original_stair->longitude;
        $original_image_id = $original_stair->stored_stair_image_file_id;
        $original_created_at = $original_stair->created_at;

        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'description' => 'Updated description',
            'steps' => '100'
        ]));

        $repo->updateStairInfo($params);

        $updated_stair = $repo->getStairInfoById(1);
        $this->assertSame($original_latitude, $updated_stair->latitude);
        $this->assertSame($original_longitude, $updated_stair->longitude);
        $this->assertSame($original_image_id, $updated_stair->stored_stair_image_file_id);
        $this->assertSame($original_created_at, $updated_stair->created_at);
        $this->assertSame(0, $updated_stair->is_deleted);
    }

    /**
     * Test updateStairInfo updates the updated_at timestamp
     */
    public function test_updateStairInfo_updates_timestamp(): void
    {
        $repo = new FakeBristolStairsRepo();

        $original_stair = $repo->getStairInfoById(1);
        $original_updated_at = $original_stair->updated_at;

        // Sleep briefly to ensure timestamp difference
        usleep(10000); // 10ms

        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'description' => 'Updated description',
            'steps' => '100'
        ]));

        $repo->updateStairInfo($params);

        $updated_stair = $repo->getStairInfoById(1);
        $this->assertGreaterThan($original_updated_at->getTimestamp(), $updated_stair->updated_at->getTimestamp());
    }

    /**
     * Test updateStairInfo throws exception for non-existent ID
     */
    public function test_updateStairInfo_throws_exception_for_nonexistent_id(): void
    {
        $repo = new FakeBristolStairsRepo();

        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '999',
            'description' => 'Updated description',
            'steps' => '100'
        ]));

        $this->expectException(ContentNotFoundException::class);
        $repo->updateStairInfo($params);
    }

    /**
     * Test updateStairInfo affects total step count
     */
    public function test_updateStairInfo_affects_total_step_count(): void
    {
        $repo = new FakeBristolStairsRepo();

        [$flights_before, $steps_before] = $repo->get_total_number_of_steps();
        $this->assertSame(105, $steps_before); // 45 + 32 + 28

        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'description' => 'Updated description',
            'steps' => '100' // Changed from 45 to 100
        ]));

        $repo->updateStairInfo($params);

        [$flights_after, $steps_after] = $repo->get_total_number_of_steps();
        $this->assertSame(3, $flights_after); // Same number of flights
        $this->assertSame(160, $steps_after); // 100 + 32 + 28
    }

    /**
     * Test updateStairPosition updates latitude and longitude
     */
    public function test_updateStairPosition_updates_coordinates(): void
    {
        $repo = new FakeBristolStairsRepo();

        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'latitude' => 51.5000,
            'longitude' => -2.7000
        ]));

        $repo->updateStairPosition($params);

        $updated_stair = $repo->getStairInfoById(1);
        $this->assertSame(51.5, $updated_stair->latitude);
        $this->assertSame(-2.7, $updated_stair->longitude);
    }

    /**
     * Test updateStairPosition preserves other fields
     */
    public function test_updateStairPosition_preserves_other_fields(): void
    {
        $repo = new FakeBristolStairsRepo();

        $original_stair = $repo->getStairInfoById(1);
        $original_description = $original_stair->description;
        $original_steps = $original_stair->steps;
        $original_image_id = $original_stair->stored_stair_image_file_id;
        $original_created_at = $original_stair->created_at;

        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'latitude' => 51.5000,
            'longitude' => -2.7000
        ]));

        $repo->updateStairPosition($params);

        $updated_stair = $repo->getStairInfoById(1);
        $this->assertSame($original_description, $updated_stair->description);
        $this->assertSame($original_steps, $updated_stair->steps);
        $this->assertSame($original_image_id, $updated_stair->stored_stair_image_file_id);
        $this->assertSame($original_created_at, $updated_stair->created_at);
        $this->assertSame(0, $updated_stair->is_deleted);
    }

    /**
     * Test updateStairPosition updates the updated_at timestamp
     */
    public function test_updateStairPosition_updates_timestamp(): void
    {
        $repo = new FakeBristolStairsRepo();

        $original_stair = $repo->getStairInfoById(1);
        $original_updated_at = $original_stair->updated_at;

        // Sleep briefly to ensure timestamp difference
        usleep(10000); // 10ms

        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'latitude' => 51.5000,
            'longitude' => -2.7000
        ]));

        $repo->updateStairPosition($params);

        $updated_stair = $repo->getStairInfoById(1);
        $this->assertGreaterThan($original_updated_at->getTimestamp(), $updated_stair->updated_at->getTimestamp());
    }

    /**
     * Test updateStairPosition throws exception for non-existent ID
     */
    public function test_updateStairPosition_throws_exception_for_nonexistent_id(): void
    {
        $repo = new FakeBristolStairsRepo();

        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '999',
            'latitude' => 51.5000,
            'longitude' => -2.7000
        ]));

        $this->expectException(ContentNotFoundException::class);
        $repo->updateStairPosition($params);
    }

    /**
     * Test multiple operations in sequence
     */
    public function test_multiple_operations_in_sequence(): void
    {
        $repo = new FakeBristolStairsRepo();

        // Store new stair
        $new_stair = $repo->store_stairs_info(
            'new_image',
            'New stairs',
            51.4700,
            -2.6100,
            60
        );
        $this->assertSame(4, $new_stair->id);

        // Update its info
        $info_params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '4',
            'description' => 'Updated new stairs',
            'steps' => '70'
        ]));
        $repo->updateStairInfo($info_params);

        // Update its position
        $position_params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '4',
            'latitude' => 51.4800,
            'longitude' => -2.6200
        ]));
        $repo->updateStairPosition($position_params);

        // Verify final state
        $final_stair = $repo->getStairInfoById(4);
        $this->assertSame('Updated new stairs', $final_stair->description);
        $this->assertSame(70, $final_stair->steps);
        $this->assertSame(51.48, $final_stair->latitude);
        $this->assertSame(-2.62, $final_stair->longitude);

        // Verify total count
        [$flights, $total_steps] = $repo->get_total_number_of_steps();
        $this->assertSame(4, $flights);
        $this->assertSame(175, $total_steps); // 45 + 32 + 28 + 70
    }

    /**
     * Test store_stairs_info with various coordinate formats
     */
    public function test_store_stairs_info_with_various_coordinates(): void
    {
        $repo = new FakeBristolStairsRepo();

        // Test with precise coordinates
        $stair1 = $repo->store_stairs_info(
            'image1',
            'Test 1',
            51.123456,
            -2.654321,
            10
        );
        $this->assertSame(51.123456, $stair1->latitude);
        $this->assertSame(-2.654321, $stair1->longitude);

        // Test with integer-like coordinates
        $stair2 = $repo->store_stairs_info(
            'image2',
            'Test 2',
            51.0,
            -2.0,
            15
        );
        $this->assertSame(51.0, $stair2->latitude);
        $this->assertSame(-2.0, $stair2->longitude);
    }

    /**
     * Test that stairs have proper Bristol area coordinates
     */
    public function test_default_stairs_have_bristol_coordinates(): void
    {
        $repo = new FakeBristolStairsRepo();
        $all_stairs = $repo->getAllStairsInfo();

        foreach ($all_stairs as $stair) {
            $lat = (float)$stair->latitude;
            $lon = (float)$stair->longitude;

            // Check coordinates are roughly in Bristol area
            // Bristol is around 51.45N, 2.59W
            $this->assertGreaterThan(51.4, $lat);
            $this->assertLessThan(51.5, $lat);
            $this->assertGreaterThan(-2.6, $lon);
            $this->assertLessThan(-2.5, $lon);
        }
    }
}
