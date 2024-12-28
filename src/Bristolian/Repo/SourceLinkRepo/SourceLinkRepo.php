<?php

namespace Bristolian\Repo\SourceLinkRepo;

use Bristolian\DataType\SourceLinkHighlightParam;

interface SourceLinkRepo
{
    /**
     * @param string $user_id
     * @param string $title
     * @param SourceLinkHighlightParam[] $highlights
     * @return string
     */
    public function addSourceLink(string $user_id, string $title, array $highlights): string;
}
