<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\App;
use Bristolian\Config\Config;
use Bristolian\CSPViolation\CSPViolationStorage;
use Bristolian\Model\TinnedFish\Product;
use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Model\Types\MigrationThatHasBeenRun;
use Bristolian\Repo\DbInfo\DbInfo;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;
use Bristolian\Service\DeployLogRenderer\DeployLogRenderer;
use Bristolian\Session\UserSession;
use OpenApi\OpenApiGenerator;
use SlimDispatcher\Response\JsonResponse;
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
  <li><a href="/system/deploy_log">Deploy log</a></li>
  <li><a href="/system/route_explorer">Route explorer</a></li>
  <li><a href="/system/tinned_fish_products">Tinned fish products</a></li>
  <li><a href="/system/debugging">Debugging</a></li>
</ul>
HTML;

        $deploy_template = <<< HTML
    <p>Deployed at :html_deploy_time</p>
    <p>Version is :html_version</p>

    <p>Error log: :html_error_log</p>
HTML;

        $error_log = "Does not exist \o/";

        if (file_exists("/var/app/data/git_pull_error.log") === true) {
            $file_contents = file_get_contents("/var/app/data/git_pull_error.log");
            if ($file_contents === false) {
                $error_log = "Unable to read log file";
            }
            else {
                $error_log = $file_contents;
            }
        }


        $params = [
            ':html_deploy_time' => $config->getDeployTime(),
            ':html_version' => $config->getVersion(),
            ':html_error_log' => $error_log,
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
            'Queries',
            'Created at'
        ];

        $rowFns = [
            ':html_id' => fn(MigrationThatHasBeenRun $migration) => $migration->id,
            ':html_description' => fn(MigrationThatHasBeenRun $migration) => $migration->description,
            ':html_queries' => fn(MigrationThatHasBeenRun $migration) => $migration->json_encoded_queries,
            ':html_created_at' => fn(MigrationThatHasBeenRun $migration) => $migration->created_at->format(App::DATE_TIME_FORMAT)
        ];

        $table_info .= renderTableHtml(
            $headers,
            $dbInfo->getMigrations(),
            $rowFns
        );

        return $table_info;
    }

    public function debugging(UserSession $appSession,): string
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


    public function route_explorer(): string
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

    public function tinned_fish_products(
        TinnedFishProductRepo $productRepo,
        \Bristolian\SiteHtml\ExtraAssets $extraAssets
    ): string {
        $products = $productRepo->getAll();
        
        // Convert products to array for React component
        $productsData = array_map(function (Product $product) {
            return [
                'barcode' => $product->barcode,
                'name' => $product->name,
                'brand' => $product->brand,
                'species' => $product->species,
                'weight' => $product->weight,
                'weight_drained' => $product->weight_drained,
                'product_code' => $product->product_code,
                'image_url' => $product->image_url,
                'validation_status' => $product->validation_status->value,
                'created_at' => $product->created_at?->format(App::DATE_TIME_FORMAT) ?? '',
            ];
        }, $products);

        $widget = createReactWidget('tinned_fish_products_admin', [
            'products' => $productsData,
            'validation_statuses' => [
                ['value' => ValidationStatus::NOT_VALIDATED->value, 'label' => ValidationStatus::NOT_VALIDATED->getDisplayName()],
                ['value' => ValidationStatus::VALIDATED_NOT_FISH->value, 'label' => ValidationStatus::VALIDATED_NOT_FISH->getDisplayName()],
                ['value' => ValidationStatus::VALIDATED_IS_FISH->value, 'label' => ValidationStatus::VALIDATED_IS_FISH->getDisplayName()],
            ],
        ]);

        $html = "<h2>Tinned Fish Products</h2>";
        $html .= "<p>Manage validation status for products. All products start as 'Not Validated' and can be marked as validated by admin users.</p>";
        $html .= $widget;

        return $html;
    }

    public function updateProductValidationStatus(
        string $barcode,
        \VarMap\VarMap $varMap,
        TinnedFishProductRepo $productRepo
    ): \SlimDispatcher\Response\JsonResponse {
        $validation_status = $varMap->getStringOrNull('validation_status');
        
        if ($validation_status === null) {
            return new \SlimDispatcher\Response\JsonResponse(
                ['success' => false, 'error' => 'validation_status parameter is required'],
                400
            );
        }

        try {
            $validationStatus = ValidationStatus::from($validation_status);
        } catch (\ValueError $e) {
            return new \SlimDispatcher\Response\JsonResponse(
                ['success' => false, 'error' => 'Invalid validation status'],
                400
            );
        }

        $productRepo->updateValidationStatus($barcode, $validationStatus);

        return new \SlimDispatcher\Response\JsonResponse([
            'success' => true,
            'barcode' => $barcode,
            'validation_status' => $validationStatus->value,
        ]);
    }
}
