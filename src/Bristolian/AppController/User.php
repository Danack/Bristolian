<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\Model\Generated\StoredMeme;
use Bristolian\Parameters\MemeSearchParams;
use Bristolian\Parameters\MemeTagDeleteParams;
use Bristolian\Parameters\MemeTagParams;
use Bristolian\Parameters\MemeTagUpdateParams;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagType;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\GetMemeTagSuggestionsResponse;
use Bristolian\Response\GetMemeTextResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Response\Typed\GetMemesResponse;
use Bristolian\Response\Typed\GetMemesTagsResponse;
use Bristolian\Response\Typed\PostMemetagaddResponse;
use Bristolian\Response\Typed\PostMemetagdeleteResponse;
use Bristolian\Session\AppSessionManager;
use Bristolian\Session\UserSession;
use Psr\Http\Message\ServerRequestInterface as Request;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\StubResponse;

class User
{
    public const MEMES_DISPLAY_LIMIT = 50;

    public function listMemes(
        MemeStorageRepo $memeStorageRepo,
        UserSession $appSession,
    ): StubResponse {
        $memes = $memeStorageRepo->listMemesForUser($appSession->getUserId());

        $storedMemes = array_map(
            fn($meme) => new StoredMeme(
                $meme->id,
                $meme->normalized_name,
                $meme->original_filename,
                $meme->state,
                $meme->size,
                $meme->user_id,
                $meme->created_at,
                $meme->deleted ? 1 : 0
            ),
            $memes
        );

        $truncated = count($storedMemes) > self::MEMES_DISPLAY_LIMIT;
        if ($truncated) {
            $storedMemes = array_slice($storedMemes, 0, self::MEMES_DISPLAY_LIMIT);
        }

        return new GetMemesResponse($storedMemes, $truncated);
    }

    public function searchMemes(
        MemeStorageRepo $memeStorageRepo,
        MemeTextRepo $memeTextRepo,
        UserSession $appSession,
        MemeSearchParams $memeSearchParams,
    ): GetMemesResponse {
        $tag_search_memes = [];
        $exact_tag_search_memes = [];
        $text_search_memes = [];

        // Search by exact tags if tags parameter is provided (comma-separated)
        if ($memeSearchParams->tags !== null && $memeSearchParams->tags !== '') {
            $tagTexts = array_filter(array_map('trim', explode(',', $memeSearchParams->tags)));
            if (count($tagTexts) > 0) {
                $exact_tag_search_memes = $memeStorageRepo->searchMemesByExactTags(
                    $appSession->getUserId(),
                    $tagTexts
                );
            }
        }

        // Search by tags if query is provided (LIKE search for backward compatibility)
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

        // Combine results (union - memes that match any search criteria)
        $all_meme_ids = [];
        $memes_by_id = [];
        
        foreach ($exact_tag_search_memes as $meme) {
            $all_meme_ids[$meme->id] = true;
            $memes_by_id[$meme->id] = $meme;
        }
        
        foreach ($tag_search_memes as $meme) {
            if (!isset($all_meme_ids[$meme->id])) {
                $all_meme_ids[$meme->id] = true;
                $memes_by_id[$meme->id] = $meme;
            }
        }
        
        foreach ($text_search_memes as $meme) {
            if (!isset($all_meme_ids[$meme->id])) {
                $all_meme_ids[$meme->id] = true;
                $memes_by_id[$meme->id] = $meme;
            }
        }

        // If no search criteria provided, return all memes
        if ($memeSearchParams->query === null &&
            $memeSearchParams->text_search === null &&
            ($memeSearchParams->tags === null || $memeSearchParams->tags === '')) {
            $memes = $memeStorageRepo->listMemesForUser($appSession->getUserId());
        }
        else {
            $memes = array_values($memes_by_id);
        }

        $storedMemes = array_map(
            fn($meme) => new StoredMeme(
                $meme->id,
                $meme->normalized_name,
                $meme->original_filename,
                $meme->state,
                $meme->size,
                $meme->user_id,
                $meme->created_at,
                $meme->deleted ? 1 : 0
            ),
            $memes
        );

        $truncated = count($storedMemes) > self::MEMES_DISPLAY_LIMIT;
        if ($truncated) {
            $storedMemes = array_slice($storedMemes, 0, self::MEMES_DISPLAY_LIMIT);
        }

        return new GetMemesResponse($storedMemes, $truncated);
    }

