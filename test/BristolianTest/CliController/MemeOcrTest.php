<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\MemeOcr;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo;
use Bristolian\Repo\ProcessorRepo\FakeProcessorRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\FakeProcessorRunRecordRepo;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\MemeFileLocalCache\FakeEnsureMemeFileCached;
use Bristolian\Service\MemeImageOcr\FakeMemeImageOcrRunner;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * @coversNothing
 */
class MemeOcrTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\CliController\MemeOcr::__construct
     * @covers \Bristolian\CliController\MemeOcr::runInternal
     */
    public function test_runInternal_writes_when_processor_disabled(): void
    {
        $root = sys_get_temp_dir();
        $memeStorageRepo = new FakeMemeStorageRepo();
        $processorRepo = new FakeProcessorRepo();
        $processorRepo->setProcessorEnabled(ProcessType::meme_ocr, false);
        $cliOutput = new CapturingCliOutput();
        $memeOcr = new MemeOcr(
            new MemeFilesystem(new LocalFilesystemAdapter($root)),
            new LocalCacheFilesystem(new LocalFilesystemAdapter($root), $root),
            new FakeMemeTextRepo($memeStorageRepo),
            new FakeProcessorRunRecordRepo(),
            $processorRepo,
            $memeStorageRepo,
            $cliOutput,
            new FakeMemeImageOcrRunner(),
            new FakeEnsureMemeFileCached(true),
        );
        $memeOcr->runInternal();
        $this->assertStringContainsString('not enabled', $cliOutput->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\MemeOcr::runInternal
     */
    public function test_runInternal_writes_when_no_meme_to_ocr(): void
    {
        $root = sys_get_temp_dir();
        $memeStorageRepo = new FakeMemeStorageRepo();
        $processorRepo = new FakeProcessorRepo();
        $processorRepo->setProcessorEnabled(ProcessType::meme_ocr, true);
        $cliOutput = new CapturingCliOutput();
        $memeOcr = new MemeOcr(
            new MemeFilesystem(new LocalFilesystemAdapter($root)),
            new LocalCacheFilesystem(new LocalFilesystemAdapter($root), $root),
            new FakeMemeTextRepo($memeStorageRepo),
            new FakeProcessorRunRecordRepo(),
            $processorRepo,
            $memeStorageRepo,
            $cliOutput,
            new FakeMemeImageOcrRunner(),
            new FakeEnsureMemeFileCached(true),
        );
        $memeOcr->runInternal();
        $this->assertStringContainsString('No memes need text', $cliOutput->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\MemeOcr::runInternal
     */
    public function test_runInternal_saves_text_when_ocr_succeeds(): void
    {
        $root = sys_get_temp_dir();
        $memeStorageRepo = new FakeMemeStorageRepo();
        $memeTextRepo = new FakeMemeTextRepo($memeStorageRepo);
        $processorRepo = new FakeProcessorRepo();
        $processorRepo->setProcessorEnabled(ProcessType::meme_ocr, true);
        $cliOutput = new CapturingCliOutput();
        $memeOcr = new MemeOcr(
            new MemeFilesystem(new LocalFilesystemAdapter($root)),
            new LocalCacheFilesystem(new LocalFilesystemAdapter($root), $root),
            $memeTextRepo,
            new FakeProcessorRunRecordRepo(),
            $processorRepo,
            $memeStorageRepo,
            $cliOutput,
            new FakeMemeImageOcrRunner('hello from fake ocr'),
            new FakeEnsureMemeFileCached(true),
        );
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $memeId = $memeStorageRepo->storeMeme('user-1', 'ocr-meme.pdf', UploadedFile::fromFile($pdfPath));
        $memeOcr->runInternal();
        $this->assertStringContainsString('OCRed meme text: hello from fake ocr', $cliOutput->getCapturedOutput());
        $saved = $memeTextRepo->getMemeText($memeId);
        $this->assertNotNull($saved);
        $this->assertSame('hello from fake ocr', $saved->text);
    }

    /**
     * @covers \Bristolian\CliController\MemeOcr::runInternal
     */
    public function test_runInternal_truncates_long_text(): void
    {
        $root = sys_get_temp_dir();
        $memeStorageRepo = new FakeMemeStorageRepo();
        $memeTextRepo = new FakeMemeTextRepo($memeStorageRepo);
        $processorRepo = new FakeProcessorRepo();
        $processorRepo->setProcessorEnabled(ProcessType::meme_ocr, true);
        $cliOutput = new CapturingCliOutput();
        $veryLongText = str_repeat('x', \Bristolian\CliController\MemeOcr::MAX_MEME_TEXT_LENGTH + 10);
        $memeOcr = new MemeOcr(
            new MemeFilesystem(new LocalFilesystemAdapter($root)),
            new LocalCacheFilesystem(new LocalFilesystemAdapter($root), $root),
            $memeTextRepo,
            new FakeProcessorRunRecordRepo(),
            $processorRepo,
            $memeStorageRepo,
            $cliOutput,
            new FakeMemeImageOcrRunner($veryLongText),
            new FakeEnsureMemeFileCached(true),
        );
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $memeId = $memeStorageRepo->storeMeme('user-1', 'ocr-meme-long.pdf', UploadedFile::fromFile($pdfPath));
        $memeOcr->runInternal();
        $saved = $memeTextRepo->getMemeText($memeId);
        $this->assertNotNull($saved);
        $this->assertSame(
            \Bristolian\CliController\MemeOcr::MAX_MEME_TEXT_LENGTH - 1,
            strlen($saved->text)
        );
    }

    /**
     * @covers \Bristolian\CliController\MemeOcr::runInternal
     */
    public function test_runInternal_writes_failure_when_ocr_throws(): void
    {
        $root = sys_get_temp_dir();
        $memeStorageRepo = new FakeMemeStorageRepo();
        $processorRepo = new FakeProcessorRepo();
        $processorRepo->setProcessorEnabled(ProcessType::meme_ocr, true);
        $cliOutput = new CapturingCliOutput();
        $memeOcr = new MemeOcr(
            new MemeFilesystem(new LocalFilesystemAdapter($root)),
            new LocalCacheFilesystem(new LocalFilesystemAdapter($root), $root),
            new FakeMemeTextRepo($memeStorageRepo),
            new FakeProcessorRunRecordRepo(),
            $processorRepo,
            $memeStorageRepo,
            $cliOutput,
            new FakeMemeImageOcrRunner('', new \RuntimeException('ocr failed')),
            new FakeEnsureMemeFileCached(true),
        );
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $memeStorageRepo->storeMeme('user-1', 'ocr-fail.pdf', UploadedFile::fromFile($pdfPath));
        $memeOcr->runInternal();
        $this->assertStringContainsString('Failed to process OCR', $cliOutput->getCapturedOutput());
        $this->assertStringContainsString('ocr failed', $cliOutput->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\MemeOcr::runInternal
     */
    public function test_runInternal_finishes_when_cache_fails(): void
    {
        $root = sys_get_temp_dir();
        $memeStorageRepo = new FakeMemeStorageRepo();
        $processorRunRecordRepo = new FakeProcessorRunRecordRepo();
        $processorRepo = new FakeProcessorRepo();
        $processorRepo->setProcessorEnabled(ProcessType::meme_ocr, true);
        $cliOutput = new CapturingCliOutput();
        $memeOcr = new MemeOcr(
            new MemeFilesystem(new LocalFilesystemAdapter($root)),
            new LocalCacheFilesystem(new LocalFilesystemAdapter($root), $root),
            new FakeMemeTextRepo($memeStorageRepo),
            $processorRunRecordRepo,
            $processorRepo,
            $memeStorageRepo,
            $cliOutput,
            new FakeMemeImageOcrRunner(),
            new FakeEnsureMemeFileCached(false, 'missing blob'),
        );
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $memeStorageRepo->storeMeme('user-1', 'cache-fail.pdf', UploadedFile::fromFile($pdfPath));
        $memeOcr->runInternal();
        $this->assertStringContainsString('Failed to download file', $cliOutput->getCapturedOutput());
        $this->assertStringContainsString('missing blob', $cliOutput->getCapturedOutput());
        $records = $processorRunRecordRepo->getRunRecords(ProcessType::meme_ocr);
        $this->assertNotEmpty($records);
        $this->assertStringContainsString('Failed to download file', $records[0]->debug_info);
    }
}
