<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\SiteHtml\PageStubResponseGenerator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Bristolian\MarkdownRenderer\MarkdownRenderer;

class Pages
{
    public function index()
    {
        $content = "<h1>Floating point shenanigans</h1>";

        $content .= "<div class='floating_point_panel'></div>";

        return $content;
    }

    public function floating_point_page()
    {
        $content = "<h1>Floating point shenanigans</h1>";

        $content .= "<div class='floating_point_panel'></div>";

        return $content;
    }

    public function timeline_page()
    {
        $content = "<h1>Time line page goes here</h1>";

        $content .= "<div class='time_line_panel'></div>";

        return $content;
    }

    public function teleprompter_page()
    {
        $content = "<h1>Teleprompter</h1>";

        $content .= "<div class='teleprompter_panel'></div>";

        return $content;
    }



    public function tools_page()
    {
        $content = "<h1>Tools page</h1>";
        $content .= <<< HTML
<ul>
  <li><a href="/tools/floating_point">Floating point</a></li>          
  <li><a href="/tools/twitter_splitter">Twitter splitter</a></li>          
  <li><a href="/tools/teleprompter">Teleprompter</a></li>
  <li><a href="/tools/timeline">Timeline</a></li>          
  <li><a href="/tools/notes">Notes</a></li>          
</ul>

HTML;

        return $content;
    }


    public function notes_page()
    {
        $content = "<h1>Note page goes here</h1>";

        $content .= "<div class='notes_panel'></div>";

        return $content;
    }

    public function twitter_splitter_page()
    {
        $content = "<h1>Twitter splitter</h1>";

        $content .= "<p>Write some text in the box, it will be split into tweets on the right.";
        $content .= "<div class='twitter_splitter_panel'></div>";
        $content .= "<p>Emojis might not be handled correctly. Or links.";

        return $content;
    }

    public function homepage()
    {
        return "Hello there";
    }

    public function get404Page(
        Request $request,
        PageStubResponseGenerator $pageStubResponseGenerator
    ) {
        $path = $request->getUri()->getPath();

        return $pageStubResponseGenerator->create404Page($path);
    }

    public function about(MarkdownRenderer $markdownRenderer)
    {
        $fullPath = __DIR__ . "/../../../docs/site/about_page.md";
        return $markdownRenderer->renderFile($fullPath);
    }
}
