<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\MarkdownRenderer\MarkdownRenderer;
use Bristolian\Parameters\DebugParams;
use Bristolian\SiteHtml\ExtraAssets;
use Bristolian\SiteHtml\PageStubResponseGenerator;
use Psr\Http\Message\ServerRequestInterface as Request;
use SlimDispatcher\Response\StubResponse;

class Pages
{
    public function index(): string
    {
        $content = "<h1>Absolute alpha</h1>";

        $content .= "<p>There is really very little here...</p>";

        $content .= "<p>At some point, there will be more.</p>";

        $content .= "<p>Maybe have a look at the <a href='/bcc/committee_meetings'>BCC committee meetings</a> about committees.</p>";
        $content .= "<p>Or why you should object to the <a href='/complaints/triangle_road'>Triangle road change</a>.</p>";

        $content .= <<< HTML
<p>Oh, a tiny bit more; <a href='/questions'>questions</a>.</p>

<h3>Explanations / F.A.Q.s</h3>

<a href='/explanations/bristol_rovers'>Bristol Rovers</a><br/>
<a href='/explanations/avon_crescent'>Avon Crescent</a><br/>
<a href='/explanations/advice_for_speaking_at_council'>Advice for speaking at council</a><br/>

<a href='/explanations/shenanigans_planning'>Shenanigans at Development Committee B</a><br/>


<a href='/explanations/monitoring_officer_notes'>Monitoring Officer shenanigans</a><br/>

<a href='/explanations/development_committee_rules'>Development Committee Made Up Rules</a><br/>


<!--
<ul>
  <li><a href="/tags">Tags on the site</a></li>
  <li><a href="/foi_requests">Interesting FOI requests</foi></li>
</ul>
 -->

HTML;

        $content .= <<< HTML

<h3>Eldon House music</h3>
<p>
  <a href="https://www.youtube.com/watch?v=hCNsspqVXMk&ab_channel=Danack">Act 1</a><br/> 
  <a href="https://www.youtube.com/watch?v=3tpIn6oVkPE&ab_channel=Danack">Act 2</a> <br/>
  <a href="https://www.youtube.com/watch?v=fvNFsCnoSn0&ab_channel=Danack">Act 3</a><br/>
</p>
HTML;

        return $content;
    }

    public function homepage(): string
    {
        return "Hello there";
    }

    public function get404Page(
        Request $request,
        ExtraAssets $extraAssets,
        PageStubResponseGenerator $pageStubResponseGenerator
    ): StubResponse {
        $path = $request->getUri()->getPath();

        return $pageStubResponseGenerator->create404Page($extraAssets, $path);
    }

    public function about(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../notes/site/about_page.md";
        return $markdownRenderer->renderFile($fullPath);
    }

    public function experimental_debug_param(DebugParams $debugParam): string
    {
        return "Hello world: " . $debugParam->message;
    }

    public function experimental(): string
    {
        $content = "This is a page for experimenting with. Current experiment is notifications.";
        $data = [
            'public_key' => getVapidPublicKey()
        ];

        $widget_json = json_encode_safe($data);
        $widget_data = htmlspecialchars($widget_json);

        $content .= "<div class='notification_panel' data-widgety_json='$widget_data'></div>";


        $content .= "chrome://serviceworker-internals/";
        $content .= "<div></div><hr/>";
        $content .= "<div></div><hr/>";

        $content .= "Meme upload panel:";
        $content .= "<div class='meme_upload_panel' ></div>";

        $content .= "Notification test panel:";
        $content .= "<div class='notification_test_panel' ></div>";


        return $content;
    }
}
