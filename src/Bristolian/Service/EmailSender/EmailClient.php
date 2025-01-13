<?php

namespace Bristolian\Service\EmailSender;

use Bristolian\Model\Email;

interface EmailClient
{
    public function send(Email $email): bool;
}

