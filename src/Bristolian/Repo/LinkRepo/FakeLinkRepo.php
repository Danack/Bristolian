<?php

namespace Bristolian\Repo\LinkRepo;

use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class FakeLinkRepo implements LinkRepo
{
    private $storedLinks = [];

    public function store_link(
        string $user_id,
        string $url,
    ): string {

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $params = [
            'id' => $id,
            'user_id' => $user_id,
            'url' => $url,
        ];

        $this->storedLinks[$id] = $params;

        return $id;
    }

    /**
     * @return array
     */
    public function getStoredLinks(): array
    {
        return $this->storedLinks;
    }

    public function getLastAddedLink()
    {
        return end($this->storedLinks);
    }
}
