<?php

namespace Bristolian\Service\Mailgun;

use VarMap\VarMap;

class StandardPayloadValidator implements PayloadValidator
{
    public function validate(VarMap $payload): bool
    {
        $required_keys = [
            'signature',
            'timestamp',
            'token'
        ];

        foreach ($required_keys as $required_key) {
            if ($payload->has($required_key) === false) {
                return false;
            }
        }

        $calculated_hmac = hash_hmac(
            'sha256',
            $payload->get('timestamp') . $payload->get('token'),
            getMailgunSigningKey()
        );
        $provided_hmac = $payload->get('signature');

        if ($calculated_hmac !== $provided_hmac) {
            return false;
        }

        return true;
    }
}

