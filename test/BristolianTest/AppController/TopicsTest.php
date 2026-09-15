<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Topics;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class TopicsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\AppController\Topics::index
     */
    public function test_index(): void
    {
        $result = $this->injector->execute([Topics::class, 'index']);
        $this->assertIsString($result);
    }
}
