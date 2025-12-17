<?php
$base = 'http://localhost:8001';
$email = 'testlogin'.time().'@example.com';
$password = 'pass1234';

// Register via gateway API (JSON)
$register = json_encode(['name'=>'Test Login','email'=>$email,'password'=>$password]);
$opts = ['http'=>['method'=>'POST','header'=>"Content-Type: application/json\r\nAccept: application/json\r\n",'content'=>$register,'ignore_errors'=>true]];
$reg = file_get_contents($base.'/api/register', false, stream_context_create($opts));
echo "REGISTER RESPONSE:\n".$reg."\n\n";

// Login via gateway API (JSON)
$login = json_encode(['email'=>$email,'password'=>$password]);
$opts = ['http'=>['method'=>'POST','header'=>"Content-Type: application/json\r\nAccept: application/json\r\n",'content'=>$login,'ignore_errors'=>true]];
$log = file_get_contents($base.'/api/login', false, stream_context_create($opts));
echo "LOGIN RESPONSE:\n".$log."\n";
