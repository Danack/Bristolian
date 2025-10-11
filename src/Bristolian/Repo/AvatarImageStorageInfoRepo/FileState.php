<?php

namespace Bristolian\Repo\AvatarImageStorageInfoRepo;

enum FileState: string
{
    case INITIAL = 'initial';
    case UPLOADED = 'uploaded';
    case DELETED = 'deleted';
}

