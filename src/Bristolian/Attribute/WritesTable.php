<?php

declare(strict_types=1);

namespace Bristolian\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class WritesTable
{
    /**
     * @param class-string $tableHelperClass Fully-qualified Bristolian\Database\* helper class
     */
    public function __construct(
        public readonly string $tableHelperClass
    ) {
    }
}
