<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

class SectionList
{
    /** @var Section[] */
    private array $sections;

    /**
     *
     * @param Section[] $sections
     */
    public function __construct(array $sections)
    {
        $this->sections = $sections;
    }

    /**
     * @return Section[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }
}
