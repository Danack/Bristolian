<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\EmailIncoming;

use Bristolian\Model\Types\IncomingEmailParam;
use Bristolian\Repo\EmailIncoming\EmailIncoming;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for EmailIncoming implementations.
 */
abstract class EmailIncomingFixture extends BaseTestCase
{
    /**
     * Get a test instance of the EmailIncoming implementation.
     *
     * @return EmailIncoming
     */
    abstract public function getTestInstance(): EmailIncoming;


    /**
     * @covers \Bristolian\Repo\EmailIncoming\EmailIncoming::saveEmail
     */
    public function test_saveEmail_stores_email(): void
    {
        $repo = $this->getTestInstance();

        $emailParam = new IncomingEmailParam(
            message_id: 'test-message-1',
            body_plain: 'Test body',
            provider_variables: json_encode(['key' => 'value']),
            raw_email: 'Raw email content',
            recipient: 'test@example.com',
            retries: '0',
            sender: 'sender@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Stripped text',
            subject: 'Test Subject'
        );

        // Should not throw exception
        $repo->saveEmail($emailParam);
    }


    /**
     * @covers \Bristolian\Repo\EmailIncoming\EmailIncoming::saveEmail
     */
    public function test_saveEmail_can_save_multiple_emails(): void
    {
        $repo = $this->getTestInstance();

        $email1 = new IncomingEmailParam(
            message_id: 'test-message-1',
            body_plain: 'Body 1',
            provider_variables: json_encode([]),
            raw_email: 'Raw 1',
            recipient: 'test1@example.com',
            retries: '0',
            sender: 'sender1@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Stripped 1',
            subject: 'Subject 1'
        );

        $email2 = new IncomingEmailParam(
            message_id: 'test-message-2',
            body_plain: 'Body 2',
            provider_variables: json_encode([]),
            raw_email: 'Raw 2',
            recipient: 'test2@example.com',
            retries: '0',
            sender: 'sender2@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Stripped 2',
            subject: 'Subject 2'
        );

        // Should not throw exception
        $repo->saveEmail($email1);
        $repo->saveEmail($email2);
    }
}
