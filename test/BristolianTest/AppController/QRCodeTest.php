<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\QRCode;
use Bristolian\Parameters\QRParams;
use Bristolian\Parameters\QRTokenParams;
use Bristolian\Response\SVGResponse;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class QRCodeTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\AppController\QRCode::get
     */
    public function test_get(): void
    {
        $qrParams = QRParams::createFromVarMap(new ArrayVarMap([
            'url' => 'https://www.example.com/',
        ]));
        $this->injector->share($qrParams);

        $result = $this->injector->execute([QRCode::class, 'get']);
        $this->assertInstanceOf(SVGResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\QRCode::getToken
     */
    public function test_getToken(): void
    {
        $qrParams = QRTokenParams::createFromVarMap(new ArrayVarMap([
            'token' => 'my-secret-token-abc123',
        ]));
        $this->injector->share($qrParams);

        $result = $this->injector->execute([QRCode::class, 'getToken']);
        $this->assertInstanceOf(SVGResponse::class, $result);
    }
}
