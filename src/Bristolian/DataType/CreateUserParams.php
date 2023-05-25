<?php

declare(strict_types = 1);

namespace Bristolian\DataType;

use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class CreateUserParams implements DataType
{
    private string $email_address;

    private string $password;

    use CreateFromVarMap;
    use CreateFromArray;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[EmailAddress('email_address')]
        string $email_address,
        #[PasswordOrRandom('password')]
        string $password
    ) {
        $this->email_address = $email_address;
        $this->password = $password;
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
