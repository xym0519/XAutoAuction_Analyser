<?php
if (!isset($app)) {
    return;
}
$app->router->get('example', ['uses' => 'App\Http\Example\API\V1\ExampleAPI@example']);
