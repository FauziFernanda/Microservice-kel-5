<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$users = App\Models\User::all()->map(function($u){ return ['id'=>$u->id,'email'=>$u->email,'name'=>$u->name]; })->toArray();
echo json_encode($users, JSON_PRETTY_PRINT);
