<?php

namespace Bristolian\Repo\UserDocumentRepo;

use Bristolian\Model\Types\UserDocument;
use User;

interface UserDocumentRepo
{
    /**
     * @param User $user
     * @return UserDocument[]
     */
    public function getUserDocuments(User $user);

    public function renderUserDocument(User $user, string $title): string;
}
