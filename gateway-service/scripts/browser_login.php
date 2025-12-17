<?php
// Emulate browser login and access the news dashboard
$base = 'http://localhost:8001';
$email = 'testlogin1765976255@example.com';
$password = 'pass1234';

// GET /login
$opts = ['http' => ['method' => 'GET', 'header' => "Accept: text/html\r\n", 'ignore_errors' => true]];
$html = file_get_contents($base . '/login', false, stream_context_create($opts));
$headers = $http_response_header ?? [];

// extract cookies
$cookies = [];
foreach ($headers as $h) {
    if (stripos($h, 'Set-Cookie:') === 0) {
        $parts = explode(':', $h, 2);
        $cookie = trim($parts[1]);
        $cookies[] = preg_replace('/;.*$/', '', $cookie);
    }
}

// extract CSRF token from form
if (preg_match('/name="_token" value="([^"]+)"/i', $html, $m)) {
    $token = $m[1];
} else {
    echo "CSRF token not found\n";
    exit(1);
}

$postData = http_build_query([
    '_token' => $token,
    'email' => $email,
    'password' => $password,
]);

$cookieHeader = implode('; ', $cookies);

$opts = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\nCookie: $cookieHeader\r\n", 'content' => $postData, 'ignore_errors' => true]];
$resp = file_get_contents($base . '/api/login', false, stream_context_create($opts));
$meta = $http_response_header ?? [];

echo "--- LOGIN RESPONSE HEADERS:\n" . implode("\n", $meta) . "\n\n";
echo "--- LOGIN BODY:\n" . ($resp ?? '') . "\n\n";

// extract Set-Cookie headers from login response and merge into current cookies
foreach ($meta as $h) {
    if (stripos($h, 'Set-Cookie:') === 0) {
        $parts = explode(':', $h, 2);
        $cookie = trim($parts[1]);
        $cookies[] = preg_replace('/;.*$/', '', $cookie);
    }
}
$cookieHeader = implode('; ', $cookies);

// After login, request /news/dashboard with updated cookies
$opts = ['http' => ['method' => 'GET', 'header' => "Accept: text/html\r\nCookie: $cookieHeader\r\n", 'ignore_errors' => true]];
$dashboard = file_get_contents($base . '/news/dashboard', false, stream_context_create($opts));
$meta2 = $http_response_header ?? [];

echo "--- DASHBOARD RESPONSE HEADERS:\n" . implode("\n", $meta2) . "\n\n";
echo "--- DASHBOARD BODY:\n" . ($dashboard ?? '') . "\n";
