<?php

declare(strict_types=1);

namespace BristolianTest\AppController;

use Bristolian\AppController\BristolStairs;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\Parameters\OpenmapNearbyParams;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Response\Typed\GetBristolStairsResponse;
use Bristolian\Session\FakeUserSession;
use Bristolian\Session\UserSession;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\JsonNoCacheResponse;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class BristolStairsTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->setupAppControllerFakes();
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::update_stairs_info_get
     */
    public function test_update_stairs_info_get(): void
    {
        $result = $this->injector->execute([BristolStairs::class, 'update_stairs_info_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::stairs_page
     */
    public function test_stairs_page(): void
    {
        $result = $this->injector->execute([BristolStairs::class, 'stairs_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('A map of Bristol Stairs', $result);
        $this->assertStringContainsString('bristol_stairs_map', $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::stairs_page_stair_selected
     */
    public function test_stairs_page_stair_selected(): void
    {
        $this->injector->defineParam('stair_id', 1);
        $result = $this->injector->execute([BristolStairs::class, 'stairs_page_stair_selected']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Steep stairs near Park Street', $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::getData
     */
    public function test_getData(): void
    {
        $result = $this->injector->execute([BristolStairs::class, 'getData']);
        $this->assertInstanceOf(GetBristolStairsResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::update_stairs_info
     */
    public function test_update_stairs_info(): void
    {
        $this->setupFakeUserSession();
        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'description' => 'Updated description',
            'steps' => '50',
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([BristolStairs::class, 'update_stairs_info']);

        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::update_stairs_position
     */
    public function test_update_stairs_position(): void
    {
        $this->setupFakeUserSession();
        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'latitude' => 51.46,
            'longitude' => -2.60,
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([BristolStairs::class, 'update_stairs_position']);

        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::getOpenmapNearby
     */
    public function test_getOpenmapNearby_returns_error_when_not_logged_in(): void
    {
        $session = new FakeUserSession(false, '', '');
        $this->injector->alias(UserSession::class, FakeUserSession::class);
        $this->injector->share($session);
        $params = OpenmapNearbyParams::createFromVarMap(new ArrayVarMap([
            'latitude' => 51.45,
            'longitude' => -2.59,
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([BristolStairs::class, 'getOpenmapNearby']);

        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
        $this->assertStringContainsString('Not logged in', $result->getBody());
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::getOpenmapNearby
     */
    public function test_getOpenmapNearby_returns_locations_when_logged_in(): void
    {
        $this->setupFakeUserSession();
        $params = OpenmapNearbyParams::createFromVarMap(new ArrayVarMap([
            'latitude' => 51.45,
            'longitude' => -2.59,
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([BristolStairs::class, 'getOpenmapNearby']);

        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
        $this->assertStringContainsString('locations', $result->getBody());
    }
}
