<?php

namespace BristolianChat;

use Amp\Http\HttpStatus;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;

/**
 * This is just for debugging. The only path the the websocket server is listeing on
 * is /chat but if someone goes to the wrong path, give them an nice response rather
 * than just an error.
 * And maybe we handle assets here in future?
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
