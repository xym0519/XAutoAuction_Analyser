<?php

use App\Config\Config;
use Tourmaline\DBLog\DBLogServiceProvider;
use Tourmaline\Infrastructure\Config\ConfigLib;
use Tourmaline\Infrastructure\InfrastructureServiceProvider;
use Tourmaline\RequestLog\RequestLogServiceProvider;

if (!isset($app)) {
    return;
}
ConfigLib::addConfigurationBinding(Config::AppID, Config::class);

$app->withFacades();
$app->withEloquent();
$app->register(InfrastructureServiceProvider::class);
$app->register(DBLogServiceProvider::class);
$app->register(RequestLogServiceProvider::class);

$app->middleware([
//    ExampleMiddleWare::class
]);

$app->routeMiddleware([
//    'admin' => ExampleMiddleWare::class
]);


require 'Http/Example/ExampleRoute.php';
