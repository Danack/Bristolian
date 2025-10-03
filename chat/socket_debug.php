<?php

// This is useful for debugging low level socket problems.

$host = 'local.chat.bristolian.org';
$port = 8015;

// Generate a random Sec-WebSocket-Key
$key = base64_encode(random_bytes(16));

// Build the WebSocket handshake request
$headers = "GET /chat HTTP/1.1\r\n" .
    "Host: $host:$port\r\n" .
    "Upgrade: websocket\r\n" .
    "Connection: Upgrade\r\n" .
    "Sec-WebSocket-Key: $key\r\n" .
    "Sec-WebSocket-Version: 13\r\n\r\n";

// Open a TCP connection to the WebSocket server
$fp = @fsockopen($host, $port, $errno, $errstr, 5);

if (!$fp) {
    echo "Unable to connect to $host:$port. Error $errno: $errstr\n";
    exit(1);
}

// Send the handshake request
fwrite($fp, $headers);

// Read the server response
$response = fread($fp, 1500);

if (strpos($response, '101 Switching Protocols') !== false) {
    echo "WebSocket server is reachable and handshake succeeded.\n";
} else {
    echo "Handshake failed. Server response:\n$response\n";
}

fclose($fp);

