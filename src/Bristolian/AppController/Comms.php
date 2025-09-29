<?php

namespace Bristolian\AppController;

class Comms
{
    public function get_test_page()
    {

        $html = <<< HTML
<div>
    <h3>Comms test</h3>


    <div class="comms_panel">
    </div>
    after comms panel

</div>

HTML;

        return $html;
    }
}