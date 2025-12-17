<?php
$data = json_encode(['name' => 'GatewayTest', 'email' => 'gatewaytest1@example.com', 'password' => 'secret123']);
$options = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => $data]];
$result = file_get_contents('http://localhost:8002/api/auth/register', false, stream_context_create($options));
var_export($result);
