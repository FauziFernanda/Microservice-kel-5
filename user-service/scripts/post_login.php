<?php
$data = json_encode(['email' => 'fauzifernanda1407@gmail.com', 'password' => 'secret123']);
$options = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\nAccept: application/json\r\n", 'content' => $data, 'ignore_errors' => true]];
$result = file_get_contents('http://localhost:8002/api/auth/login', false, stream_context_create($options));
var_export($result);
