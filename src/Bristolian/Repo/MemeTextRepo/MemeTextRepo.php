<?php

namespace Bristolian\Repo\MemeTextRepo;


use Bristolian\Model\Generated\StoredMeme;


interface MemeTextRepo
{
    public function getNextMemeToOCR(): StoredMeme|null;

    public function saveMemeText(
        StoredMeme $storedMeme,
        string $found_text
    );
}


