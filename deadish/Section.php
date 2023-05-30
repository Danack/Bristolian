<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

class Section
{
    // The prefix used for routing on this site
    private string $prefix;

    // The name of the section
    private string $name;

    private string $purpose;

    private SectionInfo $sectionInfo;


    public function __construct(
        string $prefix,
        string $name,
        string $purpose,
        SectionInfo $sectionInfo
    ) {
        $this->prefix = $prefix;
        $this->name = $name;
        $this->purpose = $purpose;
        $this->sectionInfo = $sectionInfo;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPurpose(): string
    {
        return $this->purpose;
    }

    /**
     * @return SectionInfo
     */
    public function getSectionInfo(): SectionInfo
    {
        return $this->sectionInfo;
    }
}
