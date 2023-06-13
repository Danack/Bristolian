<?php

declare(strict_types = 1);

namespace Bristolian\Model;

use Bristolian\ToArray;
use DateTime;

class AdminUser
{
    use ToArray;

    protected string $user_id;

    protected string $email_address;

    protected string $password_hash;

    /**
     * @param $id
     * @param $username
     * @param $password_hash
     */
    public static function new(string $user_id, string $email_address, string $password_hash): self
    {
        $instance = new self();
        $instance->user_id = $user_id;
        $instance->email_address = $email_address;
        $instance->password_hash = $password_hash;

        return $instance;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->email_address;
    }

    /**
     * @return string
     */
    public function getPasswordHash(): string
    {
        return $this->password_hash;
    }

    public static function fromPartial(
        string $email_address,
        string $password_hash,
    ): self {
        $instance = new self();
        $instance->email_address = $email_address;
        $instance->password_hash = $password_hash;

        return $instance;
    }


//
//    public static function fromArray(array $data)
//    {
//        $instance = new self();
//
//        $instance->id = $data['id'];
//        $instance->username = $data['username'];
//        $instance->password_hash = $data['password_hash'];
//        $instance->google_2fa_secret = $data['google_2fa_secret'];
//
//        $format_RFC3339_micro = "Y-m-d\TH:i:s.uP";
//
//        $instance->created_at = DateTime::createFromFormat($format_RFC3339_micro, $data['created_at']);
//        $instance->updated_at = DateTime::createFromFormat($format_RFC3339_micro, $data['updated_at']);
//
//        return $instance;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getId()
//    {
//        return $this->id;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getUsername()
//    {
//        return $this->username;
//    }
//
//
//    /**
//     * @param mixed $password_hash
//     */
//    public function setPasswordHash($password_hash)
//    {
//        $this->password_hash = $password_hash;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPasswordHash()
//    {
//        return $this->password_hash;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getGoogle2faSecret()
//    {
//        return $this->google_2fa_secret;
//    }
//
//
//    public function hasGoogle2FaEnabled(): bool
//    {
//        return ($this->google_2fa_secret !== null);
//    }
//
//
//    public function setGoogle2faSecret(string $google_2fa_secret)
//    {
//        $this->google_2fa_secret = $google_2fa_secret;
//    }
//
//    public function clearGoogle2faSecret()
//    {
//        $this->google_2fa_secret = null;
//    }
}
