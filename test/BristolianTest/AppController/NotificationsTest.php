<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use BristolianBehat\Mink\Mink;
use BristolianBehat\Mink\Session;
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
 * @coversNothing
 */
class NotificationsTest extends BaseTestCase
{
    use TestPlaceholders;

  /**
   * @covers \Bristolian\AppController\Notifications
   * @return void
   */
    public function testNotificationsEndPointIsWorking()
    {
        $this->markTestSkipped('Test not implemented yet');
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
