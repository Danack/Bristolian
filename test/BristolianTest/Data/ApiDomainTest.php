<?php

declare(strict_types=1);

namespace BristolianTest\Data;

use BristolianTest\BaseTestCase;
use Bristolian\Data\ApiDomain;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Data\ApiDomain::class)]

class ApiDomainTest extends BaseTestCase
{
    public function testBasic(): void
    {
        $domain = 'www.example.com';
        $apiDomain = new ApiDomain($domain);

        $this->assertSame(
            $domain,
            $apiDomain->getDomain()
        );
    }
}
