<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeImageOcr;

use Bristolian\Service\MemeImageOcr\FakeMemeImageOcrRunner;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\MemeImageOcr\FakeMemeImageOcrRunner::class, '__construct')]
#[CoversMethod(\Bristolian\Service\MemeImageOcr\FakeMemeImageOcrRunner::class, 'extractTextFromImageFile')]

class FakeMemeImageOcrRunnerTest extends BaseTestCase
{
    public function test_extractTextFromImageFile_returns_configured_text(): void
    {
        $runner = new FakeMemeImageOcrRunner('expected text');
        $this->assertSame('expected text', $runner->extractTextFromImageFile('/any/path'));
    }

    public function test_extractTextFromImageFile_rethrows_when_configured(): void
    {
        $runner = new FakeMemeImageOcrRunner('', new \RuntimeException('boom'));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('boom');
        $runner->extractTextFromImageFile('/x');
    }
}
