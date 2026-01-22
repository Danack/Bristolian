<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ProcessorRepo;

use Bristolian\Repo\ProcessorRepo\FakeProcessorRepo;
use Bristolian\Repo\ProcessorRepo\ProcessorRepo;

/**
 * @group standard_repo
 */
class FakeProcessorRepoFixture extends ProcessorRepoFixture
{
    public function getTestInstance(): ProcessorRepo
    {
        return new FakeProcessorRepo();
    }
}
