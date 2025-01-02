<?php

namespace Bristolian\Repo\LinkRepo;

use Ramsey\Uuid\Uuid;
use Bristolian\Model\Link;

class FakeLinkRepo implements LinkRepo
{
    /**
     * @var Link[]
     */
    private array $storedLinks = [];

    public function store_link(
        string $user_id,
        string $url,
    ): string {

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $datetime = new \DateTimeImmutable();

        $this->storedLinks[$id] = new Link(
            $id,
            $user_id,
            $url,
            $created_at = $datetime->format("Y-m-d H:i:s")
        );

        return $id;
    }

    /**
     * @return Link[]
     */
    public function getStoredLinks(): array
    {
        return $this->storedLinks;
    }

    /**
     * @return Link|null
     */
    public function getLastAddedLink(): Link|null
    {
        if (count($this->storedLinks) === 0) {
            return null;
        }

        return end($this->storedLinks);
    }
}
