<?php
/**
 * Base Test Class
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

namespace Tests;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;

class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Database object
     *
     * @var \PDO
     */
    protected static $db;

    /**
     * Config object
     *
     * @var array
     */
    protected static $config;

    /**
     * Called before the test class first test
     */
    public static function setUpBeforeClass()
    {
        self::$config = require __DIR__ . '/../config/config.php';

        try {
            $dsn = 'mysql:host=' . self::$config['db']['params']['host'] . ';dbname=' . self::$config['db']['params']['dbname'] . ';charset=utf8';
            $pdo = new \PDO($dsn, self::$config['db']['params']['username'], self::$config['db']['params']['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            self::$db = $pdo;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * Called after the test class last test
     */
    public static function tearDownAfterClass()
    {
        self::$db = null;
    }

    /**
     * Use middleware when running application?
     *
     * @var bool
     */
    protected $withMiddleware = true;

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     *
     * @return \Slim\Http\Response
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function runApp($requestMethod, $requestUri, $requestData = null)
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri,
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Set up jwt auth header
        $jwt = encodeJWT($data = ['scope' => ['read', 'create', 'update', 'delete']]);
        $request = $request->withHeader('Authorization', 'Bearer ' . $jwt);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        // Set up a response object
        $response = new Response();

        // Instantiate the application
        $app = new App(self::$config);

        // Set up dependencies
        require __DIR__ . '/../src/dependencies.php';

        // Register middleware
        if ($this->withMiddleware) {
            require __DIR__ . '/../src/middleware.php';
        }

        // Register routes
        require __DIR__ . '/../src/initiators-routes.php';
        require __DIR__ . '/../src/appearances-routes.php';
        require __DIR__ . '/../src/guides-routes.php';
        require __DIR__ . '/../src/languages-routes.php';
        require __DIR__ . '/../src/excursions-routes.php';

        // Process the application
        $response = $app->process($request, $response);

        return $response;
    }
}
