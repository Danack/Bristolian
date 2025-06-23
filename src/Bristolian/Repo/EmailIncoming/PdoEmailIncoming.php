<?php

namespace Bristolian\Repo\EmailIncoming;

use Bristolian\Model\IncomingEmailParam;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Database\email_incoming;

class PdoEmailIncoming implements EmailIncoming
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    public function saveEmail(IncomingEmailParam $email): void
    {
        $sql = email_incoming::INSERT;

        $params = [
            ':message_id' => $email->message_id,
            ':body_plain' => $email->body_plain,
            ':provider_variables' => $email->provider_variables,
            ':raw_email' => $email->raw_email,
            ':recipient' => $email->recipient,
            ':retries' => $email->retries,
            ':sender' => $email->sender,
            ':status' => $email->status,
            ':stripped_text' => $email->stripped_text,
            ':subject' => $email->subject
        ];

        $insert_id = $this->pdoSimple->insert($sql, $params);
    }
}
