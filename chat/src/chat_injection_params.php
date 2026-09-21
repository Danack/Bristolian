<?php

use Bristolian\InjectionParams;

function chatInjectionParams(): InjectionParams
{
    $shares = [
        \Amp\Mysql\MysqlConnection::class,
        \BristolianChat\ClientHandler\StandardClientHandler::class,
    ];

    $aliases = [
        \BristolianChat\RoomMessagesWatcher\RoomMessagesWatcher::class =>
            \BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher::class,

        \BristolianChat\ClientHandler\ClientHandler::class =>
            \BristolianChat\ClientHandler\StandardClientHandler::class,

        Bristolian\MarkdownRenderer\MarkdownRenderer::class =>
            \Bristolian\MarkdownRenderer\CommonMarkRenderer::class,

        \Amp\Websocket\Server\WebsocketGateway::class =>
            \Amp\Websocket\Server\WebsocketClientGateway::class,
    ];

    $delegates = [
        \Amp\Mysql\MysqlConnection::class => 'createMysqlClient',
    ];

    return new InjectionParams(
        $shares,
        $aliases,
        $delegates,
        [],
        [],
        []
    );
}
