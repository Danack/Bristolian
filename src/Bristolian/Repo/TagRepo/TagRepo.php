<?php

namespace Bristolian\Repo\TagRepo;

use Bristolian\Model\Types\Tag;
use Bristolian\Parameters\TagParams;

interface TagRepo
{
    /**
     * @return \Bristolian\Model\Types\Tag[]
     */
    public function getAllTags(): array;


    public function createTag(TagParams $tagParam): Tag;
}
