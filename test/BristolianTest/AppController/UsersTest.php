<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Users;
use Bristolian\Parameters\UserProfileUpdateParams;
use Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;
use Bristolian\Response\GetUserInfoResponse;
use Bristolian\Response\UpdateUserProfileResponse;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class UsersTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(UserProfileRepo::class, FakeUserProfileRepo::class);
        $this->injector->share(FakeUserProfileRepo::class);
        $this->setupFakeUserSession();
    }

    /**
     * @covers \Bristolian\AppController\Users::index
     */
    public function test_index(): void
    {
        $result = $this->injector->execute([Users::class, 'index']);
        $this->assertIsString($result);
        $this->assertStringContainsString('User list', $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::getUserInfo
     */
    public function test_getUserInfo(): void
    {
        $this->injector->defineParam('user_id', 'test-user-id-001');
        $result = $this->injector->execute([Users::class, 'getUserInfo']);
        $this->assertInstanceOf(GetUserInfoResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::updateProfile
     */
    public function test_updateProfile(): void
    {
        $params = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'TestUser',
            'about_me' => 'About me text here.',
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([Users::class, 'updateProfile']);
        $this->assertInstanceOf(UpdateUserProfileResponse::class, $result);
    }
}
