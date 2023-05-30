<?php

declare(strict_types = 1);

namespace Bristolian\ApiController;

use SlimDispatcher\Response\JsonResponse;

class HealthCheck
{
    public function get(): JsonResponse
    {
        return new JsonResponse(['ok']);
    }
}
