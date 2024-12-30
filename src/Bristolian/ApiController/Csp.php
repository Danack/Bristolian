<?php

declare(strict_types = 1);

namespace Bristolian\ApiController;

use Bristolian\CSPViolation\CSPViolationStorage;
use SlimDispatcher\Response\JsonResponse;
use VarMap\VarMap;

class Csp
{

    public function get_reports_for_page(
        VarMap $varMap,
        CSPViolationStorage $cspViolationStorage
    ): JsonResponse {

        $page = (int)$_REQUEST['page'];

        $count = $cspViolationStorage->getCount();
        $reports = $cspViolationStorage->getReportsByPage($page);

        $data = [
            'count' => $count,
            'reports' => [],//$reports
        ];

        foreach ($reports as $report) {
            [$error, $value] = convertToValue($report);
            $data['reports'][] = $value;
        }

        return new JsonResponse($data);
    }
}
