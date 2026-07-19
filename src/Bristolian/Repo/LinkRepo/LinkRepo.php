<?php

namespace Bristolian\Repo\LinkRepo;

use Bristolian\Attribute\WritesTable;
use Bristolian\Database\link;

interface LinkRepo
{
    #[WritesTable(link::class)]
    public function store_link(string $user_id, string $url): string;
}
