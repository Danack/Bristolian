<?php

declare(strict_types=1);

namespace BristolianTest\Service\EmailSender;

use Bristolian\Model\Types\Email;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\EmailSender\FakeMailgunHttpClient;
use Bristolian\Service\EmailSender\MailgunEmailClient;
use Bristolian\Service\EmailSender\TestableMailgun;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class MailgunEmailClientTest extends BaseTestCase
{
    private function createMailgunWithFakeClient(FakeMailgunHttpClient $fakeClient): TestableMailgun
    {
        return TestableMailgun::createWithHttpClient($fakeClient);
    }

    private function createEmail(): Email
    {
        return new Email(
            id: 1,
            body: 'Test body',
            recipient: 'recipient@example.com',
            retries: 0,
            status: 'pending',
            subject: 'Test subject',
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable()
        );
    }

    /**
     * @covers \Bristolian\Service\EmailSender\MailgunEmailClient::__construct
     * @covers \Bristolian\Service\EmailSender\MailgunEmailClient::send
     * @covers \Bristolian\Service\EmailSender\TestableMailgun::createWithHttpClient
     */
    public function test_send_returns_true_when_mailgun_succeeds(): void
    {
        $fakeClient = new FakeMailgunHttpClient();
        $fakeClient->setNextResponseStatusCode(200);
        $mailgun = $this->createMailgunWithFakeClient($fakeClient);
        $cliOutput = new CapturingCliOutput();
        $client = new MailgunEmailClient($mailgun, $cliOutput);
        $email = $this->createEmail();

        $result = $client->send($email);

        $this->assertTrue($result);
    }

    /**
     * @covers \Bristolian\Service\EmailSender\MailgunEmailClient::send
     * @covers \Bristolian\Service\EmailSender\FakeMailgunHttpClient::sendRequest
     * @covers \Bristolian\Service\EmailSender\FakeMailgunHttpClient::setNextResponseStatusCode
     * @covers \Bristolian\Service\EmailSender\FakeMailgunHttpClient::setNextBody
     * Covers the failure path: catch block logs via CliOutput and returns false.
     */
    public function test_send_returns_false_when_mailgun_throws_http_client_exception(): void
    {
        $fakeClient = new FakeMailgunHttpClient();
        $fakeClient->setNextResponseStatusCode(400);
        $fakeClient->setNextBody('{"message":"Bad request"}');
        $mailgun = $this->createMailgunWithFakeClient($fakeClient);
        $cliOutput = new CapturingCliOutput();
        $client = new MailgunEmailClient($mailgun, $cliOutput);
        $email = $this->createEmail();

        $result = $client->send($email);

        $this->assertFalse($result);
        $errorLines = $cliOutput->getCapturedErrorLines();
        $this->assertCount(1, $errorLines);
        $this->assertStringContainsString('Exception:', $errorLines[0]);
        $this->assertStringContainsString('Bad request', $errorLines[0]);
    }

    /**
     * @covers \Bristolian\Service\EmailSender\FakeMailgunHttpClient::sendRequest
     */
    public function test_fake_client_returns_configured_status_and_body(): void
    {
        $fakeClient = new FakeMailgunHttpClient();
        $fakeClient->setNextResponseStatusCode(201);
        $fakeClient->setNextBody('{"id":"custom-id"}');
        $request = new \Laminas\Diactoros\Request('https://api.mailgun.net/v3/domain/messages', 'POST');

        $response = $fakeClient->sendRequest($request);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame('{"id":"custom-id"}', $response->getBody()->__toString());
    }
}
