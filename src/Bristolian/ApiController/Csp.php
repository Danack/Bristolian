<?php

declare(strict_types = 1);

namespace Bristolian\ApiController;

use Bristolian\CSPViolation\CSPViolationStorage;
use Bristolian\Response\Typed\GetCspReportsResponse;
use VarMap\VarMap;

class Csp
{

    public function get_reports_for_page(
        VarMap $varMap,
        CSPViolationStorage $cspViolationStorage
    ): GetCspReportsResponse {

        $page = 0;

        if ($varMap->has('page')) {
            $page = (int)$varMap->get('page');
        }

        $count = $cspViolationStorage->getCount();
        $reports = $cspViolationStorage->getReportsByPage($page);

//
//        $data = [
//            'count' => $count,
//            'reports' => []
//        ];
//
//
//        foreach ($reports as $report) {
//            [$error, $value] = convertToValue($report);
//            $data['reports'][] = $value;
//        }

        return new GetCspReportsResponse($count, $reports);
    }
}
