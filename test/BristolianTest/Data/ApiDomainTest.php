<?php

declare(strict_types=1);

namespace BristolianTest\Data;

use BristolianTest\BaseTestCase;
use Bristolian\Data\ApiDomain;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class ApiDomainTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Data\ApiDomain
     */
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
