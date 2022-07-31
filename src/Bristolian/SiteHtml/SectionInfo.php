<?php

declare(strict_types = 1);

namespace Bristolian\SiteHtml;

interface SectionInfo
{
    /** @return GetRoute[] */
    public function getRoutes();
}
