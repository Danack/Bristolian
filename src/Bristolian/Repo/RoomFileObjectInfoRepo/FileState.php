<?php

namespace Bristolian\Repo\RoomFileObjectInfoRepo;

enum FileState: string
{
    case INITIAL = 'initial';
    case UPLOADED = 'uploaded';
    case DELETED = 'deleted';
}
