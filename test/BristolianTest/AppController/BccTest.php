<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Bcc;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\AppController\Bcc::class, 'committee_meetings')]

class BccTest extends BaseTestCase
{
    public function test_committee_meetings(): void
    {
        $result = $this->injector->execute([Bcc::class, 'committee_meetings']);
        $this->assertIsString($result);
        $this->assertStringContainsString('BCC committee meetings', $result);
    }
}
