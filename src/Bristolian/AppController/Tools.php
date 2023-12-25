<?php

namespace Bristolian\AppController;

use Asm\RequestSessionStorage;
use Bristolian\AppSession;
use Bristolian\UserSession;

class Tools
{
    public function index(
        UserSession $appSession
    ): string {

        $username = "not logged in";
        if ($appSession->isLoggedIn()) {
            $username = $appSession->getUsername();
        }

        $content = "<h1>Tools page</h1>";
        $content .= <<< HTML

Well, hello there '$username' !

<ul>
  <li><a href="/tools/email_link_generator">Email link generator</a></li>
  <li><a href="/tools/twitter_splitter">Twitter splitter</a></li>          
  <!-- <li><a href="/tools/teleprompter">Teleprompter</a></li> -->
  <!-- <li><a href="/tools/timeline">Timeline</a></li> -->          
  <!-- <li><a href="/tools/notes">Notes</a></li> -->
  <li><a href="/tools/qr_code_generator">QR generator</a></li>
  <li><a href="/tools/floating_point">Floating point visualiser</a></li>
</ul>

HTML;

        return $content;
    }
}
