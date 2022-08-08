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
<p>That sounds important doesn't it?</p>
<p>Well, it's not really.</p>

<ul>
  <li><a href="/system/csp/reports">CSP reports</a></li>
</ul>
HTML;

        return $html;
    }

    public function show_csp_reports(CSPViolationStorage $cspViolationStorage)
    {
        $count = $cspViolationStorage->getCount();
        $reports = $cspViolationStorage->getReportsByPage(0);

        $data = [
            'count' => $count,
            'reports' => $reports
        ];

        $widget = createReactWidget('csp_report_table', $data);
        $html = <<< HTML
<h3>CSP reports page</h3>
<p>This site has quite restricted 'content security policies' to prevent security issues. This page shows the list of CSP violations. It is mostly for debugging problems that theoretically shouldn't exist.
</p>
HTML;

        $html .= $widget;

        return $html;
    }
}
