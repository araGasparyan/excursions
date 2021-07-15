<?php

/**
 * Ckeck health of the API
 *
 * GET /healthz
 */
$app->get('/healthz', function ($request, $response, $args) {
    /** @var \Slim\Http\Response $response */

    return $response->withStatus(200);
});
