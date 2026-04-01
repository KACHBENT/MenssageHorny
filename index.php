<?php

use Illuminate\Contracts\Http\Kernel;

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../MenssageHorny/vendor/autoload.php';

$app = require_once __DIR__ . '/../MenssageHorny/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);