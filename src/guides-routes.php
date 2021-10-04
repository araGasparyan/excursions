<?php

/**
 * Fetch data for a specific Guide by id or secure id
 *
 * GET /guides/{id}
 */
$app->get('/guides/{id}', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    $languages = filter_var($request->getParam('languages', 'no'), FILTER_SANITIZE_STRING);

    try {
        $guide = new \LinesC\Model\Guide($this->get('database'));

        if (!(is_numeric($args['id']) && $guide->find($args['id']))) {
            // Try to fetch the guide by the secure id
            $guide = $guide->findBy('secure_id', $args['id']);
        }

        if (!$guide) {
            return $response->withStatus(404);
        }

        $guide = $guide->toArray();
        if ($languages == 'yes') {
            $guideLanguages = [];
            $db = $this->get('database');

            // Prepare sql for fetching associations
            $sql = 'SELECT languages.* FROM guide_language_associations
                    JOIN languages ON guide_language_associations.language_id = languages.language_id
                    WHERE guide_id = ?';

            $stmt = $db->prepare($sql);
            $stmt->execute([$guide['guide_id']]);

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($results as $result) {
                array_push($guideLanguages, $result["name"]);
            }

            $guide['languages'] = implode(";", $guideLanguages);
        }

        return $response->withJson($guide, 200);
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Fetch all Guides with associated languages
 *
 * GET /guides-with-languages
 */
$app->get('/guides-with-languages', function ($request, $response) {
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
    $order = filter_var($request->getParam('order', 'email'), FILTER_SANITIZE_STRING);
    $dir = filter_var($request->getParam('dir', 'ASC'), FILTER_SANITIZE_STRING);

    $createdDate = filter_var($request->getParam('createdDate'), FILTER_SANITIZE_STRING);
    $updatedDate = filter_var($request->getParam('updatedDate'), FILTER_SANITIZE_STRING);
    $secureId = filter_var($request->getParam('secureId'), FILTER_SANITIZE_STRING);
    $firstName = filter_var($request->getParam('firstName'), FILTER_SANITIZE_STRING);
    $firstNameLike = filter_var($request->getParam('firstNameLike'), FILTER_SANITIZE_STRING);
    $middleName = filter_var($request->getParam('middleName'), FILTER_SANITIZE_STRING);
    $lastName = filter_var($request->getParam('lastName'), FILTER_SANITIZE_STRING);
    $birthDate = filter_var($request->getParam('birthDate'), FILTER_SANITIZE_STRING);
    $email = filter_var($request->getParam('email'), FILTER_SANITIZE_STRING);
    $affiliation = filter_var($request->getParam('affiliation'), FILTER_SANITIZE_STRING);
    $jobTitle = filter_var($request->getParam('jobTitle'), FILTER_SANITIZE_STRING);
    $country = filter_var($request->getParam('country'), FILTER_SANITIZE_STRING);
    $education = filter_var($request->getParam('education'), FILTER_SANITIZE_STRING);
    $phone = filter_var($request->getParam('phone'), FILTER_SANITIZE_STRING);
    $position = filter_var($request->getParam('position'), FILTER_SANITIZE_NUMBER_INT);
    $type = filter_var($request->getParam('type'), FILTER_SANITIZE_NUMBER_INT);
    $rank = filter_var($request->getParam('rank'), FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($request->getParam('status'), FILTER_SANITIZE_NUMBER_INT);

    $db = $this->get('database');

    // Prepare sql for fetching Guide's data
    $sql = 'SELECT guides.*, GROUP_CONCAT(languages.name SEPARATOR ";") AS languages
            FROM guides
            LEFT JOIN guide_language_associations ON guide_language_associations.guide_id = guides.guide_id
            LEFT JOIN languages ON guide_language_associations.language_id = languages.language_id';

    $bind = [];
    $clause = [];

    if (!empty($createdDate)) {
        $clause[] = 'guides.guide_created_date >= ?';
        $bind[] = $createdDate;
    }

    if (!empty($updatedDate)) {
        $clause[] = 'guides.guide_updated_date >= ?';
        $bind[] = $updatedDate;
    }

    if (!empty($secureId)) {
        $clause[] = 'guides.secure_id = ?';
        $bind[] = $secureId;
    }

    if (!empty($firstName)) {
        $clause[] = 'guides.first_name = ?';
        $bind[] = $firstName;
    }

    if (empty($firstName) && !empty($firstNameLike)) {
        $clause[] = 'guides.first_name LIKE ?';
        $bind[] = $firstNameLike . '%';
    }

    if (!empty($middleName)) {
        $clause[] = 'guides.middle_name = ?';
        $bind[] = $middleName;
    }

    if (!empty($lastName)) {
        $clause[] = 'guides.last_name = ?';
        $bind[] = $lastName;
    }

    if (!empty($birthDate)) {
        $clause[] = 'guides.birth_date = ?';
        $bind[] = $birthDate;
    }

    if (!empty($email)) {
        $clause[] = 'guides.email = ?';
        $bind[] = $email;
    }

    if (!empty($affiliation)) {
        $clause[] = 'guides.affiliation = ?';
        $bind[] = $affiliation;
    }

    if (!empty($jobTitle)) {
        $clause[] = 'guides.job_title = ?';
        $bind[] = $jobTitle;
    }

    if (!empty($country)) {
        $clause[] = 'guides.country = ?';
        $bind[] = $country;
    }

    if (!empty($education)) {
        $clause[] = 'guides.education = ?';
        $bind[] = $education;
    }

    if (!empty($phone)) {
        $clause[] = 'guides.phone = ?';
        $bind[] = $phone;
    }

    if (!empty($position)) {
        $clause[] = 'guides.position = ?';
        $bind[] = $position;
    }

    if (!empty($type)) {
        $clause[] = 'guides.type = ?';
        $bind[] = $type;
    }

    if (!empty($rank)) {
        $clause[] = 'guides.rank = ?';
        $bind[] = $rank;
    }

    if (!empty($status)) {
        $clause[] = 'guides.status = ?';
        $bind[] = $status;
    }

    if ($clause) {
        $sql .= ' WHERE ' . implode(' AND ', $clause);
    }

    $sql .= ' GROUP BY guides.guide_id';
    $sql .= ' ORDER BY ' . $order . ' ' . $dir;

    $totalItemsSQL = 'SELECT COUNT(*) AS totalItems FROM (' . $sql . ') AS tmp';
    $totalItemsStmt = $db->prepare($totalItemsSQL);
    $totalItemsStmt->execute($bind);

    $totalItems = $totalItemsStmt->fetchAll(\PDO::FETCH_ASSOC);
    $totalItems = $totalItems[0]['totalItems'];

    try {
        $pager = new Pager($db, $page, $perPage);
        $pager->setTotalItems($totalItems);
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
 * Fetch all available guides
 *
 * GET /available-guides
 */
$app->get('/available-guides', function ($request, $response) {
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
    $perPage = filter_var($request->getParam('per_page', 100), FILTER_SANITIZE_NUMBER_INT);
    $order = filter_var($request->getParam('order', 'type'), FILTER_SANITIZE_STRING);
    $dir = filter_var($request->getParam('dir', 'ASC'), FILTER_SANITIZE_STRING);

    $type = filter_var($request->getParam('type'), FILTER_SANITIZE_NUMBER_INT);

    $db = $this->get('database');

    // Prepare sql for fetching Guide's data
    // TO DO - we should habdle appearance logic here
    $sql = 'SELECT guides.*, GROUP_CONCAT(languages.name SEPARATOR ";") AS languages
            FROM guides
            LEFT JOIN guide_language_associations ON guide_language_associations.guide_id = guides.guide_id
            LEFT JOIN languages ON guide_language_associations.language_id = languages.language_id';

    $clause[] = 'guides.status = ?';
    $bind[] = \LinesC\Model\Guide::STATUS_ACTIVE;

    if (!empty($type)) {
        $clause[] = 'guides.type = ?';
        $bind[] = $type;
    }

    if ($clause) {
        $sql .= ' WHERE ' . implode(' AND ', $clause);
    }

    $sql .= ' GROUP BY guides.guide_id';
    $sql .= ' ORDER BY ' . $order . ' ' . $dir;

    $totalItemsSQL = 'SELECT COUNT(*) AS totalItems FROM (' . $sql . ') AS tmp';
    $totalItemsStmt = $db->prepare($totalItemsSQL);
    $totalItemsStmt->execute($bind);

    $totalItems = $totalItemsStmt->fetchAll(\PDO::FETCH_ASSOC);
    $totalItems = $totalItems[0]['totalItems'];

    try {
        $pager = new Pager($db, $page, $perPage);
        $pager->setTotalItems($totalItems);
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
 * Fetch all Guides
 *
 * GET /guides
 */
$app->get('/guides', function ($request, $response) {
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
    $order = filter_var($request->getParam('order', 'email'), FILTER_SANITIZE_STRING);
    $dir = filter_var($request->getParam('dir', 'ASC'), FILTER_SANITIZE_STRING);

    $createdDate = filter_var($request->getParam('createdDate'), FILTER_SANITIZE_STRING);
    $updatedDate = filter_var($request->getParam('updatedDate'), FILTER_SANITIZE_STRING);
    $secureId = filter_var($request->getParam('secureId'), FILTER_SANITIZE_STRING);
    $firstName = filter_var($request->getParam('firstName'), FILTER_SANITIZE_STRING);
    $firstNameLike = filter_var($request->getParam('firstNameLike'), FILTER_SANITIZE_STRING);
    $middleName = filter_var($request->getParam('middleName'), FILTER_SANITIZE_STRING);
    $lastName = filter_var($request->getParam('lastName'), FILTER_SANITIZE_STRING);
    $birthDate = filter_var($request->getParam('birthDate'), FILTER_SANITIZE_STRING);
    $email = filter_var($request->getParam('email'), FILTER_SANITIZE_STRING);
    $affiliation = filter_var($request->getParam('affiliation'), FILTER_SANITIZE_STRING);
    $jobTitle = filter_var($request->getParam('jobTitle'), FILTER_SANITIZE_STRING);
    $country = filter_var($request->getParam('country'), FILTER_SANITIZE_STRING);
    $education = filter_var($request->getParam('education'), FILTER_SANITIZE_STRING);
    $phone = filter_var($request->getParam('phone'), FILTER_SANITIZE_STRING);
    $position = filter_var($request->getParam('position'), FILTER_SANITIZE_NUMBER_INT);
    $type = filter_var($request->getParam('type'), FILTER_SANITIZE_NUMBER_INT);
    $rank = filter_var($request->getParam('rank'), FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($request->getParam('status'), FILTER_SANITIZE_NUMBER_INT);

    $db = $this->get('database');

    // Prepare sql for fetching Guide's data
    $sql = 'SELECT * FROM guides';

    $bind = [];
    $clause = [];

    if (!empty($createdDate)) {
        $clause[] = 'guide_created_date >= ?';
        $bind[] = $createdDate;
    }

    if (!empty($updatedDate)) {
        $clause[] = 'guide_updated_date >= ?';
        $bind[] = $updatedDate;
    }

    if (!empty($secureId)) {
        $clause[] = 'secure_id = ?';
        $bind[] = $secureId;
    }

    if (!empty($firstName)) {
        $clause[] = 'first_name = ?';
        $bind[] = $firstName;
    }

    if (empty($firstName) && !empty($firstNameLike)) {
        $clause[] = 'first_name LIKE ?';
        $bind[] = $firstNameLike . '%';
    }

    if (!empty($middleName)) {
        $clause[] = 'middle_name = ?';
        $bind[] = $middleName;
    }

    if (!empty($lastName)) {
        $clause[] = 'last_name = ?';
        $bind[] = $lastName;
    }

    if (!empty($birthDate)) {
        $clause[] = 'birth_date = ?';
        $bind[] = $birthDate;
    }

    if (!empty($email)) {
        $clause[] = 'email = ?';
        $bind[] = $email;
    }

    if (!empty($affiliation)) {
        $clause[] = 'affiliation = ?';
        $bind[] = $affiliation;
    }

    if (!empty($jobTitle)) {
        $clause[] = 'job_title = ?';
        $bind[] = $jobTitle;
    }

    if (!empty($country)) {
        $clause[] = 'country = ?';
        $bind[] = $country;
    }

    if (!empty($education)) {
        $clause[] = 'education = ?';
        $bind[] = $education;
    }

    if (!empty($phone)) {
        $clause[] = 'phone = ?';
        $bind[] = $phone;
    }

    if (!empty($position)) {
        $clause[] = 'position = ?';
        $bind[] = $position;
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
 * Register a new guide with correspondeing associated languages (with language secure_id -s)
 *
 * POST /registerGuide
 */
$app->post('/registerGuide', function ($request, $response) {
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
    $firstName = filter_var($request->getParam('firstName'), FILTER_SANITIZE_STRING);
    $lastName = filter_var($request->getParam('lastName'), FILTER_SANITIZE_STRING);
    $email = filter_var($request->getParam('email'), FILTER_SANITIZE_STRING);
    $position = filter_var($request->getParam('position'), FILTER_SANITIZE_NUMBER_INT);
    $languages = filter_var($request->getParam('languages'), FILTER_SANITIZE_STRING);

    $validationMessage = [];

    if (empty($firstName)) {
        if (!($firstName === '0' | $firstName === 0 | $firstName === 0.0)) {
            $validationMessage[] = 'firstName is a required field';
        }
    }

    if (empty($lastName)) {
        if (!($lastName === '0' | $lastName === 0 | $lastName === 0.0)) {
            $validationMessage[] = 'lastName is a required field';
        }
    }

    if (empty($email)) {
        if (!($email === '0' | $email === 0 | $email === 0.0)) {
            $validationMessage[] = 'email is a required field';
        }
    }

    if (empty($position)) {
        if (!($position === '0' | $position === 0 | $position === 0.0)) {
            $validationMessage[] = 'position is a required field';
        }
    }

    if (empty($languages)) {
        if (!($languages === '0' | $languages === 0 | $languages === 0.0)) {
            $validationMessage[] = 'languages is a required field';
        }
    }

    $languages = explode(";", $languages);
    if (count($languages) == 0) {
        $validationMessage[] = 'At least one languages should be presented';
    }

    $checkedParams = checkRequestForGuide($request);

    if (array_merge($checkedParams['validationMessage'], $validationMessage)) {
        return $response->withJson(array_merge($checkedParams['validationMessage'], $validationMessage), 400);
    }

    // Validate guide status
    $status = (int)$checkedParams['status'];
    if (!\LinesC\Model\Guide::isValidStatus($status)) {
        $status = \LinesC\Model\Guide::STATUS_ACTIVE;
    }

    // Validate guide rank
    $rank = (int)$checkedParams['rank'];
    if (!\LinesC\Model\Guide::isValidRank($rank)) {
        $rank = \LinesC\Model\Guide::RANK_DEFAULT;
    }

    // Validate guide type
    $type = (int)$checkedParams['type'];
    if (!\LinesC\Model\Guide::isValidType($type)) {
        $type = \LinesC\Model\Guide::TYPE_GENERAL;
    }

    // Validate guide position
    $position = (int)$checkedParams['position'];
    if (!\LinesC\Model\Guide::isValidPosition($position)) {
        $position = \LinesC\Model\Guide::TYPE_POSITION_FULL;
    }

    // Create the model for the Guide
    $guide = new \LinesC\Model\Guide($this->get('database'));
    $guide->setSecureId(generateSecureId());
    $guide->setFirstName((string)$checkedParams['firstName']);
    $guide->setMiddleName((string)$checkedParams['middleName']);
    $guide->setLastName((string)$checkedParams['lastName']);

    $birthDate = (string)$checkedParams['birthDate'];
    if (!empty($birthDate)) {
        $guide->setBirthDate(new \DateTime($birthDate));
    }

    $guide->setEmail((string)$checkedParams['email']);
    $guide->setAddress((string)$checkedParams['address']);
    $guide->setAffiliation((string)$checkedParams['affiliation']);
    $guide->setJobTitle((string)$checkedParams['jobTitle']);
    $guide->setCountry((string)$checkedParams['country']);
    $guide->setEducation((string)$checkedParams['education']);
    $guide->setPhone((string)$checkedParams['phone']);
    $guide->setImagePath((string)$checkedParams['imagePath']);
    $guide->setAdditionalInfo((string)$checkedParams['additionalInfo']);
    $guide->setPosition($position);
    $guide->setDescription((string)$checkedParams['description']);
    $guide->setType($type);
    $guide->setRank($rank);
    $guide->setStatus($status);

    try {
        if ($guide->findBy('email', $checkedParams['email'])) {
            return $response->withJson(['message' => 'A resource with email ' . $checkedParams['email'] . ' already exists'], 409);
        }

        // Get database object
        $db = $guide->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();
        $guideId = $guide->insert();

        $languageModel = new \LinesC\Model\Language($db);
        foreach ($languages as $languageSecureId) {
            $languageId = $languageModel->findBy('secure_id', $languageSecureId)->toArray()['language_id'];
            $dateTime = new \DateTime();
            $dateTime = $dateTime->format('Y-m-d H:i:s');

            // Prepare sql for creating the association
            $sql = 'INSERT INTO guide_language_associations (guide_id, language_id, guide_language_associations_created_date, guide_language_associations_updated_date) VALUES (?, ?, ?, ?)';

            $stmt = $db->prepare($sql);
            $stmt->execute([$guideId, $languageId, $dateTime, $dateTime]);
        }

        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    $responseCode = 500;
    if ($guideId) {
        $response = $response->withHeader('Location', '/guides/' . $guideId);
        $responseCode = 201;
    }

    return $response->withStatus($responseCode);
});

/**
 * Create a new record for Guide
 *
 * POST /guides
 */
$app->post('/guides', function ($request, $response) {
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
    $firstName = filter_var($request->getParam('firstName'), FILTER_SANITIZE_STRING);
    $lastName = filter_var($request->getParam('lastName'), FILTER_SANITIZE_STRING);
    $email = filter_var($request->getParam('email'), FILTER_SANITIZE_STRING);
    $position = filter_var($request->getParam('position'), FILTER_SANITIZE_NUMBER_INT);

    $validationMessage = [];

    if (empty($firstName)) {
        if (!($firstName === '0' | $firstName === 0 | $firstName === 0.0)) {
            $validationMessage[] = 'firstName is a required field';
        }
    }

    if (empty($lastName)) {
        if (!($lastName === '0' | $lastName === 0 | $lastName === 0.0)) {
            $validationMessage[] = 'lastName is a required field';
        }
    }

    if (empty($email)) {
        if (!($email === '0' | $email === 0 | $email === 0.0)) {
            $validationMessage[] = 'email is a required field';
        }
    }

    if (empty($position)) {
        if (!($position === '0' | $position === 0 | $position === 0.0)) {
            $validationMessage[] = 'position is a required field';
        }
    }

    $checkedParams = checkRequestForGuide($request);

    if (array_merge($checkedParams['validationMessage'], $validationMessage)) {
        return $response->withJson(array_merge($checkedParams['validationMessage'], $validationMessage), 400);
    }

    // Validate guide status
    $status = (int)$checkedParams['status'];
    if (!\LinesC\Model\Guide::isValidStatus($status)) {
        $status = \LinesC\Model\Guide::STATUS_ACTIVE;
    }

    // Validate guide rank
    $rank = (int)$checkedParams['rank'];
    if (!\LinesC\Model\Guide::isValidRank($rank)) {
        $rank = \LinesC\Model\Guide::RANK_DEFAULT;
    }

    // Validate guide type
    $type = (int)$checkedParams['type'];
    if (!\LinesC\Model\Guide::isValidType($type)) {
        $type = \LinesC\Model\Guide::TYPE_GENERAL;
    }

    // Validate guide position
    $position = (int)$checkedParams['position'];
    if (!\LinesC\Model\Guide::isValidPosition($position)) {
        $position = \LinesC\Model\Guide::TYPE_POSITION_FULL;
    }

    // Create the model for the Guide
    $guide = new \LinesC\Model\Guide($this->get('database'));
    $guide->setSecureId(generateSecureId());
    $guide->setFirstName((string)$checkedParams['firstName']);
    $guide->setMiddleName((string)$checkedParams['middleName']);
    $guide->setLastName((string)$checkedParams['lastName']);

    $birthDate = (string)$checkedParams['birthDate'];
    if (!empty($birthDate)) {
        $guide->setBirthDate(new \DateTime($birthDate));
    }

    $guide->setEmail((string)$checkedParams['email']);
    $guide->setAddress((string)$checkedParams['address']);
    $guide->setAffiliation((string)$checkedParams['affiliation']);
    $guide->setJobTitle((string)$checkedParams['jobTitle']);
    $guide->setCountry((string)$checkedParams['country']);
    $guide->setEducation((string)$checkedParams['education']);
    $guide->setPhone((string)$checkedParams['phone']);
    $guide->setImagePath((string)$checkedParams['imagePath']);
    $guide->setAdditionalInfo((string)$checkedParams['additionalInfo']);
    $guide->setPosition($position);
    $guide->setDescription((string)$checkedParams['description']);
    $guide->setType($type);
    $guide->setRank($rank);
    $guide->setStatus($status);

    try {
        if ($guide->findBy('email', $checkedParams['email'])) {
            return $response->withJson(['message' => 'A resource with email ' . $checkedParams['email'] . ' already exists'], 409);
        }

        // Get database object
        $db = $guide->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();
        $guideId = $guide->insert();
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    $responseCode = 500;
    if ($guideId) {
        $response = $response->withHeader('Location', '/guides/' . $guideId);
        $responseCode = 201;
    }

    return $response->withStatus($responseCode);
});

/**
 * Update a specific Guide by id or secure id
 *
 * PUT /guides/{id}
 */
$app->put('/guides/{id}', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('update', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    $languages = filter_var($request->getParam('languages'), FILTER_SANITIZE_STRING);

    /**
     * Sanitize input
     */
    $checkedParams = checkRequestForGuide($request);

    if ($checkedParams['validationMessage']) {
        return $response->withJson($checkedParams['validationMessage'], 400);
    }

    try {
        $guide = new \LinesC\Model\Guide($this->get('database'));

        if (!(is_numeric($args['id']) && $guide->find($args['id']))) {
            // Try to fetch the guide by the secure id
            $guide = $guide->findBy('secure_id', $args['id']);
        }

        if (!$guide) {
            return $response->withStatus(404);
        }
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    try {
        if (($checkedParams['guide'] != $guide->toArray()['guide']) && $guide->findBy('email', $checkedParams['email'])) {
            return $response->withJson(['message' => 'A resource with email ' . $checkedParams['email'] . ' already exists'], 409);
        }

        $firstName = (string)$checkedParams['firstName'];
        if (!empty($firstName)) {
            $guide->setFirstName($firstName);
        }

        $middleName = (string)$checkedParams['middleName'];
        if (!empty($middleName)) {
            $guide->setMiddleName($middleName);
        }

        $lastName = (string)$checkedParams['lastName'];
        if (!empty($lastName)) {
            $guide->setLastName($lastName);
        }

        $birthDate = (string)$checkedParams['birthDate'];
        if (!empty($birthDate)) {
            $guide->setBirthDate(new \DateTime($birthDate));
        }

        $email = (string)$checkedParams['email'];
        if (!empty($email)) {
            $guide->setEmail($email);
        }

        $address = (string)$checkedParams['address'];
        if (!empty($address)) {
            $guide->setAddress($address);
        }

        $affiliation = (string)$checkedParams['affiliation'];
        if (!empty($affiliation)) {
            $guide->setAffiliation($affiliation);
        }

        $jobTitle = (string)$checkedParams['jobTitle'];
        if (!empty($jobTitle)) {
            $guide->setJobTitle($jobTitle);
        }

        $country = (string)$checkedParams['country'];
        if ($country == 'delete_country') {
            $guide->setCountry('');
        } else if (!empty($country)) {
            $guide->setCountry($country);
        }

        $education = (string)$checkedParams['education'];
        if (!empty($education)) {
            $guide->setEducation($education);
        }

        $phone = (string)$checkedParams['phone'];
        if (!empty($phone)) {
            $guide->setPhone($phone);
        }

        $imagePath = (string)$checkedParams['imagePath'];
        if (!empty($imagePath)) {
            $guide->setImagePath($imagePath);
        }

        $additionalInfo = (string)$checkedParams['additionalInfo'];
        if (!empty($additionalInfo)) {
            $guide->setAdditionalInfo($additionalInfo);
        }

        $position = (int)$checkedParams['position'];
        if (!empty($position)) {
            if (!\LinesC\Model\Guide::isValidPosition($position)) {
                $position = \LinesC\Model\Guide::TYPE_POSITION_FULL;
            }

            $guide->setPosition($position);
        }

        $description = (string)$checkedParams['description'];
        if (!empty($description)) {
            $guide->setDescription($description);
        }

        $type = (int)$checkedParams['type'];
        if (!empty($type)) {
            if (!\LinesC\Model\Guide::isValidType($type)) {
                $type = \LinesC\Model\Guide::TYPE_GENERAL;
            }

            $guide->setType($type);
        }

        $rank = (int)$checkedParams['rank'];
        if (!empty($rank)) {
            if (!\LinesC\Model\Guide::isValidRank($rank)) {
                $rank = \LinesC\Model\Guide::RANK_DEFAULT;
            }

            $guide->setRank($rank);
        }

        $status = (int)$checkedParams['status'];
        if (!empty($status)) {
            if (!\LinesC\Model\Guide::isValidStatus($status)) {
                $status = \LinesC\Model\Guide::STATUS_ACTIVE;
            }

            $guide->setStatus($status);
        }


        // Get database object
        $db = $guide->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();

        $guide->update();
        $guideId = $guide->toArray()["guide_id"];

        if (!empty($languages)) {
            $languages = explode(";", $languages);

            // Prepare sql for deleting associations
            $deleteSql = 'DELETE FROM guide_language_associations WHERE guide_id = ?';

            $deleteStmt = $db->prepare($deleteSql);
            $deleteStmt->execute([$guideId]);

            $languageModel = new \LinesC\Model\Language($db);
            foreach ($languages as $languageSecureId) {
                $languageId = $languageModel->findBy('secure_id', $languageSecureId)->toArray()['language_id'];
                $dateTime = new \DateTime();
                $dateTime = $dateTime->format('Y-m-d H:i:s');

                // Prepare sql for creating the association
                $sql = 'INSERT INTO guide_language_associations (guide_id, language_id, guide_language_associations_created_date, guide_language_associations_updated_date) VALUES (?, ?, ?, ?)';

                $stmt = $db->prepare($sql);
                $stmt->execute([$guideId, $languageId, $dateTime, $dateTime]);
            }
        }

        $db->commit();

        return $response->withStatus(204);
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

       return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Delete data for Guide
 *
 * DELETE /guides/{id}
 */
$app->delete('/guides/{id:[0-9]+}', function ($request, $response, $args) {
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
        $guide = new \LinesC\Model\Guide($this->get('database'));

        // Get database object
        $db = $guide->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();

        // Get the Guide with given id
        if ($guide->find($args['id'])) {
            $delete = $guide->delete();
        }

        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    if ($delete) {
        return $response->withJson(['message' => 'The guide with Id ' . $args['id'] . ' is deleted successfully'], 204);
    }

    return $response->withStatus(404);
});

/**
 * Get all Languages associated with a specific Guide
 *
 * GET /guides/{id}/languages
 */
$app->get('/guides/{id:[0-9]+}/languages', function ($request, $response, $args) {
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
        $sql = 'SELECT languages.* FROM guide_language_associations
                JOIN languages ON guide_language_associations.language_id = languages.language_id
                WHERE guide_id = ?';

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
 * Get all Guides associated with a specific Language
 *
 * GET /languages/{id}/guides
 */
$app->get('/languages/{id:[0-9]+}/guides', function ($request, $response, $args) {
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
        $sql = 'SELECT guides.* FROM guide_language_associations
                JOIN guides ON guide_language_associations.guide_id = guides.guide_id
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
 * Get all Excursions associated with a specific Guide
 *
 * GET /guides/{id}/excursions
 */
$app->get('/guides/{id:[0-9]+}/excursions', function ($request, $response, $args) {
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
        $sql = 'SELECT excursions.* FROM guide_excursion_associations
                JOIN excursions ON guide_excursion_associations.excursion_id = excursions.excursion_id
                WHERE guide_id = ?';

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
 * Get all Guides associated with a specific Excursion
 *
 * GET /excursions/{id}/guides
 */
$app->get('/excursions/{id:[0-9]+}/guides', function ($request, $response, $args) {
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
        $sql = 'SELECT guides.* FROM guide_excursion_associations
                JOIN guides ON guide_excursion_associations.guide_id = guides.guide_id
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
 * Get all Appearances associated with a specific Guide
 *
 * GET /guides/{id}/appearances
 */
$app->get('/guides/{id:[0-9]+}/appearances', function ($request, $response, $args) {
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
        $sql = 'SELECT appearances.* FROM guide_appearance_associations
                JOIN appearances ON guide_appearance_associations.appearance_id = appearances.appearance_id
                WHERE guide_id = ?';

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
 * Get all Guides associated with a specific Appearance
 *
 * GET /appearances/{id}/guides
 */
$app->get('/appearances/{id:[0-9]+}/guides', function ($request, $response, $args) {
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
        $sql = 'SELECT guides.* FROM guide_appearance_associations
                JOIN guides ON guide_appearance_associations.guide_id = guides.guide_id
                WHERE appearance_id = ?';

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
 * Associate a Guide with a Language
 *
 * POST /guides/{guideId}/languages/{languageId}
 */
$app->post('/guides/{guideId:[0-9]+}/languages/{languageId:[0-9]+}', function ($request, $response, $args) {
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
        $sql = 'INSERT INTO guide_language_associations (guide_id, language_id, guide_language_associations_created_date, guide_language_associations_updated_date) VALUES (?, ?, ?, ?)';

        $stmt = $db->prepare($sql);
        $stmt->execute([$args['guideId'], $args['languageId'], $dateTime, $dateTime]);
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    return $response->withStatus(201);
});

/**
 * Associate a Guide with a Excursion
 *
 * POST /guides/{guideId}/excursions/{excursionId}
 */
$app->post('/guides/{guideId:[0-9]+}/excursions/{excursionId:[0-9]+}', function ($request, $response, $args) {
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
        $sql = 'INSERT INTO guide_excursion_associations (guide_id, excursion_id, guide_excursion_associations_created_date, guide_excursion_associations_updated_date) VALUES (?, ?, ?, ?)';

        $stmt = $db->prepare($sql);
        $stmt->execute([$args['guideId'], $args['excursionId'], $dateTime, $dateTime]);
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    return $response->withStatus(201);
});

/**
 * Associate a Guide with a Appearance
 *
 * POST /guides/{guideId}/appearances/{appearanceId}
 */
$app->post('/guides/{guideId:[0-9]+}/appearances/{appearanceId:[0-9]+}', function ($request, $response, $args) {
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
        $sql = 'INSERT INTO guide_appearance_associations (guide_id, appearance_id, guide_appearance_associations_created_date, guide_appearance_associations_updated_date) VALUES (?, ?, ?, ?)';

        $stmt = $db->prepare($sql);
        $stmt->execute([$args['guideId'], $args['appearanceId'], $dateTime, $dateTime]);
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    return $response->withStatus(201);
});


/**
 * Delete all the Languages associated with a specific Guide
 *
 * DELETE /guides/{id}/languages
 */
$app->delete('/guides/{id:[0-9]+}/languages', function ($request, $response, $args) {
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
        $sql = 'DELETE FROM guide_language_associations WHERE guide_id = ?';

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
 * Delete all the Guides associated with a specific Language
 *
 * DELETE /languages/{id}/guides
 */
$app->delete('/languages/{id:[0-9]+}/guides', function ($request, $response, $args) {
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
        $sql = 'DELETE FROM guide_language_associations WHERE language_id = ?';

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
 * Delete all the Excursions associated with a specific Guide
 *
 * DELETE /guides/{id}/excursions
 */
$app->delete('/guides/{id:[0-9]+}/excursions', function ($request, $response, $args) {
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
        $sql = 'DELETE FROM guide_excursion_associations WHERE guide_id = ?';

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
 * Delete all the Guides associated with a specific Excursion
 *
 * DELETE /excursions/{id}/guides
 */
$app->delete('/excursions/{id:[0-9]+}/guides', function ($request, $response, $args) {
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
        $sql = 'DELETE FROM guide_excursion_associations WHERE excursion_id = ?';

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
 * Delete all the Appearances associated with a specific Guide
 *
 * DELETE /guides/{id}/appearances
 */
$app->delete('/guides/{id:[0-9]+}/appearances', function ($request, $response, $args) {
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
        $sql = 'DELETE FROM guide_appearance_associations WHERE guide_id = ?';

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
 * Delete all the Guides associated with a specific Appearance
 *
 * DELETE /appearances/{id}/guides
 */
$app->delete('/appearances/{id:[0-9]+}/guides', function ($request, $response, $args) {
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
        $sql = 'DELETE FROM guide_appearance_associations WHERE appearance_id = ?';

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
 * Fetch guide types
 *
 * GET /guide-types
 */
$app->get('/guide-types', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    return $response->withJson(\LinesC\Model\Guide::getTypes(), 200);
});

/**
 * Fetch guide Statuses
 *
 * GET /guide-statuses
 */
$app->get('/guide-statuses', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    return $response->withJson(\LinesC\Model\Guide::getStatuses(), 200);
});

/**
 * Fetch guide positions
 *
 * GET /guide-positions
 */
$app->get('/guide-positions', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    return $response->withJson(\LinesC\Model\Guide::getPositions(), 200);
});
