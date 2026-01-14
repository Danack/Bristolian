<?php

declare(strict_types = 1);


use Bristolian\ToArray;

class Room
{
    use ToArray;

    public function __construct(
        public readonly string $id,
        public readonly string $owner_user_id,
        public readonly string $name,
        public readonly string $purpose,
    ) {
    }

    /**
     * @return string
     */
    public function getRoomId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOwnerUserId(): string
    {
        return $this->owner_user_id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPurpose(): string
    {
        return $this->purpose;
    }
}
