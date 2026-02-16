<?php

declare(strict_types=1);

namespace BristolianTest\ApiController;

use Bristolian\ApiController\MailgunEmailHandler;
use Bristolian\Response\SuccessResponse;
use Bristolian\Service\Mailgun\FakePayloadValidator;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use SlimDispatcher\Response\JsonResponse;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\ApiController\MailgunEmailHandler::handleIncomingEmail
 */
class MailgunEmailHandlerTest extends BaseTestCase
{
    use TestPlaceholders;

    public function test_handleIncomingEmail_returns_406_when_payload_invalid(): void
    {
        $payloadValidator = new FakePayloadValidator(false);
        $varMap = new ArrayVarMap([]);

        $controller = new MailgunEmailHandler();
        $response = $controller->handleIncomingEmail($payloadValidator, $varMap);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(406, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('invalid payload', $data['error']);
    }

    public function test_handleIncomingEmail_returns_success_when_payload_valid(): void
    {
        $json = file_get_contents(__DIR__ . '/../../data/mailgun/incoming_email_2025_01_18_06_57_45.json');
        $data = json_decode($json, true);
        $data['raw_email'] = $json;

        $payloadValidator = new FakePayloadValidator(true);
        $varMap = new ArrayVarMap($data);

        $controller = new MailgunEmailHandler();
        $response = $controller->handleIncomingEmail($payloadValidator, $varMap);

        $this->assertInstanceOf(SuccessResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $responseData = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('result', $responseData);
        $this->assertSame('success', $responseData['result']);
    }
}
