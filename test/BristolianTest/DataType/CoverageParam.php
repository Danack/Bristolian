<?php

namespace BristolianTest\DataType;

use DataType\DataType;
use DataType\Create\CreateFromVarMap;
use DataType\GetInputTypesFromAttributes;

use Bristolian\DataType\PropertyType\SourceLinkPositionValue;
use Bristolian\DataType\PropertyType\BasicString;
use Bristolian\DataType\PropertyType\Url;
use Bristolian\DataType\PropertyType\Username;
use Bristolian\DataType\PropertyType\BasicDateTime;
use Bristolian\DataType\PropertyType\EmailAddress;
use Bristolian\DataType\PropertyType\LinkDescription;
use Bristolian\DataType\PropertyType\LinkTitle;
use Bristolian\DataType\PropertyType\WebPushEndPoint;
use Bristolian\DataType\PropertyType\WebPushExpirationTime;
use Bristolian\DataType\PropertyType\PasswordOrRandom;

/**
 * This is a class solely used for adding coverage to
 * InputTypes.
 *
 * @coversNothing
 */
class CoverageParam implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('string')]
        public string $string,
        #[SourceLinkPositionValue('integer')]
        public int $integer,
        #[Url('url')]
        public string $url,
        #[Username('username')]
        public string $username,
        #[BasicDateTime('datetime')]
        public \DateTimeInterface $datetime,
        #[EmailAddress('email_address')]
        public string $email_address,
        #[LinkTitle('link_title')]
        public string $link_title,
        #[LinkDescription('link_description')]
        public string $link_description,
        #[WebPushEndPoint('web_push_end_point')]
        public string $web_push_end_point,
        #[WebPushExpirationTime('web_push_expiration_time')]
        public string $web_push_expiration_time,
        #[PasswordOrRandom('password')]
        public string $passwordOrRandom,
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
