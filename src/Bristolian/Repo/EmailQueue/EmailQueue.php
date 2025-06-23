<?php

namespace Bristolian\Repo\EmailQueue;

use Bristolian\Model\Email;

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

    public function setEmailSent(Email $email);

    public function setEmailFailed(Email $email);

    public function setEmailToRetry(Email $email);
}
