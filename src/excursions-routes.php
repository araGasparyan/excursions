<?php
/**
 * Fetch data for a specific Excursion by id or secure id
 *
 * GET /excursions/{id}
 */
$app->get('/excursions/{id}', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    $language = filter_var($request->getParam('language', 'no'), FILTER_SANITIZE_STRING);
    $initiator = filter_var($request->getParam('initiator', 'no'), FILTER_SANITIZE_STRING);
    $guide = filter_var($request->getParam('guide', 'no'), FILTER_SANITIZE_STRING);

    try {
        $excursion = new \LinesC\Model\Excursion($this->get('database'));

        if (!(is_numeric($args['id']) && $excursion->find($args['id']))) {
            // Try to fetch the Excursion by the secure id
            $excursion = $excursion->findBy('secure_id', $args['id']);
        }

        if (!$excursion) {
            return $response->withStatus(404);
        }

        $excursion = $excursion->toArray();
        $excursionId = $excursion['excursion_id'];

        if ($language == 'yes') {
            $db = $this->get('database');

            // Prepare sql for fetching associations
            $sql = 'SELECT languages.* FROM language_excursion_associations
                    JOIN languages ON language_excursion_associations.language_id = languages.language_id
                    WHERE excursion_id = ?';

            $stmt = $db->prepare($sql);
            $stmt->execute([$excursionId]);

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $excursion['language'] = $results[0]["name"];
            $excursion['language_id'] = $results[0]["secure_id"];
        }

        if ($initiator == 'yes') {
            $db = $this->get('database');

            // Prepare sql for fetching associations
            $sql = 'SELECT initiators.* FROM excursion_initiator_associations
                    JOIN initiators ON excursion_initiator_associations.initiator_id = initiators.initiator_id
                    WHERE excursion_id = ?';

            $stmt = $db->prepare($sql);
            $stmt->execute([$excursionId]);

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $excursion['initiator'] = $results[0]["name"];
            $excursion['initiator_id'] = $results[0]["secure_id"];
        }

        if ($guide == 'yes') {
            $db = $this->get('database');

            // Prepare sql for fetching associations
            $sql = 'SELECT guides.* FROM guide_excursion_associations
                    JOIN guides ON guide_excursion_associations.guide_id = guides.guide_id
                    WHERE excursion_id = ?';

            $stmt = $db->prepare($sql);
            $stmt->execute([$excursionId]);

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $excursion['guide'] = $results[0]["first_name"] . ' ' . $results[0]["last_name"];
            $excursion['guide_id'] = $results[0]["secure_id"];
        }

        return $response->withJson($excursion, 200);
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Fetch all Excursions with associated stuff
 *
 * GET /excursions-with-associations
 */
$app->get('/excursions-with-associations', function ($request, $response) {
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
    $order = filter_var($request->getParam('order', 'excursion_start_date'), FILTER_SANITIZE_STRING);
    $dir = filter_var($request->getParam('dir', 'ASC'), FILTER_SANITIZE_STRING);

    $createdDate = filter_var($request->getParam('createdDate'), FILTER_SANITIZE_STRING);
    $updatedDate = filter_var($request->getParam('updatedDate'), FILTER_SANITIZE_STRING);
    $secureId = filter_var($request->getParam('secureId'), FILTER_SANITIZE_STRING);
    $groupMembersCount = filter_var($request->getParam('groupMembersCount'), FILTER_SANITIZE_NUMBER_INT);
    $expectedExcursionStartDate = filter_var($request->getParam('expectedExcursionStartDate'), FILTER_SANITIZE_STRING);
    $started = filter_var($request->getParam('started'), FILTER_SANITIZE_STRING);
    $expectedExcursionStartTime = filter_var($request->getParam('expectedExcursionStartTime'), FILTER_SANITIZE_STRING);
    $verifyStartTimeInHours = filter_var($request->getParam('verifyStartTimeInHours'), FILTER_SANITIZE_NUMBER_INT);
    $expectedDurationOfExcursion = filter_var($request->getParam('expectedDurationOfExcursion'), FILTER_SANITIZE_NUMBER_INT);
    $excursionStartDate = filter_var($request->getParam('excursionStartDate'), FILTER_SANITIZE_STRING);
    $excursionStartTime = filter_var($request->getParam('excursionStartTime'), FILTER_SANITIZE_STRING);
    $excursionEndTime = filter_var($request->getParam('excursionEndTime'), FILTER_SANITIZE_STRING);
    $country = filter_var($request->getParam('country'), FILTER_SANITIZE_STRING);
    $expectedGroupMembersCount = filter_var($request->getParam('expectedGroupMembersCount'), FILTER_SANITIZE_NUMBER_INT);
    $radioGuide = filter_var($request->getParam('radioGuide'), FILTER_SANITIZE_NUMBER_INT);
    $isFree = filter_var($request->getParam('isFree'), FILTER_SANITIZE_NUMBER_INT);
    $type = filter_var($request->getParam('type'), FILTER_SANITIZE_NUMBER_INT);
    $rank = filter_var($request->getParam('rank'), FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($request->getParam('status'), FILTER_SANITIZE_NUMBER_INT);

    $db = $this->get('database');

    // Prepare sql for fetching Excursion's data
    $sql = 'SELECT excursions.*, guides.secure_id AS guideId, guides.first_name AS guideFirstName, guides.last_name AS guideLastName,
	          languages.secure_id AS languageId, languages.name AS language, initiators.secure_id AS initiatorId, initiators.name AS initiator, initiators.identifier AS initiatorIdentity
            FROM excursions
            LEFT JOIN guide_excursion_associations ON guide_excursion_associations.excursion_id = excursions.excursion_id
            LEFT JOIN guides ON guide_excursion_associations.guide_id = guides.guide_id
            LEFT JOIN language_excursion_associations ON language_excursion_associations.excursion_id = excursions.excursion_id
            LEFT JOIN languages ON language_excursion_associations.language_id = languages.language_id
            LEFT JOIN excursion_initiator_associations ON excursion_initiator_associations.excursion_id = excursions.excursion_id
            LEFT JOIN initiators ON excursion_initiator_associations.initiator_id = initiators.initiator_id';

    $bind = [];
    $clause = [];

    if (!empty($createdDate)) {
        $clause[] = 'excursions.excursion_created_date >= ?';
        $bind[] = $createdDate;
    }

    if (!empty($updatedDate)) {
        $clause[] = 'excursions.excursion_updated_date >= ?';
        $bind[] = $updatedDate;
    }

    if (!empty($secureId)) {
        $clause[] = 'excursions.secure_id = ?';
        $bind[] = $secureId;
    }

    if (!empty($groupMembersCount)) {
        $clause[] = 'excursions.group_members_count = ?';
        $bind[] = $groupMembersCount;
    }

    if (!empty($expectedExcursionStartDate)) {
        $clause[] = 'excursions.expected_excursion_start_date = ?';
        $bind[] = $expectedExcursionStartDate;
    }

    if (!empty($started)) {
        $clause[] = 'excursions.expected_excursion_start_date = ? OR excursions.excursion_start_date = ?';
        $bind[] = $started;
        $bind[] = $started;
    }

    if (!empty($expectedExcursionStartTime)) {
        $clause[] = 'excursions.expected_excursion_start_time = ?';
        $bind[] = $expectedExcursionStartTime;
    }

    if (!empty($verifyStartTimeInHours)) {
        $clause[] = 'excursions.verify_start_time_in_hours = ?';
        $bind[] = $verifyStartTimeInHours;
    }

    if (!empty($expectedDurationOfExcursion)) {
        $clause[] = 'excursions.expected_duration_of_excursion = ?';
        $bind[] = $expectedDurationOfExcursion;
    }

    if (!empty($excursionStartDate)) {
        $clause[] = 'excursions.excursion_start_date = ?';
        $bind[] = $excursionStartDate;
    }

    if (!empty($excursionStartTime)) {
        $clause[] = 'excursions.excursion_start_time = ?';
        $bind[] = $excursionStartTime;
    }

    if (!empty($excursionEndTime)) {
        $clause[] = 'excursions.excursion_end_time = ?';
        $bind[] = $excursionEndTime;
    }

    if (!empty($country)) {
        $clause[] = 'excursions.country = ?';
        $bind[] = $country;
    }

    if (!empty($expectedGroupMembersCount)) {
        $clause[] = 'excursions.expected_group_members_count = ?';
        $bind[] = $expectedGroupMembersCount;
    }

    if (!empty($radioGuide)) {
        $clause[] = 'excursions.radio_guide = ?';
        $bind[] = $radioGuide;
    }

    if (!empty($isFree)) {
        $clause[] = 'excursions.is_free = ?';
        $bind[] = $isFree;
    }

    if (!empty($type)) {
        $clause[] = 'excursions.type = ?';
        $bind[] = $type;
    }

    if (!empty($rank)) {
        $clause[] = 'excursions.rank = ?';
        $bind[] = $rank;
    }

    if (!empty($status)) {
        $clause[] = 'excursions.status = ?';
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
 * Fetch all the planned excursions
 *
 * GET /planed-excursions
 */
$app->get('/planed-excursions', function ($request, $response) {
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

    $expectedExcursionStartDate = filter_var($request->getParam('expectedExcursionStartDate'), FILTER_SANITIZE_STRING);
    $country = filter_var($request->getParam('country'), FILTER_SANITIZE_STRING);
    $expectedGroupMembersCount = filter_var($request->getParam('expectedGroupMembersCount'), FILTER_SANITIZE_NUMBER_INT);
    $languages = filter_var($request->getParam('languages'), FILTER_SANITIZE_STRING);

    $db = $this->get('database');

    // Prepare sql for fetching Excursion's data
    $sql = 'SELECT excursions.*, guides.secure_id AS guideId, guides.first_name AS guideFirstName, guides.last_name AS guideLastName,
	          languages.secure_id AS languageId, languages.name AS language, initiators.secure_id AS initiatorId, initiators.name AS initiator, initiators.identifier AS initiatorIdentity
            FROM excursions
            LEFT JOIN guide_excursion_associations ON guide_excursion_associations.excursion_id = excursions.excursion_id
            LEFT JOIN guides ON guide_excursion_associations.guide_id = guides.guide_id
            LEFT JOIN language_excursion_associations ON language_excursion_associations.excursion_id = excursions.excursion_id
            LEFT JOIN languages ON language_excursion_associations.language_id = languages.language_id
            LEFT JOIN excursion_initiator_associations ON excursion_initiator_associations.excursion_id = excursions.excursion_id
            LEFT JOIN initiators ON excursion_initiator_associations.initiator_id = initiators.initiator_id';

    $clause[] = 'excursions.status = ?';
    $bind[] = \LinesC\Model\Excursion::STATUS_REGISTERED;

    if (!empty($expectedExcursionStartDate)) {
        $clause[] = 'excursions.expected_excursion_start_date = ?';
        $bind[] = $expectedExcursionStartDate;
    } else {
        $now = new \DateTime();
        $clause[] = 'excursions.expected_excursion_start_date >= ?';
        $bind[] = $now->format('Y-m-d');
    }

    if (!empty($languages)) {
        $languagesArray = explode(";", $languages);
        $clause[] = implode(" OR ", array_fill(0, count($languagesArray), 'languages.secure_id = ?'));

        foreach ($languagesArray as $l) {
           $bind[] = $l;
        }
    }

    if (!empty($country)) {
        $clause[] = 'excursions.country = ?';
        $bind[] = $country;
    }

    if (!empty($expectedGroupMembersCount)) {
        $clause[] = 'excursions.expected_group_members_count > ?';
        $bind[] = $expectedGroupMembersCount;
    }

    if ($clause) {
        $sql .= ' WHERE ' . implode(' AND ', $clause);
    }

    $sql .= ' ORDER BY expected_excursion_start_date ASC, expected_excursion_start_time ASC';

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($bind);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $response->withJson($result, 200);
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }
});

/**
 * Fetch all Excursions
 *
 * GET /excursions
 */
$app->get('/excursions', function ($request, $response) {
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
    $order = filter_var($request->getParam('order', 'excursion_start_date'), FILTER_SANITIZE_STRING);
    $dir = filter_var($request->getParam('dir', 'ASC'), FILTER_SANITIZE_STRING);

    $createdDate = filter_var($request->getParam('createdDate'), FILTER_SANITIZE_STRING);
    $updatedDate = filter_var($request->getParam('updatedDate'), FILTER_SANITIZE_STRING);
    $secureId = filter_var($request->getParam('secureId'), FILTER_SANITIZE_STRING);
    $groupMembersCount = filter_var($request->getParam('groupMembersCount'), FILTER_SANITIZE_NUMBER_INT);
    $expectedExcursionStartDate = filter_var($request->getParam('expectedExcursionStartDate'), FILTER_SANITIZE_STRING);
    $expectedExcursionStartTime = filter_var($request->getParam('expectedExcursionStartTime'), FILTER_SANITIZE_STRING);
    $verifyStartTimeInHours = filter_var($request->getParam('verifyStartTimeInHours'), FILTER_SANITIZE_NUMBER_INT);
    $expectedDurationOfExcursion = filter_var($request->getParam('expectedDurationOfExcursion'), FILTER_SANITIZE_NUMBER_INT);
    $excursionStartDate = filter_var($request->getParam('excursionStartDate'), FILTER_SANITIZE_STRING);
    $excursionStartTime = filter_var($request->getParam('excursionStartTime'), FILTER_SANITIZE_STRING);
    $excursionEndTime = filter_var($request->getParam('excursionEndTime'), FILTER_SANITIZE_STRING);
    $country = filter_var($request->getParam('country'), FILTER_SANITIZE_STRING);
    $expectedGroupMembersCount = filter_var($request->getParam('expectedGroupMembersCount'), FILTER_SANITIZE_NUMBER_INT);
    $radioGuide = filter_var($request->getParam('radioGuide'), FILTER_SANITIZE_NUMBER_INT);
    $isFree = filter_var($request->getParam('isFree'), FILTER_SANITIZE_NUMBER_INT);
    $type = filter_var($request->getParam('type'), FILTER_SANITIZE_NUMBER_INT);
    $rank = filter_var($request->getParam('rank'), FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($request->getParam('status'), FILTER_SANITIZE_NUMBER_INT);

    $db = $this->get('database');

    // Prepare sql for fetching Excursion's data
    $sql = 'SELECT * FROM excursions';

    $bind = [];
    $clause = [];

    if (!empty($createdDate)) {
        $clause[] = 'excursion_created_date >= ?';
        $bind[] = $createdDate;
    }

    if (!empty($updatedDate)) {
        $clause[] = 'excursion_updated_date >= ?';
        $bind[] = $updatedDate;
    }

    if (!empty($secureId)) {
        $clause[] = 'secure_id = ?';
        $bind[] = $secureId;
    }

    if (!empty($groupMembersCount)) {
        $clause[] = 'group_members_count = ?';
        $bind[] = $groupMembersCount;
    }

    if (!empty($expectedExcursionStartDate)) {
        $clause[] = 'expected_excursion_start_date = ?';
        $bind[] = $expectedExcursionStartDate;
    }

    if (!empty($expectedExcursionStartTime)) {
        $clause[] = 'expected_excursion_start_time = ?';
        $bind[] = $expectedExcursionStartTime;
    }

    if (!empty($verifyStartTimeInHours)) {
        $clause[] = 'verify_start_time_in_hours = ?';
        $bind[] = $verifyStartTimeInHours;
    }

    if (!empty($expectedDurationOfExcursion)) {
        $clause[] = 'expected_duration_of_excursion = ?';
        $bind[] = $expectedDurationOfExcursion;
    }

    if (!empty($excursionStartDate)) {
        $clause[] = 'excursion_start_date = ?';
        $bind[] = $excursionStartDate;
    }

    if (!empty($excursionStartTime)) {
        $clause[] = 'excursion_start_time = ?';
        $bind[] = $excursionStartTime;
    }

    if (!empty($excursionEndTime)) {
        $clause[] = 'excursion_end_time = ?';
        $bind[] = $excursionEndTime;
    }

    if (!empty($country)) {
        $clause[] = 'country = ?';
        $bind[] = $country;
    }

    if (!empty($expectedGroupMembersCount)) {
        $clause[] = 'expected_group_members_count = ?';
        $bind[] = $expectedGroupMembersCount;
    }

    if (!empty($radioGuide)) {
        $clause[] = 'radio_guide = ?';
        $bind[] = $radioGuide;
    }

    if (!empty($isFree)) {
        $clause[] = 'is_free = ?';
        $bind[] = $isFree;
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
 * Register a new excursion with correspondeing associated language and initiator (and may be guide)
 *
 * POST /registerExcursion
 */
$app->post('/registerExcursion', function ($request, $response) {
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
    $expectedExcursionStartDate = filter_var($request->getParam('expectedExcursionStartDate'), FILTER_SANITIZE_STRING);
    $expectedGroupMembersCount = filter_var($request->getParam('expectedGroupMembersCount'), FILTER_SANITIZE_NUMBER_INT);
    $language = filter_var($request->getParam('language'), FILTER_SANITIZE_STRING);
    $initiator = filter_var($request->getParam('initiator'), FILTER_SANITIZE_STRING);
    $guide = filter_var($request->getParam('guide'), FILTER_SANITIZE_STRING);

    $validationMessage = [];

    if (empty($expectedExcursionStartDate)) {
        if (!($expectedExcursionStartDate === '0' | $expectedExcursionStartDate === 0 | $expectedExcursionStartDate === 0.0)) {
            $validationMessage[] = 'expectedExcursionStartDate is a required field';
        }
    }

    if (empty($expectedGroupMembersCount)) {
        if (!($expectedGroupMembersCount === '0' | $expectedGroupMembersCount === 0 | $expectedGroupMembersCount === 0.0)) {
            $validationMessage[] = 'expectedGroupMembersCount is a required field';
        }
    }

    if (empty($language)) {
        if (!($language === '0' | $language === 0 | $language === 0.0)) {
            $validationMessage[] = 'language is a required field';
        }
    }

    if (empty($initiator)) {
        if (!($initiator === '0' | $initiator === 0 | $initiator === 0.0)) {
            $validationMessage[] = 'initiator is a required field';
        }
    }

    // A political issue
    // if (empty($guide)) {
    //     if (!($guide === '0' | $guide === 0 | $guide === 0.0)) {
    //         $validationMessage[] = 'guide is a required field';
    //     }
    // }

    $checkedParams = checkRequestForExcursion($request);

    if (array_merge($checkedParams['validationMessage'], $validationMessage)) {
        return $response->withJson(array_merge($checkedParams['validationMessage'], $validationMessage), 400);
    }

    // Validate excursion status
    $status = (int)$checkedParams['status'];
    if (!\LinesC\Model\Excursion::isValidStatus($status)) {
        $status = \LinesC\Model\Excursion::STATUS_REGISTERED;
    }

    // Validate excursion rank
    $rank = (int)$checkedParams['rank'];
    if (!\LinesC\Model\Excursion::isValidRank($rank)) {
        $rank = \LinesC\Model\Excursion::RANK_DEFAULT;
    }

    // Validate excursion type
    $type = (int)$checkedParams['type'];
    if (!\LinesC\Model\Excursion::isValidType($type)) {
        $type = \LinesC\Model\Excursion::TYPE_GENERAL;
    }

    // Validate excursion isFree
    $isFree = (int)$checkedParams['isFree'];
    if (!\LinesC\Model\Excursion::isIsFreeState($isFree)) {
        $isFree = \LinesC\Model\Excursion::IS_FREE_NO;
    }

    // Validate excursion radioGuide
    $radioGuide = (int)$checkedParams['radioGuide'];
    if (!\LinesC\Model\Excursion::isValidRadioGuideState($radioGuide)) {
        $radioGuide = \LinesC\Model\Excursion::RADIO_GUIDE_NO;
    }

    // Create the model for the Excursion
    $excursion = new \LinesC\Model\Excursion($this->get('database'));
    $excursion->setSecureId(generateSecureId());
    $excursion->setGroupMembersCount((int)$checkedParams['groupMembersCount']);

    $expectedExcursionStartDate = (string)$checkedParams['expectedExcursionStartDate'];
    if (!empty($expectedExcursionStartDate)) {
        $excursion->setExpectedExcursionStartDate(new \DateTime($expectedExcursionStartDate));
    }

    $expectedExcursionStartTime = (string)$checkedParams['expectedExcursionStartTime'];
    if (!empty($expectedExcursionStartTime)) {
        $excursion->setExpectedExcursionStartTime(new \DateTime($expectedExcursionStartTime));
    }

    $excursion->setVerifyStartTimeInHours((int)$checkedParams['verifyStartTimeInHours']);
    $excursion->setExpectedDurationOfExcursion((int)$checkedParams['expectedDurationOfExcursion']);

    $excursionStartDate = (string)$checkedParams['excursionStartDate'];
    if (!empty($excursionStartDate)) {
        $excursion->setExcursionStartDate(new \DateTime($excursionStartDate));
    }

    $excursionStartTime = (string)$checkedParams['excursionStartTime'];
    if (!empty($excursionStartTime)) {
        $excursion->setExcursionStartTime(new \DateTime($excursionStartTime));
    }

    $excursionEndTime = (string)$checkedParams['excursionEndTime'];
    if (!empty($excursionEndTime)) {
        $excursion->setExcursionEndTime(new \DateTime($excursionEndTime));
    }

    $excursion->setCountry((string)$checkedParams['country']);
    $excursion->setDescription((string)$checkedParams['description']);
    $excursion->setExpectedGroupMembersCount((int)$checkedParams['expectedGroupMembersCount']);
    $excursion->setRadioGuide($radioGuide);
    $excursion->setIsFree($isFree);
    $excursion->setAdditionalInfo((string)$checkedParams['additionalInfo']);
    $excursion->setType($type);
    $excursion->setRank($rank);
    $excursion->setStatus($status);

    try {
        // Get database object
        $db = $excursion->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();
        $excursionId = $excursion->insert();

        // Fix current date and time
        $dateTime = new \DateTime();
        $dateTime = $dateTime->format('Y-m-d H:i:s');

        // Find language by it's secure id
        $languageModel = new \LinesC\Model\Language($db);
        $languageId = $languageModel->findBy('secure_id', $language)->toArray()['language_id'];

        // Prepare sql for creating the association between language and excursion
        $languageExcursionSQL = 'INSERT INTO language_excursion_associations (language_id, excursion_id, language_excursion_associations_created_date, language_excursion_associations_updated_date) VALUES (?, ?, ?, ?)';
        $languageExcursionSTMT = $db->prepare($languageExcursionSQL);
        $languageExcursionSTMT->execute([$languageId, $excursionId, $dateTime, $dateTime]);

        // Find initiator by it's secure id
        $initiatorModel = new \LinesC\Model\Initiator($db);
        $initiatorModel = $initiatorModel->findBy('secure_id', $initiator);
        if (!is_null($initiatorModel)) {
            $initiatorId = $initiatorModel->toArray()['initiator_id'];
        } else {
            $initiatorModel = new \LinesC\Model\Initiator($db);

            $initiatorModel->setSecureId(generateSecureId());
            $initiatorModel->setName((string)$initiator);
            $initiatorModel->setAdditionalInfo('Quick Creation');
            $initiatorModel->setType(\LinesC\Model\Initiator::TYPE_GENERAL);
            $initiatorModel->setRank(\LinesC\Model\Initiator::RANK_DEFAULT);
            $initiatorModel->setStatus(\LinesC\Model\Initiator::STATUS_ACTIVE);
            $initiatorModel->setIdentifier(0);

            $initiatorId = $initiatorModel->insert();
            // dd($initiatorId);
        }

        // Prepare sql for creating the association between initiator and excursion
        $initiatorExcursionSQL = 'INSERT INTO excursion_initiator_associations (excursion_id, initiator_id, excursion_initiator_associations_created_date, excursion_initiator_associations_updated_date) VALUES (?, ?, ?, ?)';
        $initiatorExcursionSTMT = $db->prepare($initiatorExcursionSQL);
        $initiatorExcursionSTMT->execute([$excursionId, $initiatorId, $dateTime, $dateTime]);

        // If guide is presented
        if (!empty($guide)) {
            if (!($guide === '0' | $guide === 0 | $guide === 0.0)) {
              // Find guide by it's secure id
              $guideModel = new \LinesC\Model\Guide($db);
              $guideId = $guideModel->findBy('secure_id', $guide)->toArray()['guide_id'];

              // Prepare sql for creating the association between guide and excursion
              $guideExcursionSQL = 'INSERT INTO guide_excursion_associations (guide_id, excursion_id, guide_excursion_associations_created_date, guide_excursion_associations_updated_date) VALUES (?, ?, ?, ?)';
              $guideExcursionSTMT = $db->prepare($guideExcursionSQL);
              $guideExcursionSTMT->execute([$guideId, $excursionId, $dateTime, $dateTime]);
            }
        }

        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    $responseCode = 500;
    if ($excursionId) {
        $response = $response->withHeader('Location', '/excursions/' . $excursionId);
        $responseCode = 201;
    }

    return $response->withStatus($responseCode);
});

/**
 * Create a new record for Excursion
 *
 * POST /excursions
 */
$app->post('/excursions', function ($request, $response) {
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
    $expectedExcursionStartDate = filter_var($request->getParam('expectedExcursionStartDate'), FILTER_SANITIZE_STRING);
    $expectedGroupMembersCount = filter_var($request->getParam('expectedGroupMembersCount'), FILTER_SANITIZE_NUMBER_INT);

    $validationMessage = [];

    if (empty($expectedExcursionStartDate)) {
        if (!($expectedExcursionStartDate === '0' | $expectedExcursionStartDate === 0 | $expectedExcursionStartDate === 0.0)) {
            $validationMessage[] = 'expectedExcursionStartDate is a required field';
        }
    }

    if (empty($expectedGroupMembersCount)) {
        if (!($expectedGroupMembersCount === '0' | $expectedGroupMembersCount === 0 | $expectedGroupMembersCount === 0.0)) {
            $validationMessage[] = 'expectedGroupMembersCount is a required field';
        }
    }

    $checkedParams = checkRequestForExcursion($request);

    if (array_merge($checkedParams['validationMessage'], $validationMessage)) {
        return $response->withJson(array_merge($checkedParams['validationMessage'], $validationMessage), 400);
    }

    // Validate excursion status
    $status = (int)$checkedParams['status'];
    if (!\LinesC\Model\Excursion::isValidStatus($status)) {
        $status = \LinesC\Model\Excursion::STATUS_REGISTERED;
    }

    // Validate excursion rank
    $rank = (int)$checkedParams['rank'];
    if (!\LinesC\Model\Excursion::isValidRank($rank)) {
        $rank = \LinesC\Model\Excursion::RANK_DEFAULT;
    }

    // Validate excursion type
    $type = (int)$checkedParams['type'];
    if (!\LinesC\Model\Excursion::isValidType($type)) {
        $type = \LinesC\Model\Excursion::TYPE_GENERAL;
    }

    // Validate excursion isFree
    $isFree = (int)$checkedParams['isFree'];
    if (!\LinesC\Model\Excursion::isIsFreeState($isFree)) {
        $isFree = \LinesC\Model\Excursion::IS_FREE_NO;
    }

    // Validate excursion radioGuide
    $radioGuide = (int)$checkedParams['radioGuide'];
    if (!\LinesC\Model\Excursion::isValidRadioGuideState($radioGuide)) {
        $radioGuide = \LinesC\Model\Excursion::RADIO_GUIDE_NO;
    }

    // Create the model for the Excursion
    $excursion = new \LinesC\Model\Excursion($this->get('database'));
    $excursion->setSecureId(generateSecureId());
    $excursion->setGroupMembersCount((int)$checkedParams['groupMembersCount']);

    $expectedExcursionStartDate = (string)$checkedParams['expectedExcursionStartDate'];
    if (!empty($expectedExcursionStartDate)) {
        $excursion->setExpectedExcursionStartDate(new \DateTime($expectedExcursionStartDate));
    }

    $expectedExcursionStartTime = (string)$checkedParams['expectedExcursionStartTime'];
    if (!empty($expectedExcursionStartTime)) {
        $excursion->setExpectedExcursionStartTime(new \DateTime($expectedExcursionStartTime));
    }

    $excursion->setVerifyStartTimeInHours((int)$checkedParams['verifyStartTimeInHours']);
    $excursion->setExpectedDurationOfExcursion((int)$checkedParams['expectedDurationOfExcursion']);

    $excursionStartDate = (string)$checkedParams['excursionStartDate'];
    if (!empty($excursionStartDate)) {
        $excursion->setExcursionStartDate(new \DateTime($excursionStartDate));
    }

    $excursionStartTime = (string)$checkedParams['excursionStartTime'];
    if (!empty($excursionStartTime)) {
        $excursion->setExcursionStartTime(new \DateTime($excursionStartTime));
    }

    $excursionEndTime = (string)$checkedParams['excursionEndTime'];
    if (!empty($excursionEndTime)) {
        $excursion->setExcursionEndTime(new \DateTime($excursionEndTime));
    }

    $excursion->setCountry((string)$checkedParams['country']);
    $excursion->setDescription((string)$checkedParams['description']);
    $excursion->setExpectedGroupMembersCount((int)$checkedParams['expectedGroupMembersCount']);
    $excursion->setRadioGuide($radioGuide);
    $excursion->setIsFree($isFree);
    $excursion->setAdditionalInfo((string)$checkedParams['additionalInfo']);
    $excursion->setType($type);
    $excursion->setRank($rank);
    $excursion->setStatus($status);

    try {
        // Get database object
        $db = $excursion->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();
        $excursionId = $excursion->insert();
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    $responseCode = 500;
    if ($excursionId) {
        $response = $response->withHeader('Location', '/excursions/' . $excursionId);
        $responseCode = 201;
    }

    return $response->withStatus($responseCode);
});

/**
 * Update a specific Excursion by id or secure id
 *
 * PUT /excursions/{id}
 */
$app->put('/excursions/{id}', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('update', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    $language = filter_var($request->getParam('language'), FILTER_SANITIZE_STRING); // secure_id
    $initiator = filter_var($request->getParam('initiator'), FILTER_SANITIZE_STRING); // secure_id
    $guide = filter_var($request->getParam('guide'), FILTER_SANITIZE_STRING); // secure_id

    /**
     * Sanitize input
     */
    $checkedParams = checkRequestForExcursion($request);

    if ($checkedParams['validationMessage']) {
        return $response->withJson($checkedParams['validationMessage'], 400);
    }

    try {
        $excursion = new \LinesC\Model\Excursion($this->get('database'));

        if (!(is_numeric($args['id']) && $excursion->find($args['id']))) {
            // Try to fetch the excursion by the secure id
            $excursion = $excursion->findBy('secure_id', $args['id']);
        }

        if (!$excursion) {
            return $response->withStatus(404);
        }
    } catch (PDOException $e) {
        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    try {
        $groupMembersCount = (int)$checkedParams['groupMembersCount'];
        if (!empty($groupMembersCount)) {
            $excursion->setGroupMembersCount($groupMembersCount);
        }

        $expectedExcursionStartDate = (string)$checkedParams['expectedExcursionStartDate'];
        if (!empty($expectedExcursionStartDate)) {
            $excursion->setExpectedExcursionStartDate(new \DateTime($expectedExcursionStartDate));
        }

        $expectedExcursionStartTime = (string)$checkedParams['expectedExcursionStartTime'];
        if ($expectedExcursionStartTime == 'delete_expected_excursion_start_time') {
            $excursion->emptyExpectedExcursionStartTime();
        } else if (!empty($expectedExcursionStartTime)) {
            $excursion->setExpectedExcursionStartTime(new \DateTime($expectedExcursionStartTime));
        }

        $verifyStartTimeInHours = (int)$checkedParams['verifyStartTimeInHours'];
        if (!empty($verifyStartTimeInHours)) {
            $excursion->setVerifyStartTimeInHours($verifyStartTimeInHours);
        }

        $expectedDurationOfExcursion = (int)$checkedParams['expectedDurationOfExcursion'];
        if (!empty($expectedDurationOfExcursion)) {
            $excursion->setExpectedDurationOfExcursion($expectedDurationOfExcursion);
        }

        $excursionStartDate = (string)$checkedParams['excursionStartDate'];
        if ($excursionStartDate == 'delete_excursion_start_date') {
            $excursion->emptyExcursionStartDate();
        } else if (!empty($excursionStartDate)) {
            $excursion->setExcursionStartDate(new \DateTime($excursionStartDate));
        }

        $excursionStartTime = (string)$checkedParams['excursionStartTime'];
        if ($excursionStartTime == 'delete_excursion_start_time') {
            $excursion->emptyExcursionStartTime();
        } else if (!empty($excursionStartTime)) {
            $excursion->setExcursionStartTime(new \DateTime($excursionStartTime));
        }

        $excursionEndTime = (string)$checkedParams['excursionEndTime'];
        if ($excursionEndTime == 'delete_excursion_end_time') {
            $excursion->emptyExcursionEndTime();
        } else if (!empty($excursionEndTime)) {
            $excursion->setExcursionEndTime(new \DateTime($excursionEndTime));
        }

        $country = (string)$checkedParams['country'];
        if ($country == 'delete_country') {
            $excursion->setCountry('');
        } else if (!empty($country)) {
            $excursion->setCountry($country);
        }

        $description = (string)$checkedParams['description'];
        if ($description == 'delete_description') {
            $excursion->setDescription('');
        } else if (!empty($description)) {
            $excursion->setDescription($description);
        }

        $expectedGroupMembersCount = (int)$checkedParams['expectedGroupMembersCount'];
        if (!empty($expectedGroupMembersCount)) {
            $excursion->setExpectedGroupMembersCount($expectedGroupMembersCount);
        }

        $radioGuide = (int)$checkedParams['radioGuide'];
        if (!empty($radioGuide)) {
            if (!\LinesC\Model\Excursion::isValidRadioGuideState($radioGuide)) {
                $radioGuide = \LinesC\Model\Excursion::RADIO_GUIDE_NO;
            }

            $excursion->setRadioGuide($radioGuide);
        }

        $isFree = (int)$checkedParams['isFree'];
        if (!empty($isFree)) {
            if (!\LinesC\Model\Excursion::isIsFreeState($isFree)) {
                $isFree = \LinesC\Model\Excursion::IS_FREE_NO;
            }

            $excursion->setIsFree($isFree);
        }

        $additionalInfo = (string)$checkedParams['additionalInfo'];
        if (!empty($additionalInfo)) {
            $excursion->setAdditionalInfo($additionalInfo);
        }

        $type = (int)$checkedParams['type'];
        if (!empty($type)) {
            if (!\LinesC\Model\Excursion::isValidType($type)) {
                $type = \LinesC\Model\Excursion::TYPE_GENERAL;
            }

            $excursion->setType($type);
        }

        $rank = (int)$checkedParams['rank'];
        if (!empty($rank)) {
            if (!\LinesC\Model\Excursion::isValidRank($rank)) {
                $rank = \LinesC\Model\Excursion::RANK_DEFAULT;
            }

            $excursion->setRank($rank);
        }

        $status = (int)$checkedParams['status'];
        if (!empty($status)) {
            if (!\LinesC\Model\Excursion::isValidStatus($status)) {
                $status = \LinesC\Model\Excursion::STATUS_REGISTERED;
            }

            $excursion->setStatus($status);
        }

        // Get database object
        $db = $excursion->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();

        $excursion->update();
        $excursionId = $excursion->toArray()["excursion_id"];

        // Fix current date and time
        $dateTime = new \DateTime();
        $dateTime = $dateTime->format('Y-m-d H:i:s');

        if (!empty($language)) {
            // Prepare sql for deleting language excursion associations
            $deleteLanguageSql = 'DELETE FROM language_excursion_associations WHERE excursion_id = ?';

            $deleteLanguageStmt = $db->prepare($deleteLanguageSql);
            $deleteLanguageStmt->execute([$excursionId]);

            // Find language by it's secure id
            $languageModel = new \LinesC\Model\Language($db);
            $languageId = $languageModel->findBy('secure_id', $language)->toArray()['language_id'];

            // Prepare sql for creating the association between language and excursion
            $languageExcursionSQL = 'INSERT INTO language_excursion_associations (language_id, excursion_id, language_excursion_associations_created_date, language_excursion_associations_updated_date) VALUES (?, ?, ?, ?)';
            $languageExcursionSTMT = $db->prepare($languageExcursionSQL);
            $languageExcursionSTMT->execute([$languageId, $excursionId, $dateTime, $dateTime]);
        }

        if (!empty($initiator)) {
            // Prepare sql for deleting initiator excursion associations
            $deleteInitiatorSql = 'DELETE FROM excursion_initiator_associations WHERE excursion_id = ?';

            $deleteInitiatorStmt = $db->prepare($deleteInitiatorSql);
            $deleteInitiatorStmt->execute([$excursionId]);

            // Find initiator by it's secure id
            $initiatorModel = new \LinesC\Model\Initiator($db);
            $initiatorId = $initiatorModel->findBy('secure_id', $initiator)->toArray()['initiator_id'];

            // Prepare sql for creating the association between initiator and excursion
            $initiatorExcursionSQL = 'INSERT INTO excursion_initiator_associations (excursion_id, initiator_id, excursion_initiator_associations_created_date, excursion_initiator_associations_updated_date) VALUES (?, ?, ?, ?)';
            $initiatorExcursionSTMT = $db->prepare($initiatorExcursionSQL);
            $initiatorExcursionSTMT->execute([$excursionId, $initiatorId, $dateTime, $dateTime]);
        }

        if ($guide == 'delete_guide') {
            // Prepare sql for deleting guide excursion associations
            $deleteGuideSql = 'DELETE FROM guide_excursion_associations WHERE excursion_id = ?';

            $deleteGuideStmt = $db->prepare($deleteGuideSql);
            $deleteGuideStmt->execute([$excursionId]);
        } else if (!empty($guide)) {
            // Prepare sql for deleting guide excursion associations
            $deleteGuideSql = 'DELETE FROM guide_excursion_associations WHERE excursion_id = ?';

            $deleteGuideStmt = $db->prepare($deleteGuideSql);
            $deleteGuideStmt->execute([$excursionId]);

            // Find guide by it's secure id
            $guideModel = new \LinesC\Model\Guide($db);
            $guideId = $guideModel->findBy('secure_id', $guide)->toArray()['guide_id'];

            // Prepare sql for creating the association between guide and excursion
            $guideExcursionSQL = 'INSERT INTO guide_excursion_associations (guide_id, excursion_id, guide_excursion_associations_created_date, guide_excursion_associations_updated_date) VALUES (?, ?, ?, ?)';
            $guideExcursionSTMT = $db->prepare($guideExcursionSQL);
            $guideExcursionSTMT->execute([$guideId, $excursionId, $dateTime, $dateTime]);
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
 * Delete data for Excursion
 *
 * DELETE /excursions/{id}
 */
$app->delete('/excursions/{id:[0-9]+}', function ($request, $response, $args) {
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
        $excursion = new \LinesC\Model\Excursion($this->get('database'));

        // Get database object
        $db = $excursion->getDatabase();

        // Begin transaction and commit the changes
        $db->beginTransaction();

        // Get the Excursion with given id
        if ($excursion->find($args['id'])) {
            $delete = $excursion->delete();
        }

        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    if ($delete) {
        return $response->withJson(['message' => 'The excursion with Id ' . $args['id'] . ' is deleted successfully'], 204);
    }

    return $response->withStatus(404);
});

/**
 * Get all Initiators associated with a specific Excursion
 *
 * GET /excursions/{id}/initiators
 */
$app->get('/excursions/{id:[0-9]+}/initiators', function ($request, $response, $args) {
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
        $sql = 'SELECT initiators.* FROM excursion_initiator_associations
                JOIN initiators ON excursion_initiator_associations.initiator_id = initiators.initiator_id
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
 * Get all Excursions associated with a specific Initiator
 *
 * GET /initiators/{id}/excursions
 */
$app->get('/initiators/{id:[0-9]+}/excursions', function ($request, $response, $args) {
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
        $sql = 'SELECT excursions.* FROM excursion_initiator_associations
                JOIN excursions ON excursion_initiator_associations.excursion_id = excursions.excursion_id
                WHERE initiator_id = ?';

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
 * Associate a Excursion with a Initiator
 *
 * POST /excursions/{excursionId}/initiators/{initiatorId}
 */
$app->post('/excursions/{excursionId:[0-9]+}/initiators/{initiatorId:[0-9]+}', function ($request, $response, $args) {
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
        $sql = 'INSERT INTO excursion_initiator_associations (excursion_id, initiator_id, excursion_initiator_associations_created_date, excursion_initiator_associations_updated_date) VALUES (?, ?, ?, ?)';

        $stmt = $db->prepare($sql);
        $stmt->execute([$args['excursionId'], $args['initiatorId'], $dateTime, $dateTime]);
        $db->commit();
    } catch (PDOException $e) {
        // Revert changes
        $db->rollBack();

        return $response->withJson(['message' => $e->getMessage()], 500);
    }

    return $response->withStatus(201);
});


/**
 * Delete all the Initiators associated with a specific Excursion
 *
 * DELETE /excursions/{id}/initiators
 */
$app->delete('/excursions/{id:[0-9]+}/initiators', function ($request, $response, $args) {
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
        $sql = 'DELETE FROM excursion_initiator_associations WHERE excursion_id = ?';

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
 * Delete all the Excursions associated with a specific Initiator
 *
 * DELETE /initiators/{id}/excursions
 */
$app->delete('/initiators/{id:[0-9]+}/excursions', function ($request, $response, $args) {
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
        $sql = 'DELETE FROM excursion_initiator_associations WHERE initiator_id = ?';

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
 * Fetch excursion types
 *
 * GET /excursion-types
 */
$app->get('/excursion-types', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    return $response->withJson(\LinesC\Model\Excursion::getTypes(), 200);
});

/**
 * Fetch excursion Statuses
 *
 * GET /excursion-statuses
 */
$app->get('/excursion-statuses', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    return $response->withJson(\LinesC\Model\Excursion::getStatuses(), 200);
});

/**
 * Fetch excursion radio guide states
 *
 * GET /excursion-radio-guide-states
 */
$app->get('/excursion-radio-guide-states', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    return $response->withJson(\LinesC\Model\Excursion::getRadioGuideStates(), 200);
});

/**
 * Fetch excursion is free states
 *
 * GET /excursion-is-free-states
 */
$app->get('/excursion-is-free-states', function ($request, $response, $args) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */

    /**
     * Authorize input
     */
    $jwt = $request->getAttribute('jwt');
    if (!in_array('read', $jwt['scope'])) {
        return $response->withStatus(405);
    }

    return $response->withJson(\LinesC\Model\Excursion::getIsFreeStates(), 200);
});


/**
 * Get all guides with corresponding excursions count, duration and tourist count
 *
 * GET /activity-report
 */
$app->get('/activity-report', function ($request, $response, $args) {
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

    $start = filter_var($request->getParam('start'), FILTER_SANITIZE_STRING);
    $end = filter_var($request->getParam('end'), FILTER_SANITIZE_STRING);
    $language = filter_var($request->getParam('language'), FILTER_SANITIZE_STRING);

    $validationMessage = [];

    if (empty($start)) {
        if (!($start === '0' | $start === 0 | $start === 0.0)) {
            $validationMessage[] = 'start is a required field';
        }
    }

    if (empty($end)) {
        if (!($end === '0' | $end === 0 | $end === 0.0)) {
            $validationMessage[] = 'end is a required field';
        }
    }

    $checkedParams = checkRequestForExcursion($request);

    if (array_merge($checkedParams['validationMessage'], $validationMessage)) {
        return $response->withJson(array_merge($checkedParams['validationMessage'], $validationMessage), 400);
    }

    try {
        $db = $this->get('database');
        $logger = $this->get('logger');

        // Prepare sql
        $sql = 'SELECT tmp.guideId, tmp.guideFirstName, tmp.guideLastName, guidePosition, SUM(groupMembersCount) AS tourists, COUNT(excursion_id) AS excursions, CEIL(SUM(time)) AS time
        FROM (SELECT IF(excursions.group_members_count = 0, excursions.expected_group_members_count, excursions.group_members_count) AS groupMembersCount,
        TIME_TO_SEC(TIMEDIFF(excursions.excursion_end_time, excursions.excursion_start_time)) / 60 AS time, excursions.excursion_id, guides.first_name AS guideFirstName, guides.last_name AS guideLastName, guides.position AS guidePosition, guides.secure_id AS guideId
        FROM excursions
        LEFT JOIN guide_excursion_associations ON guide_excursion_associations.excursion_id = excursions.excursion_id
        LEFT JOIN guides ON guide_excursion_associations.guide_id = guides.guide_id
        LEFT JOIN language_excursion_associations ON language_excursion_associations.excursion_id = excursions.excursion_id
        LEFT JOIN languages ON language_excursion_associations.language_id = languages.language_id
        LEFT JOIN excursion_initiator_associations ON excursion_initiator_associations.excursion_id = excursions.excursion_id
        LEFT JOIN initiators ON excursion_initiator_associations.initiator_id = initiators.initiator_id';

        $clause[] = "excursions.status = ?";
        $bind[] = \LinesC\Model\Excursion::STATUS_FINISHED;

        if (!empty($start) && !empty($end)) {
            $clause[] = 'excursions.excursion_start_date BETWEEN ? AND ?';
            $bind[] = $start;
            $bind[] = $end;
        }

        if (!empty($language)) {
            $clause[] = 'languages.secure_id = ?';
            $bind[] = $language;
        }

        if ($clause) {
            $sql .= ' WHERE ' . implode(' AND ', $clause). ') AS tmp';
        }

        $sql .= ' GROUP BY tmp.guideId';

        $stmt = $db->prepare($sql);
        $stmt->execute($bind);

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $logger->error($e->getMessage());
        $logger->error($e->getTraceAsString());

        trigger503Response();
    }

    return $response->withJson($result, 200);
});

/**
 * Get excursions count, duration and tourist count as time series
 *
 * GET /excursions-time-series-report
 */
$app->get('/excursions-time-series-report', function ($request, $response, $args) {
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

    $start = filter_var($request->getParam('start'), FILTER_SANITIZE_STRING);
    $end = filter_var($request->getParam('end'), FILTER_SANITIZE_STRING);
    $language = filter_var($request->getParam('language'), FILTER_SANITIZE_STRING);

    $validationMessage = [];

    if (empty($start)) {
        if (!($start === '0' | $start === 0 | $start === 0.0)) {
            $validationMessage[] = 'start is a required field';
        }
    }

    if (empty($end)) {
        if (!($end === '0' | $end === 0 | $end === 0.0)) {
            $validationMessage[] = 'end is a required field';
        }
    }

    $checkedParams = checkRequestForExcursion($request);

    if (array_merge($checkedParams['validationMessage'], $validationMessage)) {
        return $response->withJson(array_merge($checkedParams['validationMessage'], $validationMessage), 400);
    }

    try {
        $db = $this->get('database');
        $logger = $this->get('logger');

        // Prepare sql
        $sql = 'SELECT COUNT(excursion_id) AS excursions, SUM(groupMembersCount) AS tourists, CEIL(SUM(time)) AS time, tmp.excursion_start_date, tmp.languageName
        FROM (SELECT IF(excursions.group_members_count = 0, excursions.expected_group_members_count, excursions.group_members_count) AS groupMembersCount,
        TIME_TO_SEC(TIMEDIFF(excursions.excursion_end_time, excursions.excursion_start_time)) / 60 AS time, excursions.excursion_start_date, excursions.country, languages.secure_id AS languageId, languages.name AS languageName, excursions.excursion_id
        FROM excursions
        LEFT JOIN language_excursion_associations ON language_excursion_associations.excursion_id = excursions.excursion_id
        LEFT JOIN languages ON language_excursion_associations.language_id = languages.language_id';

        $clause[] = "excursions.status = ?";
        $bind[] = \LinesC\Model\Excursion::STATUS_FINISHED;

        if (!empty($start) && !empty($end)) {
            $clause[] = 'excursions.excursion_start_date BETWEEN ? AND ?';
            $bind[] = $start;
            $bind[] = $end;
        }

        if (!empty($language)) {
            $clause[] = 'languages.secure_id = ?';
            $bind[] = $language;
        }

        if ($clause) {
            $sql .= ' WHERE ' . implode(' AND ', $clause). ') AS tmp';
        }

        $sql .= ' GROUP BY tmp.excursion_start_date, languageName';

        $stmt = $db->prepare($sql);
        $stmt->execute($bind);

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $logger->error($e->getMessage());
        $logger->error($e->getTraceAsString());

        trigger503Response();
    }

    return $response->withJson($result, 200);
});
