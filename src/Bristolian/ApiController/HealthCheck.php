<?php

declare(strict_types = 1);

namespace Bristolian\ApiController;

use SlimAuryn\Response\JsonResponse;

class HealthCheck
{
    public function get()
    {
        return new JsonResponse(['ok']);
    }
}
