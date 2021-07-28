<?php

/**
 * Fetch data for a specific Language by id or secure id
 *
 * GET /languages/{id}
 */
$app->get('/languages/{id}', function ($request, $response, $args) {
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
        $language = new \LinesC\Model\Language($this->get('database'));

        if (!(is_numeric($args['id']) && $language->find($args['id']))) {
            // Try to fetch the language by the secure id
            $language = $language->findBy('secure_id', $args['id']);
        }

        if (!$language) {
            return $response->withStatus(404);
        }

        return $response->withJson($language->toArray(), 200);
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Fetch all Languages
 *
 * GET /languages
 */
$app->get('/languages', function ($request, $response) {
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
    $type = filter_var($request->getParam('type'), FILTER_SANITIZE_NUMBER_INT);
    $rank = filter_var($request->getParam('rank'), FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($request->getParam('status'), FILTER_SANITIZE_NUMBER_INT);

    $db = $this->get('database');

    // Prepare sql for fetching Language's data
    $sql = 'SELECT * FROM languages';

    $bind = [];
    $clause = [];

    if (!empty($createdDate)) {
        $clause[] = 'language_created_date >= ?';
        $bind[] = $createdDate;
    }

    if (!empty($updatedDate)) {
        $clause[] = 'language_updated_date >= ?';
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
 * Create a new record for Language
 *
 * POST /languages
 */
$app->post('/languages', function ($request, $response) {
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

    $checkedParams = checkRequestForLanguage($request);

    if (array_merge($checkedParams['validationMessage'], $validationMessage)) {
        return $response->withJson(array_merge($checkedParams['validationMessage'], $validationMessage), 400);
    }

    // Validate language status
    $status = (int)$checkedParams['status'];
    if (!\LinesC\Model\Language::isValidStatus($status)) {
        $status = \LinesC\Model\Language::STATUS_ACTIVE;
    }

    // Validate language rank
    $rank = (int)$checkedParams['rank'];
    if (!\LinesC\Model\Language::isValidRank($rank)) {
        $rank = \LinesC\Model\Language::RANK_DEFAULT;
    }

    // Validate language type
    $type = (int)$checkedParams['type'];
    if (!\LinesC\Model\Language::isValidType($type)) {
        $type = \LinesC\Model\Language::TYPE_GENERAL;
    }

    // Create the model for the Language
    $language = new \LinesC\Model\Language($this->get('database'));

    $language->setSecureId(generateSecureId());
    $language->setName((string)$checkedParams['name']);
    $language->setAdditional((string)$checkedParams['additional']);
    $language->setDescription((string)$checkedParams['description']);
    $language->setType((int)$type);
    $language->setRank((int)$rank);
    $language->setStatus((int)$status);

    try {
        if ($language->findBy('name', $checkedParams['name'])) {
            return $response->withJson(['message' => 'A resource with name ' . $checkedParams['name'] . ' already exists'], 409);
        }

        // Get database object
        $db = $language->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();
        $languageId = $language->insert();
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    $responseCode = 500;
    if ($languageId) {
        $response = $response->withHeader('Location', '/languages/' . $languageId);
        $responseCode = 201;
    }

    return $response->withStatus($responseCode);
});

/**
 * Update a specific Language by id or secure id
 *
 * PUT /languages/{id}
 */
$app->put('/languages/{id}', function ($request, $response, $args) {
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
    $checkedParams = checkRequestForLanguage($request);

    if ($checkedParams['validationMessage']) {
        return $response->withJson($checkedParams['validationMessage'], 400);
    }

    try {
        $language = new \LinesC\Model\Language($this->get('database'));

        if (!(is_numeric($args['id']) && $language->find($args['id']))) {
            // Try to fetch the language by the secure id
            $language = $language->findBy('secure_id', $args['id']);
        }

        if (!$language) {
            return $response->withStatus(404);
        }

    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    try {
        if (($checkedParams['language'] != $language->toArray()['language']) && $language->findBy('name', $checkedParams['name'])) {
            return $response->withJson(['message' => 'A resource with name ' . $checkedParams['name'] . ' already exists'], 409);
        }

        $name = (string)$checkedParams['name'];
        if (!empty($name)) {
            $language->setName($name);
        }

        $additional = (string)$checkedParams['additional'];
        if (!empty($additional)) {
            $language->setAdditional($additional);
        }

        $description = (string)$checkedParams['description'];
        if (!empty($description)) {
            $language->setDescription($description);
        }

        $type = (int)$checkedParams['type'];
        if (!empty($type)) {
            if (!\LinesC\Model\Language::isValidType($type)) {
                $type = \LinesC\Model\Language::TYPE_GENERAL;
            }

            $language->setType($type);
        }

        $rank = (int)$checkedParams['rank'];
        if (!empty($rank)) {
            if (!\LinesC\Model\Language::isValidRank($rank)) {
                $rank = \LinesC\Model\Language::RANK_DEFAULT;
            }

            $language->setRank($rank);
        }

        $status = (int)$checkedParams['status'];
        if (!empty($status)) {
            if (!\LinesC\Model\Language::isValidStatus($status)) {
                $status = \LinesC\Model\Language::STATUS_ACTIVE;
            }

            $language->setStatus($status);
        }

        // Get database object
        $db = $language->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();
        $language->update();
        $db->commit();

        return $response->withStatus(204);
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

       return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Delete data for Language
 *
 * DELETE /languages/{id}
 */
$app->delete('/languages/{id:[0-9]+}', function ($request, $response, $args) {
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
        $language = new \LinesC\Model\Language($this->get('database'));

        // Get database object
        $db = $language->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();

        // Get the Language with given id
        if ($language->find($args['id'])) {
            $delete = $language->delete();
        }

        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    if ($delete) {
        return $response->withJson(['message' => 'The language with Id ' . $args['id'] . ' is deleted successfully'], 204);
    }

    return $response->withStatus(404);
});

/**
 * Get all Excursions associated with a specific Language
 *
 * GET /languages/{id}/excursions
 */
$app->get('/languages/{id:[0-9]+}/excursions', function ($request, $response, $args) {
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

    try {
        $db = $this->get('database');
        $logger = $this->get('logger');

        // Prepare sql for fetching associations
        $sql = 'SELECT excursions.* FROM language_excursion_associations
                JOIN excursions ON language_excursion_associations.excursion_id = excursions.excursion_id
                WHERE language_id = ?';

        $stmt = $db->prepare($sql);
        $stmt->execute([$args['id']]);

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $logger->error($e->getMessage());
        $logger->error($e->getTraceAsString());

        trigger503Response();
    }

    return $response->withJson($result, 200);
});

/**
 * Get all Languages associated with a specific Excursion
 *
 * GET /excursions/{id}/languages
 */
$app->get('/excursions/{id:[0-9]+}/languages', function ($request, $response, $args) {
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

    try {
        $db = $this->get('database');
        $logger = $this->get('logger');

        // Prepare sql for fetching associations
        $sql = 'SELECT languages.* FROM language_excursion_associations
                JOIN languages ON language_excursion_associations.language_id = languages.language_id
                WHERE excursion_id = ?';

        $stmt = $db->prepare($sql);
        $stmt->execute([$args['id']]);

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $logger->error($e->getMessage());
        $logger->error($e->getTraceAsString());

        trigger503Response();
    }

    return $response->withJson($result, 200);
});


/**
 * Associate a Language with a Excursion
 *
 * POST /languages/{languageId}/excursions/{excursionId}
 */
$app->post('/languages/{languageId:[0-9]+}/excursions/{excursionId:[0-9]+}', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('create', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    $dateTime = new \DateTime();
    $dateTime = $dateTime->format('Y-m-d H:i:s');

    try {
        /** @var \PDO $db */
        $db = $this->get('database');

        // Begin transaction and commit the changes
        $db->beginTransaction();

        // Prepare sql for creating the association
        $sql = 'INSERT INTO language_excursion_associations (language_id, excursion_id, language_excursion_associations_created_date, language_excursion_associations_updated_date) VALUES (?, ?, ?, ?)';

        $stmt = $db->prepare($sql);
        $stmt->execute([$args['languageId'], $args['excursionId'], $dateTime, $dateTime]);
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    return $response->withStatus(201);
});


/**
 * Delete all the Excursions associated with a specific Language
 *
 * DELETE /languages/{id}/excursions
 */
$app->delete('/languages/{id:[0-9]+}/excursions', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */
    /** @var \PDO $db */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('delete', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    try {
        $db = $this->get('database');

        // Begin transaction and commit the changes
        $db->beginTransaction();

        // Prepare sql for deleting associations
        $sql = 'DELETE FROM language_excursion_associations WHERE language_id = ?';

        $stmt = $db->prepare($sql);
        $stmt->execute([$args['id']]);
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    return $response->withStatus(204);
});

/**
 * Delete all the Languages associated with a specific Excursion
 *
 * DELETE /excursions/{id}/languages
 */
$app->delete('/excursions/{id:[0-9]+}/languages', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */
    /** @var \PDO $db */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('delete', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    try {
        $db = $this->get('database');

        // Begin transaction and commit the changes
        $db->beginTransaction();

        // Prepare sql for deleting associations
        $sql = 'DELETE FROM language_excursion_associations WHERE excursion_id = ?';

        $stmt = $db->prepare($sql);
        $stmt->execute([$args['id']]);
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    return $response->withStatus(204);
});

/**
 * Fetch language types
 *
 * GET /language-types
 */
$app->get('/language-types', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    return $response->withJson(\LinesC\Model\Language::getTypes(), 200);
});

/**
 * Fetch language Statuses
 *
 * GET /language-statuses
 */
$app->get('/language-statuses', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    return $response->withJson(\LinesC\Model\Language::getStatuses(), 200);
});
