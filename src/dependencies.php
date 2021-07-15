<?php

$container = $app->getContainer();

/**
 * Error Logger
 */
$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger('my_logger');
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));

    return $logger;
};

/**
 * Database Connection
 *
 * @param \Slim\Container $c
 *
 * @return \PDO
 * @throws \Exception
 */
$container['database'] = function ($c) {
    try {
        $dsn = 'mysql:host=' . $c['db']['params']['host'] . ';dbname=' . $c['db']['params']['dbname'] . ';charset=utf8';
        $pdo = new \PDO($dsn, $c['db']['params']['username'], $c['db']['params']['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    } catch (\Exception $e) {
        error_log($e->getMessage());
        trigger503Response();
    }
};

if (!function_exists('formatException')) {
    /**
     * Format an exception interface to our log format
     *
     * @param \Exception $e
     *
     * @return string
     */
    function formatException(\Exception $e)
    {
        return $e->getMessage() . ' - ' . $e->getTraceAsString();
    }
}
