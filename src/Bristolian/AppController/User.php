<?php

namespace Bristolian\AppController;

use Bristolian\DataType\MemeTagDeleteParam;
use Bristolian\DataType\MemeTagParam;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use Bristolian\Session\AppSessionManager;
use Bristolian\Session\UserSession;
use Psr\Http\Message\ServerRequestInterface as Request;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\StubResponse;

class User
{
    public function listMemes(
        MemeStorageRepo $memeStorageRepo,
        UserSession $appSession,
    ): StubResponse {
        if ($appSession->isLoggedIn() !== true) {
            $data = ['not logged in' => true];
            return new JsonResponse($data, [], 400);
        }

        $memes = $memeStorageRepo->listMemesForUser($appSession->getUserId());


        [$error, $values] = convertToValue($memes);

        $data = [
            'result' => 'success',
            'memes' => $values
        ];

        return new JsonResponse($data);
    }


    public function manageMemes(): string
    {
        $content = "";

        $content .= "Meme upload panel:";
        $content .= "<div class='meme_upload_panel'></div>";

        $content .= "Meme management panel:";
        $content .= "<div class='meme_management_panel'></div>";

        return $content;
    }

    public function getTagsForMeme(
        UserSession $appSession,
        string $meme_id,
        MemeTagRepo $memeTagRepo
    ): JsonResponse {

        if ($appSession->isLoggedIn() !== true) {
            return new JsonResponse([]);
        }

        $data = $memeTagRepo->getUserTagsForMeme(
            $appSession->getUserId(),
            $meme_id
        );

        return new JsonResponse($data);
    }



    public function handleMemeTagAdd(
        UserSession $appSession,
        Request $request,
        MemeTagRepo $memeTagRepo
    ): StubResponse {
        $memeTagParam = MemeTagParam::createFromRequest($request);

        if ($appSession->isLoggedIn() !== true) {
            $data = ['not logged in' => true];
            return new JsonResponse($data, [], 400);
        }

        $memeTagRepo->addTagForMeme(
            $appSession->getUserId(),
            $memeTagParam
        );

        $data = $memeTagRepo->getUserTagsForMeme(
            $appSession->getUserId(),
            $memeTagParam->meme_id
        );

        return new JsonResponse($data);
    }

    public function handleMemeTagAdd_get(): string
    {
        return "This is a GET only end-point";
    }


    public function handleMemeTagDelete(
        UserSession $appSession,
        Request $request,
        MemeTagRepo $memeTagRepo
    ): StubResponse {
        if ($appSession->isLoggedIn() !== true) {
            $data = ['not logged in' => true];
            return new JsonResponse($data, [], 400);
        }

        $memeTagDeleteParam = MemeTagDeleteParam::createFromRequest($request);

        $memeTagRepo->deleteTagForUser(
            $appSession->getUserId(),
            $memeTagDeleteParam->meme_tag_id
        );

        $data = $memeTagRepo->getUserTagsForMeme(
            $appSession->getUserId(),
            $memeTagDeleteParam->meme_id
        );
        return new JsonResponse($data);
    }

    public function handleMemeTagDelete_get(): string
    {
        return "This is a GET only end-point";
    }


    public function get_login_status(AppSessionManager $appSessionManager): JsonNoCacheResponse
    {
        $data = [
            'logged_in' => false,
        ];

        $appSession = $appSessionManager->getCurrentAppSession();
        if ($appSession && $appSession->isLoggedIn()) {
            $data = [
                'logged_in' => true,
            ];
        }

        return new JsonNoCacheResponse($data);
    }
}
