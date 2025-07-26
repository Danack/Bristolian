<?php

namespace Bristolian\Model;

use Bristolian\Exception\BristolianException;

class IncomingEmailParam
{
    const STATUS_INITIAL = 'initial';


    public function __construct(
        public readonly string $message_id,
        public readonly string $body_plain,
        public readonly string $provider_variables,
        public readonly string $raw_email,
        public readonly string $recipient,
        public readonly string $retries,
        public readonly string $sender,
        public readonly string $status,
        public readonly string $stripped_text,
        public readonly string $subject
    ) {
    }

    /**
     * @param array<string, string> $data
     * @return self
     * @throws BristolianException
     */
    public static function createFromData(array $data): self
    {
        $required_keys = [
            'message_id' => "Message-Id",
            'body_plain' => 'body-plain',
            'recipient' => 'recipient',
            'sender' => 'sender',
            'stripped_text' => 'stripped-text',
            'subject' => 'subject',
            //'provider_variables' => ,
            'raw_email' => 'raw_email',
//            'created_at' => 'created_at',
        ];
        $calling_data = [];

        foreach ($required_keys as $key => $original_key) {
            if (array_key_exists($original_key, $data) === true) {
                $calling_data[$key] = $data[$original_key];
            }
            else {
                throw new BristolianException("Missing key $original_key");
            }
        }

        return new self(
            $calling_data['message_id'],
            $calling_data['body_plain'],
            $provider_variables = json_encode_safe([]),
            $raw_email = $calling_data['raw_email'],
            $calling_data['recipient'],
            $retries = "0",
            $calling_data['sender'],
            $status = IncomingEmailParam::STATUS_INITIAL,
            $calling_data['stripped_text'],
            $calling_data['subject'],
        );
    }
}
