<?php

namespace BristolianChat;

use Amp\Http\HttpStatus;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;

/**
 * This is just for debugging
 */
class FallbackHandler implements RequestHandler
{
    public function handleRequest(Request $request): Response
    {
        $response = new Response(
            HttpStatus::OK,
            [],
            "This is a default response. Maybe try a specific end-point..."
        );

        return $response;
    }
}
