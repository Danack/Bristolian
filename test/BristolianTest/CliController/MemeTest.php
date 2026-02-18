<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\Meme;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\CliOutput\CliExitRequestedException;
use BristolianTest\BaseTestCase;
use Bristolian\UploadedFiles\UploadedFile;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToListContents;

/**
 * Adapter that throws UnableToListContents when listContents is iterated.
 */
final class MemeTestThrowingListAdapter extends LocalFilesystemAdapter
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
 * @coversNothing
 */
class MemeTest extends BaseTestCase
{
    private ?string $testFsDir = null;

    public function tearDown(): void
    {
        if ($this->testFsDir !== null && is_dir($this->testFsDir)) {
            foreach (['known.jpg', 'unknown.jpg', 'present.jpg', 'a.jpg', 'b.jpg'] as $f) {
                $path = $this->testFsDir . '/' . $f;
                if (is_file($path)) {
                    unlink($path);
                }
            }
            rmdir($this->testFsDir);
        }
        parent::tearDown();
    }

    /**
     * @covers \Bristolian\CliController\Meme::__construct
     * @covers \Bristolian\CliController\Meme::check_contents_of_storage
     */
    public function test_check_contents_of_storage_reports_unknown_files(): void
    {
        $this->testFsDir = __DIR__ . '/MemeTest_fs_' . uniqid();
        mkdir($this->testFsDir);
        file_put_contents($this->testFsDir . '/known.jpg', 'x');
        file_put_contents($this->testFsDir . '/unknown.jpg', 'y');

        $output = new CapturingCliOutput();
        $this->injector->share($output);

        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user-1', 'known.jpg', UploadedFile::fromFile(__FILE__));

        $adapter = new LocalFilesystemAdapter($this->testFsDir);
        $memeFilesystem = new MemeFilesystem($adapter, []);

        $controller = $this->injector->make(Meme::class);
        $controller->check_contents_of_storage($repo, $memeFilesystem);

        $full = $output->getCapturedOutput();
        $this->assertStringContainsString('The files that exist in storage, but not in the database are:', $full);
        $this->assertStringContainsString("'unknown.jpg'", $full);
    }

    /**
     * @covers \Bristolian\CliController\Meme::check_contents_of_storage
     * @covers \Bristolian\Service\CliOutput\CapturingCliOutput::exit
     * @covers \Bristolian\Service\CliOutput\CliExitRequestedException::getExitCode
     */
    public function test_check_contents_of_storage_when_list_throws_writes_message_and_exits(): void
    {
        $output = new CapturingCliOutput();
        $this->injector->share($output);

        $adapter = new MemeTestThrowingListAdapter(sys_get_temp_dir());
        $memeFilesystem = new MemeFilesystem($adapter, []);
        $repo = new FakeMemeStorageRepo();

        $controller = $this->injector->make(Meme::class);

        try {
            $controller->check_contents_of_storage($repo, $memeFilesystem);
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $e) {
            $this->assertSame(-1, $e->getExitCode());
        }
        $this->assertStringContainsString('Failed to list files in storage', $output->getCapturedOutput());
        $this->assertStringContainsString('test list failure', $output->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\Meme::check_contents_of_database
     */
    public function test_check_contents_of_database_marks_missing_files_and_outputs_deleted_line(): void
    {
        $this->testFsDir = __DIR__ . '/MemeTest_fs_' . uniqid();
        mkdir($this->testFsDir);
        file_put_contents($this->testFsDir . '/present.jpg', 'x');

        $output = new CapturingCliOutput();
        $this->injector->share($output);

        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $repo = new FakeMemeStorageRepo();
        $idPresent = $repo->storeMeme('user-1', 'present.jpg', $uploadedFile);
        $idMissing = $repo->storeMeme('user-1', 'missing.jpg', $uploadedFile);
        $repo->setUploaded($idPresent);
        $repo->setUploaded($idMissing);

        $adapter = new LocalFilesystemAdapter($this->testFsDir);
        $memeFilesystem = new MemeFilesystem($adapter, []);

        $controller = $this->injector->make(Meme::class);
        $controller->check_contents_of_database($repo, $memeFilesystem);

        $full = $output->getCapturedOutput();
        $this->assertStringContainsString('.', $full);
        $this->assertStringContainsString("\nmissing.jpg is deleted\n", $full);
        $this->assertCount(1, $repo->listAllMemes());
        $this->assertSame('present.jpg', $repo->listAllMemes()[0]->normalized_name);
        $this->assertTrue($repo->getMeme($idMissing)->deleted);
    }

    /**
     * @covers \Bristolian\CliController\Meme::check_contents_of_database
     */
    public function test_check_contents_of_database_when_all_files_present_outputs_dots_only(): void
    {
        $this->testFsDir = __DIR__ . '/MemeTest_fs_' . uniqid();
        mkdir($this->testFsDir);
        file_put_contents($this->testFsDir . '/a.jpg', 'x');
        file_put_contents($this->testFsDir . '/b.jpg', 'y');

        $output = new CapturingCliOutput();
        $this->injector->share($output);

        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user-1', 'a.jpg', $uploadedFile);
        $repo->storeMeme('user-1', 'b.jpg', $uploadedFile);
        $repo->setUploaded($repo->getByNormalizedName('a.jpg')->id);
        $repo->setUploaded($repo->getByNormalizedName('b.jpg')->id);

        $adapter = new LocalFilesystemAdapter($this->testFsDir);
        $memeFilesystem = new MemeFilesystem($adapter, []);

        $controller = $this->injector->make(Meme::class);
        $controller->check_contents_of_database($repo, $memeFilesystem);

        $full = $output->getCapturedOutput();
        $this->assertSame("..\n", $full);
        $this->assertCount(2, $repo->listAllMemes());
    }
}
