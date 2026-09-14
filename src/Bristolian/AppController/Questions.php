<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\MarkdownRenderer\MarkdownRenderer;

class Questions
{
    public function index(): string
    {
        $content = "<h1>Questions for WECA</h1>";

        $content .= <<< HTML
<p>
    <a href="/questions/1_weca_active_travel">WECA active travel</a>
</p>

<p>
    <a href="/questions/2_weca_cumberland_basin_tram">Cumberland Basin trams</a>
</p>

HTML;


        return $content;
    }

    public function weca_question_active_travel(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../notes/questions/1_active_travel_weca.md";

        return $markdownRenderer->renderFile($fullPath);
    }

    public function weca_question_tram(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../notes/questions/2_cumberland_basin_weca_road_feasilbity.md";

        return $markdownRenderer->renderFile($fullPath);
    }
}
