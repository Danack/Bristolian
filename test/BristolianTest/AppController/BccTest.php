<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Bcc;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class BccTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\AppController\Bcc::committee_meetings
     */
    public function test_committee_meetings(): void
    {
        $result = $this->injector->execute([Bcc::class, 'committee_meetings']);
        $this->assertIsString($result);
        $this->assertStringContainsString('BCC committee meetings', $result);
    }
}
