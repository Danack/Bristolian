<?php

namespace Bristolian\Repo\EmailQueue;

use Bristolian\Model\Types\Email;

interface EmailQueue
{
    /**
     * @param string[] $users
     * @param string $subject
     * @param string $body
     * @return void
     */
    public function queueEmailToUsers(array $users, string $subject, string $body): void;

    public function clearQueue(): int;

    public function getEmailToSendAndUpdateState(): Email|null;

    public function setEmailSent(Email $email): void;

    public function setEmailFailed(Email $email): void;

    public function setEmailToRetry(Email $email): void;
}
