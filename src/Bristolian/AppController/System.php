<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\CSPViolation\CSPViolationStorage;

use function Bristolian\createReactWidget;

class System
{
    public function index()
    {
        $html = <<< HTML

<h2>System page</h2>

<p>Hello there</p>

<ul>
  <li><a href="/system/csp_reports">CSP reports</a></li>
</ul>
HTML;





        return $html;
    }

    public function show_csp_reports(CSPViolationStorage $cspViolationStorage)
    {

        $count = $cspViolationStorage->getCount();

        $reports = $cspViolationStorage->getReportsByPage(0);

        $widget = createReactWidget('csp_report_table', $reports);

        $html = <<< HTML

<h3>CSP reports page</h3>
<p>
  There are currently $count reports.
</p>
HTML;

        $html .= $widget;



        return $html;
    }
}
