<?php

namespace Bristolian\Repo\FileStorageRepo;

enum FileState: string
{
    case INITIAL = 'initial';
    case UPLOADED = 'uploaded';
}
