<?php

namespace Bristolian\Service\EmailSender;

use Bristolian\Model\Types\Email;

interface EmailClient
{
    public function send(Email $email): bool;
}
