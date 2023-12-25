<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use Bristolian\AppSession;
use Bristolian\JsonInput\JsonInput;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;
use BristolianTest\Repo\TestPlaceholders;
use DMore\ChromeDriver\ChromeDriver;
use PHPUnit\Framework\TestCase;
use Bristolian\CSPViolation\RedisCSPViolationStorage;
use Bristolian\App;
use Bristolian\AppController\Notifications;
use BristolianTest\BaseTestCase;
use Bristolian\JsonInput\FakeJsonInput;
use SlimDispatcher\Response\JsonResponse;

/**
 * @group wip
 * @coversNothing
 */
class NotificationsTest extends BaseTestCase
{
    use TestPlaceholders;

  /**
   * @covers \Bristolian\AppController\Notifications
   * @return void
   * @throws \DI\InjectionException
   */
    public function testNotificationsEndPointIsWorking()
    {
        $data = [
        "endpoint" => "https://www.example.com/foo",
        "keys" => [
          "p256dh" => "p256dh",
          "auth" => "HOywJc9Uxx7Nn4aHlE2VqA",
        ]
        ];
        $inputData = new FakeJsonInput($data);
        $this->initLoggedInUser([$inputData]);

        $result = $this->injector->execute(
            '\Bristolian\AppController\Notifications::save_subscription'
        );
        $this->assertInstanceOf(JsonResponse::class, $result);
    }
}
