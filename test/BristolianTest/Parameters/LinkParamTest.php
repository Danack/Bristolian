<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\LinkParam;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 * @group wip
 */
class LinkParamTest extends BaseTestCase
{

    public function provides_test_works()
    {
        $unique = date("Ymdhis").uniqid();

        $title = 'short text ' . $unique;
        $description = 'this is a description ' . $unique;
        $url = "http://www.example.com?unique=" . $unique;

        yield [$title, $url, $description];

        $title = "Open Council Network";
        $url = "https://opencouncil.network/";
        $description = "Open Council Network makes local government decision-making accessible to everyone.

    We track council meetings and decisions, turning them into clear, concise summaries and weekly emails. Our updates explain what's happening, why it matters, and how you can get involved — so your voice is heard where it counts.";

        yield [$title, $url, $description,];
    }

    /**
     * @covers \Bristolian\Parameters\LinkParam
     * @dataProvider provides_test_works
     */
    public function testWorks(string $title, string $url, string|null $description,)
    {
        $data = [
            'title' => $title,
            'description' => $description,
            'url' => $url,
        ];

        $linkParam = LinkParam::createFromVarMap(new ArrayVarMap($data));

        $this->assertSame($title, $linkParam->title);
        $this->assertSame($url, $linkParam->url);
        $this->assertSame($description, $linkParam->description);
    }
}
