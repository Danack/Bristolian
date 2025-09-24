<?php

namespace BristolianTest\Repo\EmailIncoming;

use BristolianTest\BaseTestCase;
use Bristolian\Repo\EmailIncoming\PdoEmailIncoming;
use BristolianTest\Repo\TestPlaceholders;

/**
 * @coversNothing
 */
class PdoEmailIncomingTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks()
    {
        $incoming_email = $this->getTestIncomingEmeail();
        $pdoEmailIncoming = $this->injector->make(PdoEmailIncoming::class);
    }
}
