<?php

namespace BristolianTest\CliController;

use BristolianTest\BaseTestCase;
use Bristolian\CliController\BristolStairs;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\Filesystem\BristolStairsFilesystem;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage;
use Bristolian\Service\BristolStairImageStorage\UploadError;
use Bristolian\Service\BristolStairImageStorage\ObjectStoredFileInfo;
use Bristolian\UploadedFiles\UploadedFile;
use PHPUnit\Framework\MockObject\MockObject;
use Bristolian\Repo\BristolStairsRepo\FakeBristolStairsRepo;

/**
 * @coversNothing
 */
class BristolStairsTest extends BaseTestCase
{
    /**
     * Test the total method with valid data
     * @covers \Bristolian\CliController\BristolStairs::total
     */
    public function test_total_with_valid_data(): void
    {
        $this->injector->alias(
            BristolStairsRepo::class,
            FakeBristolStairsRepo::class
        );

        $stairs_repo = new FakeBristolStairsRepo();
        $this->injector->share($stairs_repo);

        ob_start();
        $this->injector->execute([BristolStairs::class, 'total']);
        $output = ob_get_clean();
        // TODO - any possible asserts?
    }

    /**
     * Test the total method with zero steps
     */
    public function test_total_with_zero_steps(): void
    {
        // TODO: Implement test
        // - Mock BristolStairsRepo to return 0 flights and 0 steps
        // - Call total method
        // - Assert correct output is echoed
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test the total method with large numbers
     */
    public function test_total_with_large_numbers(): void
    {
        // TODO: Implement test
        // - Mock BristolStairsRepo to return large numbers
        // - Call total method
        // - Assert correct output is echoed
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test check_contents with no files in storage
     */
    public function test_check_contents_with_no_files(): void
    {
        // TODO: Implement test
        // - Mock BristolStairsFilesystem to return empty list
        // - Call check_contents method
        // - Assert correct output (no unknown files)
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test check_contents with known files only
     */
    public function test_check_contents_with_known_files_only(): void
    {
        // TODO: Implement test
        // - Mock BristolStairsFilesystem to return list of files
        // - Mock BristolStairImageStorageInfoRepo to return non-null for all files
        // - Call check_contents method
        // - Assert no unknown files are reported
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test check_contents with unknown files
     */
    public function test_check_contents_with_unknown_files(): void
    {
        // TODO: Implement test
        // - Mock BristolStairsFilesystem to return list of files
        // - Mock BristolStairImageStorageInfoRepo to return null for some files
        // - Call check_contents method
        // - Assert unknown files are reported correctly
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test check_contents with mixed known and unknown files
     */
    public function test_check_contents_with_mixed_files(): void
    {
        // TODO: Implement test
        // - Mock BristolStairsFilesystem to return list of files
        // - Mock BristolStairImageStorageInfoRepo to return mixed results
        // - Call check_contents method
        // - Assert both known and unknown files are handled correctly
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test check_contents when filesystem throws UnableToListContents exception
     */
    public function test_check_contents_filesystem_exception(): void
    {
        // TODO: Implement test
        // - Mock BristolStairsFilesystem to throw UnableToListContents exception
        // - Call check_contents method
        // - Assert correct error message is output and exit(-1) is called
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * @group wip2
     * Test create method with valid admin user and successful upload
     */
    public function test_create_successful_upload(): void
    {
        $this->markTestSkipped('Test not implemented yet');

        $this->setupStandardWorkingFakes();
        ob_start();
        $result = $this->injector->execute([BristolStairs::class, 'create']);
        $output = ob_get_clean();

        var_dump($output);
    }

    /**
     * Test create method when admin user is not found
     */
    public function test_create_admin_user_not_found(): void
    {
        // TODO: Implement test

        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test create method when file upload fails
     */
    public function test_create_upload_failure(): void
    {
        // TODO: Implement test

        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test create method with non-existent image file
     */
    public function test_create_with_nonexistent_file(): void
    {
        // TODO: Implement test
        // - Call create method with non-existent file path
        // - Assert appropriate error handling
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test create method with invalid image file format
     */
    public function test_create_with_invalid_file_format(): void
    {
        // TODO: Implement test
        // - Create temporary file with invalid format
        // - Mock AdminRepo to return valid user ID
        // - Mock BristolStairImageStorage to return UploadError for invalid format
        // - Call create method
        // - Assert upload failure is handled correctly
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test create method with valid image file but storage service error
     */
    public function test_create_storage_service_error(): void
    {
        // TODO: Implement test
        // - Mock AdminRepo to return valid user ID
        // - Mock BristolStairImageStorage to throw exception
        // - Create temporary test image file
        // - Call create method
        // - Assert error is handled appropriately
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test create method with different image file extensions
     */
    public function test_create_with_different_image_extensions(): void
    {
        // TODO: Implement test
        // - Test with .jpg, .jpeg, .png, .gif files
        // - Mock AdminRepo and BristolStairImageStorage appropriately
        // - Assert all supported formats work correctly
        $this->markTestIncomplete('Test not implemented yet');
    }

    /**
     * Test create method with GPS parameters
     */
    public function test_create_with_gps_parameters(): void
    {
        // TODO: Implement test
        // - Mock AdminRepo to return valid user ID
        // - Mock BristolStairImageStorage to verify GPS parameters are passed correctly
        // - Create temporary test image file
        // - Call create method
        // - Assert GPS parameters are handled correctly
        $this->markTestIncomplete('Test not implemented yet');
    }
}
