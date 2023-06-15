<?php

namespace Bristolian\Repo\TagRepo;

use Bristolian\DataType\TagParam;
use Bristolian\Model\Tag;

interface TagRepo
{
    /**
     * @return \Bristolian\Model\Tag[]
     */
    public function getAllTags(): array;


    public function createTag(TagParam $tagParam): Tag;
}
