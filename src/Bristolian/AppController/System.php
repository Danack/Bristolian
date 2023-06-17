<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\App;
use Bristolian\Config;
use Bristolian\CSPViolation\CSPViolationStorage;
use Bristolian\Repo\DbInfo\DbInfo;
use function Bristolian\createReactWidget;


class System
{
    public function index(Config $config): string
    {
        $html = <<< HTML

<h2>System page</h2>
<p>That sounds important doesn't it?</p>
<p>Well, it's not really.</p>

<ul>
  <li><a href="/system/csp/reports">CSP reports</a></li>
  <li><a href="/system/database_tables">Database tables</a></li>
</ul>
HTML;

        $deploy_template = <<< HTML
    <p>Deployed at :html_deploy_time</p>
    <p>Version is :html_version</p>
HTML;

        $params = [
            ':html_deploy_time' => $config->getDeployTime(),
            ':html_version' => $config->getVersion()
        ];
        $html .= esprintf($deploy_template, $params);
        return $html;
    }

    public function showDbInfo(DbInfo $dbInfo): string
    {
        $content = "";

        $content .= $this->showDbTables($dbInfo);
        $content .= $this->showMigrationInfo($dbInfo);

        return $content;
    }

    public function showDbTables(DbInfo $dbInfo): string
    {
        $table_info = <<< HTML
<h2>Tables in DB</h2>

<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Rows</th>
    </tr>
  </thead>
  <tbody>
HTML;

        $row_template = <<<HTML
<tr>
    <td>:html_name</td>
    <td>:html_rows</td>
</tr>
HTML;

        foreach ($dbInfo->getTableInfo() as $table) {
            $params = [
                ':html_name' => $table->name,
                ':html_rows' => $table->number_of_rows
            ];

            $table_info .= esprintf($row_template, $params);
        }

        $table_info .= <<< HTML
  </tbody>
</table>
HTML;



        return $table_info;
    }

    function showMigrationInfo(DbInfo $dbInfo): string
    {
        $table_info = <<< HTML
<h2>Migrations</h2>

<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Rows</th>
    </tr>
  </thead>
  <tbody>
HTML;

        $row_template = <<<HTML
        <tr>
            <td>:html_id</td>
            <td>:html_description</td>
            <td>:html_checksum</td>
            <td>:html_created_at</td>
        </tr>
HTML;


        foreach ($dbInfo->getMigrations() as $migration) {
            $params = [
                ':html_id' => $migration->id,
                ':html_description' => $migration->description,
                ':html_checksum' => $migration->checksum,
                ':html_created_at' => $migration->created_at->format(App::DATE_TIME_FORMAT)
            ];

            $table_info .= esprintf($row_template, $params);
        }

        $table_info .= <<< HTML
  </tbody>
</table>
HTML;

        return $table_info;
    }



    public function show_csp_reports(CSPViolationStorage $cspViolationStorage): string
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
