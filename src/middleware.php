<?php
/**
 * Add jwt auth to each request
 */
$app->add(new Tuupola\Middleware\JwtAuthentication([
    'ignore' => ['/jwtToken', '/healthz'],
    'secure' => getenv('MIDDLEWARE_SECURE'),
    'attribute' => 'jwt',
    'secret' => getenv('JWT_SECRET'),
]));
