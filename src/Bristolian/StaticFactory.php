<?php

namespace Bristolian;

use Psr\Http\Message\ServerRequestInterface;

interface StaticFactory
{
    public static function createFromRequest(ServerRequestInterface $request): static;
}
