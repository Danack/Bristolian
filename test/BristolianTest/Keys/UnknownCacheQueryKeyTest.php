<?php

declare(strict_types=1);

namespace BristolianTest\Keys;

use Bristolian\Keys\UnknownCacheQueryKey;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bristolian\Keys\UnknownCacheQueryKey
 */
class UnknownCacheQueryKeyTest extends TestCase
{
    public function testSetKeyConstant(): void
    {
        $this->assertSame('UnknownCacheQueryKey_set', UnknownCacheQueryKey::SET_KEY);
    }

    public function testGetAbsoluteKeyNameIsDeterministic(): void
    {
        $query = 'SELECT * FROM users WHERE id = :id';
        $first = UnknownCacheQueryKey::getAbsoluteKeyName($query);
        $second = UnknownCacheQueryKey::getAbsoluteKeyName($query);

        $this->assertSame($first, $second);
    }

    public function testGetAbsoluteKeyNameDiffersForDifferentInputs(): void
    {
        $keyA = UnknownCacheQueryKey::getAbsoluteKeyName('SELECT * FROM users');
        $keyB = UnknownCacheQueryKey::getAbsoluteKeyName('SELECT * FROM rooms');

        $this->assertNotSame($keyA, $keyB);
    }

    public function testGetAbsoluteKeyNameContainsClassName(): void
    {
        $key = UnknownCacheQueryKey::getAbsoluteKeyName('some query');

        $this->assertStringContainsString(
            'Bristolian\Keys\UnknownCacheQueryKey',
            $key
        );
    }

    public function testGetAbsoluteKeyNameContainsSha256Hash(): void
    {
        $query = 'SELECT 1';
        $expectedHash = hash('sha256', $query);
        $key = UnknownCacheQueryKey::getAbsoluteKeyName($query);

        $this->assertStringEndsWith('_' . $expectedHash, $key);
    }

    public function testGetAbsoluteKeyNameEmptyString(): void
    {
        $key = UnknownCacheQueryKey::getAbsoluteKeyName('');
        $expectedHash = hash('sha256', '');

        $this->assertSame(
            'Bristolian\Keys\UnknownCacheQueryKey_' . $expectedHash,
            $key
        );
    }
}
