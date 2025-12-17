<?php
// Simple script to emulate browser register: GET /register to get CSRF, then POST form
$base = 'http://localhost:8001';

// GET /register
$opts = ['http' => ['method' => 'GET', 'header' => "Accept: text/html\r\n", 'ignore_errors' => true]];
$html = file_get_contents($base . '/register', false, stream_context_create($opts));
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
    'name' => 'BrowserUser',
    'email' => 'browseruser1@example.com',
    'password' => 'secret123',
]);

$cookieHeader = implode('; ', $cookies);

$opts = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\nCookie: $cookieHeader\r\n", 'content' => $postData, 'ignore_errors' => true]];
$resp = file_get_contents($base . '/api/register', false, stream_context_create($opts));
$meta = $http_response_header ?? [];

echo "--- REQUEST COOKIES:\n" . implode("\n", $cookies) . "\n\n";
echo "--- RESPONSE HEADERS:\n" . implode("\n", $meta) . "\n\n";
echo "--- BODY:\n" . ($resp ?? '') . "\n";
