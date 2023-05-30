<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

class Breadcrumb
{
    private string $description;

    /** The path relative to the root of the content for this section */
    private string $path;

    /**
     *
     * @param string $description
     * @param string $path
     */
    public function __construct(
        string $path,
        string $description
    ) {
        $this->description = $description;
        $this->path = $path;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
