<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\BucketManagementDev;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\CliOutput\CliExitRequestedException;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToListContents;
use PHPUnit\Framework\Attributes\CoversMethod;
use function Safe\file_put_contents;
use function Safe\mkdir;
use function Safe\rmdir;
use function Safe\unlink;

/**
 * Adapter that throws UnableToListContents when listContents is iterated.
 */
final class BucketManagementDevThrowingListAdapter extends LocalFilesystemAdapter
{
    public function listContents(string $path, bool $deep): iterable
    {
        return (function () use ($path, $deep): \Generator {
            throw UnableToListContents::atLocation($path, $deep, new \Exception('test list failure'));
            yield;
        })();
    }
}

#[CoversMethod(\Bristolian\CliController\BucketManagementDev::class, '__construct')]
#[CoversMethod(\Bristolian\CliController\BucketManagementDev::class, 'clearBucket')]
#[CoversMethod(\Bristolian\CliController\BucketManagementDev::class, 'clear')]

class BucketManagementDevTest extends BaseTestCase
{
    private ?string $testFsDir = null;

    public function tearDown(): void
    {
        if ($this->testFsDir !== null && is_dir($this->testFsDir)) {
            foreach (['a.jpg', 'nested/b.txt', 'nested'] as $relativePath) {
                $path = $this->testFsDir . '/' . $relativePath;
                if (is_file($path)) {
                    unlink($path);
                }
                if (is_dir($path)) {
                    rmdir($path);
                }
            }
            rmdir($this->testFsDir);
        }
        parent::tearDown();
    }

    public function test_clearBucket_dry_run_lists_files_without_deleting(): void
    {
        $this->testFsDir = __DIR__ . '/BucketManagementDevTest_fs_' . uniqid();
        mkdir($this->testFsDir);
        mkdir($this->testFsDir . '/nested');
        file_put_contents($this->testFsDir . '/a.jpg', 'x');
        file_put_contents($this->testFsDir . '/nested/b.txt', 'y');

        $output = new CapturingCliOutput();
        $controller = new BucketManagementDev($output);
        $filesystem = new Filesystem(new LocalFilesystemAdapter($this->testFsDir));

        $controller->clearBucket('bristolian-memes-dev', $filesystem, false);

        $captured = $output->getCapturedOutput();
        $this->assertStringContainsString('Bucket: bristolian-memes-dev', $captured);
        $this->assertStringContainsString('would delete: a.jpg', $captured);
        $this->assertStringContainsString('would delete: nested/b.txt', $captured);
        $this->assertStringContainsString('(2 file(s))', $captured);
        $this->assertTrue(is_file($this->testFsDir . '/a.jpg'));
        $this->assertTrue(is_file($this->testFsDir . '/nested/b.txt'));
    }

    public function test_clearBucket_delete_removes_files(): void
    {
        $this->testFsDir = __DIR__ . '/BucketManagementDevTest_fs_' . uniqid();
        mkdir($this->testFsDir);
        mkdir($this->testFsDir . '/nested');
        file_put_contents($this->testFsDir . '/a.jpg', 'x');
        file_put_contents($this->testFsDir . '/nested/b.txt', 'y');

        $output = new CapturingCliOutput();
        $controller = new BucketManagementDev($output);
        $filesystem = new Filesystem(new LocalFilesystemAdapter($this->testFsDir));

        $controller->clearBucket('bristolian-memes-dev', $filesystem, true);

        $captured = $output->getCapturedOutput();
        $this->assertStringContainsString('deleted: a.jpg', $captured);
        $this->assertStringContainsString('deleted: nested/b.txt', $captured);
        $this->assertFalse(is_file($this->testFsDir . '/a.jpg'));
        $this->assertFalse(is_file($this->testFsDir . '/nested/b.txt'));
    }

    public function test_clearBucket_exits_when_listing_fails(): void
    {
        $this->testFsDir = __DIR__ . '/BucketManagementDevTest_fs_' . uniqid();
        mkdir($this->testFsDir);

        $output = new CapturingCliOutput();
        $controller = new BucketManagementDev($output);
        $filesystem = new Filesystem(new BucketManagementDevThrowingListAdapter($this->testFsDir));

        try {
            $controller->clearBucket('bristolian-memes-dev', $filesystem, false);
            $this->fail('Expected CliExitRequestedException');
        }
        catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
        }

        $this->assertStringContainsString('Failed to list contents', $output->getCapturedOutput());
    }

    public function test_clear_rejects_unknown_mode(): void
    {
        $output = new CapturingCliOutput();
        $controller = new BucketManagementDev($output);

        try {
            $controller->clear('explode');
            $this->fail('Expected CliExitRequestedException');
        }
        catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
        }

        $this->assertStringContainsString("Unknown mode 'explode'", $output->getCapturedOutput());
    }
}
