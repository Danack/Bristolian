<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\EmailQueue;

use Bristolian\CliController\Email as EmailController;
use Bristolian\Model\Types\Email;
use Bristolian\Repo\EmailQueue\EmailQueue;
use Bristolian\Repo\EmailQueue\FakeEmailQueue;

/**
 * @coversNothing
 * @group standard_repo
 */
class FakeEmailQueueFixture extends EmailQueueFixture
{
    public function getTestInstance(): EmailQueue
    {
        return new FakeEmailQueue();
    }

    /**
     * Fake-specific test: verify emails are stored
     */
    public function test_queueEmailToUsers_stores_emails(): void
    {
        $fakeQueue = new FakeEmailQueue();

        $fakeQueue->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');

        $emails = $fakeQueue->getAllEmails();
        $this->assertCount(1, $emails);
        $this->assertSame('user@example.com', $emails[0]->recipient);
        $this->assertSame('Test Subject', $emails[0]->subject);
        $this->assertSame('Test Body', $emails[0]->body);
        $this->assertSame(EmailController::STATE_INITIAL, $emails[0]->status);
    }

    /**
     * Fake-specific test: verify getEmailToSendAndUpdateState updates status atomically
     */
    public function test_getEmailToSendAndUpdateState_atomically_updates_status(): void
    {
        $fakeQueue = new FakeEmailQueue();

        $fakeQueue->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');

        $email = $fakeQueue->getEmailToSendAndUpdateState();
        $this->assertNotNull($email);

        // Status should be updated in storage
        $storedEmail = $fakeQueue->getEmailById($email->id);
        $this->assertNotNull($storedEmail);
        $this->assertSame(EmailController::STATE_SENDING, $storedEmail->status);

        // But returned email should have original status
        $this->assertSame(EmailController::STATE_INITIAL, $email->status);
    }

    /**
     * Fake-specific test: verify setEmailSent updates status
     */
    public function test_setEmailSent_updates_status_in_storage(): void
    {
        $fakeQueue = new FakeEmailQueue();

        $fakeQueue->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');
        $email = $fakeQueue->getEmailToSendAndUpdateState();
        $this->assertNotNull($email);

        $fakeQueue->setEmailSent($email);

        $storedEmail = $fakeQueue->getEmailById($email->id);
        $this->assertNotNull($storedEmail);
        $this->assertSame(EmailController::STATE_SENT, $storedEmail->status);
    }

    /**
     * Fake-specific test: verify setEmailFailed updates status
     */
    public function test_setEmailFailed_updates_status_in_storage(): void
    {
        $fakeQueue = new FakeEmailQueue();

        $fakeQueue->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');
        $email = $fakeQueue->getEmailToSendAndUpdateState();
        $this->assertNotNull($email);

        $fakeQueue->setEmailFailed($email);

        $storedEmail = $fakeQueue->getEmailById($email->id);
        $this->assertNotNull($storedEmail);
        $this->assertSame(EmailController::STATE_FAILED, $storedEmail->status);
    }

    /**
     * Fake-specific test: verify setEmailToRetry increments retry count and updates status
     */
    public function test_setEmailToRetry_increments_retry_count(): void
    {
        $fakeQueue = new FakeEmailQueue();

        $fakeQueue->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');
        $email = $fakeQueue->getEmailToSendAndUpdateState();
        $this->assertNotNull($email);
        $this->assertSame(0, $email->retries);

        $fakeQueue->setEmailToRetry($email);

        $storedEmail = $fakeQueue->getEmailById($email->id);
        $this->assertNotNull($storedEmail);
        $this->assertSame(1, $storedEmail->retries);
        $this->assertSame(EmailController::STATE_RETRY, $storedEmail->status);
    }

    /**
     * Fake-specific test: verify multiple retries increment correctly
     */
    public function test_setEmailToRetry_increments_multiple_times(): void
    {
        $fakeQueue = new FakeEmailQueue();

        $fakeQueue->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');
        $email = $fakeQueue->getEmailToSendAndUpdateState();
        $this->assertNotNull($email);

        $fakeQueue->setEmailToRetry($email);
        $email1 = $fakeQueue->getEmailById($email->id);
        $this->assertSame(1, $email1->retries);

        $fakeQueue->setEmailToRetry($email1);
        $email2 = $fakeQueue->getEmailById($email->id);
        $this->assertSame(2, $email2->retries);

        $fakeQueue->setEmailToRetry($email2);
        $email3 = $fakeQueue->getEmailById($email->id);
        $this->assertSame(3, $email3->retries);
    }

    /**
     * Fake-specific test: verify clearQueue updates status to SKIPPED
     */
    public function test_clearQueue_updates_status_to_skipped(): void
    {
        $fakeQueue = new FakeEmailQueue();

        $fakeQueue->queueEmailToUsers(['user@example.com'], 'Test Subject', 'Test Body');

        $count = $fakeQueue->clearQueue();
        $this->assertSame(1, $count);

        $emails = $fakeQueue->getAllEmails();
        $this->assertCount(1, $emails);
        $this->assertSame(EmailController::STATE_SKIPPED, $emails[0]->status);
    }

    /**
     * Fake-specific test: verify clearQueue only affects initial, sending, and retry emails
     */
    public function test_clearQueue_only_affects_pending_emails(): void
    {
        $fakeQueue = new FakeEmailQueue();

        $fakeQueue->queueEmailToUsers(['user1@example.com', 'user2@example.com'], 'Test Subject', 'Test Body');

        // Get one email and mark it as sent
        $email1 = $fakeQueue->getEmailToSendAndUpdateState();
        if ($email1 !== null) {
            $fakeQueue->setEmailSent($email1);
        }

        // Clear queue - should only clear the other email
        $count = $fakeQueue->clearQueue();
        $this->assertSame(1, $count);

        $emails = $fakeQueue->getAllEmails();
        $this->assertCount(2, $emails);

        // Find the sent email - it should still be SENT
        $sentEmail = null;
        foreach ($emails as $email) {
            if ($email->status === EmailController::STATE_SENT) {
                $sentEmail = $email;
                break;
            }
        }
        $this->assertNotNull($sentEmail);
    }
}
