<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\BucketManagementProd;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\CliOutput\CliExitRequestedException;
use BristolianTest\BaseTestCase;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversMethod;
use function Safe\mkdir;
use function Safe\rmdir;
use function Safe\unlink;

#[CoversMethod(\Bristolian\CliController\BucketManagementProd::class, '__construct')]
#[CoversMethod(\Bristolian\CliController\BucketManagementProd::class, 'archiveBucket')]
#[CoversMethod(\Bristolian\CliController\BucketManagementProd::class, 'archive')]

class BucketManagementProdTest extends BaseTestCase
{
    private ?string $remoteDir = null;

    private ?string $localDir = null;

    public function tearDown(): void
    {
        $this->cleanupDir($this->remoteDir);
        $this->cleanupDir($this->localDir);
        parent::tearDown();
    }

    public function test_archiveBucket_dry_run_lists_without_writing(): void
    {
        [$remote, $local] = $this->createFilesystems();
        $remote->write('a.jpg', 'remote-a');
        $remote->write('nested/b.txt', 'remote-b');

        $output = new CapturingCliOutput();
        $controller = new BucketManagementProd($output);

        $controller->archiveBucket('bristolian-memes', $remote, $local, false);

        $captured = $output->getCapturedOutput();
        $this->assertStringContainsString('Bucket: bristolian-memes', $captured);
        $this->assertStringContainsString('would download: bristolian-memes/a.jpg', $captured);
        $this->assertStringContainsString('would download: bristolian-memes/nested/b.txt', $captured);
        $this->assertStringContainsString('(would download: 2, skipped: 0)', $captured);
        $this->assertFalse($local->fileExists('bristolian-memes/a.jpg'));
        $this->assertFalse($local->fileExists('bristolian-memes/nested/b.txt'));
    }

    public function test_archiveBucket_download_copies_new_files(): void
    {
        [$remote, $local] = $this->createFilesystems();
        $remote->write('a.jpg', 'remote-a');
        $remote->write('nested/b.txt', 'remote-b');

        $output = new CapturingCliOutput();
        $controller = new BucketManagementProd($output);

        $controller->archiveBucket('bristolian-memes', $remote, $local, true);

        $captured = $output->getCapturedOutput();
        $this->assertStringContainsString('downloaded: bristolian-memes/a.jpg', $captured);
        $this->assertStringContainsString('downloaded: bristolian-memes/nested/b.txt', $captured);
        $this->assertSame('remote-a', $local->read('bristolian-memes/a.jpg'));
        $this->assertSame('remote-b', $local->read('bristolian-memes/nested/b.txt'));
    }

    public function test_archiveBucket_second_run_skips_unchanged_files(): void
    {
        [$remote, $local] = $this->createFilesystems();
        $remote->write('a.jpg', 'remote-a');

        $output = new CapturingCliOutput();
        $controller = new BucketManagementProd($output);
        $controller->archiveBucket('bristolian-memes', $remote, $local, true);

        $secondOutput = new CapturingCliOutput();
        $secondController = new BucketManagementProd($secondOutput);
        $secondController->archiveBucket('bristolian-memes', $remote, $local, true);

        $captured = $secondOutput->getCapturedOutput();
        $this->assertStringContainsString('skip (exists): bristolian-memes/a.jpg', $captured);
        $this->assertStringContainsString('(downloaded: 0, skipped: 1)', $captured);
        $this->assertSame('remote-a', $local->read('bristolian-memes/a.jpg'));
    }

    public function test_archiveBucket_size_mismatch_re_downloads(): void
    {
        [$remote, $local] = $this->createFilesystems();
        $remote->write('a.jpg', 'new-content-longer');
        $local->write('bristolian-memes/a.jpg', 'old');

        $output = new CapturingCliOutput();
        $controller = new BucketManagementProd($output);
        $controller->archiveBucket('bristolian-memes', $remote, $local, true);

        $captured = $output->getCapturedOutput();
        $this->assertStringContainsString('replaced: bristolian-memes/a.jpg', $captured);
        $this->assertSame('new-content-longer', $local->read('bristolian-memes/a.jpg'));
    }

    public function test_archiveBucket_dry_run_reports_size_mismatch(): void
    {
        [$remote, $local] = $this->createFilesystems();
        $remote->write('a.jpg', 'new-content-longer');
        $local->write('bristolian-memes/a.jpg', 'old');

        $output = new CapturingCliOutput();
        $controller = new BucketManagementProd($output);
        $controller->archiveBucket('bristolian-memes', $remote, $local, false);

        $captured = $output->getCapturedOutput();
        $this->assertStringContainsString(
            'would replace (size mismatch): bristolian-memes/a.jpg',
            $captured
        );
        $this->assertSame('old', $local->read('bristolian-memes/a.jpg'));
    }

    public function test_archive_rejects_unknown_mode(): void
    {
        $output = new CapturingCliOutput();
        $controller = new BucketManagementProd($output);

        try {
            $controller->archive('explode');
            $this->fail('Expected CliExitRequestedException');
        }
        catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
        }

        $this->assertStringContainsString("Unknown mode 'explode'", $output->getCapturedOutput());
    }

    /**
     * @return array{0: Filesystem, 1: Filesystem}
     */
    private function createFilesystems(): array
    {
        $this->remoteDir = sys_get_temp_dir() . '/BucketManagementProd_remote_' . uniqid();
        $this->localDir = sys_get_temp_dir() . '/BucketManagementProd_local_' . uniqid();
        mkdir($this->remoteDir);
        mkdir($this->localDir);

        $remote = new Filesystem(new LocalFilesystemAdapter($this->remoteDir));
        $local = new Filesystem(new LocalFilesystemAdapter($this->localDir));

        return [$remote, $local];
    }

    private function cleanupDir(string|null $directory): void
    {
        if ($directory === null || is_dir($directory) !== true) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($fileInfo->getPathname());
            }
            else {
                unlink($fileInfo->getPathname());
            }
        }

        rmdir($directory);
    }
}
