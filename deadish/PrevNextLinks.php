<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

class PrevNextLinks
{
    private ?ContentLink $prevLink;

    private ?ContentLink $nextLink;

    public function __construct(
        ?ContentLink $prev,
        ?ContentLink $next
    ) {
        $this->prevLink = $prev;
        $this->nextLink = $next;
    }

    public function getPrevLink(): ?ContentLink
    {
        return $this->prevLink;
    }

    public function getNextLink(): ?ContentLink
    {
        return $this->nextLink;
    }
}
