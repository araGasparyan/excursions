<?php

/**
 * Fetch data for a specific Appearance
 *
 * GET /appearances/{id}
 */
$app->get('/appearances/{id:[0-9]+}', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    try {
        $appearance = new \LinesC\Model\Appearance($this->get('database'));

        // Get the Appearance with given id
        if ($appearance->find($args['id'])) {
            return $response->withJson($appearance->toArray(), 200);
        }

        return $response->withStatus(404);
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Fetch all Appearances
 *
 * GET /appearances
 */
$app->get('/appearances', function ($request, $response) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */
    /** @var \PDO $db */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    $page = filter_var($request->getParam('page', 1), FILTER_SANITIZE_NUMBER_INT);
    $perPage = filter_var($request->getParam('per_page', 25), FILTER_SANITIZE_NUMBER_INT);
    $order = filter_var($request->getParam('order', 'appearance_start_datetime'), FILTER_SANITIZE_STRING);
    $dir = filter_var($request->getParam('dir', 'ASC'), FILTER_SANITIZE_STRING);

    $createdDate = filter_var($request->getParam('createdDate'), FILTER_SANITIZE_STRING);
    $updatedDate = filter_var($request->getParam('updatedDate'), FILTER_SANITIZE_STRING);
    $secureId = filter_var($request->getParam('secureId'), FILTER_SANITIZE_STRING);
    $mode = filter_var($request->getParam('mode'), FILTER_SANITIZE_NUMBER_INT);
    $appearanceStartDatetime = filter_var($request->getParam('appearanceStartDatetime'), FILTER_SANITIZE_STRING);
    $appearanceEndDatetime = filter_var($request->getParam('appearanceEndDatetime'), FILTER_SANITIZE_STRING);
    $type = filter_var($request->getParam('type'), FILTER_SANITIZE_NUMBER_INT);
    $rank = filter_var($request->getParam('rank'), FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($request->getParam('status'), FILTER_SANITIZE_NUMBER_INT);

    $db = $this->get('database');

    // Prepare sql for fetching Appearance's data
    $sql = 'SELECT * FROM appearances';

    $bind = [];
    $clause = [];

    if (!empty($createdDate)) {
        $clause[] = 'appearance_created_date >= ?';
        $bind[] = $createdDate;
    }

    if (!empty($updatedDate)) {
        $clause[] = 'appearance_updated_date >= ?';
        $bind[] = $updatedDate;
    }

    if (!empty($secureId)) {
        $clause[] = 'secure_id = ?';
        $bind[] = $secureId;
    }

    if (!empty($mode)) {
        $clause[] = 'mode = ?';
        $bind[] = $mode;
    }

    if (!empty($appearanceStartDatetime)) {
        $clause[] = 'appearance_start_datetime = ?';
        $bind[] = $appearanceStartDatetime;
    }

    if (!empty($appearanceEndDatetime)) {
        $clause[] = 'appearance_end_datetime = ?';
        $bind[] = $appearanceEndDatetime;
    }

    if (!empty($type)) {
        $clause[] = 'type = ?';
        $bind[] = $type;
    }

    if (!empty($rank)) {
        $clause[] = 'rank = ?';
        $bind[] = $rank;
    }

    if (!empty($status)) {
        $clause[] = 'status = ?';
        $bind[] = $status;
    }

    if ($clause) {
        $sql .= ' WHERE ' . implode(' AND ', $clause);
    }

    $sql .= ' ORDER BY ' . $order . ' ' . $dir;

    try {
        $pager = new Pager($db, $page, $perPage);

        $pager->setSql($sql);
        $pager->setBind($bind);
        $pager->paginateData();

        $result['data'] = $pager->getPagedData();
        $result['meta'] = $pager->getPageMeta();

        return $response->withJson($result, 200);
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Create a new record for Appearance
 *
 * POST /appearances
 */
$app->post('/appearances', function ($request, $response) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('create', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    /**
     * Sanitize input
     */
    $mode = filter_var($request->getParam('mode'), FILTER_SANITIZE_NUMBER_INT);
    $appearanceStartDatetime = filter_var($request->getParam('appearanceStartDatetime'), FILTER_SANITIZE_STRING);
    $appearanceEndDatetime = filter_var($request->getParam('appearanceEndDatetime'), FILTER_SANITIZE_STRING);

    $validationMessage = [];

    if (empty($mode)) {
        if (!($mode === '0' | $mode === 0 | $mode === 0.0)) {
            $validationMessage[] = 'mode is a required field';
        }
    }

    if (empty($appearanceStartDatetime)) {
        if (!($appearanceStartDatetime === '0' | $appearanceStartDatetime === 0 | $appearanceStartDatetime === 0.0)) {
            $validationMessage[] = 'appearanceStartDatetime is a required field';
        }
    }

    if (empty($appearanceEndDatetime)) {
        if (!($appearanceEndDatetime === '0' | $appearanceEndDatetime === 0 | $appearanceEndDatetime === 0.0)) {
            $validationMessage[] = 'appearanceEndDatetime is a required field';
        }
    }

    $checkedParams = checkRequestForAppearance($request);

    if (array_merge($checkedParams['validationMessage'], $validationMessage)) {
        return $response->withJson(array_merge($checkedParams['validationMessage'], $validationMessage), 400);
    }

    // Create the model for the Appearance
    $appearance = new \LinesC\Model\Appearance($this->get('database'));

        $appearance->setSecureId(generateSecureId());
        $appearance->setMode((int)$checkedParams['mode']);
        $appearance->setReason((string)$checkedParams['reason']);
        $appearance->setAppearanceStartDatetime(new \DateTime($checkedParams['appearanceStartDatetime']));
        $appearance->setAppearanceEndDatetime(new \DateTime($checkedParams['appearanceEndDatetime']));
        $appearance->setType((int)$checkedParams['type']);
        $appearance->setRank((int)$checkedParams['rank']);
        $appearance->setStatus((int)$checkedParams['status']);
    
    try {
        // Get database object
        $db = $appearance->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();
        $appearanceId = $appearance->insert();
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    $responseCode = 500;
    if ($appearanceId) {
        $response = $response->withHeader('Location', '/appearances/' . $appearanceId);
        $responseCode = 201;
    }

    return $response->withStatus($responseCode);
});

/**
 * Update a specific Appearance
 *
 * PUT /appearances/{id}
 */
$app->put('/appearances/{id:[0-9]+}', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('update', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    /**
     * Sanitize input
     */
    $checkedParams = checkRequestForAppearance($request);

    if ($checkedParams['validationMessage']) {
        return $response->withJson($checkedParams['validationMessage'], 400);
    }

    try {
        $appearance = new \LinesC\Model\Appearance($this->get('database'));

        if (!$appearance->find($args['id'])) {
            return $response->withStatus(404);
        }
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    try {
        $mode = (int)$checkedParams['mode'];
        if (!empty($mode)) {
            $appearance->setMode($mode);
        }

        $reason = (string)$checkedParams['reason'];
        if (!empty($reason)) {
            $appearance->setReason($reason);
        }

        $appearanceStartDatetime = (string)$checkedParams['appearanceStartDatetime'];
        if (!empty($appearanceStartDatetime)) {
            $appearance->setAppearanceStartDatetime(new \DateTime($appearanceStartDatetime));
        }

        $appearanceEndDatetime = (string)$checkedParams['appearanceEndDatetime'];
        if (!empty($appearanceEndDatetime)) {
            $appearance->setAppearanceEndDatetime(new \DateTime($appearanceEndDatetime));
        }

        $type = (int)$checkedParams['type'];
        if (!empty($type)) {
            $appearance->setType($type);
        }

        $rank = (int)$checkedParams['rank'];
        if (!empty($rank)) {
            $appearance->setRank($rank);
        }

        $status = (int)$checkedParams['status'];
        if (!empty($status)) {
            $appearance->setStatus($status);
        }


        // Get database object
        $db = $appearance->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();
        $appearance->update();
        $db->commit();

        return $response->withStatus(204);
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

       return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Delete data for Appearance
 *
 * DELETE /appearances/{id}
 */
$app->delete('/appearances/{id:[0-9]+}', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('delete', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    try {
        $appearance = new \LinesC\Model\Appearance($this->get('database'));

        // Get database object
        $db = $appearance->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();

        // Get the Appearance with given id
        if ($appearance->find($args['id'])) {
            $delete = $appearance->delete();
        }

        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    if ($delete) {
        return $response->withJson(['message' => 'The appearance with Id ' . $args['id'] . ' is deleted successfully'], 204);
    }

    return $response->withStatus(404);
});



