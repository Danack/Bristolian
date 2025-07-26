<?php

namespace BristolianTest\Service;

use Bristolian\Service\RequestNonce;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Service\RequestNonce
 */
class RequestNonceTest extends BaseTestCase
{
    public function testWorks()
    {
        $nonce = new RequestNonce();

//        $this->assertIsString($nonce->getRandom());
        $this->assertSame(2 * RequestNonce::SIZE_IN_BYTES, strlen($nonce->getRandom()));
    }
}
