<?php

namespace BristolianTest\DataType;

use DataType\DataType;
use DataType\Create\CreateFromVarMap;
use DataType\GetInputTypesFromAttributes;

use Bristolian\DataType\BasicInteger;
use Bristolian\DataType\BasicString;
use Bristolian\DataType\Url;
use Bristolian\DataType\Username;
use Bristolian\DataType\BasicDateTime;
use Bristolian\DataType\EmailAddress;

class CoverageParam implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('string')]
        public string $string,
        #[BasicInteger('integer')]
        public int $integer,
        #[Url('url')]
        public string $url,
        #[Username('username')]
        public string $username,
        #[BasicDateTime('datetime')]
        public \DateTimeInterface $datetime,
        #[EmailAddress('email_address')]
        public string $email_address,
    ) {
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getInteger(): int
    {
        return $this->integer;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getDatetime(): \DateTimeInterface
    {
        return $this->datetime;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->email_address;
    }
}
