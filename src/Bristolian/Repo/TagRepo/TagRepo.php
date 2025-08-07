<?php

namespace Bristolian\Repo\TagRepo;

use Bristolian\Parameters\TagParams;
use Bristolian\Model\Tag;

interface TagRepo
{
    /**
     * @return \Bristolian\Model\Tag[]
     */
    public function getAllTags(): array;


    public function createTag(TagParams $tagParam): Tag;
}
