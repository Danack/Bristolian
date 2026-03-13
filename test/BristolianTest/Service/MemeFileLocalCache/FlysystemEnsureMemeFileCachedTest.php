<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeFileLocalCache;

use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Psr7\Response;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\MemeFileLocalCache\FlysystemEnsureMemeFileCached;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToReadFile;

/**
 * @covers \Bristolian\Service\MemeFileLocalCache\FlysystemEnsureMemeFileCached
 */
class FlysystemEnsureMemeFileCachedTest extends BaseTestCase
{
    public function test_success_when_file_already_in_cache(): void
    {
        $cacheDir = sys_get_temp_dir() . '/fly_cache_' . uniqid();
        $memeDir = sys_get_temp_dir() . '/fly_meme_' . uniqid();
        mkdir($cacheDir, 0755, true);
        mkdir($memeDir, 0755, true);
        try {
            $local = new LocalCacheFilesystem(new LocalFilesystemAdapter($cacheDir), $cacheDir);
            $meme = new MemeFilesystem(new LocalFilesystemAdapter($memeDir));
            $local->write('in-cache.png', 'cached');
            $service = new FlysystemEnsureMemeFileCached();
            $out = new CapturingCliOutput();
            $repo = new FakeMemeStorageRepo();
            $result = $service->ensureMemeFileCached($local, $meme, 'in-cache.png', 'm1', $repo, $out);
            $this->assertTrue($result->succeeded);
            $this->assertSame('', $out->getCapturedOutput());
        }
        finally {
            $this->rrmdir($cacheDir);
            $this->rrmdir($memeDir);
        }
    }

    public function test_success_copies_from_meme_filesystem(): void
    {
        $cacheDir = sys_get_temp_dir() . '/fly_cache2_' . uniqid();
        $memeDir = sys_get_temp_dir() . '/fly_meme2_' . uniqid();
        mkdir($cacheDir, 0755, true);
        mkdir($memeDir, 0755, true);
        try {
            $local = new LocalCacheFilesystem(new LocalFilesystemAdapter($cacheDir), $cacheDir);
            $meme = new MemeFilesystem(new LocalFilesystemAdapter($memeDir));
            $meme->write('copy-me.png', 'blob');
            $service = new FlysystemEnsureMemeFileCached();
            $out = new CapturingCliOutput();
            $repo = new FakeMemeStorageRepo();
            $result = $service->ensureMemeFileCached($local, $meme, 'copy-me.png', 'm1', $repo, $out);
            $this->assertTrue($result->succeeded);
            $this->assertSame('blob', $local->read('copy-me.png'));
        }
        finally {
            $this->rrmdir($cacheDir);
            $this->rrmdir($memeDir);
        }
    }

    public function test_failure_writes_cli_and_returns_debug_when_read_fails(): void
    {
        $cacheDir = sys_get_temp_dir() . '/fly_cache3_' . uniqid();
        $memeDir = sys_get_temp_dir() . '/fly_meme3_' . uniqid();
        mkdir($cacheDir, 0755, true);
        mkdir($memeDir, 0755, true);
        try {
            $local = new LocalCacheFilesystem(new LocalFilesystemAdapter($cacheDir), $cacheDir);
            $meme = new MemeFilesystem(new LocalFilesystemAdapter($memeDir));
            $service = new FlysystemEnsureMemeFileCached();
            $out = new CapturingCliOutput();
            $repo = new FakeMemeStorageRepo();
            $result = $service->ensureMemeFileCached($local, $meme, 'missing.png', 'meme-99', $repo, $out);
            $this->assertFalse($result->succeeded);
            $this->assertNotNull($result->failureDebugInfo);
            $this->assertStringContainsString('Failed to download file', $result->failureDebugInfo);
            $this->assertStringContainsString('Failed to download file', $out->getCapturedOutput());
        }
        finally {
            $this->rrmdir($cacheDir);
            $this->rrmdir($memeDir);
        }
    }

    public function test_s3_404_marks_meme_deleted(): void
    {
        $cacheDir = sys_get_temp_dir() . '/fly_cache4_' . uniqid();
        $memeDir = sys_get_temp_dir() . '/fly_meme4_' . uniqid();
        mkdir($cacheDir, 0755, true);
        mkdir($memeDir, 0755, true);
        try {
            $local = new LocalCacheFilesystem(new LocalFilesystemAdapter($cacheDir), $cacheDir);
            $s3 = new S3Exception(
                'Not Found',
                new \Aws\Command('GetObject'),
                ['response' => new Response(404)]
            );
            $unable = UnableToReadFile::fromLocation('gone.png', 'read failed', $s3);
            $meme = new MemeFilesystemThrowsOnReadStream($memeDir, $unable);
            $service = new FlysystemEnsureMemeFileCached();
            $out = new CapturingCliOutput();
            $repo = new FakeMemeStorageRepo();
            $pdfPath = __DIR__ . '/../../../sample.pdf';
            $this->assertFileExists($pdfPath);
            $memeId = $repo->storeMeme('u1', 'gone.pdf', \Bristolian\UploadedFiles\UploadedFile::fromFile($pdfPath));
            $memeRow = $repo->getMeme($memeId);
            $this->assertNotNull($memeRow);
            $result = $service->ensureMemeFileCached(
                $local,
                $meme,
                $memeRow->normalized_name,
                $memeId,
                $repo,
                $out
            );
            $this->assertFalse($result->succeeded);
            $this->assertStringContainsString('marking as deleted', $out->getCapturedOutput());
            $after = $repo->getMeme($memeId);
            $this->assertNotNull($after);
            $this->assertTrue($after->deleted);
        }
        finally {
            $this->rrmdir($cacheDir);
            $this->rrmdir($memeDir);
        }
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->rrmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
