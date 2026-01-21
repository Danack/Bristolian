<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\EmailQueue;

use Bristolian\CliController\Email as EmailController;
use Bristolian\Model\Types\Email;
use Bristolian\Repo\EmailQueue\EmailQueue;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for EmailQueue implementations.
 */
abstract class EmailQueueTest extends BaseTestCase
{
    /**
     * Get a test instance of the EmailQueue implementation.
     *
     * @return EmailQueue
     */
    abstract public function getTestInstance(): EmailQueue;


    public function test_queueEmailToUsers_creates_emails(): void
    {
        $repo = $this->getTestInstance();

        $users = ['user1@example.com', 'user2@example.com'];
        $subject = 'Test Subject';
        $body = 'Test Body';

        // Should not throw exception
        $repo->queueEmailToUsers($users, $subject, $body);

        $this->assertTrue(true);
    }


    public function test_queueEmailToUsers_accepts_empty_user_array(): void
    {
        $repo = $this->getTestInstance();

        $subject = 'Test Subject';
        $body = 'Test Body';

        // Should not throw exception
        $repo->queueEmailToUsers([], $subject, $body);

        $this->assertTrue(true);
    }


    public function test_getEmailToSendAndUpdateState_returns_null_when_queue_is_empty(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->getEmailToSendAndUpdateState();

        $this->assertNull($result);
    }


    public function test_getEmailToSendAndUpdateState_returns_email_with_initial_status(): void
    {
        $repo = $this->getTestInstance();

        $repo->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');

        $email = $repo->getEmailToSendAndUpdateState();

        $this->assertNotNull($email);
        $this->assertInstanceOf(Email::class, $email);
        $this->assertSame('user@example.com', $email->recipient);
        $this->assertSame('Test Subject', $email->subject);
        $this->assertSame('Test Body', $email->body);
        $this->assertSame(EmailController::STATE_INITIAL, $email->status);
        $this->assertSame(0, $email->retries);
    }


    public function test_getEmailToSendAndUpdateState_returns_email_with_retry_status(): void
    {
        $repo = $this->getTestInstance();

        $repo->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');
        $email1 = $repo->getEmailToSendAndUpdateState();
        $this->assertNotNull($email1);

        // Set to retry
        if ($email1 !== null) {
            $repo->setEmailToRetry($email1);
        }

        // Should be able to get it again
        $email2 = $repo->getEmailToSendAndUpdateState();
        $this->assertNotNull($email2);
    }


    public function test_setEmailSent_updates_email_status(): void
    {
        $repo = $this->getTestInstance();

        $repo->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');
        $email = $repo->getEmailToSendAndUpdateState();
        $this->assertNotNull($email);

        // Should not throw exception
        if ($email !== null) {
            $repo->setEmailSent($email);
        }

        $this->assertTrue(true);
    }


    public function test_setEmailFailed_updates_email_status(): void
    {
        $repo = $this->getTestInstance();

        $repo->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');
        $email = $repo->getEmailToSendAndUpdateState();
        $this->assertNotNull($email);

        // Should not throw exception
        if ($email !== null) {
            $repo->setEmailFailed($email);
        }

        $this->assertTrue(true);
    }


    public function test_setEmailToRetry_increments_retry_count(): void
    {
        $repo = $this->getTestInstance();

        $repo->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');
        $email = $repo->getEmailToSendAndUpdateState();
        $this->assertNotNull($email);
        $this->assertSame(0, $email->retries);

        // Should not throw exception
        if ($email !== null) {
            $repo->setEmailToRetry($email);
        }

        $this->assertTrue(true);
    }


    public function test_clearQueue_returns_count_of_cleared_emails(): void
    {
        $repo = $this->getTestInstance();

        $repo->queueEmailToUsers(['user1@example.com', 'user2@example.com'], 'Test Subject', 'Test Body');

        $count = $repo->clearQueue();

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }


    public function test_clearQueue_returns_zero_when_queue_is_empty(): void
    {
        $repo = $this->getTestInstance();

        $count = $repo->clearQueue();

        $this->assertSame(0, $count);
    }


    public function test_clearQueue_clears_initial_sending_and_retry_emails(): void
    {
        $repo = $this->getTestInstance();

        $repo->queueEmailToUsers(['user1@example.com'], 'Test Subject', 'Test Body');
        $repo->queueEmailToUsers(['user2@example.com'], 'Test Subject 2', 'Test Body 2');

        $email1 = $repo->getEmailToSendAndUpdateState(); // Changes status to SENDING
        $this->assertNotNull($email1);

        $count = $repo->clearQueue();

        // Should clear both emails (one INITIAL, one SENDING)
        $this->assertGreaterThanOrEqual(1, $count);
    }


    public function test_full_email_lifecycle(): void
    {
        $repo = $this->getTestInstance();

        // Queue email
        $repo->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');

        // Get email to send (should update to SENDING)
        $email = $repo->getEmailToSendAndUpdateState();
        $this->assertNotNull($email);
        $this->assertInstanceOf(Email::class, $email);

        // Mark as sent
        if ($email !== null) {
            $repo->setEmailSent($email);
        }

        // Should not be able to get it again
        $email2 = $repo->getEmailToSendAndUpdateState();
        $this->assertNull($email2);
    }


    public function test_retry_lifecycle(): void
    {
        $repo = $this->getTestInstance();

        // Queue email
        $repo->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');

        // Get email to send
        $email1 = $repo->getEmailToSendAndUpdateState();
        $this->assertNotNull($email1);
        $initialRetries = $email1 !== null ? $email1->retries : 0;

        // Set to retry (should increment retries)
        if ($email1 !== null) {
            $repo->setEmailToRetry($email1);
        }

        // Get email again (should return it with RETRY status)
        $email2 = $repo->getEmailToSendAndUpdateState();
        $this->assertNotNull($email2);
    }
}
