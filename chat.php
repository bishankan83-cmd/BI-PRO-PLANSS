<?php
// PHP WebSocket Server

error_reporting(E_ALL);
ini_set('display_errors', 1);
$server = new swoole_websocket_server("0.0.0.0", 9501);

$server->on('open', function (swoole_websocket_server $server, $request) {
    echo "New connection: {$request->fd}\n";
});

$server->on('message', function (swoole_websocket_server $server, $frame) {
    echo "Received message: {$frame->data}\n";
    // Broadcast received message to all connected clients
    foreach ($server->connections as $fd) {
        $server->push($fd, $frame->data);
    }
});

$server->on('close', function ($ser, $fd) {
    echo "Connection {$fd} closed\n";
});

$server->start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
</head>
<body>
    <ul id="messages"></ul>
    <input id="input" autocomplete="off" /><button onclick="sendMessage()">Send</button>

    <script>
        var websocket = new WebSocket("ws://localhost:9501");

        websocket.onopen = function(event) {
            console.log("Connection opened");
        };

        websocket.onmessage = function(event) {
            var node = document.createElement("LI");
            var textnode = document.createTextNode(event.data);
            node.appendChild(textnode);
            document.getElementById("messages").appendChild(node);
        };

        websocket.onclose = function(event) {
            console.log("Connection closed");
        };

        function sendMessage() {
            var message = document.getElementById("input").value;
            websocket.send(message);
            document.getElementById("input").value = '';
        }
    </script>
</body>
</html>
