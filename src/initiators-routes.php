<?php

/**
 * Fetch data for a specific Initiator
 *
 * GET /initiators/{id}
 */
$app->get('/initiators/{id:[0-9]+}', function ($request, $response, $args) {
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
        $initiator = new \LinesC\Model\Initiator($this->get('database'));

        // Get the Initiator with given id
        if ($initiator->find($args['id'])) {
            return $response->withJson($initiator->toArray(), 200);
        }

        return $response->withStatus(404);
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Fetch all Initiators
 *
 * GET /initiators
 */
$app->get('/initiators', function ($request, $response) {
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
    $order = filter_var($request->getParam('order', 'name'), FILTER_SANITIZE_STRING);
    $dir = filter_var($request->getParam('dir', 'ASC'), FILTER_SANITIZE_STRING);

    $createdDate = filter_var($request->getParam('createdDate'), FILTER_SANITIZE_STRING);
    $updatedDate = filter_var($request->getParam('updatedDate'), FILTER_SANITIZE_STRING);
    $secureId = filter_var($request->getParam('secureId'), FILTER_SANITIZE_STRING);
    $name = filter_var($request->getParam('name'), FILTER_SANITIZE_STRING);
    $email = filter_var($request->getParam('email'), FILTER_SANITIZE_STRING);
    $phone = filter_var($request->getParam('phone'), FILTER_SANITIZE_STRING);
    $website = filter_var($request->getParam('website'), FILTER_SANITIZE_STRING);
    $identifier = filter_var($request->getParam('identifier'), FILTER_SANITIZE_NUMBER_INT);
    $type = filter_var($request->getParam('type'), FILTER_SANITIZE_NUMBER_INT);
    $rank = filter_var($request->getParam('rank'), FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($request->getParam('status'), FILTER_SANITIZE_NUMBER_INT);

    $db = $this->get('database');

    // Prepare sql for fetching Initiator's data
    $sql = 'SELECT * FROM initiators';

    $bind = [];
    $clause = [];

    if (!empty($createdDate)) {
        $clause[] = 'initiator_created_date >= ?';
        $bind[] = $createdDate;
    }

    if (!empty($updatedDate)) {
        $clause[] = 'initiator_updated_date >= ?';
        $bind[] = $updatedDate;
    }

    if (!empty($secureId)) {
        $clause[] = 'secure_id = ?';
        $bind[] = $secureId;
    }

    if (!empty($name)) {
        $clause[] = 'name = ?';
        $bind[] = $name;
    }

    if (!empty($email)) {
        $clause[] = 'email = ?';
        $bind[] = $email;
    }

    if (!empty($phone)) {
        $clause[] = 'phone = ?';
        $bind[] = $phone;
    }

    if (!empty($website)) {
        $clause[] = 'website = ?';
        $bind[] = $website;
    }

    if (!empty($identifier)) {
        $clause[] = 'identifier = ?';
        $bind[] = $identifier;
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
 * Create a new record for Initiator
 *
 * POST /initiators
 */
$app->post('/initiators', function ($request, $response) {
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
    $name = filter_var($request->getParam('name'), FILTER_SANITIZE_STRING);

    $validationMessage = [];

    if (empty($name)) {
        if (!($name === '0' | $name === 0 | $name === 0.0)) {
            $validationMessage[] = 'name is a required field';
        }
    }

    $checkedParams = checkRequestForInitiator($request);

    if (array_merge($checkedParams['validationMessage'], $validationMessage)) {
        return $response->withJson(array_merge($checkedParams['validationMessage'], $validationMessage), 400);
    }

    // Create the model for the Initiator
    $initiator = new \LinesC\Model\Initiator($this->get('database'));

        $initiator->setSecureId(generateSecureId());
        $initiator->setName((string)$checkedParams['name']);
        $initiator->setAddress((string)$checkedParams['address']);
        $initiator->setEmail((string)$checkedParams['email']);
        $initiator->setPhone((string)$checkedParams['phone']);
        $initiator->setWebsite((string)$checkedParams['website']);
        $initiator->setAdditionalInfo((string)$checkedParams['additionalInfo']);
        $initiator->setIdentifier((int)$checkedParams['identifier']);
        $initiator->setType((int)$checkedParams['type']);
        $initiator->setRank((int)$checkedParams['rank']);
        $initiator->setStatus((int)$checkedParams['status']);
    
    try {
        // Get database object
        $db = $initiator->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();
        $initiatorId = $initiator->insert();
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    $responseCode = 500;
    if ($initiatorId) {
        $response = $response->withHeader('Location', '/initiators/' . $initiatorId);
        $responseCode = 201;
    }

    return $response->withStatus($responseCode);
});

/**
 * Update a specific Initiator
 *
 * PUT /initiators/{id}
 */
$app->put('/initiators/{id:[0-9]+}', function ($request, $response, $args) {
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
    $checkedParams = checkRequestForInitiator($request);

    if ($checkedParams['validationMessage']) {
        return $response->withJson($checkedParams['validationMessage'], 400);
    }

    try {
        $initiator = new \LinesC\Model\Initiator($this->get('database'));

        if (!$initiator->find($args['id'])) {
            return $response->withStatus(404);
        }
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    try {
        $name = (string)$checkedParams['name'];
        if (!empty($name)) {
            $initiator->setName($name);
        }

        $address = (string)$checkedParams['address'];
        if (!empty($address)) {
            $initiator->setAddress($address);
        }

        $email = (string)$checkedParams['email'];
        if (!empty($email)) {
            $initiator->setEmail($email);
        }

        $phone = (string)$checkedParams['phone'];
        if (!empty($phone)) {
            $initiator->setPhone($phone);
        }

        $website = (string)$checkedParams['website'];
        if (!empty($website)) {
            $initiator->setWebsite($website);
        }

        $additionalInfo = (string)$checkedParams['additionalInfo'];
        if (!empty($additionalInfo)) {
            $initiator->setAdditionalInfo($additionalInfo);
        }

        $identifier = (int)$checkedParams['identifier'];
        if (!empty($identifier)) {
            $initiator->setIdentifier($identifier);
        }

        $type = (int)$checkedParams['type'];
        if (!empty($type)) {
            $initiator->setType($type);
        }

        $rank = (int)$checkedParams['rank'];
        if (!empty($rank)) {
            $initiator->setRank($rank);
        }

        $status = (int)$checkedParams['status'];
        if (!empty($status)) {
            $initiator->setStatus($status);
        }


        // Get database object
        $db = $initiator->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();
        $initiator->update();
        $db->commit();

        return $response->withStatus(204);
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

       return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Delete data for Initiator
 *
 * DELETE /initiators/{id}
 */
$app->delete('/initiators/{id:[0-9]+}', function ($request, $response, $args) {
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
        $initiator = new \LinesC\Model\Initiator($this->get('database'));

        // Get database object
        $db = $initiator->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();

        // Get the Initiator with given id
        if ($initiator->find($args['id'])) {
            $delete = $initiator->delete();
        }

        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    if ($delete) {
        return $response->withJson(['message' => 'The initiator with Id ' . $args['id'] . ' is deleted successfully'], 204);
    }

    return $response->withStatus(404);
});



