<?php

declare(strict_types=1);

namespace BristolianTest\Cache;

use Bristolian\Cache\UnknownQueryException;
use Bristolian\Exception\BristolianException;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UnknownQueryException::class)]
class UnknownQueryExceptionTest extends BaseTestCase
{
    public function test_create_throws_with_query_in_message(): void
    {
        try {
            UnknownQueryException::create('SELECT * FROM users');
            $this->fail('Expected UnknownQueryException');
        }
        catch (UnknownQueryException $exception) {
            $this->assertInstanceOf(BristolianException::class, $exception);
            $this->assertStringContainsString('SELECT * FROM users', $exception->getMessage());
            $this->assertStringContainsString('QueryTagMapping.php', $exception->getMessage());
            $this->assertStringContainsString('Unknown query not in cache tag mapping', $exception->getMessage());
        }
    }

    public function test_create_truncates_long_query_in_message(): void
    {
        $longQuery = str_repeat('Z', 500);

        try {
            UnknownQueryException::create($longQuery);
            $this->fail('Expected UnknownQueryException');
        }
        catch (UnknownQueryException $exception) {
            $this->assertStringContainsString(str_repeat('Z', 200), $exception->getMessage());
            $this->assertStringNotContainsString(str_repeat('Z', 201), $exception->getMessage());
        }
    }
}
