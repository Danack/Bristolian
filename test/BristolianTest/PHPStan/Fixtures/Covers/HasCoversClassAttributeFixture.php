<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan\Fixtures\Covers;

use Bristolian\Cache\RedisLogUnknownQuery;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedisLogUnknownQuery::class)]
class HasCoversClassAttributeFixture
{
}
