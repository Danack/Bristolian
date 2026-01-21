<?php

namespace BristolianTest\Repo\BristolStairsRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo;
use Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use BristolianTest\Repo\TestPlaceholders;
use Bristolian\Model\Generated\BristolStairInfo;
use Ramsey\Uuid\Uuid;
use VarMap\ArrayVarMap;

/**
 * @group db
 */
class PdoBristolStairsRepoTest extends BristolStairsRepoTest
{
    use TestPlaceholders;

    public function getTestInstance(): BristolStairsRepo
    {
        return $this->injector->make(PdoBristolStairsRepo::class);
    }

    protected function getTestStairImageFileId(): string
    {
        return $this->createTestStairImageFileId();
    }

    /**
     * Helper method to create a valid stair image file ID
     * Required because of foreign key constraint
     */
    private function createTestStairImageFileId(): string
    {
        $stairImageRepo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user = $this->createTestAdminUser();
        
        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . '.jpg';
        
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        
        $fileId = $stairImageRepo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename,
            $uploadedFile
        );
        
        $stairImageRepo->setUploaded($fileId);
        
        return $fileId;
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_constructor(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);
        $this->assertInstanceOf(PdoBristolStairsRepo::class, $repo);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_store_stairs_info_creates_new_stair(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);
        $imageFileId = $this->createTestStairImageFileId();

        $stair_info = $repo->store_stairs_info(
            $imageFileId,
            'Test stairs description',
            51.4600,
            -2.6000,
            50
        );

        $this->assertInstanceOf(BristolStairInfo::class, $stair_info);
        $this->assertSame('Test stairs description', $stair_info->description);
        $this->assertSame(51.46, $stair_info->latitude);
        $this->assertSame(-2.6, $stair_info->longitude);
        $this->assertSame(50, $stair_info->steps);
        $this->assertSame(0, $stair_info->is_deleted);
        $this->assertInstanceOf(\DateTimeInterface::class, $stair_info->created_at);
        $this->assertInstanceOf(\DateTimeInterface::class, $stair_info->updated_at);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_getStairInfoById_returns_correct_stair(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);
        $imageFileId = $this->createTestStairImageFileId();

        $created_stair = $repo->store_stairs_info(
            $imageFileId,
            'Findable stairs',
            51.4556,
            -2.5943,
            45
        );

        $found_stair = $repo->getStairInfoById((int)$created_stair->id);

        $this->assertInstanceOf(BristolStairInfo::class, $found_stair);
        $this->assertSame($created_stair->id, $found_stair->id);
        $this->assertSame('Findable stairs', $found_stair->description);
        $this->assertSame(45, $found_stair->steps);
        $this->assertSame(51.4556, $found_stair->latitude);
        $this->assertSame(-2.5943, $found_stair->longitude);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_getStairInfoById_returns_null_for_nonexistent_id(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);

        $result = $repo->getStairInfoById(999999);

        $this->assertNull($result);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_getAllStairsInfo_returns_array(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);

        $all_stairs = $repo->getAllStairsInfo();

        $this->assertIsArray($all_stairs);
        foreach ($all_stairs as $stair) {
            $this->assertInstanceOf(BristolStairInfo::class, $stair);
            $this->assertSame(0, $stair->is_deleted);
        }
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_getAllStairsInfo_includes_newly_created_stair(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);

        $stairs_before = $repo->getAllStairsInfo();
        $count_before = count($stairs_before);

        $unique_description = 'Unique test stairs ' . time() . '_' . random_int(1000, 9999);
        $imageFileId = $this->createTestStairImageFileId();
        $repo->store_stairs_info(
            $imageFileId,
            $unique_description,
            51.4600,
            -2.6000,
            30
        );

        $stairs_after = $repo->getAllStairsInfo();
        $count_after = count($stairs_after);

        $this->assertSame($count_before + 1, $count_after);

