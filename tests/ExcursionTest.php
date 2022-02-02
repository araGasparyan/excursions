<?php
/**
 * Tests for Excursion endpoints
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

namespace Tests;

use LinesC\Model\Excursion;

class ExcursionTest extends BaseTestCase
{
    /**
     * Excursion object
     *
     * @var \LinesC\Model\Excursion
     */
    protected static $excursion;

    /**
     * Called before each test in this class
     */
    protected function setUp()
    {
        $stmt = self::$db->exec('TRUNCATE initiators;');
        $stmt = self::$db->exec('TRUNCATE appearances;');
        $stmt = self::$db->exec('TRUNCATE guides;');
        $stmt = self::$db->exec('TRUNCATE guide_language_associations;');
        $stmt = self::$db->exec('TRUNCATE guide_excursion_associations;');
        $stmt = self::$db->exec('TRUNCATE guide_appearance_associations;');
        $stmt = self::$db->exec('TRUNCATE languages;');
        $stmt = self::$db->exec('TRUNCATE language_excursion_associations;');
        $stmt = self::$db->exec('TRUNCATE excursions;');
        $stmt = self::$db->exec('TRUNCATE excursion_initiator_associations;');
        self::$excursion = new Excursion(self::$db);
    }

    /**
     * Called after each test in this class
     */
    protected function tearDown()
    {
        $stmt = self::$db->exec('TRUNCATE initiators;');
        $stmt = self::$db->exec('TRUNCATE appearances;');
        $stmt = self::$db->exec('TRUNCATE guides;');
        $stmt = self::$db->exec('TRUNCATE guide_language_associations;');
        $stmt = self::$db->exec('TRUNCATE guide_excursion_associations;');
        $stmt = self::$db->exec('TRUNCATE guide_appearance_associations;');
        $stmt = self::$db->exec('TRUNCATE languages;');
        $stmt = self::$db->exec('TRUNCATE language_excursion_associations;');
        $stmt = self::$db->exec('TRUNCATE excursions;');
        $stmt = self::$db->exec('TRUNCATE excursion_initiator_associations;');
    }

    /**
     * Get object array
     */
    protected function getObjectArray()
    {
        return [
            'status' => '2',
            'type' => '1',
            'expectedExcursionStartTime' => '15:30:00',
            'groupMembersCount' => '50',
            'excursionStartDate' => '2020/05/04',
            'expectedDurationOfExcursion' => '90',
            'radioGuide' => '2',
            'secureId' => '4dhUwwXjtEUwitWvwXsj',
            'isFree' => '1',
            'expectedExcursionStartDate' => '2020/05/04',
            'expectedGroupMembersCount' => '47',
            'rank' => '1',
            'country' => 'zPChPK2FbR',
            'verifyStartTimeInHours' => '3',
            'excursionStartTime' => '15:40:00',
            'excursionEndTime' => '17:00:00',
            'additionalInfo' => 'no reason for changing',
            'description' => 'Group of 50 tourists from Angola',
        ];
    }

    /**
     * Inserts Excursions to db with given number
     *
     * @var int
     *
     * @return int
     */
    protected function insertExcursionsToDB(int $excursionsCount)
    {
        $excursion = self::$excursion;
        $excursionArray = $this->getObjectArray();

        for ($i = 1; $i <= $excursionsCount; ++$i) {
            $dateTime = new \DateTime();

            $excursion->setStatus($excursionArray['status']);
            $excursion->setType($excursionArray['type']);
            $excursion->setExpectedExcursionStartTime(new \DateTime($excursionArray['expectedExcursionStartTime']));
            $excursion->setGroupMembersCount($excursionArray['groupMembersCount']);
            $excursion->setExcursionStartDate(new \DateTime($excursionArray['excursionStartDate']));
            $excursion->setExpectedDurationOfExcursion($excursionArray['expectedDurationOfExcursion']);
            $excursion->setRadioGuide($excursionArray['radioGuide']);
            $excursion->setSecureId(generateSecureId());
            $excursion->setIsFree($excursionArray['isFree']);
            $excursion->setExpectedExcursionStartDate(new \DateTime($excursionArray['expectedExcursionStartDate']));
            $excursion->setExpectedGroupMembersCount($excursionArray['expectedGroupMembersCount']);
            $excursion->setRank($excursionArray['rank']);
            $excursion->setCountry($excursionArray['country']);
            $excursion->setVerifyStartTimeInHours($excursionArray['verifyStartTimeInHours']);
            $excursion->setExcursionStartTime(new \DateTime($excursionArray['excursionStartTime']));
            $excursion->setExcursionEndTime(new \DateTime($excursionArray['excursionEndTime']));
            $excursion->setAdditionalInfo($excursionArray['additionalInfo']);
            $excursion->setDescription($excursionArray['description']);
            $excursion->setCreatedDate($dateTime);
            $excursion->setUpdatedDate($dateTime);

            $excursion->insert();
        }

        return self::$db->lastInsertId();
    }

    /**
     * Tests the endpoint for fetching all the Excursions
     */
    public function testGetExcursions()
    {
        $page = 4;
        $perPage = 3;
        $excursionsCount = 20;

        $excursionArray = $this->getObjectArray();

        $queryString = http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);

        $this->insertExcursionsToDB($excursionsCount);

        $response = $this->runApp('GET', '/excursions?' . $queryString);
        $this->assertEquals(200, $response->getStatusCode());

        // Let us test pagination firstly
        $resp = json_decode((string) $response->getBody());
        $meta = $resp->meta;

        $this->assertEquals($page, $meta->page);
        $this->assertEquals($perPage, $meta->perPage);
        $this->assertEquals(1, $meta->first_page);
        $this->assertEquals($page - 1, $meta->prev_page);
        $this->assertEquals($page + 1, $meta->next_page);
        $this->assertEquals(ceil($excursionsCount / $perPage), $meta->last_page);
        $this->assertEquals($excursionsCount, $meta->total);

        // Let us continue with testing data
        $excursion = $resp->data[0];
        $this->assertNotNull($excursion->secure_id);
        $this->assertEquals($excursionArray['groupMembersCount'], $excursion->group_members_count);
        $this->assertEquals(new \DateTime($excursionArray['expectedExcursionStartDate']), new \DateTime($excursion->expected_excursion_start_date));
        $this->assertEquals(new \DateTime($excursionArray['expectedExcursionStartTime']), new \DateTime($excursion->expected_excursion_start_time));
        $this->assertEquals($excursionArray['verifyStartTimeInHours'], $excursion->verify_start_time_in_hours);
        $this->assertEquals($excursionArray['expectedDurationOfExcursion'], $excursion->expected_duration_of_excursion);
        $this->assertEquals(new \DateTime($excursionArray['excursionStartDate']), new \DateTime($excursion->excursion_start_date));
        $this->assertEquals(new \DateTime($excursionArray['excursionStartTime']), new \DateTime($excursion->excursion_start_time));
        $this->assertEquals(new \DateTime($excursionArray['excursionEndTime']), new \DateTime($excursion->excursion_end_time));
        $this->assertEquals($excursionArray['country'], $excursion->country);
        $this->assertEquals($excursionArray['description'], $excursion->description);
        $this->assertEquals($excursionArray['expectedGroupMembersCount'], $excursion->expected_group_members_count);
        $this->assertEquals($excursionArray['radioGuide'], $excursion->radio_guide);
        $this->assertEquals($excursionArray['isFree'], $excursion->is_free);
        $this->assertEquals($excursionArray['additionalInfo'], $excursion->additional_info);
        $this->assertEquals($excursionArray['type'], $excursion->type);
        $this->assertEquals($excursionArray['rank'], $excursion->rank);
        $this->assertEquals($excursionArray['status'], $excursion->status);
    }

    /**
     * Tests the endpoint for fetching a specific Excursion
     */
    public function testGetSpecificExcursion()
    {
        $lastInsertedId = $this->insertExcursionsToDB(1);
        $excursionArray = $this->getObjectArray();

        // Let us test successful fetching of the created Excursion
        $response = $this->runApp('GET', '/excursions/' . $lastInsertedId);
        $this->assertEquals(200, $response->getStatusCode());

        $excursion = json_decode((string) $response->getBody());
        $this->assertNotNull($excursion->secure_id);
        $this->assertEquals($excursionArray['groupMembersCount'], $excursion->group_members_count);
        $this->assertEquals(new \DateTime($excursionArray['expectedExcursionStartDate']), new \DateTime($excursion->expected_excursion_start_date));
        $this->assertEquals(new \DateTime($excursionArray['expectedExcursionStartTime']), new \DateTime($excursion->expected_excursion_start_time));
        $this->assertEquals($excursionArray['verifyStartTimeInHours'], $excursion->verify_start_time_in_hours);
        $this->assertEquals($excursionArray['expectedDurationOfExcursion'], $excursion->expected_duration_of_excursion);
        $this->assertEquals(new \DateTime($excursionArray['excursionStartDate']), new \DateTime($excursion->excursion_start_date));
        $this->assertEquals(new \DateTime($excursionArray['excursionStartTime']), new \DateTime($excursion->excursion_start_time));
        $this->assertEquals(new \DateTime($excursionArray['excursionEndTime']), new \DateTime($excursion->excursion_end_time));
        $this->assertEquals($excursionArray['country'], $excursion->country);
        $this->assertEquals($excursionArray['description'], $excursion->description);
        $this->assertEquals($excursionArray['expectedGroupMembersCount'], $excursion->expected_group_members_count);
        $this->assertEquals($excursionArray['radioGuide'], $excursion->radio_guide);
        $this->assertEquals($excursionArray['isFree'], $excursion->is_free);
        $this->assertEquals($excursionArray['additionalInfo'], $excursion->additional_info);
        $this->assertEquals($excursionArray['type'], $excursion->type);
        $this->assertEquals($excursionArray['rank'], $excursion->rank);
        $this->assertEquals($excursionArray['status'], $excursion->status);

        // Let us test 404 status code
        $response = $this->runApp('GET', '/excursions/' . ($lastInsertedId + 1));
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for deleting a specific Excursion
     */
    public function testDeleteSpecificExcursion()
    {
        $lastInsertedId = $this->insertExcursionsToDB(1);

        // Check that Excursion is in the DB
        $excursion = self::$excursion;
        $this->assertNotFalse($excursion->find($lastInsertedId));

        // Let us test successful deleting of the created Excursion
        $response = $this->runApp('DELETE', '/excursions/' . $lastInsertedId);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertFalse($excursion->find($lastInsertedId));

        // Let us test 404 status code
        $response = $this->runApp('DELETE', '/excursions/' . $lastInsertedId);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for creating a Excursion
     */
    public function testCreateExcursion()
    {
        $excursionArray = $this->getObjectArray();

        $queryString = http_build_query($excursionArray);

        // Let's test successful creating of a Excursion
        $response = $this->runApp('POST', '/excursions?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        // Let's use Location Header for fetching just created Excursion
        $excursion = $this->runApp('GET', $response->getHeaders()['Location'][0]);
        $excursion = json_decode((string) $excursion->getBody());

        $this->assertNotNull($excursion->secure_id);
        $this->assertEquals($excursionArray['groupMembersCount'], $excursion->group_members_count);
        $this->assertEquals(new \DateTime($excursionArray['expectedExcursionStartDate']), new \DateTime($excursion->expected_excursion_start_date));
        $this->assertEquals(new \DateTime($excursionArray['expectedExcursionStartTime']), new \DateTime($excursion->expected_excursion_start_time));
        $this->assertEquals($excursionArray['verifyStartTimeInHours'], $excursion->verify_start_time_in_hours);
        $this->assertEquals($excursionArray['expectedDurationOfExcursion'], $excursion->expected_duration_of_excursion);
        $this->assertEquals(new \DateTime($excursionArray['excursionStartDate']), new \DateTime($excursion->excursion_start_date));
        $this->assertEquals(new \DateTime($excursionArray['excursionStartTime']), new \DateTime($excursion->excursion_start_time));
        $this->assertEquals(new \DateTime($excursionArray['excursionEndTime']), new \DateTime($excursion->excursion_end_time));
        $this->assertEquals($excursionArray['country'], $excursion->country);
        $this->assertEquals($excursionArray['description'], $excursion->description);
        $this->assertEquals($excursionArray['expectedGroupMembersCount'], $excursion->expected_group_members_count);
        $this->assertEquals($excursionArray['radioGuide'], $excursion->radio_guide);
        $this->assertEquals($excursionArray['isFree'], $excursion->is_free);
        $this->assertEquals($excursionArray['additionalInfo'], $excursion->additional_info);
        $this->assertEquals($excursionArray['type'], $excursion->type);
        $this->assertEquals($excursionArray['rank'], $excursion->rank);
        $this->assertEquals($excursionArray['status'], $excursion->status);

    }

    /**
     * Tests the endpoint for updating a Excursion
     */
    public function testUpdateExcursion()
    {
        $excursionArray = $this->getObjectArray();

        $lastInsertedId = $this->insertExcursionsToDB(1);

        // Check that Excursion in the DB
        $excursion = self::$excursion;
        $excursion->find($lastInsertedId);
        $this->assertNotFalse($excursion);
        $this->assertNotNull($excursion->getSecureId());
        $this->assertEquals($excursionArray['groupMembersCount'], $excursion->getGroupMembersCount());
        $this->assertEquals(new \DateTime($excursionArray['expectedExcursionStartDate']), new \DateTime($excursion->getExpectedExcursionStartDate()));
        $this->assertEquals(new \DateTime($excursionArray['expectedExcursionStartTime']), new \DateTime($excursion->getExpectedExcursionStartTime()));
        $this->assertEquals($excursionArray['verifyStartTimeInHours'], $excursion->getVerifyStartTimeInHours());
        $this->assertEquals($excursionArray['expectedDurationOfExcursion'], $excursion->getExpectedDurationOfExcursion());
        $this->assertEquals(new \DateTime($excursionArray['excursionStartDate']), new \DateTime($excursion->getExcursionStartDate()));
        $this->assertEquals(new \DateTime($excursionArray['excursionStartTime']), new \DateTime($excursion->getExcursionStartTime()));
        $this->assertEquals(new \DateTime($excursionArray['excursionEndTime']), new \DateTime($excursion->getExcursionEndTime()));
        $this->assertEquals($excursionArray['country'], $excursion->getCountry());
        $this->assertEquals($excursionArray['description'], $excursion->getDescription());
        $this->assertEquals($excursionArray['expectedGroupMembersCount'], $excursion->getExpectedGroupMembersCount());
        $this->assertEquals($excursionArray['radioGuide'], $excursion->getRadioGuide());
        $this->assertEquals($excursionArray['isFree'], $excursion->getIsFree());
        $this->assertEquals($excursionArray['additionalInfo'], $excursion->getAdditionalInfo());
        $this->assertEquals($excursionArray['type'], $excursion->getType());
        $this->assertEquals($excursionArray['rank'], $excursion->getRank());
        $this->assertEquals($excursionArray['status'], $excursion->getStatus());

        $excursionUpdateArray = [
            'status' => '4',
            'type' => '1',
            'expectedExcursionStartTime' => '09:00:00',
            'groupMembersCount' => '1',
            'excursionStartDate' => '2020/05/06',
            'expectedDurationOfExcursion' => '40',
            'radioGuide' => '1',
            'secureId' => 'rqrgn5x2Df2wRR7gaKrv',
            'isFree' => '1',
            'expectedExcursionStartDate' => '2020/05/06',
            'expectedGroupMembersCount' => '3',
            'rank' => '1',
            'country' => 'AM',
            'verifyStartTimeInHours' => '2',
            'excursionStartTime' => '09:00:00',
            'excursionEndTime' => '09:45:00',
            'additionalInfo' => 'an old confilct between ',
            'description' => 'A tourist from France',
        ];

        $queryString = http_build_query($excursionUpdateArray);

        // Let's test updating of a Excursion which doesn't exist
        $response = $this->runApp('PUT', '/excursions/' . ($lastInsertedId + 1) . '?' . $queryString);
        $this->assertEquals(404, $response->getStatusCode());

        // Let's test successful updating of the Excursion
        $response = $this->runApp('PUT', "/excursions/$lastInsertedId?" . $queryString);
        $this->assertEquals(204, $response->getStatusCode());

        // Check that Excursion's fields are updated
        $excursion->find($lastInsertedId);
        $this->assertNotNull($excursion->getSecureId());
        $this->assertEquals($excursionUpdateArray['groupMembersCount'], $excursion->getGroupMembersCount());
        $this->assertEquals(new \DateTime($excursionUpdateArray['expectedExcursionStartDate']), new \DateTime($excursion->getExpectedExcursionStartDate()));
        $this->assertEquals(new \DateTime($excursionUpdateArray['expectedExcursionStartTime']), new \DateTime($excursion->getExpectedExcursionStartTime()));
        $this->assertEquals($excursionUpdateArray['verifyStartTimeInHours'], $excursion->getVerifyStartTimeInHours());
        $this->assertEquals($excursionUpdateArray['expectedDurationOfExcursion'], $excursion->getExpectedDurationOfExcursion());
        $this->assertEquals(new \DateTime($excursionUpdateArray['excursionStartDate']), new \DateTime($excursion->getExcursionStartDate()));
        $this->assertEquals(new \DateTime($excursionUpdateArray['excursionStartTime']), new \DateTime($excursion->getExcursionStartTime()));
        $this->assertEquals(new \DateTime($excursionUpdateArray['excursionEndTime']), new \DateTime($excursion->getExcursionEndTime()));
        $this->assertEquals($excursionUpdateArray['country'], $excursion->getCountry());
        $this->assertEquals($excursionUpdateArray['description'], $excursion->getDescription());
        $this->assertEquals($excursionUpdateArray['expectedGroupMembersCount'], $excursion->getExpectedGroupMembersCount());
        $this->assertEquals($excursionUpdateArray['radioGuide'], $excursion->getRadioGuide());
        $this->assertEquals($excursionUpdateArray['isFree'], $excursion->getIsFree());
        $this->assertEquals($excursionUpdateArray['additionalInfo'], $excursion->getAdditionalInfo());
        $this->assertEquals($excursionUpdateArray['type'], $excursion->getType());
        $this->assertEquals($excursionUpdateArray['rank'], $excursion->getRank());
        $this->assertEquals($excursionUpdateArray['status'], $excursion->getStatus());
    }

    /**
     * Tests the endpoints for Excursion Initiator associations
     */
    public function testExcursionInitiatorAssociations()
    {
        $excursionArray = $this->getObjectArray();

        $initiatorOneArray = [
            'website' => 'http://website.am',
            'status' => '4',
            'name' => 'Հյուր սերվիս',
            'secureId' => 'RkDLmpwFRCPCyx6UXELr',
            'rank' => '4',
            'additionalInfo' => 'A big tour agency in Armenia',
            'phone' => '010-10-99-888,094-10-99-888',
            'address' => 'Թումանյան 23, բն. 18',
            'identifier' => '100',
            'type' => '4',
            'email' => 'hyur@mail.am',
        ];

        $initiatorTwoArray = [
            'website' => 'http://website.com',
            'status' => '3',
            'name' => 'Գերմանիա',
            'secureId' => 'Ike7qJQfYF3ea4QA0IBg',
            'rank' => '1',
            'additionalInfo' => 'A group from Germany',
            'phone' => '5558549031045995888',
            'address' => 'Բաղրամյան',
            'identifier' => '101',
            'type' => '1',
            'email' => 'guesten@mail.de',
        ];

        // Let's test successful creating of a Excursion
        $queryString = http_build_query($excursionArray);

        $response = $this->runApp('POST', '/excursions?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $excursionId = str_replace('/excursions/', '', $locationHeader);

        // Let's test successful creating of the first Initiator
        $queryString = http_build_query($initiatorOneArray);

        $response = $this->runApp('POST', '/initiators?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $initiatorOneId = str_replace('/initiators/', '', $locationHeader);

        // Let's test successful creating of the second Initiator
        $queryString = http_build_query($initiatorTwoArray);

        $response = $this->runApp('POST', '/initiators?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $initiatorTwoId = str_replace('/initiators/', '', $locationHeader);

        // Check that Excursion and Initiators obtained expected ids
        $this->assertEquals(1, $excursionId);
        $this->assertEquals(1, $initiatorOneId);
        $this->assertEquals(2, $initiatorTwoId);

        // Fetch Initiators associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/initiators');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check that there is no associations with the Excursion
        $this->assertEmpty($res);

        // Let us associate the first Initiator with the Excursion
        $response = $this->runApp('POST', '/excursions/' . $excursionId . '/initiators/' . $initiatorOneId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Initiators associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/initiators');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Initiator's associations for the Excursion
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertEquals($initiatorOneArray['email'], $res[0]->email);
        $this->assertEquals($initiatorOneId, $res[0]->initiator_id);

        // Let us associate the second Initiator with the Excursion
        $response = $this->runApp('POST', '/excursions/' . $excursionId . '/initiators/' . $initiatorTwoId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Initiators associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/initiators');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Initiator's associations for the Excursion
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
        $this->assertEquals($initiatorOneArray['email'], $res[0]->email);
        $this->assertEquals($initiatorOneId, $res[0]->initiator_id);
        $this->assertEquals($initiatorTwoArray['email'], $res[1]->email);
        $this->assertEquals($initiatorTwoId, $res[1]->initiator_id);

        // Let us delete associations for the Excursion
        $response = $this->runApp('DELETE', '/excursions/' . $excursionId . '/initiators');
        $this->assertEquals(204, $response->getStatusCode());

        // Fetch Initiators associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/initiators');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Initiator's associations for the Excursion
        $this->assertEmpty($res);
    }

    /**
     * Tests the endpoints for Initiator Excursion associations
     */
    public function testInitiatorExcursionAssociations()
    {
        $initiatorArray = [
            'website' => 'http://website.am',
            'status' => '4',
            'name' => 'Հյուր սերվիս',
            'secureId' => 'RkDLmpwFRCPCyx6UXELr',
            'rank' => '4',
            'additionalInfo' => 'A big tour agency in Armenia',
            'phone' => '010-10-99-888,094-10-99-888',
            'address' => 'Թումանյան 23, բն. 18',
            'identifier' => '100',
            'type' => '4',
            'email' => 'hyur@mail.am',
        ];

        $excursionOneArray = $this->getObjectArray();

        $excursionTwoArray = [
            'status' => '4',
            'type' => '3',
            'expectedExcursionStartTime' => '09:00:00',
            'groupMembersCount' => '1',
            'excursionStartDate' => '2020/05/06',
            'expectedDurationOfExcursion' => '40',
            'radioGuide' => '1',
            'secureId' => 'rqrgn5x2Df2wRR7gaKrv',
            'isFree' => '1',
            'expectedExcursionStartDate' => '2020/05/06',
            'expectedGroupMembersCount' => '3',
            'rank' => '2',
            'country' => 'AM',
            'verifyStartTimeInHours' => '2',
            'excursionStartTime' => '09:00:00',
            'excursionEndTime' => '09:45:00',
            'additionalInfo' => 'an old confilct between ',
            'description' => 'A tourist from France',
        ];

        // Let's test successful creating of a Initiator
        $queryString = http_build_query($initiatorArray);

        $response = $this->runApp('POST', '/initiators?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $initiatorId = str_replace('/initiators/', '', $locationHeader);

        // Let's test successful creating of the first Excursion
        $queryString = http_build_query($excursionOneArray);

        $response = $this->runApp('POST', '/excursions?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $excursionOneId = str_replace('/excursions/', '', $locationHeader);

        // Let's test successful creating of the second Excursion
        $queryString = http_build_query($excursionTwoArray);

        $response = $this->runApp('POST', '/excursions?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $excursionTwoId = str_replace('/excursions/', '', $locationHeader);

        // Check that Initiator and Excursions obtained expected ids
        $this->assertEquals(1, $initiatorId);
        $this->assertEquals(1, $excursionOneId);
        $this->assertEquals(2, $excursionTwoId);

        // Fetch Excursions associated with the Initiator
        $response = $this->runApp('GET', '/initiators/' . $initiatorId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check that there is no associations with the Initiator
        $this->assertEmpty($res);

        // Let us associate the first Excursion with the Initiator
        $response = $this->runApp('POST', '/excursions/' . $excursionOneId . '/initiators/' . $initiatorId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Excursions associated with the Initiator
        $response = $this->runApp('GET', '/initiators/' . $initiatorId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Excursion's associations for the Initiator
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertEquals($excursionOneArray['description'], $res[0]->description);
        $this->assertEquals($excursionOneId, $res[0]->excursion_id);

        // Let us associate the second Excursion with the Initiator
        $response = $this->runApp('POST', '/excursions/' . $excursionTwoId . '/initiators/' . $initiatorId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Excursions associated with the Initiator
        $response = $this->runApp('GET', '/initiators/' . $initiatorId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Excursion's associations for the Initiator
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
        $this->assertEquals($excursionOneArray['description'], $res[0]->description);
        $this->assertEquals($excursionOneId, $res[0]->excursion_id);
        $this->assertEquals($excursionTwoArray['description'], $res[1]->description);
        $this->assertEquals($excursionTwoId, $res[1]->excursion_id);

        // Let us delete associations for the Initiator
        $response = $this->runApp('DELETE', '/initiators/' . $initiatorId . '/excursions');
        $this->assertEquals(204, $response->getStatusCode());

        // Fetch Excursions associated with the Initiator
        $response = $this->runApp('GET', '/initiators/' . $initiatorId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Excursion's associations for the Initiator
        $this->assertEmpty($res);
    }
}
