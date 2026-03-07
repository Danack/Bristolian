<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Notifications;
use Bristolian\JsonInput\FakeJsonInput;
use Bristolian\JsonInput\JsonInput;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\FakeWebPushSubscriptionRepo;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Response\ValidationErrorResponse;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class NotificationsTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(WebPushSubscriptionRepo::class, FakeWebPushSubscriptionRepo::class);
        $this->injector->share(FakeWebPushSubscriptionRepo::class);
    }

    /**
     * @covers \Bristolian\AppController\Notifications::generate_keys
     */
    public function test_generate_keys(): void
    {
        $result = $this->injector->execute([Notifications::class, 'generate_keys']);
        $this->assertIsString($result);
        $this->assertStringContainsString('keys', $result);
    }

    /**
     * @covers \Bristolian\AppController\Notifications::save_subscription_get
     */
    public function test_save_subscription_get(): void
    {
        $result = $this->injector->execute([Notifications::class, 'save_subscription_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Notifications::save_subscription
     */
    public function test_save_subscription(): void
    {
        $data = [
            'endpoint' => 'https://www.example.com/push-endpoint',
            'expirationTime' => '',
            'raw' => '{"endpoint":"https://www.example.com/push-endpoint"}',
        ];

        $jsonInput = new FakeJsonInput($data);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->setupFakeUserSession();

        $result = $this->injector->execute([Notifications::class, 'save_subscription']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Notifications::save_subscription
     */
    public function test_save_subscription_with_invalid_data(): void
    {
        $data = [];

        $jsonInput = new FakeJsonInput($data);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->setupFakeUserSession();

        $result = $this->injector->execute([Notifications::class, 'save_subscription']);
        $this->assertInstanceOf(ValidationErrorResponse::class, $result);
    }
}
