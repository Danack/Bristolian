<?php

declare(strict_types=1);

namespace BristolianTest\Functions;

use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversFunction;

#[CoversFunction('createProductionOnlyObjectFilesystem')]

class CreateProductionOnlyObjectFilesystemTest extends BaseTestCase
{
    public function test_refuses_dev_bucket_names(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Refusing to create production filesystem for -dev bucket: bristolian-memes-dev'
        );

        createProductionOnlyObjectFilesystem('bristolian-memes-dev');
    }
}
