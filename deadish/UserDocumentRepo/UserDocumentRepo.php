<?php

namespace deadish\UserDocumentRepo;

use deadish\UserDocument;
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
