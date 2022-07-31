<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

class HeaderLinks
{
    /** @var HeaderLink[] */
    private array $headerLinks;

    /**
     *
     * @param HeaderLink[] $headerLinks
     */
    public function __construct(array $headerLinks)
    {
        $this->headerLinks = $headerLinks;
    }

    /**
     * @return HeaderLink[]
     */
    public function getHeaderLinks(): array
    {
        return $this->headerLinks;
    }
}
