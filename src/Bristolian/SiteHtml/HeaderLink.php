<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

class HeaderLink
{
    private string $path;

    private string $description;

    public function __construct(
        string $path,
        string $description
    ) {
        $this->path = $path;
        $this->description = $description;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
