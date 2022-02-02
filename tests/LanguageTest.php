<?php
/**
 * Tests for Language endpoints
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

namespace Tests;

use LinesC\Model\Language;

class LanguageTest extends BaseTestCase
{
    /**
     * Language object
     *
     * @var \LinesC\Model\Language
     */
    protected static $language;

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
        self::$language = new Language(self::$db);
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
            'status' => '3',
            'description' => 'A Romance language that originated in the Iberian Peninsula and today has over 483 million native speakers, mainly in Spain and the Americas.',
            'secureId' => '4mq8jkMvfI9FZ0CMZdjR',
            'rank' => '1',
            'additional' => 'español',
            'type' => '2',
            'name' => 'spanish',
        ];
    }

    /**
     * Inserts Languages to db with given number
     *
     * @var int
     *
     * @return int
     */
    protected function insertLanguagesToDB(int $languagesCount)
    {
        $language = self::$language;
        $languageArray = $this->getObjectArray();

        for ($i = 1; $i <= $languagesCount; ++$i) {
            $dateTime = new \DateTime();

            $language->setStatus($languageArray['status']);
            $language->setDescription($languageArray['description']);
            $language->setSecureId(generateSecureId());
            $language->setRank($languageArray['rank']);
            $language->setAdditional($languageArray['additional']);
            $language->setType($languageArray['type']);
            $language->setName(generateSecureId());
            $language->setCreatedDate($dateTime);
            $language->setUpdatedDate($dateTime);

            $language->insert();
        }

        return self::$db->lastInsertId();
    }

    /**
     * Tests the endpoint for fetching all the Languages
     */
    public function testGetLanguages()
    {
        $page = 4;
        $perPage = 3;
        $languagesCount = 20;

        $languageArray = $this->getObjectArray();

        $queryString = http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);

        $this->insertLanguagesToDB($languagesCount);

        $response = $this->runApp('GET', '/languages?' . $queryString);
        $this->assertEquals(200, $response->getStatusCode());

        // Let us test pagination firstly
        $resp = json_decode((string) $response->getBody());
        $meta = $resp->meta;

        $this->assertEquals($page, $meta->page);
        $this->assertEquals($perPage, $meta->perPage);
        $this->assertEquals(1, $meta->first_page);
        $this->assertEquals($page - 1, $meta->prev_page);
        $this->assertEquals($page + 1, $meta->next_page);
        $this->assertEquals(ceil($languagesCount / $perPage), $meta->last_page);
        $this->assertEquals($languagesCount, $meta->total);

        // Let us continue with testing data
        $language = $resp->data[0];
        $this->assertNotNull($language->secure_id);
        $this->assertNotNull($language->name);
        $this->assertEquals($languageArray['additional'], $language->additional);
        $this->assertEquals($languageArray['description'], $language->description);
        $this->assertEquals($languageArray['type'], $language->type);
        $this->assertEquals($languageArray['rank'], $language->rank);
        $this->assertEquals($languageArray['status'], $language->status);
    }

    /**
     * Tests the endpoint for fetching a specific Language
     */
    public function testGetSpecificLanguage()
    {
        $lastInsertedId = $this->insertLanguagesToDB(1);
        $languageArray = $this->getObjectArray();

        // Let us test successful fetching of the created Language
        $response = $this->runApp('GET', '/languages/' . $lastInsertedId);
        $this->assertEquals(200, $response->getStatusCode());

        $language = json_decode((string) $response->getBody());
        $this->assertNotNull($language->secure_id);
        $this->assertNotNull($language->name);
        $this->assertEquals($languageArray['additional'], $language->additional);
        $this->assertEquals($languageArray['description'], $language->description);
        $this->assertEquals($languageArray['type'], $language->type);
        $this->assertEquals($languageArray['rank'], $language->rank);
        $this->assertEquals($languageArray['status'], $language->status);

        // Let us test 404 status code
        $response = $this->runApp('GET', '/languages/' . ($lastInsertedId + 1));
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for deleting a specific Language
     */
    public function testDeleteSpecificLanguage()
    {
        $lastInsertedId = $this->insertLanguagesToDB(1);

        // Check that Language is in the DB
        $language = self::$language;
        $this->assertNotFalse($language->find($lastInsertedId));

        // Let us test successful deleting of the created Language
        $response = $this->runApp('DELETE', '/languages/' . $lastInsertedId);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertFalse($language->find($lastInsertedId));

        // Let us test 404 status code
        $response = $this->runApp('DELETE', '/languages/' . $lastInsertedId);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for creating a Language
     */
    public function testCreateLanguage()
    {
        $languageArray = $this->getObjectArray();

        $queryString = http_build_query($languageArray);

        // Let's test successful creating of a Language
        $response = $this->runApp('POST', '/languages?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        // Let's use Location Header for fetching just created Language
        $language = $this->runApp('GET', $response->getHeaders()['Location'][0]);
        $language = json_decode((string) $language->getBody());

        $this->assertNotNull($language->secure_id);
        $this->assertNotNull($language->name);
        $this->assertEquals($languageArray['additional'], $language->additional);
        $this->assertEquals($languageArray['description'], $language->description);
        $this->assertEquals($languageArray['type'], $language->type);
        $this->assertEquals($languageArray['rank'], $language->rank);
        $this->assertEquals($languageArray['status'], $language->status);

        // Let's test if duplication of the resource is not allowen
        $response = $this->runApp('POST', '/languages?' . $queryString);
        $this->assertEquals(409, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for updating a Language
     */
    public function testUpdateLanguage()
    {
        $languageArray = $this->getObjectArray();

        $lastInsertedId = $this->insertLanguagesToDB(1);

        // Check that Language in the DB
        $language = self::$language;
        $language->find($lastInsertedId);
        $this->assertNotFalse($language);
        $this->assertNotNull($language->getSecureId());
        $this->assertNotNull($language->getName());
        $this->assertEquals($languageArray['additional'], $language->getAdditional());
        $this->assertEquals($languageArray['description'], $language->getDescription());
        $this->assertEquals($languageArray['type'], $language->getType());
        $this->assertEquals($languageArray['rank'], $language->getRank());
        $this->assertEquals($languageArray['status'], $language->getStatus());

        $languageUpdateArray = [
            'status' => '2',
            'description' => 'A Western Romance language originating in the Iberian Peninsula. It is the sole official language of Portugal, Brazil, Cape Verde, Guinea-Bissau, Mozambique, Angola and São Tomé and Príncipe.',
            'secureId' => 'fH0GsX7H7ONNJioqX4r9',
            'rank' => '1',
            'additional' => '[spanish]',
            'type' => '1',
            'name' => 'portuguese',
        ];

        $queryString = http_build_query($languageUpdateArray);

        // Let's test updating of a Language which doesn't exist
        $response = $this->runApp('PUT', '/languages/' . ($lastInsertedId + 1) . '?' . $queryString);
        $this->assertEquals(404, $response->getStatusCode());

        // Let's test successful updating of the Language
        $response = $this->runApp('PUT', "/languages/$lastInsertedId?" . $queryString);
        $this->assertEquals(204, $response->getStatusCode());

        // Check that Language's fields are updated
        $language->find($lastInsertedId);
        $this->assertNotNull($language->getSecureId());
        $this->assertNotNull($language->getName());
        $this->assertEquals($languageUpdateArray['additional'], $language->getAdditional());
        $this->assertEquals($languageUpdateArray['description'], $language->getDescription());
        $this->assertEquals($languageUpdateArray['type'], $language->getType());
        $this->assertEquals($languageUpdateArray['rank'], $language->getRank());
        $this->assertEquals($languageUpdateArray['status'], $language->getStatus());
    }

    /**
     * Tests the endpoints for Language Excursion associations
     */
    public function testLanguageExcursionAssociations()
    {
        $languageArray = $this->getObjectArray();

        $excursionOneArray = [
            'status' => '2',
            'type' => '3',
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
            'rank' => '1',
            'country' => 'AM',
            'verifyStartTimeInHours' => '2',
            'excursionStartTime' => '09:00:00',
            'excursionEndTime' => '09:45:00',
            'additionalInfo' => 'an old confilct between ',
            'description' => 'A tourist from France',
        ];

        // Let's test successful creating of a Language
        $queryString = http_build_query($languageArray);

        $response = $this->runApp('POST', '/languages?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $languageId = str_replace('/languages/', '', $locationHeader);

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

        // Check that Language and Excursions obtained expected ids
        $this->assertEquals(1, $languageId);
        $this->assertEquals(1, $excursionOneId);
        $this->assertEquals(2, $excursionTwoId);

        // Fetch Excursions associated with the Language
        $response = $this->runApp('GET', '/languages/' . $languageId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check that there is no associations with the Language
        $this->assertEmpty($res);

        // Let us associate the first Excursion with the Language
        $response = $this->runApp('POST', '/languages/' . $languageId . '/excursions/' . $excursionOneId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Excursions associated with the Language
        $response = $this->runApp('GET', '/languages/' . $languageId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Excursion's associations for the Language
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertEquals($excursionOneArray['description'], $res[0]->description);
        $this->assertEquals($excursionOneId, $res[0]->excursion_id);

        // Let us associate the second Excursion with the Language
        $response = $this->runApp('POST', '/languages/' . $languageId . '/excursions/' . $excursionTwoId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Excursions associated with the Language
        $response = $this->runApp('GET', '/languages/' . $languageId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Excursion's associations for the Language
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
        $this->assertEquals($excursionOneArray['description'], $res[0]->description);
        $this->assertEquals($excursionOneId, $res[0]->excursion_id);
        $this->assertEquals($excursionTwoArray['description'], $res[1]->description);
        $this->assertEquals($excursionTwoId, $res[1]->excursion_id);

        // Let us delete associations for the Language
        $response = $this->runApp('DELETE', '/languages/' . $languageId . '/excursions');
        $this->assertEquals(204, $response->getStatusCode());

        // Fetch Excursions associated with the Language
        $response = $this->runApp('GET', '/languages/' . $languageId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Excursion's associations for the Language
        $this->assertEmpty($res);
    }

    /**
     * Tests the endpoints for Excursion Language associations
     */
    public function testExcursionLanguageAssociations()
    {
        $excursionArray = [
            'status' => '2',
            'type' => '3',
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

        $languageOneArray = $this->getObjectArray();

        $languageTwoArray = [
            'status' => '2',
            'description' => 'A Western Romance language originating in the Iberian Peninsula. It is the sole official language of Portugal, Brazil, Cape Verde, Guinea-Bissau, Mozambique, Angola and São Tomé and Príncipe.',
            'secureId' => 'fH0GsX7H7ONNJioqX4r9',
            'rank' => '1',
            'additional' => '[spanish]',
            'type' => '1',
            'name' => 'portuguese',
        ];

        // Let's test successful creating of a Excursion
        $queryString = http_build_query($excursionArray);

        $response = $this->runApp('POST', '/excursions?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $excursionId = str_replace('/excursions/', '', $locationHeader);

        // Let's test successful creating of the first Language
        $queryString = http_build_query($languageOneArray);

        $response = $this->runApp('POST', '/languages?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $languageOneId = str_replace('/languages/', '', $locationHeader);

        // Let's test successful creating of the second Language
        $queryString = http_build_query($languageTwoArray);

        $response = $this->runApp('POST', '/languages?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $languageTwoId = str_replace('/languages/', '', $locationHeader);

        // Check that Excursion and Languages obtained expected ids
        $this->assertEquals(1, $excursionId);
        $this->assertEquals(1, $languageOneId);
        $this->assertEquals(2, $languageTwoId);

        // Fetch Languages associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/languages');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check that there is no associations with the Excursion
        $this->assertEmpty($res);

        // Let us associate the first Language with the Excursion
        $response = $this->runApp('POST', '/languages/' . $languageOneId . '/excursions/' . $excursionId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Languages associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/languages');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Language's associations for the Excursion
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertEquals($languageOneArray['name'], $res[0]->name);
        $this->assertEquals($languageOneId, $res[0]->language_id);

        // Let us associate the second Language with the Excursion
        $response = $this->runApp('POST', '/languages/' . $languageTwoId . '/excursions/' . $excursionId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Languages associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/languages');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Language's associations for the Excursion
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
        $this->assertEquals($languageOneArray['name'], $res[0]->name);
        $this->assertEquals($languageOneId, $res[0]->language_id);
        $this->assertEquals($languageTwoArray['name'], $res[1]->name);
        $this->assertEquals($languageTwoId, $res[1]->language_id);

        // Let us delete associations for the Excursion
        $response = $this->runApp('DELETE', '/excursions/' . $excursionId . '/languages');
        $this->assertEquals(204, $response->getStatusCode());

        // Fetch Languages associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/languages');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Language's associations for the Excursion
        $this->assertEmpty($res);
    }
}
