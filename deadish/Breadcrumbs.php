<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

class Breadcrumbs
{
    /** @var Breadcrumb[] */
    private array $breadcrumbs;

    public function __construct(Breadcrumb ...$breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * @param array<string, string> $breadcrumbData Array of paths with description
     * @return static
     */
    public static function fromArray(array $breadcrumbData): self
    {
        $breadcrumbs = [];
        foreach ($breadcrumbData as $path => $description) {
            $breadcrumbs[] = new Breadcrumb($path, $description);
        }

        return new self(...$breadcrumbs);
    }

    /**
     * @return Breadcrumb[]
     */
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }
}
