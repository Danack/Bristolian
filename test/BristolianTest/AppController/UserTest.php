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
