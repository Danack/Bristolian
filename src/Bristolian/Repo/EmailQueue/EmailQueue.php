<?php

namespace Bristolian\Repo\EmailQueue;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\email_send_queue;
use Bristolian\Model\Types\Email;

interface EmailQueue
{
    /**
     * @param string[] $users
     * @param string $subject
     * @param string $body
     * @return void
     */
    #[WritesTable(email_send_queue::class)]
    public function queueEmailToUsers(array $users, string $subject, string $body): void;

    #[WritesTable(email_send_queue::class)]
    public function clearQueue(): int;

    #[WritesTable(email_send_queue::class)]
    #[ReadsTable(email_send_queue::class)]
    public function getEmailToSendAndUpdateState(): Email|null;

    #[WritesTable(email_send_queue::class)]
    public function setEmailSent(Email $email): void;

    #[WritesTable(email_send_queue::class)]
    public function setEmailFailed(Email $email): void;

    #[WritesTable(email_send_queue::class)]
    public function setEmailToRetry(Email $email): void;
}
