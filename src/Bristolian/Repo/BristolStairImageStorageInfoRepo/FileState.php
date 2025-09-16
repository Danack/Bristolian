<?php

namespace Bristolian\Repo\BristolStairImageStorageInfoRepo;

enum FileState: string
{
    case INITIAL = 'initial';
    case UPLOADED = 'uploaded';
    case DELETED = 'deleted';
}
