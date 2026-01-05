<?php

namespace Bristolian\AppController;

use Bristolian\Parameters\MemeSearchParams;
use Bristolian\Parameters\MemeTagDeleteParams;
use Bristolian\Parameters\MemeTagParams;
use Bristolian\Parameters\MemeTagUpdateParams;
use Bristolian\Response\SuccessResponse;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagType;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\Session\AppSessionManager;
use Bristolian\Session\UserSession;
use Psr\Http\Message\ServerRequestInterface as Request;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\StubResponse;
use Bristolian\Response\Typed\GetMemesResponse;
use Bristolian\Response\GetMemeTagsResponse;
use SlimDispatcher\Response\TextResponse;
use Bristolian\Response\EndpointAccessedViaGetResponse;

class User
{
    public function listMemes(
        MemeStorageRepo $memeStorageRepo,
        UserSession $appSession,
    ): StubResponse {
        $memes = $memeStorageRepo->listMemesForUser($appSession->getUserId());

        return new GetMemesResponse($memes);
    }

    public function searchMemes(
        MemeStorageRepo $memeStorageRepo,
        MemeTextRepo $memeTextRepo,
        UserSession $appSession,
        MemeSearchParams $memeSearchParams,
    ): GetMemesResponse {
        $tag_search_memes = [];
        $text_search_memes = [];

        // Search by tags if query is provided
        if ($memeSearchParams->query !== null && $memeSearchParams->query !== '') {
            $tag_search_memes = $memeStorageRepo->searchMemesForUser(
                $appSession->getUserId(),
                $memeSearchParams->query,
                MemeTagType::USER_TAG->value
            );
        }

        // Search by text if text_search is provided
        if ($memeSearchParams->text_search !== null && $memeSearchParams->text_search !== '') {
            $text_search_meme_ids = $memeTextRepo->searchMemeIdsByText(
                $appSession->getUserId(),
                $memeSearchParams->text_search
            );

            // Fetch memes by IDs
            $text_search_memes = [];
            foreach ($text_search_meme_ids as $meme_id) {
                $meme = $memeStorageRepo->getMeme($meme_id);
                if ($meme !== null) {
                    $text_search_memes[] = $meme;
                }
            }
        }

        // Combine results (union - memes that match either tag or text search)
        $all_meme_ids = [];
        $memes_by_id = [];
        
        foreach ($tag_search_memes as $meme) {
            $all_meme_ids[$meme->id] = true;
            $memes_by_id[$meme->id] = $meme;
        }
        
        foreach ($text_search_memes as $meme) {
            if (!isset($all_meme_ids[$meme->id])) {
                $all_meme_ids[$meme->id] = true;
                $memes_by_id[$meme->id] = $meme;
            }
        }

        // If no search criteria provided, return all memes
        if ($memeSearchParams->query === null && $memeSearchParams->text_search === null) {
            $memes = $memeStorageRepo->listMemesForUser($appSession->getUserId());
        } else {
            $memes = array_values($memes_by_id);
        }

        return new GetMemesResponse($memes);
    }

    public function manageMemes(): string
    {
        $content = "";

        $content .= "<h2>Here be memes</h2>";
        $content .= "<div class='meme_upload_panel'></div>";

        $content .= "<div class='meme_management_panel'></div>";

        return $content;
    }

    public function getTagsForMeme(
        UserSession $userSession,
        string      $meme_id,
        MemeTagRepo $memeTagRepo
    ): GetMemeTagsResponse {

//        if ($appSession->isLoggedIn() !== true) {
//            return new JsonResponse([]);
//        }

        $data = $memeTagRepo->getUserTagsForMeme(
            $userSession->getUserId(),
            $meme_id
        );

        return new GetMemeTagsResponse($data);
    }



    public function handleMemeTagAdd(
        UserSession $appSession,
        Request $request,
        MemeTagRepo $memeTagRepo
    ): StubResponse {
        $memeTagParam = MemeTagParams::createFromRequest($request);

//        if ($appSession->isLoggedIn() !== true) {
//            $data = ['not logged in' => true];
//            return new JsonResponse($data, [], 400);
//        }

        // Override type to always be 'user_tag' - users can only create user tags
        $memeTagParam = new MemeTagParams(
            $memeTagParam->meme_id,
            MemeTagType::USER_TAG->value,
            $memeTagParam->text
        );

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

    public function handleMemeTagAdd_get(): EndpointAccessedViaGetResponse
    {
        return new EndpointAccessedViaGetResponse();
    }

    public function handleMemeTagUpdate(
        UserSession $appSession,
        Request $request,
        MemeTagRepo $memeTagRepo
    ): SuccessResponse {
        $memeTagUpdateParams = MemeTagUpdateParams::createFromRequest($request);

        // Override type to always be 'user_tag' - users can only edit user tags
        $memeTagUpdateParams = new MemeTagUpdateParams(
            $memeTagUpdateParams->meme_tag_id,
            MemeTagType::USER_TAG->value,
            $memeTagUpdateParams->text
        );

        $memeTagRepo->updateTagForUser(
            $appSession->getUserId(),
            $memeTagUpdateParams
        );

        return new SuccessResponse();
    }

    public function handleMemeTagUpdate_get(): EndpointAccessedViaGetResponse
    {
        return new EndpointAccessedViaGetResponse();
    }

    public function handleMemeTagDelete(
        UserSession $appSession,
        Request $request,
        MemeTagRepo $memeTagRepo
    ): StubResponse {
//        if ($appSession->isLoggedIn() !== true) {
//            $data = ['not logged in' => true];
//            return new JsonResponse($data, [], 400);
//        }

        // TODO: Add body parsing for DELETE requests. Currently using POST because PHP doesn't parse DELETE request bodies by default.
        // PHP 8.4+ has request_parse_body() function that could be used, or we could manually parse php://input for DELETE requests.

        $memeTagDeleteParam = MemeTagDeleteParams::createFromRequest($request);

        $memeTagRepo->deleteTagForUser(
            $appSession->getUserId(),
            $memeTagDeleteParam->meme_tag_id
        );

        // Why are we doing this? Seems like early optimisation.
        $data = $memeTagRepo->getUserTagsForMeme(
            $appSession->getUserId(),
            $memeTagDeleteParam->meme_id
        );

        return new JsonResponse($data);
    }

    public function handleMemeTagDelete_get(): EndpointAccessedViaGetResponse
    {
        return EndpointAccessedViaGetResponse::forDelete();
    }


    public function get_login_status(AppSessionManager $appSessionManager): JsonNoCacheResponse
    {
        $data = [
            'logged_in' => false,
        ];

        $appSession = $appSessionManager->getCurrentAppSession();
        if ($appSession) { // && $appSession->isLoggedIn()) {
            $data = [
                'logged_in' => true,
            ];
        }

        return new JsonNoCacheResponse($data);
    }
}
