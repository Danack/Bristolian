<?php

namespace Bristolian\Repo\MemeStorageRepo;

enum MemeFileState: string
{
    case INITIAL = 'initial';
    case UPLOADED = 'uploaded';
    case DELETED = 'deleted';
}
