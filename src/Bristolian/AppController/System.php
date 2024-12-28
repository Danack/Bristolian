<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\App;
use Bristolian\Config\Config;
use Bristolian\CSPViolation\CSPViolationStorage;
use Bristolian\Repo\DbInfo\DbInfo;
use SlimDispatcher\Response\JsonResponse;
use function Bristolian\createReactWidget;
use Bristolian\DataType\Migration;
use Bristolian\Service\DeployLogRenderer\DeployLogRenderer;
use OpenApi\OpenApiGenerator;

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
  <li><a href="/system/deploy_log">Deploy log</a></li>
  <li><a href="/system/route_explorer">Route explorer</a></li>
  <li><a href="/system/debugging">Debugging</a></li>
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
        $content = "<h1>Database info</h1>";
        $content .= $this->showDbTables($dbInfo);
        $content .= $this->showMigrationInfo($dbInfo);

        return $content;
    }

    public function deploy_log(DeployLogRenderer $deployLogRenderer): string
    {
        $html = "<h1>Deploy log</h1>";
        $html .= $deployLogRenderer->render();
        return $html;
    }

    public function display_swagger(OpenApiGenerator $openApiGenerator): JsonResponse
    {
        return new JsonResponse(
            $openApiGenerator->getApiData()
        );
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


    public function showMigrationInfo(DbInfo $dbInfo): string
    {
        $table_info = "<h2>Migrations</h2>";

        $headers = [
            'ID',
            'Description',
            'Checksum',
            'Created at'
        ];

        $rowFns = [
            ':html_id' => fn(Migration $migration) => $migration->id,
            ':html_description' => fn(Migration $migration) => $migration->description,
            ':html_checksum' => fn(Migration $migration) => $migration->checksum,
            ':html_created_at' => fn(Migration $migration) => $migration->created_at->format(App::DATE_TIME_FORMAT)
        ];

        $table_info .= renderTableHtml(
            $headers,
            $dbInfo->getMigrations(),
            $rowFns
        );

        return $table_info;
    }

    public function debugging()
    {
        $output = null;
        $result_code = 0;
        $result = exec('whoami', $output, $result_code);

        $output = "Output is [". implode("\n", $output) . "]<br/><br/>";

        $result = @file_put_contents(__DIR__ . "/../../../data/cache/foo.txt", "Hello world");

        $output .= "file_put_result is " . var_export($result, true);

        return $output;
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


    public function route_explorer()
    {


        $app_routes = getAllAppRoutes();


        $html = "<h2>Routes</h2>";

        $headers = [
            'Path',
            'Method',
            'Controller'
        ];

        $rowFns = [
            ':html_path' => fn($route) => $route[0],
            ':html_method' => fn($route) => $route[1],
            ':html_controller' => fn($route) => $route[2],
        ];

        $html .= renderTableHtml(
            $headers,
            $app_routes,
            $rowFns
        );

        return $html;
    }
}
