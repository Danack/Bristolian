<?php

namespace Bristolian\AppController;

use Bristolian\Repo\UserDocumentRepo\UserDocumentRepo;
use Bristolian\Repo\UserRepo\UserRepo;

class Users
{
    public function index(UserRepo $userRepo): string
    {
        $contents = "<h1>User list</h1>";

        $template = "<a href='/users/:attr_username'>:html_username</a>";

        foreach ($userRepo->getUsers() as $user) {
            $params = [
                ':attr_username' => $user->username,
                ':html_username' => $user->username,
            ];

            $contents .= esprintf($template, $params);
        }

        return $contents;
    }

    public function showUser(
        UserRepo $userRepo,
        UserDocumentRepo $userDocumentRepo,
        string $username
    ): string {
        $user = $userRepo->findUser($username);

        if ($user === null) {
            return "User not found.";
        }

        $documents = $userDocumentRepo->getUserDocuments($user);
        $contents = "<h1>User has these documents</h1>";
        $template = "<a href='/users/:uri_username/docs/:uri_link'>:html_title</a>";

        foreach ($documents as $document) {
            $params = [
                ':uri_username' => $user->username,
                ':uri_link' => slugify($document->title),
                ':html_title' => $document->title
            ];

            $contents .= esprintf($template, $params);
            $contents .= "<br/>";
        }

        $contents .= "<br/><br/><br/><br/><br/>";

        return $contents;
    }

    public function showUserDocument(
        UserRepo $userRepo,
        UserDocumentRepo $userDocumentRepo,
        string $username,
        string $title
    ): string {
        $user = $userRepo->findUser($username);

        if ($user === null) {
            return "User not found.";
        }

        return $userDocumentRepo->renderUserDocument($user, $title);
    }
}
