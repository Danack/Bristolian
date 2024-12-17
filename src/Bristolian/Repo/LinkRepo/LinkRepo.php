<?php

namespace Bristolian\Repo\LinkRepo;

interface LinkRepo
{
    public function store_link(string $user_id, string $url): string;
}
