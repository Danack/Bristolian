<?php

namespace Bristolian\Repo\MemeTextRepo;

use Bristolian\Model\Meme;

interface MemeTextRepo
{
    public function getNextMemeToOCR(): Meme|null;
}