        // Verify the new stair is in the results
        $found = false;
        foreach ($stairs_after as $stair) {
            if ($stair->description === $unique_description) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Newly created stair should be in getAllStairsInfo results');
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_updateStairInfo_updates_description_and_steps(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);
        $imageFileId = $this->createTestStairImageFileId();

        $created_stair = $repo->store_stairs_info(
            $imageFileId,
            'Original description',
            51.4600,
            -2.6000,
            40
        );

        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => (string)$created_stair->id,
            'description' => 'Updated description',
            'steps' => '80'
        ]));

        $repo->updateStairInfo($params);

        $updated_stair = $repo->getStairInfoById((int)$created_stair->id);
        $this->assertSame('Updated description', $updated_stair->description);
        $this->assertSame(80, $updated_stair->steps);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_updateStairInfo_preserves_other_fields(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);
        $imageFileId = $this->createTestStairImageFileId();

        $created_stair = $repo->store_stairs_info(
            $imageFileId,
            'Original description',
            51.4600,
            -2.6000,
            40
        );

        $original_latitude = $created_stair->latitude;
        $original_longitude = $created_stair->longitude;
        $original_image_id = $created_stair->stored_stair_image_file_id;

        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => (string)$created_stair->id,
            'description' => 'Updated description',
            'steps' => '80'
        ]));

        $repo->updateStairInfo($params);

        $updated_stair = $repo->getStairInfoById((int)$created_stair->id);
        $this->assertSame($original_latitude, $updated_stair->latitude);
        $this->assertSame($original_longitude, $updated_stair->longitude);
        $this->assertSame($original_image_id, $updated_stair->stored_stair_image_file_id);
        $this->assertSame(0, $updated_stair->is_deleted);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_updateStairInfo_throws_exception_for_nonexistent_id(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);

        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '999999',
            'description' => 'Updated description',
            'steps' => '100'
        ]));

        $this->expectException(ContentNotFoundException::class);
        $repo->updateStairInfo($params);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_updateStairPosition_updates_coordinates(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);
        $imageFileId = $this->createTestStairImageFileId();

        $created_stair = $repo->store_stairs_info(
            $imageFileId,
            'Position test stairs',
            51.4600,
            -2.6000,
            40
        );

        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => (string)$created_stair->id,
            'latitude' => 51.5000,
            'longitude' => -2.7000
        ]));

        $repo->updateStairPosition($params);

        $updated_stair = $repo->getStairInfoById((int)$created_stair->id);
        $this->assertSame(51.5, $updated_stair->latitude);
        $this->assertSame(-2.7, $updated_stair->longitude);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_updateStairPosition_preserves_other_fields(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);
        $imageFileId = $this->createTestStairImageFileId();

        $created_stair = $repo->store_stairs_info(
            $imageFileId,
            'Position test stairs',
            51.4600,
            -2.6000,
            40
        );

        $original_description = $created_stair->description;
        $original_steps = $created_stair->steps;
        $original_image_id = $created_stair->stored_stair_image_file_id;

        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => (string)$created_stair->id,
            'latitude' => 51.5000,
            'longitude' => -2.7000
        ]));

        $repo->updateStairPosition($params);

        $updated_stair = $repo->getStairInfoById((int)$created_stair->id);
        $this->assertSame($original_description, $updated_stair->description);
        $this->assertSame($original_steps, $updated_stair->steps);
        $this->assertSame($original_image_id, $updated_stair->stored_stair_image_file_id);
        $this->assertSame(0, $updated_stair->is_deleted);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_updateStairPosition_throws_exception_for_nonexistent_id(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);

        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '999999',
            'latitude' => 51.5000,
            'longitude' => -2.7000
        ]));

        $this->expectException(ContentNotFoundException::class);
        $repo->updateStairPosition($params);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_get_total_number_of_steps_returns_integers(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);

        // Get current totals (database might have existing data)
        [$flights, $steps] = $repo->get_total_number_of_steps();

        // The results should be integers (cast from strings if necessary)
        $this->assertGreaterThanOrEqual(0, (int)$flights);
        $this->assertGreaterThanOrEqual(0, (int)$steps);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_get_total_number_of_steps_includes_new_stairs(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);

        [$flights_before, $steps_before] = $repo->get_total_number_of_steps();
        
        // Cast to int since the function returns integers but PHP may represent them differently
        $flights_before = (int)$flights_before;
        $steps_before = (int)$steps_before;
        
        $imageFileId = $this->createTestStairImageFileId();

        $repo->store_stairs_info(
            $imageFileId,
            'Test stairs for count',
            51.4600,
            -2.6000,
            25
        );

        [$flights_after, $steps_after] = $repo->get_total_number_of_steps();
        $flights_after = (int)$flights_after;
        $steps_after = (int)$steps_after;

        $this->assertSame($flights_before + 1, $flights_after);
        $this->assertSame($steps_before + 25, $steps_after);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_multiple_operations_in_sequence(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);
        $imageFileId = $this->createTestStairImageFileId();

        // Store new stair
        $stair = $repo->store_stairs_info(
            $imageFileId,
            'Sequential test stairs',
            51.4700,
            -2.6100,
            60
        );

        $stair_id = (int)$stair->id;

        // Update its info
        $info_params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => (string)$stair_id,
            'description' => 'Updated sequential stairs',
            'steps' => '70'
        ]));
        $repo->updateStairInfo($info_params);

        // Update its position
        $position_params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => (string)$stair_id,
            'latitude' => 51.4800,
            'longitude' => -2.6200
        ]));
        $repo->updateStairPosition($position_params);

        // Verify final state
        $final_stair = $repo->getStairInfoById($stair_id);
        $this->assertSame('Updated sequential stairs', $final_stair->description);
        $this->assertSame(70, $final_stair->steps);
        $this->assertSame(51.48, $final_stair->latitude);
        $this->assertSame(-2.62, $final_stair->longitude);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_store_stairs_info_with_precise_coordinates(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);
        $imageFileId = $this->createTestStairImageFileId();

        $stair = $repo->store_stairs_info(
            $imageFileId,
            'Precise coordinates test',
            51.123456,
            -2.654321,
            10
        );

        $this->assertSame(51.123456, $stair->latitude);
        $this->assertSame(-2.654321, $stair->longitude);

        // Verify it persists correctly
        $retrieved_stair = $repo->getStairInfoById((int)$stair->id);
        $this->assertSame(51.123456, $retrieved_stair->latitude);
        $this->assertSame(-2.654321, $retrieved_stair->longitude);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo
     */
    public function test_store_stairs_info_with_various_step_counts(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);

        // Test with small step count
        $imageFileId1 = $this->createTestStairImageFileId();
        $stair1 = $repo->store_stairs_info(
            $imageFileId1,
            'Few steps',
            51.4600,
            -2.6000,
            5
        );
        $this->assertSame(5, $stair1->steps);

        // Test with large step count
        $imageFileId2 = $this->createTestStairImageFileId();
        $stair2 = $repo->store_stairs_info(
            $imageFileId2,
            'Many steps',
            51.4601,
            -2.6001,
            200
        );
        $this->assertSame(200, $stair2->steps);

        // Test with zero steps
        $imageFileId3 = $this->createTestStairImageFileId();
        $stair3 = $repo->store_stairs_info(
            $imageFileId3,
            'No steps',
            51.4602,
            -2.6002,
            0
        );
        $this->assertSame(0, $stair3->steps);
    }
}
