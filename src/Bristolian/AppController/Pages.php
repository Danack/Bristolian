<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\SiteHtml\PageStubResponseGenerator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Bristolian\MarkdownRenderer\MarkdownRenderer;
use Bristolian\Page;
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




    public function bcc_committee_meetings(): string
    {
        $content = "<h1>BCC committee meetings</h1>";

        $content .= <<< HTML

<p>
  <a href="https://youtu.be/NwrqyODcHYw">Committee Model Working Group - Friday, 31st March</a>
</p>
    
<p>
  <a href="https://youtu.be/4UGLT_npxrE">Committee Model Working Group 28th April - part 1</a>
</p>

<p>
  <a href="https://youtu.be/qMwYwj-aPoU">Committee Model Working Group 28th April - part 2</a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=frD95ozXtRs">Committee Model Working Group 26th May</a>
</p>


<p>
  <a href="https://www.youtube.com/watch?v=LjnQMKDVFA0&ab_channel=Danack">Committee Model Working Group - 28th July</a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=DhNLwdJ8PpM&ab_channel=Danack">Committee Model Working Group - Friday, 8th September</a>
</p>


<p>
  <a href="https://www.youtube.com/watch?v=o0-bkxgpbG4&ab_channel=Danack">Value and ethics committee 25th September 2023</a>
</p>



<p>
  <a href="https://www.youtube.com/watch?v=Pput1nMMbu8&ab_channel=Danack">Audit committee 25th September 2023</a>
</p>


<p>
  <a href="https://www.youtube.com/watch?v=NDffR3zzsvM&ab_channel=Danack">Growth and Regeneration Scrutiny Commission 28th September 2023</a>
</p>



<p>
  <a href="https://www.youtube.com/watch?v=sKnCNL6jgII&ab_channel=Danack">Bristol Full Council 10th of October</a>
</p>


<p>
  <a href="https://www.youtube.com/watch?v=bSrrOTtMM6A&feature=youtu.be&ab_channel=Danack">Committee Model Working Group - Friday, 27th October</a>
</p>



<p>
  <a href="https://www.youtube.com/watch?v=TfkLIxog2xM&ab_channel=Danack">Overview and Scrutiny Management Board - Thursday, 2nd November, 2023</a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=dQoFH4b2C7Y&ab_channel=Danack">Values and Ethics Sub-Committee, 3rd November, 2023</a>
</p>


<p>
  <a href="https://www.youtube.com/watch?v=0biS6hwEjO8&ab_channel=Danack">Audit Committee 20th November</a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=3JTSMgQUv9o&ab_channel=Danack">Committee Model Working Group - 24th November, 2023</a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=BpQyjhjTV3s&ab_channel=Danack">Growth and Regeneration Scrutiny Commission, 27th November, 2023</a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=dgooVwQ-gQg&ab_channel=Danack">Bristol Schools Forum, 28th November, 2023</a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=-ifujuYXCU8&ab_channel=Danack">Committee Model Working Group - 1st December 2023</a> continued from  24th November 2023
</p>

<p>
  <a href="https://www.youtube.com/watch?v=WWejBZQrJkc&ab_channel=Danack">People Scrutiny Commission - 6th December, 2023</a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=PDYTB4PcgVE&ab_channel=Danack">Budget scrutiny - Part 1, Resources Scrutiny Commission - Friday, 8th December, 2023</a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=OtpZnkam95c&ab_channel=Danack">Bristol Full Council 9th January 2024</a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=nIvNGxXwUAk&ab_channel=Danack">Bristol Full Council 9th January 2024 - police </a>
</p>

<p>
  <a href="https://www.youtube.com/watch?v=kWEfaCoaP3g&feature=youtu.be&ab_channel=Danack">Bristol City council - Overview and Scrutiny Management Board 18th January</a>
</p>

HTML;

        return $content;
    }


    public function floating_point_page(): string
    {
        $content = "<h1>Floating point shenanigans</h1>";

        $content .= "<div class='floating_point_panel'></div>";

        return $content;
    }



    public function timeline_page(): string
    {
        $content = "<h1>Time line page goes here</h1>";

        $content .= "<div class='time_line_panel'></div>";

        return $content;
    }

    public function teleprompter_page(): string
    {
        $content = "<h1>Teleprompter</h1>";
        $content .= "<p>This probably isn't working currently.</p>";
        $content .= "<div class='teleprompter_panel'></div>";

        return $content;
    }

    public function email_link_generator_page(): string
    {
        $content = "<h1>Email link generator</h1>";
        $content .= "Email links can be setup to include pre-filled subject, CC, BCC and body text. This is a tool that does the needful to generate appropriate HTML, for embedding in other pages.";

        $content .= "<div class='email_link_generator_panel'></div>";
        $content .= "<hr/><p></p><a href='https://mailtolink.me/'/>Or use this one</a>.</p>";

        return $content;
    }

    public function qr_code_generator_page(): string
    {
        $content = "<h1>QR code generator</h1>";
        $content .= "<div class='qr_code_generator_panel'></div>";
        $content .= "<p>Or just use <a href='https://smiley.codes/qrcode/'>smiley.codes/qrcode/</a></p>";

        return $content;
    }




    public function notes_page(): string
    {
        $content = "<h1>Note page goes here</h1>";

        $content .= "<div class='notes_panel'></div>";

        return $content;
    }

    public function twitter_splitter_page(): string
    {
        $content = "<h1>Twitter splitter</h1>";


        $content .= "<div class='twitter_splitter_panel'></div>";


        return $content;
    }

    public function homepage(): string
    {
        return "Hello there";
    }

    public function get404Page(
        Request $request,
        PageStubResponseGenerator $pageStubResponseGenerator
    ): StubResponse {
        $path = $request->getUri()->getPath();

        return $pageStubResponseGenerator->create404Page($path);
    }

    public function about(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../docs/site/about_page.md";
        return $markdownRenderer->renderFile($fullPath);
    }

    public function triangle_road(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../docs/complaints/triangle_road.md";

        Page::setQrShareMessage("Please share this with anyone you know who would be affected by this road reopening. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }

    public function bristol_rovers(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../docs/complaints/bristol_rovers.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }


    public function advice_for_speaking_at_council(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../docs/explanations/advice_for_speaking_at_council.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }

    public function avon_crescent(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../docs/complaints/avon_crescent_spike_island.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }


    public function shenanigans_planning(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../docs/explanations/shenanigans_at_dcb_committee.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }

    public function monitoring_officer_notes(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../docs/explanations/monitoring_officer_problem.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }


    public function development_committee_rules(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../docs/explanations/development_committee_rules.md";

        Page::setQrShareMessage("Feel free to share this page. Show this QR code to someone, and they can scan it with the camera in their device. Or just copy pasta the URL to your socials.");

        $html = $markdownRenderer->renderFile($fullPath);
        $html .= "<hr/>";
        $html .= share_this_page();

        return $html;
    }

    public function questions(): string
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
        $fullPath = __DIR__ . "/../../../docs/questions/1_active_travel_weca.md";

        return $markdownRenderer->renderFile($fullPath);
    }

    public function weca_question_tram(MarkdownRenderer $markdownRenderer): string
    {
        $fullPath = __DIR__ . "/../../../docs/questions/2_cumberland_basin_weca_road_feasilbity.md";

        return $markdownRenderer->renderFile($fullPath);
    }

    public function experimental(): string
    {
        $content = "This is a page for experimenting with. Current experiement is notifications.";
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

        $content .= "<p>wtf.</p>";
        return $content;
    }
}
