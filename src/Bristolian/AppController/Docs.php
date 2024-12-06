<?php


namespace Bristolian\AppController;

use Bristolian\AppSession;
use SlimDispatcher\Response\RedirectResponse;

class Docs
{
    public function index(): string
    {
        $content = "<h1>Docs</h1>";
        $content .= <<< HTML
<ul>
    <li><a href="/files">Files</a></li>
    <li><a href="/memes">Memes</a></li>
</ul>
HTML;

        return $content;
    }


    public function files(): string
    {
        $content = "<h1>Files</h1>";
        $content .= "This is the files page.";

        return $content;
    }




    public function memes(AppSession $appSession): string
    {
        $content = "<h1>Memes</h1>";

        if ($appSession->isLoggedIn() !== true) {
            return "You're not logged in, so currently you can't see any memes.";
        }


        $content .= "This is the memes page.";

        $content .= "<h3>Upload memes</h3>";
        $content .= "<div class='meme_upload_panel' ></div>";

        $content .= "<h3>Meme management</h3>";
        $content .= "<div class='meme_management_panel' ></div>";

        return $content;
    }
}
