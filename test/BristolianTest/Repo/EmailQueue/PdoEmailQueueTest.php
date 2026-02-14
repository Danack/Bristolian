<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\EmailQueue;

use Bristolian\Config\EnvironmentName;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\EmailQueue\EmailQueue;
use Bristolian\Repo\EmailQueue\PdoEmailQueue;

/**
 * @group db
 * @coversNothing
 */
class PdoEmailQueueTest extends EmailQueueFixture
{
    public function setUp(): void
    {
        parent::setUp();
        // Clear queue so getEmailToSendAndUpdateState returns the email we queue in this test.
        // PdoEmailQueue uses its own transactions so we cannot use DbTransactionIsolation.
        $pdoSimple = $this->injector->make(PdoSimple::class);
        $pdoSimple->execute('DELETE FROM email_send_queue', []);
    }

    public function getTestInstance(EnvironmentName $environmentName): EmailQueue
    {
        $this->injector->alias(EnvironmentName::class, get_class($environmentName));
        $this->injector->share($environmentName);

        return $this->injector->make(PdoEmailQueue::class);
    }
}
