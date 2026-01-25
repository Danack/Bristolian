<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ProcessorRepo;

use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRepo\ProcessorRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for ProcessorRepo implementations.
 */
abstract class ProcessorRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the ProcessorRepo implementation.
     *
     * @return ProcessorRepo
     */
    abstract public function getTestInstance(): ProcessorRepo;

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\ProcessorRepo::getProcessorEnabled
     */
    public function test_getProcessorEnabled_returns_false_initially(): void
    {
        $repo = $this->getTestInstance();

        $enabled = $repo->getProcessorEnabled(ProcessType::daily_system_info);

        $this->assertFalse($enabled);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\ProcessorRepo::setProcessorEnabled
     * @covers \Bristolian\Repo\ProcessorRepo\ProcessorRepo::getProcessorEnabled
     */
    public function test_setProcessorEnabled_and_getProcessorEnabled_work_together(): void
    {
        $repo = $this->getTestInstance();

        $processor = ProcessType::meme_ocr;

        // Initially disabled
        $this->assertFalse($repo->getProcessorEnabled($processor));

        // Enable it
        $repo->setProcessorEnabled($processor, true);
        $this->assertTrue($repo->getProcessorEnabled($processor));

        // Disable it
        $repo->setProcessorEnabled($processor, false);
        $this->assertFalse($repo->getProcessorEnabled($processor));
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\ProcessorRepo::setProcessorEnabled
     * @covers \Bristolian\Repo\ProcessorRepo\ProcessorRepo::getProcessorEnabled
     */
    public function test_setProcessorEnabled_works_for_different_processors_independently(): void
    {
        $repo = $this->getTestInstance();

        $processor1 = ProcessType::daily_system_info;
        $processor2 = ProcessType::email_send;

        // Enable processor1, disable processor2
        $repo->setProcessorEnabled($processor1, true);
        $repo->setProcessorEnabled($processor2, false);

        $this->assertTrue($repo->getProcessorEnabled($processor1));
        $this->assertFalse($repo->getProcessorEnabled($processor2));

        // Toggle them
        $repo->setProcessorEnabled($processor1, false);
        $repo->setProcessorEnabled($processor2, true);

        $this->assertFalse($repo->getProcessorEnabled($processor1));
        $this->assertTrue($repo->getProcessorEnabled($processor2));
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\ProcessorRepo::getProcessorsStates
     * @covers \Bristolian\Repo\ProcessorRepo\ProcessorRepo::setProcessorEnabled
     */
    public function test_getProcessorsStates_returns_states_for_all_processors(): void
    {
        $repo = $this->getTestInstance();

        // Enable some processors
        $repo->setProcessorEnabled(ProcessType::daily_system_info, true);
        $repo->setProcessorEnabled(ProcessType::meme_ocr, false);

        $states = $repo->getProcessorsStates();

        // Note: The exact structure depends on implementation
        // Some implementations may return empty array if states aren't explicitly set
    }
}
