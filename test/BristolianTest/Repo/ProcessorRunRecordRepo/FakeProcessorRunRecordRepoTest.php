<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ProcessorRunRecordRepo;

use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Repo\ProcessorRunRecordRepo\FakeProcessorRunRecordRepo;

/**
 * @group standard_repo
 */
class FakeProcessorRunRecordRepoTest extends ProcessorRunRecordRepoTest
{
    public function getTestInstance(): ProcessorRunRecordRepo
    {
        return new FakeProcessorRunRecordRepo();
    }
}
