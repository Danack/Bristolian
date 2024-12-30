<?php

namespace Bristolian\Repo\UserDocumentRepo;

use Bristolian\Model\User;
use Bristolian\Model\UserDocument;
use Bristolian\Types\UserList;

class HardcodedUserDocumentRepo implements UserDocumentRepo
{
    public function getUserDocuments(User $user): array
    {
        if ($user->username !== UserList::sid->value) {
            return [];
        }

        $filename = standardise_username_to_filename(UserList::sid->value);

        $filepath = __DIR__ . "/../../../../user_data/" . $filename . "/documents.json";
        $documents_json = \Safe\file_get_contents($filepath);
        $user_data = json_decode_safe($documents_json);

        if (array_key_exists("documents", $user_data) === false) {
            return [];
        }

        $documents_data = $user_data["documents"];
        $documents = UserDocument::createArrayOfTypeFromArray($documents_data);

        return $documents;
    }


    public function renderUserDocument(User $user, string $title): string
    {
        if ($user->username !== UserList::sid->value) {
            return "User not found";
        }

        $documents = $this->getUserDocuments($user);

        foreach ($documents as $document) {
            if (slugify($document->title) === $title) {
                return render_user_document($document);
            }
        }

        return "Document not found.";
    }
}
