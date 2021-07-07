<?php

use Workerman\Worker;
use PHPSocketIO\SocketIO;

require_once '../../vendor/autoload.php';


$worker = new SocketIO(8080);

$worker->on('connection', function ($connection) {
    $connection->addedUser = false;

    // when the client emits 'new message', this listens and executes
    $connection->on('new message', function ($data) use ($connection) {
        // we tell the client to execute 'new message'
        $connection->broadcast->emit('new message', [
            'username' => $connection->username,
            'message'  => $data
        ]);
    });

    // when the client emits 'add user', this listens and executes
    $connection->on('add user', function ($username) use ($connection) {
        global $usernames, $numUsers;
        // we store the username in the socket session for this client
        $connection->username = $username;
        // add the client's username to the global list
        $usernames[$username] = $username;
        ++$numUsers;
        $connection->addedUser = true;
        $connection->emit('login', array(
            'numUsers' => $numUsers
        ));
        // echo globally (all clients) that a person has connected
        $connection->broadcast->emit('user joined', array(
            'username' => $connection->username,
            'numUsers' => $numUsers
        ));
    });

    // when the client emits 'typing', we broadcast it to others
    $connection->on('typing', function () use ($connection) {
        $connection->broadcast->emit('typing', array(
            'username' => $connection->username
        ));
    });

    // when the client emits 'stop typing', we broadcast it to others
    $connection->on('stop typing', function () use ($connection) {
        $connection->broadcast->emit('stop typing', array(
            'username' => $connection->username
        ));
    });

    // when the user disconnects.. perform this
    $connection->on('disconnect', function () use ($connection) {
        global $usernames, $numUsers;
        // remove the username from global usernames list
        if ($connection->addedUser) {
            unset($usernames[$connection->username]);
            --$numUsers;

            // echo globally that this client has left
            $connection->broadcast->emit('user left', array(
                'username' => $connection->username,
                'numUsers' => $numUsers
            ));
        }
    });

});


Worker::runAll();

