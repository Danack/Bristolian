<?php

declare(strict_types=1);

namespace BristolianTest\Functions;

use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversFunction;

#[CoversFunction('createDevOnlyObjectFilesystem')]

class CreateDevOnlyObjectFilesystemTest extends BaseTestCase
{
    public function test_refuses_non_dev_bucket_names(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Refusing to create filesystem for non-dev bucket: bristolian-memes');

        createDevOnlyObjectFilesystem('bristolian-memes');
    }
}
