<?php

namespace Bristolian\Repo\EmailIncoming;

use Bristolian\Model\IncomingEmailParam;

class FakeEmailIncoming implements EmailIncoming
{
    /**
     * @var IncomingEmailParam[]
     */
    private $emails = [];

    public function saveEmail(IncomingEmailParam $email): void
    {
        $this->emails[] = $email;
    }

    /**
     * @return IncomingEmailParam[]
     */
    public function getEmails(): array
    {
        return $this->emails;
    }
}
