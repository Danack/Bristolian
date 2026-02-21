<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\BristolStairs;
use Bristolian\Filesystem\BristolStairsFilesystem;
use Bristolian\Model\Generated\StairImageObjectInfo;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\FileState;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo;
use Bristolian\Repo\BristolStairsRepo\FakeBristolStairsRepo;
use Bristolian\Repo\AdminRepo\FakeAdminRepo;
use Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage;
use Bristolian\Service\BristolStairImageStorage\UploadError;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\CliOutput\CliExitRequestedException;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToListContents;

/**
 * Adapter that throws UnableToListContents when the listing is iterated (so BristolStairs' try/foreach catches it).
 */
final class ThrowingListContentsAdapter extends LocalFilesystemAdapter
{
    public function listContents(string $path, bool $deep): iterable
    {
        return (function () use ($path, $deep): \Generator {
            throw UnableToListContents::atLocation($path, $deep, new \Exception('test list failure'));
            yield;
        })();
    }
}

/**
 * Storage info repo that returns one known file for a given path so we can hit the "known files" branch.
 */
final class BristolStairImageStorageInfoRepoWithOneKnown implements BristolStairImageStorageInfoRepo
{
    private string $knownNormalizedName;

    public function __construct(string $knownNormalizedName)
    {
        $this->knownNormalizedName = $knownNormalizedName;
    }

    public function storeFileInfo(string $user_id, string $normalized_filename, \Bristolian\UploadedFiles\UploadedFile $uploadedFile): string
    {
        throw new \BadMethodCallException('not used in this test');
    }

    public function getById(string $bristol_stairs_image_id): StairImageObjectInfo|null
    {
        return null;
    }

    public function getByNormalizedName(string $normalized_name): StairImageObjectInfo|null
    {
        if ($normalized_name === $this->knownNormalizedName) {
            return new StairImageObjectInfo(
                id: 'id-1',
                normalized_name: $this->knownNormalizedName,
                original_filename: 'original',
                state: FileState::INITIAL->value,
                size: 0,
                user_id: 'user-1',
                created_at: new \DateTimeImmutable(),
            );
        }
        return null;
    }

    public function setUploaded(string $file_storage_id): void
    {
    }
}

/**
 * BristolStairImageStorage that always returns UploadError for create() failure path.
 */
final class BristolStairImageStorageReturningUploadError implements BristolStairImageStorage
{
    public function storeFileForUser(
        string $user_id,
        \Bristolian\UploadedFiles\UploadedFile $uploadedFile,
        array $allowedExtensions,
        \Bristolian\Parameters\BristolStairsGpsParams $gpsParams
    ): UploadError {
        return UploadError::unsupportedFileType();
    }
}

/**
 * @coversNothing
 */
class BristolStairsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\CliController\BristolStairs::__construct
     * @covers \Bristolian\CliController\BristolStairs::total
     * @covers \Bristolian\Service\CliOutput\CapturingCliOutput::write
     * @covers \Bristolian\Service\CliOutput\CapturingCliOutput::getCapturedOutput
     */
    public function test_total_outputs_steps_and_flights(): void
    {
        $output = new CapturingCliOutput();
        $this->injector->share($output);
        $this->injector->alias(\Bristolian\Repo\BristolStairsRepo\BristolStairsRepo::class, FakeBristolStairsRepo::class);
        $repo = new FakeBristolStairsRepo();
        $this->injector->share($repo);

        $controller = $this->injector->make(BristolStairs::class);
        $controller->total($repo);

        $this->assertStringContainsString('105 steps', $output->getCapturedOutput());
        $this->assertStringContainsString('3 flights_of_stairs', $output->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\BristolStairs::check_contents
     * @covers \Bristolian\Service\CliOutput\CapturingCliOutput::getCapturedLines
     */
    public function test_check_contents_reports_unknown_files(): void
    {
        $output = new CapturingCliOutput();
        $this->injector->share($output);
        $storageInfoRepo = new FakeBristolStairImageStorageInfoRepo();
        $adapter = new LocalFilesystemAdapter(__DIR__);
        $filesystem = new BristolStairsFilesystem($adapter, []);

        $controller = $this->injector->make(BristolStairs::class);
        $controller->check_contents($storageInfoRepo, $filesystem);

        $lines = $output->getCapturedLines();
        $this->assertNotEmpty($lines);
        $this->assertSame('Unknown files:', trim($lines[0]));
        $full = $output->getCapturedOutput();
        $this->assertStringContainsString('Unknown files:', $full);
    }

    /**
     * @covers \Bristolian\CliController\BristolStairs::create
     * @covers \Bristolian\Service\CliOutput\CapturingCliOutput::exit
     * @covers \Bristolian\Service\CliOutput\CliExitRequestedException::__construct
     * @covers \Bristolian\Service\CliOutput\CliExitRequestedException::getExitCode
     */
    public function test_create_when_admin_not_found_writes_message_and_exits(): void
    {
        $output = new CapturingCliOutput();
        $this->injector->share($output);
        $adminRepo = new FakeAdminRepo([]);
        $this->injector->share($adminRepo);
        $this->injector->alias(\Bristolian\Repo\AdminRepo\AdminRepo::class, FakeAdminRepo::class);
        $storage = new \Bristolian\Service\BristolStairImageStorage\FakeWorksBristolStairImageStorage();
        $this->injector->share($storage);
        $this->injector->alias(\Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage::class, \Bristolian\Service\BristolStairImageStorage\FakeWorksBristolStairImageStorage::class);

        $controller = $this->injector->make(BristolStairs::class);

        try {
            $controller->create($adminRepo, $storage, __DIR__ . '/../../sample.pdf');
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $e) {
            $this->assertSame(-1, $e->getExitCode());
            $this->assertStringContainsString('Failed to find admin user', $output->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\BristolStairs::create
     */
    public function test_create_success_does_not_exit(): void
    {
        $output = new CapturingCliOutput();
        $this->injector->share($output);
        $adminUser = \Bristolian\Model\Types\AdminUser::new(
            'user-1',
            'testing@example.com',
            generate_password_hash('testing')
        );
        $adminRepo = new FakeAdminRepo([$adminUser]);
        $this->injector->share($adminRepo);
        $this->injector->alias(\Bristolian\Repo\AdminRepo\AdminRepo::class, FakeAdminRepo::class);
        $storage = new \Bristolian\Service\BristolStairImageStorage\FakeWorksBristolStairImageStorage();
        $this->injector->share($storage);
        $this->injector->alias(\Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage::class, \Bristolian\Service\BristolStairImageStorage\FakeWorksBristolStairImageStorage::class);

        $controller = $this->injector->make(BristolStairs::class);
        $controller->create($adminRepo, $storage, __DIR__ . '/../../sample.pdf');

        $this->assertSame('', $output->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\BristolStairs::check_contents
     */
    public function test_check_contents_when_listContents_throws_writes_message_and_exits(): void
    {
        $output = new CapturingCliOutput();
        $this->injector->share($output);
        $adapter = new ThrowingListContentsAdapter(sys_get_temp_dir());
        $filesystem = new BristolStairsFilesystem($adapter, []);
        $storageInfoRepo = new FakeBristolStairImageStorageInfoRepo();

        $controller = $this->injector->make(BristolStairs::class);

        try {
            $controller->check_contents($storageInfoRepo, $filesystem);
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $e) {
            $this->assertSame(-1, $e->getExitCode());
        }
        $this->assertStringContainsString('Failed to list files in storage', $output->getCapturedOutput());
        $this->assertStringContainsString('test list failure', $output->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\BristolStairs::create
     */
    public function test_create_when_storage_returns_UploadError_writes_message_and_exits(): void
    {
        $output = new CapturingCliOutput();
        $this->injector->share($output);
        $adminUser = \Bristolian\Model\Types\AdminUser::new(
            'user-1',
            'testing@example.com',
            generate_password_hash('testing')
        );
        $adminRepo = new FakeAdminRepo([$adminUser]);
        $this->injector->share($adminRepo);
        $this->injector->alias(\Bristolian\Repo\AdminRepo\AdminRepo::class, FakeAdminRepo::class);
        $storage = new BristolStairImageStorageReturningUploadError();
        $this->injector->share($storage);
        $this->injector->alias(\Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage::class, BristolStairImageStorageReturningUploadError::class);

        $controller = $this->injector->make(BristolStairs::class);

        try {
            $controller->create($adminRepo, $storage, __DIR__ . '/../../sample.pdf');
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $e) {
            $this->assertSame(-1, $e->getExitCode());
        }
        $this->assertStringContainsString('Failed to upload file', $output->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\BristolStairs::check_contents
     */
    public function test_check_contents_with_mix_of_known_and_unknown_files(): void
    {
        $output = new CapturingCliOutput();
        $this->injector->share($output);
        $adapter = new LocalFilesystemAdapter(__DIR__);
        $filesystem = new BristolStairsFilesystem($adapter, []);
        $firstPath = null;
        foreach ($filesystem->listContents('') as $file) {
            $firstPath = $file->path();
            break;
        }
        $this->assertNotNull($firstPath, 'test dir must have at least one file');
        $storageInfoRepo = new BristolStairImageStorageInfoRepoWithOneKnown($firstPath);

        $controller = $this->injector->make(BristolStairs::class);
        $controller->check_contents($storageInfoRepo, $filesystem);

        $full = $output->getCapturedOutput();
        $this->assertStringContainsString('Unknown files:', $full);
    }
}
