<?php

namespace Bristolian\Repo\EmailIncoming;

use Bristolian\Attribute\WritesTable;
use Bristolian\Database\email_incoming;
use Bristolian\Model\Types\IncomingEmailParam;

interface EmailIncoming
{
    #[WritesTable(email_incoming::class)]
    public function saveEmail(IncomingEmailParam $email): void;
}
