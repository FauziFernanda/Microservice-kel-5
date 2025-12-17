<?php
$data = json_encode(['name' => 'GatewayTestViaGateway', 'email' => 'gatewayvia1@example.com', 'password' => 'secret123']);
$options = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => $data, 'ignore_errors' => true]];
$result = file_get_contents('http://localhost:8001/api/register', false, stream_context_create($options));
$meta = $http_response_header ?? [];
echo "RESPONSE HEADERS:\n" . implode("\n", $meta) . "\n\nBODY:\n" . ($result ?? '');
