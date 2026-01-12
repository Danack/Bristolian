<?php

namespace BristolianTest\Model\TinnedFish;

use Bristolian\Model\TinnedFish\Copyright;
use BristolianTest\BaseTestCase;

/**
 * Tests for Copyright model
 *
 * @covers \Bristolian\Model\TinnedFish\Copyright
 */
class CopyrightTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\TinnedFish\Copyright::openFoodFacts
     */
    public function test_openFoodFacts_creates_correct_attribution(): void
    {
        $copyright = Copyright::openFoodFacts();

        $this->assertSame('Data subject to copyright', $copyright->notice);
        $this->assertSame('OpenFoodFacts contributors', $copyright->owner);
        $this->assertSame('OpenFoodFacts', $copyright->source);
        $this->assertSame('ODbL 1.0', $copyright->license);
        $this->assertTrue($copyright->attribution_required);
    }

    /**
     * @covers \Bristolian\Model\TinnedFish\Copyright::__construct
     */
    public function test_constructor_sets_all_fields(): void
    {
        $copyright = new Copyright(
            notice: 'Custom notice',
            owner: 'Custom owner',
            source: 'Custom source',
            license: 'MIT',
            attribution_required: false
        );

        $this->assertSame('Custom notice', $copyright->notice);
        $this->assertSame('Custom owner', $copyright->owner);
        $this->assertSame('Custom source', $copyright->source);
        $this->assertSame('MIT', $copyright->license);
        $this->assertFalse($copyright->attribution_required);
    }

    /**
     * @covers \Bristolian\Model\TinnedFish\Copyright::__construct
     */
    public function test_constructor_allows_null_license(): void
    {
        $copyright = new Copyright(
            notice: 'Notice',
            owner: 'Owner',
            source: 'Source',
            license: null,
            attribution_required: true
        );

        $this->assertNull($copyright->license);
    }
}
