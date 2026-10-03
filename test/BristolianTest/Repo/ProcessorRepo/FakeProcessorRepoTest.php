<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ProcessorRepo;

use Bristolian\Repo\ProcessorRepo\FakeProcessorRepo;
use Bristolian\Repo\ProcessorRepo\ProcessorRepo;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @group standard_repo
 */
#[CoversClass(FakeProcessorRepo::class)]
class FakeProcessorRepoTest extends ProcessorRepoFixture
{
    public function getTestInstance(): ProcessorRepo
    {
        return new FakeProcessorRepo();
    }
}
