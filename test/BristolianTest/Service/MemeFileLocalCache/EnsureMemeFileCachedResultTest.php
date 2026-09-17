<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeFileLocalCache;

use Bristolian\Service\MemeFileLocalCache\EnsureMemeFileCachedResult;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\MemeFileLocalCache\EnsureMemeFileCachedResult::class, '__construct')]
#[CoversMethod(\Bristolian\Service\MemeFileLocalCache\EnsureMemeFileCachedResult::class, 'failure')]
#[CoversMethod(\Bristolian\Service\MemeFileLocalCache\EnsureMemeFileCachedResult::class, 'success')]

class EnsureMemeFileCachedResultTest extends BaseTestCase
{
    public function test_success_returns_succeeded_true_and_null_debug_info(): void
    {
        $result = EnsureMemeFileCachedResult::success();
        $this->assertTrue($result->succeeded);
        $this->assertNull($result->failureDebugInfo);
    }

    public function test_failure_returns_succeeded_false_and_debug_info(): void
    {
        $debugInfo = 'file not found in bucket';
        $result = EnsureMemeFileCachedResult::failure($debugInfo);
        $this->assertFalse($result->succeeded);
        $this->assertSame($debugInfo, $result->failureDebugInfo);
    }
}
