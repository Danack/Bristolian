<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\Session\OptionalUserSession;

class Tools
{
    public function index(
        OptionalUserSession $optionalUserSessionSession
    ): string {

        $username = "not logged in";
        if ($appSession = $optionalUserSessionSession->getAppSession()) {
            $username = $appSession->getUsername();
        }

        $content = "<h1>Tools page</h1>";
        $content .= <<< HTML

Well, hello there '$username' !

<ul>
  <li><a href="/tools/email_link_generator">Email link generator</a></li>
  <li><a href="/tools/bristol_stairs">Bristol Stairs</a></li>
  <li><a href="/tools/twitter_splitter">Twitter splitter</a></li>
  <li><a href="/tools/committee_seats">Committee seat allocation calculator</a></li>
  <li><a href="/tools/qr_code_generator">QR generator</a></li>
  <li><a href="/tools/floating_point">Floating point visualiser</a></li>
</ul>

HTML;

        return $content;
    }

    public function floating_point_page(): string
    {
        $content = "<h1>Floating point shenanigans</h1>";

        $content .= "<div class='floating_point_panel'></div>";

        return $content;
    }

    public function floating_point_page_8(): string
    {
        $content = "<h1>Floating point shenanigans</h1>";

        $content .= "<div class='floating_point_8_bit_panel'></div>";

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

    public function committee_seats_page(): string
    {
        return <<< HTML
<div class="committee_seats_app">
  <div class="committee_seats_panel"></div>
</div>
HTML;
    }
}
