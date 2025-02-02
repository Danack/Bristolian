<?php

namespace Bristolian\Repo\FileStorageInfoRepo;

enum FileState: string
{
    case INITIAL = 'initial';
    case UPLOADED = 'uploaded';
    case DELETED = 'deleted';
}