    public function manageMemes(): string
    {
        $content = "";

        $content .= "<h2>Here be memes</h2>";
        $content .= "<div class='meme_management_panel'></div>";

        return $content;
    }

    public function getTagsForMeme(
        UserSession $userSession,
        string      $meme_id,
        MemeTagRepo $memeTagRepo
    ): GetMemesTagsResponse {

//        if ($appSession->isLoggedIn() !== true) {
//            return new JsonResponse([]);
//        }

        $memeTags = $memeTagRepo->getUserTagsForMeme(
            $userSession->getUserId(),
            $meme_id
        );

        return new GetMemesTagsResponse($memeTags);
    }

    public function getMemeText(
        UserSession $userSession,
        string $meme_id,
        MemeTextRepo $memeTextRepo,
        MemeStorageRepo $memeStorageRepo
    ): GetMemeTextResponse {
        // Verify the meme belongs to the user
        $meme = $memeStorageRepo->getMeme($meme_id);
        if ($meme === null || $meme->user_id !== $userSession->getUserId()) {
            return new GetMemeTextResponse(null);
        }

        $meme_text = $memeTextRepo->getMemeText($meme_id);
        return new GetMemeTextResponse($meme_text);
    }

    public function updateMemeText(
        UserSession $userSession,
        string $meme_id,
        Request $request,
        MemeTextRepo $memeTextRepo,
        MemeStorageRepo $memeStorageRepo
    ): SuccessResponse {
        // Verify the meme belongs to the user
        $meme = $memeStorageRepo->getMeme($meme_id);
        if ($meme === null || $meme->user_id !== $userSession->getUserId()) {
            return new SuccessResponse(); // Return success even if not found for security
        }

        // Get text from request body - FormData should be parsed by Slim middleware
        $body = $request->getParsedBody();
        $text = '';
        if (is_array($body) && isset($body['text'])) {
            $text = $body['text'];
        }
        elseif ($body instanceof \ArrayAccess && isset($body['text'])) {
            $text = $body['text'];
        }
        else {
            // Fallback: try getting from $_POST (FormData might populate it)
            $text = $_POST['text'] ?? '';
        }

        // Validate text length (matches the database column size)
        if (strlen($text) > 4096) {
            $text = substr($text, 0, 4096);
        }

        $memeTextRepo->updateMemeText($meme_id, $text);
        return new SuccessResponse();
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

        $memeTags = $memeTagRepo->getUserTagsForMeme(
            $appSession->getUserId(),
            $memeTagParam->meme_id
        );

        return new PostMemetagaddResponse($memeTags);
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
        $memeTags = $memeTagRepo->getUserTagsForMeme(
            $appSession->getUserId(),
            $memeTagDeleteParam->meme_id
        );

        return new PostMemetagdeleteResponse($memeTags);
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

    public function getMemeTagSuggestions(
        UserSession $userSession,
        MemeTagRepo $memeTagRepo,
        Request $request
    ): GetMemeTagSuggestionsResponse {
        $queryParams = $request->getQueryParams();
        $memeIds = [];
        
        // If meme_ids parameter is provided, get tags for those specific memes
        if (isset($queryParams['meme_ids']) && is_string($queryParams['meme_ids'])) {
            $memeIds = array_filter(array_map('trim', explode(',', $queryParams['meme_ids'])));
        }

        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;

        if (count($memeIds) > 0) {
            $tags = $memeTagRepo->getMostCommonTagsForMemes(
                $userSession->getUserId(),
                $memeIds,
                $limit
            );
        }
        else {
            $tags = $memeTagRepo->getMostCommonTags(
                $userSession->getUserId(),
                $limit
            );
        }

        return new GetMemeTagSuggestionsResponse($tags);
    }

    public function getMemeTagSuggestions_get(): EndpointAccessedViaGetResponse
    {
        return new EndpointAccessedViaGetResponse();
    }
}
