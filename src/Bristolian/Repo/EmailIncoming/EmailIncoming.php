<?php

namespace Bristolian\Repo\EmailIncoming;

use Bristolian\Model\IncomingEmailParam;

interface EmailIncoming
{
    public function saveEmail(IncomingEmailParam $email): void;
}
