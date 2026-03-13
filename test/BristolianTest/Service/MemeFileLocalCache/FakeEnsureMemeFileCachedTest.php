<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeFileLocalCache;

use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\MemeFileLocalCache\FakeEnsureMemeFileCached;
use Bristolian\Service\MemeFileLocalCache\EnsureMemeFileCachedResult;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * @coversNothing
 */
class FakeEnsureMemeFileCachedTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\MemeFileLocalCache\FakeEnsureMemeFileCached::__construct
     * @covers \Bristolian\Service\MemeFileLocalCache\FakeEnsureMemeFileCached::ensureMemeFileCached
     */
    public function test_ensureMemeFileCached_returns_success_when_configured_to_succeed(): void
    {
        $cacheDir = sys_get_temp_dir() . '/fake_cache_' . uniqid();
        $memeDir = sys_get_temp_dir() . '/fake_meme_' . uniqid();
        mkdir($cacheDir, 0755, true);
        mkdir($memeDir, 0755, true);
        try {
            $local = new LocalCacheFilesystem(new LocalFilesystemAdapter($cacheDir), $cacheDir);
            $meme = new MemeFilesystem(new LocalFilesystemAdapter($memeDir));
            $fake = new FakeEnsureMemeFileCached(succeed: true);
            $output = new CapturingCliOutput();
            $repo = new FakeMemeStorageRepo();

            $result = $fake->ensureMemeFileCached($local, $meme, 'any.png', 'm1', $repo, $output);

            $this->assertInstanceOf(EnsureMemeFileCachedResult::class, $result);
            $this->assertTrue($result->succeeded);
            $this->assertNull($result->failureDebugInfo);
            $this->assertSame('', $output->getCapturedOutput());
        } finally {
            $this->rrmdir($cacheDir);
            $this->rrmdir($memeDir);
        }
    }

    /**
     * @covers \Bristolian\Service\MemeFileLocalCache\FakeEnsureMemeFileCached::ensureMemeFileCached
     */
    public function test_ensureMemeFileCached_writes_to_cli_and_returns_failure_when_configured_to_fail(): void
    {
        $cacheDir = sys_get_temp_dir() . '/fake_cache2_' . uniqid();
        $memeDir = sys_get_temp_dir() . '/fake_meme2_' . uniqid();
        mkdir($cacheDir, 0755, true);
        mkdir($memeDir, 0755, true);
        try {
            $local = new LocalCacheFilesystem(new LocalFilesystemAdapter($cacheDir), $cacheDir);
            $meme = new MemeFilesystem(new LocalFilesystemAdapter($memeDir));
            $debugInfo = 'simulated download error';
            $fake = new FakeEnsureMemeFileCached(succeed: false, failureDebugInfo: $debugInfo);
            $output = new CapturingCliOutput();
            $repo = new FakeMemeStorageRepo();

            $result = $fake->ensureMemeFileCached($local, $meme, 'any.png', 'm1', $repo, $output);

            $this->assertFalse($result->succeeded);
            $this->assertSame('Failed to download file: ' . $debugInfo, $result->failureDebugInfo);
            $this->assertStringContainsString('Failed to download file:', $output->getCapturedOutput());
            $this->assertStringContainsString($debugInfo, $output->getCapturedOutput());
        } finally {
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
