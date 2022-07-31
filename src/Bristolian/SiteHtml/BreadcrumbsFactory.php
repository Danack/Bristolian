<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

class BreadcrumbsFactory
{
    private Section $currentSection;

    /**
     * BreadcrumbsFactory constructor.
     * @param Section $currentSection
     */
    public function __construct(Section $currentSection)
    {
        $this->currentSection = $currentSection;
    }

    // TODO - move to section.
    public function createFromArray(array $subParts)
    {
//        $allParts = [
//            $this->currentSection->getPrefix() => $this->currentSection->getName(),
//        ];

//        foreach ($subParts as $key => $value) {
//            $path = $this->currentSection->getPrefix() . $key;
//
//            $allParts[$path] = $value;
//        }

        return Breadcrumbs::fromArray($subParts);
    }
}
