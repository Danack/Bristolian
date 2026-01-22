<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\EmailQueue;

use Bristolian\Repo\EmailQueue\EmailQueue;
use Bristolian\Repo\EmailQueue\PdoEmailQueue;

/**
 * @group db
 */
class PdoEmailQueueFixture extends EmailQueueFixture
{
    public function getTestInstance(): EmailQueue
    {
        return $this->injector->make(PdoEmailQueue::class);
    }
}
