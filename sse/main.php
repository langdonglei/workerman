<?php

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\ServerSentEvents;
use Workerman\Timer;
use Workerman\Worker;

require_once __DIR__ . '/../vendor/autoload.php';

$worker = new Worker('http://0.0.0.0:8060');

$worker->onMessage = function (TcpConnection $connection, Request $request) {
    switch ($request->path()) {
        case '/':
            $connection->send("<script>let es = new EventSource('/sse'); es.addEventListener('message',function(e) { document.body.innerHTML += '<text>' + e.data + '</text><br>'; document.body.scrollTo(0,document.body.scrollHeight); });</script>");
            break;
        case '/sse':
            $connection->send(new Response(200, ['Content-Type' => 'text/event-stream']));
            $timer_id = Timer::add(2, function () use (&$timer_id, $connection) {
                if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                    Timer::del($timer_id);
                } else {
                    $connection->send(new ServerSentEvents([
                        'event' => 'message',
                        'data'  => 'ddd'
                    ]));
                }
            });
            break;
        default:
            $connection->close('没有此路由');
    }
};

Worker::runAll();





