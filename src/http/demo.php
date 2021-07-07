<?php

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Worker;

include __DIR__ . '/../bootstrap.php';

$worker = new Worker('http://0.0.0.0:8050');

$worker->onMessage = function (TcpConnection $connection, Request $request) {
    $path = $request->path();
    var_dump($path);
    ob_start();
    include __DIR__ . '/demo.html';
    $connection->send(ob_get_clean());
};

Worker::runAll();
