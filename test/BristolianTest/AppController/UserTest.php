<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\User;
use Bristolian\Parameters\MemeSearchParams;
use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\GetMemeTagSuggestionsResponse;
use Bristolian\Response\GetMemeTextResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Response\Typed\GetMemesResponse;
use Bristolian\Response\Typed\GetMemesTagsResponse;
use Bristolian\Response\Typed\PostMemetagaddResponse;
use Bristolian\Response\Typed\PostMemetagdeleteResponse;
use Bristolian\Model\Generated\StoredMeme;
use Bristolian\Session\AppSessionManagerInterface;
use Bristolian\Session\FakeAppSessionManager;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ServerRequestInterface as Request;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class UserTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(MemeStorageRepo::class, FakeMemeStorageRepo::class);
        $this->injector->share(FakeMemeStorageRepo::class);
        $this->injector->alias(MemeTagRepo::class, FakeMemeTagRepo::class);
        $this->injector->share(FakeMemeTagRepo::class);
        $this->injector->alias(MemeTextRepo::class, FakeMemeTextRepo::class);
        $this->injector->share(FakeMemeTextRepo::class);
        $this->setupFakeUserSession();
    }

    /**
     * @covers \Bristolian\AppController\User::listMemes
     */
    public function test_listMemes(): void
    {
        $result = $this->injector->execute([User::class, 'listMemes']);
        $this->assertInstanceOf(GetMemesResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::listUntaggedMemes
     */
    public function test_listUntaggedMemes(): void
    {
        $result = $this->injector->execute([User::class, 'listUntaggedMemes']);
        $this->assertInstanceOf(GetMemesResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::listMemes
     */
    public function test_listMemes_truncates_when_over_display_limit(): void
    {
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $uploadedFile = \Bristolian\UploadedFiles\UploadedFile::fromFile($pdfPath);
        $repo = $this->injector->make(FakeMemeStorageRepo::class);
        for ($i = 0; $i < User::MEMES_DISPLAY_LIMIT + 1; $i++) {
            $repo->storeMeme('test-user-id-001', 'meme-' . $i . '.pdf', $uploadedFile);
        }

        $result = $this->injector->execute([User::class, 'listMemes']);

        $this->assertInstanceOf(GetMemesResponse::class, $result);
        $data = json_decode($result->getBody(), true);
        $this->assertTrue($data['data']['truncated']);
        $this->assertCount(User::MEMES_DISPLAY_LIMIT, $data['data']['memes']);
    }

    /**
     * @covers \Bristolian\AppController\User::listUntaggedMemes
     */
    public function test_listUntaggedMemes_truncates_when_over_display_limit(): void
    {
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $uploadedFile = \Bristolian\UploadedFiles\UploadedFile::fromFile($pdfPath);
        $repo = $this->injector->make(FakeMemeStorageRepo::class);
        for ($i = 0; $i < User::MEMES_DISPLAY_LIMIT + 1; $i++) {
            $repo->storeMeme('test-user-id-001', 'meme-' . $i . '.pdf', $uploadedFile);
        }

        $result = $this->injector->execute([User::class, 'listUntaggedMemes']);

        $this->assertInstanceOf(GetMemesResponse::class, $result);
        $data = json_decode($result->getBody(), true);
        $this->assertTrue($data['data']['truncated']);
        $this->assertCount(User::MEMES_DISPLAY_LIMIT, $data['data']['memes']);
    }

    /**
     * @covers \Bristolian\AppController\User::searchMemes
     */
    public function test_searchMemes_no_criteria(): void
    {
        $params = MemeSearchParams::createFromVarMap(new ArrayVarMap([]));
        $this->injector->share($params);

        $result = $this->injector->execute([User::class, 'searchMemes']);
        $this->assertInstanceOf(GetMemesResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::searchMemes
     */
    public function test_searchMemes_with_query(): void
    {
        $params = MemeSearchParams::createFromVarMap(new ArrayVarMap([
            'query' => 'funny',
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([User::class, 'searchMemes']);
        $this->assertInstanceOf(GetMemesResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::searchMemes
     */
    public function test_searchMemes_with_tags(): void
    {
        $params = MemeSearchParams::createFromVarMap(new ArrayVarMap([
            'tags' => 'a,b',
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([User::class, 'searchMemes']);
        $this->assertInstanceOf(GetMemesResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::searchMemes
     */
    public function test_searchMemes_with_text_search_returns_matching_memes(): void
    {
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $uploadedFile = \Bristolian\UploadedFiles\UploadedFile::fromFile($pdfPath);
        $storageRepo = $this->injector->make(FakeMemeStorageRepo::class);
        $memeId = $storageRepo->storeMeme('test-user-id-001', 'meme-with-text.pdf', $uploadedFile);
        $meme = $storageRepo->getMeme($memeId);
        $storedMeme = new StoredMeme(
            id: $meme->id,
            normalized_name: $meme->normalized_name,
            original_filename: $meme->original_filename,
            state: $meme->state,
            size: $meme->size,
            user_id: $meme->user_id,
            created_at: $meme->created_at,
            deleted: $meme->deleted ? 1 : 0,
        );
        $textRepo = $this->injector->make(FakeMemeTextRepo::class);
        $textRepo->saveMemeText($storedMeme, 'needle in haystack');

        $params = MemeSearchParams::createFromVarMap(new ArrayVarMap([
            'text_search' => 'needle',
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([User::class, 'searchMemes']);

        $this->assertInstanceOf(GetMemesResponse::class, $result);
        $data = json_decode($result->getBody(), true);
        $memes = $data['data']['memes'];
        $this->assertCount(1, $memes);
        $this->assertSame($memeId, $memes[0]['id']);
    }

    /**
     * @covers \Bristolian\AppController\User::searchMemes
     */
    public function test_searchMemes_combined_results_truncated(): void
    {
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $uploadedFile = \Bristolian\UploadedFiles\UploadedFile::fromFile($pdfPath);
        $repo = $this->injector->make(FakeMemeStorageRepo::class);
        for ($i = 0; $i < User::MEMES_DISPLAY_LIMIT + 1; $i++) {
            $repo->storeMeme('test-user-id-001', 'meme-' . $i . '.pdf', $uploadedFile);
        }

        $params = MemeSearchParams::createFromVarMap(new ArrayVarMap([
            'query' => 'any',
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([User::class, 'searchMemes']);

        $this->assertInstanceOf(GetMemesResponse::class, $result);
        $data = json_decode($result->getBody(), true);
        $this->assertTrue($data['data']['truncated']);
        $this->assertCount(User::MEMES_DISPLAY_LIMIT, $data['data']['memes']);
    }

    /**
     * @covers \Bristolian\AppController\User::manageMemes
     */
    public function test_manageMemes(): void
    {
        $result = $this->injector->execute([User::class, 'manageMemes']);
        $this->assertIsString($result);
        $this->assertStringContainsString('meme_management_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\User::getTagsForMeme
     */
    public function test_getTagsForMeme(): void
    {
        $this->injector->defineParam('meme_id', 'fake-meme-id');
        $result = $this->injector->execute([User::class, 'getTagsForMeme']);
        $this->assertInstanceOf(GetMemesTagsResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::getMemeText
     */
    public function test_getMemeText_not_found(): void
    {
        $this->injector->defineParam('meme_id', 'nonexistent-meme-id');
        $result = $this->injector->execute([User::class, 'getMemeText']);
        $this->assertInstanceOf(GetMemeTextResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::getMemeText
     */
    public function test_getMemeText_found_returns_text(): void
    {
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $uploadedFile = \Bristolian\UploadedFiles\UploadedFile::fromFile($pdfPath);
        $storageRepo = $this->injector->make(FakeMemeStorageRepo::class);
        $memeId = $storageRepo->storeMeme('test-user-id-001', 'meme-with-text.pdf', $uploadedFile);
        $meme = $storageRepo->getMeme($memeId);
        $storedMeme = new StoredMeme(
            id: $meme->id,
            normalized_name: $meme->normalized_name,
            original_filename: $meme->original_filename,
            state: $meme->state,
            size: $meme->size,
            user_id: $meme->user_id,
            created_at: $meme->created_at,
            deleted: $meme->deleted ? 1 : 0,
        );
        $textRepo = $this->injector->make(FakeMemeTextRepo::class);
        $textRepo->saveMemeText($storedMeme, 'Stored meme text');

        $this->injector->defineParam('meme_id', $memeId);
        $result = $this->injector->execute([User::class, 'getMemeText']);

        $this->assertInstanceOf(GetMemeTextResponse::class, $result);
        $data = json_decode($result->getBody(), true);
        $this->assertNotNull($data['data']['meme_text']);
        $this->assertSame('Stored meme text', $data['data']['meme_text']['text']);
    }

    /**
     * @covers \Bristolian\AppController\User::handleMemeTagAdd
     */
    public function test_handleMemeTagAdd(): void
    {
        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: '/api/meme_tag_add',
            method: 'POST',
            body: 'php://memory',
            headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
            parsedBody: [
                'meme_id' => 'test-meme-id',
                'type' => 'user_tag',
                'text' => 'funny',
            ]
        );
        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);

        $result = $this->injector->execute([User::class, 'handleMemeTagAdd']);
        $this->assertInstanceOf(PostMemetagaddResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::handleMemeTagAdd_get
     */
    public function test_handleMemeTagAdd_get(): void
    {
        $result = $this->injector->execute([User::class, 'handleMemeTagAdd_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::handleMemeTagUpdate_get
     */
    public function test_handleMemeTagUpdate_get(): void
    {
        $result = $this->injector->execute([User::class, 'handleMemeTagUpdate_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::handleMemeTagDelete_get
     */
    public function test_handleMemeTagDelete_get(): void
    {
        $result = $this->injector->execute([User::class, 'handleMemeTagDelete_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::getMemeTagSuggestions
     */
    public function test_getMemeTagSuggestions(): void
    {
        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: '/api/meme_tag_suggestions?limit=5',
            method: 'GET',
        );
        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);

        $result = $this->injector->execute([User::class, 'getMemeTagSuggestions']);
        $this->assertInstanceOf(GetMemeTagSuggestionsResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::getMemeTagSuggestions
     */
    public function test_getMemeTagSuggestions_with_meme_ids_calls_getMostCommonTagsForMemes(): void
    {
        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: '/api/meme_tag_suggestions',
            method: 'GET',
        );
        $request = $request->withQueryParams(['meme_ids' => 'id-one,id-two', 'limit' => '5']);
        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);

        $result = $this->injector->execute([User::class, 'getMemeTagSuggestions']);

        $this->assertInstanceOf(GetMemeTagSuggestionsResponse::class, $result);
        $data = json_decode($result->getBody(), true);
        $this->assertIsArray($data['data']['tags']);
    }

    /**
     * @covers \Bristolian\AppController\User::get_login_status
     */
    public function test_get_login_status_not_logged_in(): void
    {
        $sessionManager = new FakeAppSessionManager();
        $this->injector->alias(AppSessionManagerInterface::class, FakeAppSessionManager::class);
        $this->injector->share($sessionManager);

        $result = $this->injector->execute([User::class, 'get_login_status']);

        $this->assertInstanceOf(\SlimDispatcher\Response\JsonNoCacheResponse::class, $result);
        $body = json_decode($result->getBody(), true);
        $this->assertFalse($body['logged_in']);
    }

    /**
     * @covers \Bristolian\AppController\User::get_login_status
     */
    public function test_get_login_status_logged_in(): void
    {
        $sessionManager = FakeAppSessionManager::createLoggedIn();
        $this->injector->alias(AppSessionManagerInterface::class, FakeAppSessionManager::class);
        $this->injector->share($sessionManager);

        $result = $this->injector->execute([User::class, 'get_login_status']);

        $this->assertInstanceOf(\SlimDispatcher\Response\JsonNoCacheResponse::class, $result);
        $body = json_decode($result->getBody(), true);
        $this->assertTrue($body['logged_in']);
    }

    /**
     * @covers \Bristolian\AppController\User::getMemeTagSuggestions_get
     */
    public function test_getMemeTagSuggestions_get(): void
    {
        $result = $this->injector->execute([User::class, 'getMemeTagSuggestions_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::updateMemeText
     */
    public function test_updateMemeText_not_found(): void
    {
        $this->injector->defineParam('meme_id', 'nonexistent-meme-id');
        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: '/api/meme_text',
            method: 'PUT',
            body: 'php://memory',
            headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
            parsedBody: ['text' => 'Updated meme text']
        );
        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);

        $result = $this->injector->execute([User::class, 'updateMemeText']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::updateMemeText
     */
    public function test_updateMemeText_success(): void
    {
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $uploadedFile = \Bristolian\UploadedFiles\UploadedFile::fromFile($pdfPath);
        $storageRepo = $this->injector->make(FakeMemeStorageRepo::class);
        $memeId = $storageRepo->storeMeme('test-user-id-001', 'meme-to-update.pdf', $uploadedFile);

        $this->injector->defineParam('meme_id', $memeId);
        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: '/api/meme_text',
            method: 'PUT',
            body: 'php://memory',
            headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
            parsedBody: ['text' => 'New meme text']
        );
        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);

        $result = $this->injector->execute([User::class, 'updateMemeText']);

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $textRepo = $this->injector->make(FakeMemeTextRepo::class);
        $memeText = $textRepo->getMemeText($memeId);
        $this->assertNotNull($memeText);
        $this->assertSame('New meme text', $memeText->text);
    }

    /**
     * @covers \Bristolian\AppController\User::updateMemeText
     */
    public function test_updateMemeText_truncates_long_text(): void
    {
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $uploadedFile = \Bristolian\UploadedFiles\UploadedFile::fromFile($pdfPath);
        $storageRepo = $this->injector->make(FakeMemeStorageRepo::class);
        $memeId = $storageRepo->storeMeme('test-user-id-001', 'meme-long-text.pdf', $uploadedFile);

        $longText = str_repeat('x', 5000);
        $this->injector->defineParam('meme_id', $memeId);
        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: '/api/meme_text',
            method: 'PUT',
            body: 'php://memory',
            headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
            parsedBody: ['text' => $longText]
        );
        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);

        $this->injector->execute([User::class, 'updateMemeText']);

        $textRepo = $this->injector->make(FakeMemeTextRepo::class);
        $memeText = $textRepo->getMemeText($memeId);
        $this->assertNotNull($memeText);
        $this->assertSame(4096, strlen($memeText->text));
        $this->assertSame(str_repeat('x', 4096), $memeText->text);
    }

    /**
     * @covers \Bristolian\AppController\User::updateMemeText
     */
    public function test_updateMemeText_uses_array_access_parsed_body(): void
    {
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $uploadedFile = \Bristolian\UploadedFiles\UploadedFile::fromFile($pdfPath);
        $storageRepo = $this->injector->make(FakeMemeStorageRepo::class);
        $memeId = $storageRepo->storeMeme('test-user-id-001', 'meme-array-access.pdf', $uploadedFile);

        $body = new \ArrayObject(['text' => 'Text from ArrayAccess']);
        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: '/api/meme_text',
            method: 'PUT',
            body: 'php://memory',
            headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
            parsedBody: $body
        );
        $this->injector->defineParam('meme_id', $memeId);
        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);

        $this->injector->execute([User::class, 'updateMemeText']);

        $textRepo = $this->injector->make(FakeMemeTextRepo::class);
        $memeText = $textRepo->getMemeText($memeId);
        $this->assertNotNull($memeText);
        $this->assertSame('Text from ArrayAccess', $memeText->text);
    }

    /**
     * @covers \Bristolian\AppController\User::updateMemeText
     */
    public function test_updateMemeText_uses_post_fallback_when_parsed_body_empty(): void
    {
        $pdfPath = __DIR__ . '/../../sample.pdf';
        $this->assertFileExists($pdfPath);
        $uploadedFile = \Bristolian\UploadedFiles\UploadedFile::fromFile($pdfPath);
        $storageRepo = $this->injector->make(FakeMemeStorageRepo::class);
        $memeId = $storageRepo->storeMeme('test-user-id-001', 'meme-post-fallback.pdf', $uploadedFile);

        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: '/api/meme_text',
            method: 'PUT',
            body: 'php://memory',
            headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
            parsedBody: null
        );
        $this->injector->defineParam('meme_id', $memeId);
        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);

        $_POST['text'] = 'Text from POST';

        try {
            $this->injector->execute([User::class, 'updateMemeText']);
            $textRepo = $this->injector->make(FakeMemeTextRepo::class);
            $memeText = $textRepo->getMemeText($memeId);
            $this->assertNotNull($memeText);
            $this->assertSame('Text from POST', $memeText->text);
        } finally {
            unset($_POST['text']);
        }
    }

    /**
     * @covers \Bristolian\AppController\User::handleMemeTagDelete
     */
    public function test_handleMemeTagDelete(): void
    {
        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: '/api/meme_tag_delete',
            method: 'POST',
            body: 'php://memory',
            headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
            parsedBody: [
                'meme_id' => 'test-meme-id',
                'meme_tag_id' => 'nonexistent-tag-id',
            ]
        );
        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);

        $result = $this->injector->execute([User::class, 'handleMemeTagDelete']);
        $this->assertInstanceOf(PostMemetagdeleteResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\User::handleMemeTagUpdate
     */
    public function test_handleMemeTagUpdate(): void
    {
        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: '/api/meme_tag_update',
            method: 'POST',
            body: 'php://memory',
            headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
            parsedBody: [
                'meme_tag_id' => 'test-tag-id',
                'type' => 'user_tag',
                'text' => 'updated-tag',
            ]
        );
        $this->injector->alias(Request::class, ServerRequest::class);
        $this->injector->share($request);

        $result = $this->injector->execute([User::class, 'handleMemeTagUpdate']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }
}
