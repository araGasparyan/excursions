<?php

require __DIR__ . '/../vendor/autoload.php';

$dotEnv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotEnv->load();

date_default_timezone_set('Europe/Moscow');

session_start();

$config = include(__DIR__ . '/../config/config.php');

try {
    $app = new \Slim\App($config);

    /**
     * Register middleware.
     */
    require __DIR__ . '/../src/middleware.php';

    /**
     * Needed prior to the routes being run.
     */
    require __DIR__ . '/../src/dependencies.php';

    /**
     * Routes
     */
    require __DIR__ . '/../src/initiators-routes.php';
    require __DIR__ . '/../src/appearances-routes.php';
    require __DIR__ . '/../src/guides-routes.php';
    require __DIR__ . '/../src/languages-routes.php';
    require __DIR__ . '/../src/excursions-routes.php';

    require __DIR__ . '/../src/health-routes.php';

    $app->run();
} catch (\Exception $e) {
    error_log($e->getMessage());

    trigger503Response();
}
