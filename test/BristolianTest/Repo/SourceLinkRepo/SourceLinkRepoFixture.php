<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\SourceLinkRepo;

use Bristolian\Parameters\SourceLinkHighlightParam;
use Bristolian\Repo\SourceLinkRepo\SourceLinkRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for SourceLinkRepo implementations.
 */
abstract class SourceLinkRepoFixture extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * Get a test instance of the SourceLinkRepo implementation.
     *
     * @return SourceLinkRepo
     */
    abstract public function getTestInstance(): SourceLinkRepo;

    /**
     * Get a test user ID. Override in PDO tests to create actual user.
     */
    protected function getTestUserId(): string
    {
        return 'user-123';
    }

    public function test_addSourceLink_returns_string_id(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $title = 'Test Source Link Title';
        $highlights = [
            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap([
                'page' => 1,
                'left' => 100,
                'top' => 200,
                'right' => 300,
                'bottom' => 400,
            ])),
        ];

        $sourceLinkId = $repo->addSourceLink($user_id, $title, $highlights);

        $this->assertNotEmpty($sourceLinkId);
    }

    public function test_addSourceLink_returns_different_ids_for_different_calls(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $title = 'Test Source Link Title';
        $highlights = [
            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap([
                'page' => 1,
                'left' => 100,
                'top' => 200,
                'right' => 300,
                'bottom' => 400,
            ])),
        ];

        $sourceLinkId1 = $repo->addSourceLink($user_id, $title, $highlights);
        $sourceLinkId2 = $repo->addSourceLink($user_id, $title, $highlights);

        $this->assertNotSame($sourceLinkId1, $sourceLinkId2);
    }

    public function test_addSourceLink_accepts_empty_highlights_array(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $title = 'Test Source Link Title';
        $highlights = [];

        $sourceLinkId = $repo->addSourceLink($user_id, $title, $highlights);

        $this->assertNotEmpty($sourceLinkId);
    }

    public function test_addSourceLink_accepts_multiple_highlights(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $title = 'Test Source Link Title';
        $highlights = [
            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap([
                'page' => 1,
                'left' => 100,
                'top' => 200,
                'right' => 300,
                'bottom' => 400,
            ])),
            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap([
                'page' => 2,
                'left' => 150,
                'top' => 250,
                'right' => 350,
                'bottom' => 450,
            ])),
        ];

        $sourceLinkId = $repo->addSourceLink($user_id, $title, $highlights);

        $this->assertNotEmpty($sourceLinkId);
    }
}
