<?php

namespace Bristolian\Repo\EmailIncoming;

use Bristolian\Model\Types\IncomingEmailParam;

interface EmailIncoming
{
    public function saveEmail(IncomingEmailParam $email): void;
}
