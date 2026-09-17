<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Topics;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\AppController\Topics::class, 'index')]

class TopicsTest extends BaseTestCase
{
    public function test_index(): void
    {
        $result = $this->injector->execute([Topics::class, 'index']);
        $this->assertIsString($result);
    }
}
