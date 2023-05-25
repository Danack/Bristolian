<?php

declare(strict_types = 1);

namespace Osf\Model;

use Bristolian\ToArray;
use DateTime;


class AdminUser
{
    use ToArray;

    protected $id;

    protected $username;

    protected $password_hash;

//    protected $google_2fa_secret;
//
//    protected $created_at;
//
//    protected $updated_at;


    public static function fromPartial(
        string $username,
        string $password_hash,
//        ?string $google2FA
    ) {
        $instance = new self();
        $instance->username = $username;
        $instance->password_hash = $password_hash;
//        $instance->google_2fa_secret = $google2FA;

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
