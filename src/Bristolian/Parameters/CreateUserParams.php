<?php

declare(strict_types = 1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\EmailAddress;
use Bristolian\Parameters\PropertyType\PasswordOrRandom;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class CreateUserParams implements DataType
{
    use CreateFromVarMap;
    use CreateFromArray;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[EmailAddress('email_address')]
        private string $email_address,
        #[PasswordOrRandom('password')]
        private string $password
    ) {
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
    public function getPassword(): string
    {
        return $this->password;
    }
}
