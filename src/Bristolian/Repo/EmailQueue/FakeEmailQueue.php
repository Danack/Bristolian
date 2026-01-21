<?php

declare(strict_types = 1);

namespace Bristolian\Repo\EmailQueue;

use Bristolian\CliController\Email as EmailController;
use Bristolian\Model\Types\Email;

/**
 * Fake implementation of EmailQueue for testing.
 */
class FakeEmailQueue implements EmailQueue
{
    /**
     * @var Email[]
     */
    private array $emails = [];

    private int $nextId = 1;

    /**
     * @param string[] $users
     * @param string $subject
     * @param string $body
     * @return void
     */
    public function queueEmailToUsers(array $users, string $subject, string $body): void
    {
        $now = new \DateTimeImmutable();

        foreach ($users as $user) {
            $email = new Email(
                id: $this->nextId++,
                body: $body,
                recipient: $user,
                retries: 0,
                status: EmailController::STATE_INITIAL,
                subject: $subject,
                created_at: $now,
                updated_at: $now,
            );

            $this->emails[$email->id] = $email;
        }
    }

    public function clearQueue(): int
    {
        $count = 0;
        $now = new \DateTimeImmutable();

        foreach ($this->emails as $id => $email) {
            if (in_array($email->status, [EmailController::STATE_INITIAL, EmailController::STATE_SENDING, EmailController::STATE_RETRY], true)) {
                $this->emails[$id] = new Email(
                    id: $email->id,
                    body: $email->body,
                    recipient: $email->recipient,
                    retries: $email->retries,
                    status: EmailController::STATE_SKIPPED,
                    subject: $email->subject,
                    created_at: $email->created_at,
                    updated_at: $now,
                );
                $count++;
            }
        }

        return $count;
    }

    public function getEmailToSendAndUpdateState(): Email|null
    {
        $now = new \DateTimeImmutable();

        // Find first email with INITIAL or RETRY status
        foreach ($this->emails as $id => $email) {
            if (in_array($email->status, [EmailController::STATE_INITIAL, EmailController::STATE_RETRY], true)) {
                // Update status to SENDING
                $updatedEmail = new Email(
                    id: $email->id,
                    body: $email->body,
                    recipient: $email->recipient,
                    retries: $email->retries,
                    status: EmailController::STATE_SENDING,
                    subject: $email->subject,
                    created_at: $email->created_at,
                    updated_at: $now,
                );

                $this->emails[$id] = $updatedEmail;

                return $email; // Return original email (before state change)
            }
        }

        return null;
    }

    public function setEmailSent(Email $email): void
    {
        if (!isset($this->emails[$email->id])) {
            return;
        }

        $now = new \DateTimeImmutable();
        $current = $this->emails[$email->id];

        $this->emails[$email->id] = new Email(
            id: $current->id,
            body: $current->body,
            recipient: $current->recipient,
            retries: $current->retries,
            status: EmailController::STATE_SENT,
            subject: $current->subject,
            created_at: $current->created_at,
            updated_at: $now,
        );
    }

    public function setEmailFailed(Email $email): void
    {
        if (!isset($this->emails[$email->id])) {
            return;
        }

        $now = new \DateTimeImmutable();
        $current = $this->emails[$email->id];

        $this->emails[$email->id] = new Email(
            id: $current->id,
            body: $current->body,
            recipient: $current->recipient,
            retries: $current->retries,
            status: EmailController::STATE_FAILED,
            subject: $current->subject,
            created_at: $current->created_at,
            updated_at: $now,
        );
    }

    public function setEmailToRetry(Email $email): void
    {
        if (!isset($this->emails[$email->id])) {
            return;
        }

        $now = new \DateTimeImmutable();
        $current = $this->emails[$email->id];

        $this->emails[$email->id] = new Email(
            id: $current->id,
            body: $current->body,
            recipient: $current->recipient,
            retries: $current->retries + 1,
            status: EmailController::STATE_RETRY,
            subject: $current->subject,
            created_at: $current->created_at,
            updated_at: $now,
        );
    }

    /**
     * Helper method for testing: get all emails
     * @return Email[]
     */
    public function getAllEmails(): array
    {
        return array_values($this->emails);
    }

    /**
     * Helper method for testing: get email by ID
     */
    public function getEmailById(int $id): Email|null
    {
        return $this->emails[$id] ?? null;
    }
}
