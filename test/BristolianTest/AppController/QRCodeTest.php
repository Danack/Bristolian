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
     * @return \Generator<string, array{string}>
     */
    public static function provides_get(): \Generator
    {
        yield 'short example url' => ['https://www.example.com/'];

        yield 'long file url with encoded path' => [
            'https://bristolian.org/rooms/01939e0a-d174-700f-874b-a52e72e99327/file/019cd51d-9350-71ea-9506-7aa4f9ec9e4c/Landlord%20Compliance%20Performance%20Report%20-%20September%202025.pdf',
        ];
    }

    /**
     * @dataProvider provides_get
     * @covers \Bristolian\AppController\QRCode::get
     */
    public function test_get(string $url): void
    {
        $qrParams = QRParams::createFromVarMap(new ArrayVarMap([
            'url' => $url,
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
