<?php

/**
 * Ckeck health of the API
 *
 * GET /healthz
 */
$app->get('/healthz', function ($request, $response, $args) {
    /** @var \Slim\Http\Response $response */
    $hello = "Hello Guide";

    return $response->withStatus(200);
});
