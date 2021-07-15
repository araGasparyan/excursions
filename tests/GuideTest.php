<?php
/**
 * Tests for Guide endpoints
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

namespace Tests;

use LinesC\Model\Guide;

class GuideTest extends BaseTestCase
{
    /**
     * Guide object
     *
     * @var \LinesC\Model\Guide
     */
    protected static $guide;

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
        self::$guide = new Guide(self::$db);
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
            'status' => '1',
            'firstName' => 'Little',
            'lastName' => 'Richard',
            'middleName' => 'A',
            'imagePath' => 'images/richi.png',
            'phone' => '098-234-583-456',
            'country' => 'US',
            'description' => '2e68mrAaEj',
            'secureId' => 'nvZjwDRoUm1zOzRl0Ezj',
            'rank' => '3',
            'additionalInfo' => 'add 1',
            'affiliation' => 'Metropoliten Museum',
            'address' => 'New York city 45, A. 6',
            'birthDate' => '1987/05/04',
            'position' => '2',
            'education' => 'Master',
            'type' => '1',
            'email' => 'little.richard@gmail.com',
            'jobTitle' => 'Resaercher',
        ];
    }

    /**
     * Inserts Guides to db with given number
     *
     * @var int
     *
     * @return int
     */
    protected function insertGuidesToDB(int $guidesCount)
    {
        $guide = self::$guide;
        $guideArray = $this->getObjectArray();

        for ($i = 1; $i <= $guidesCount; ++$i) {
            $dateTime = new \DateTime();

            $guide->setStatus($guideArray['status']);
            $guide->setFirstName($guideArray['firstName']);
            $guide->setLastName($guideArray['lastName']);
            $guide->setMiddleName($guideArray['middleName']);
            $guide->setImagePath($guideArray['imagePath']);
            $guide->setPhone($guideArray['phone']);
            $guide->setCountry($guideArray['country']);
            $guide->setDescription($guideArray['description']);
            $guide->setSecureId(generateSecureId());
            $guide->setRank($guideArray['rank']);
            $guide->setAdditionalInfo($guideArray['additionalInfo']);
            $guide->setAffiliation($guideArray['affiliation']);
            $guide->setAddress($guideArray['address']);
            $guide->setBirthDate(new \DateTime($guideArray['birthDate']));
            $guide->setPosition($guideArray['position']);
            $guide->setEducation($guideArray['education']);
            $guide->setType($guideArray['type']);
            $guide->setEmail(generateSecureId());
            $guide->setJobTitle($guideArray['jobTitle']);
            $guide->setCreatedDate($dateTime);
            $guide->setUpdatedDate($dateTime);

            $guide->insert();
        }

        return self::$db->lastInsertId();
    }

    /**
     * Tests the endpoint for fetching all the Guides
     */
    public function testGetGuides()
    {
        $page = 4;
        $perPage = 3;
        $guidesCount = 20;

        $guideArray = $this->getObjectArray();

        $queryString = http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);

        $this->insertGuidesToDB($guidesCount);

        $response = $this->runApp('GET', '/guides?' . $queryString);
        $this->assertEquals(200, $response->getStatusCode());

        // Let us test pagination firstly
        $resp = json_decode((string) $response->getBody());
        $meta = $resp->meta;

        $this->assertEquals($page, $meta->page);
        $this->assertEquals($perPage, $meta->perPage);
        $this->assertEquals(1, $meta->first_page);
        $this->assertEquals($page - 1, $meta->prev_page);
        $this->assertEquals($page + 1, $meta->next_page);
        $this->assertEquals(ceil($guidesCount / $perPage), $meta->last_page);
        $this->assertEquals($guidesCount, $meta->total);

        // Let us continue with testing data
        $guide = $resp->data[0];
        $this->assertNotNull($guide->secure_id);
        $this->assertEquals($guideArray['firstName'], $guide->first_name);
        $this->assertEquals($guideArray['middleName'], $guide->middle_name);
        $this->assertEquals($guideArray['lastName'], $guide->last_name);
        $this->assertEquals(new \DateTime($guideArray['birthDate']), new \DateTime($guide->birth_date));
        $this->assertNotNull($guide->email);
        $this->assertEquals($guideArray['address'], $guide->address);
        $this->assertEquals($guideArray['affiliation'], $guide->affiliation);
        $this->assertEquals($guideArray['jobTitle'], $guide->job_title);
        $this->assertEquals($guideArray['country'], $guide->country);
        $this->assertEquals($guideArray['education'], $guide->education);
        $this->assertEquals($guideArray['phone'], $guide->phone);
        $this->assertEquals($guideArray['imagePath'], $guide->image_path);
        $this->assertEquals($guideArray['additionalInfo'], $guide->additional_info);
        $this->assertEquals($guideArray['position'], $guide->position);
        $this->assertEquals($guideArray['description'], $guide->description);
        $this->assertEquals($guideArray['type'], $guide->type);
        $this->assertEquals($guideArray['rank'], $guide->rank);
        $this->assertEquals($guideArray['status'], $guide->status);
    }

    /**
     * Tests the endpoint for fetching a specific Guide
     */
    public function testGetSpecificGuide()
    {
        $lastInsertedId = $this->insertGuidesToDB(1);
        $guideArray = $this->getObjectArray();

        // Let us test successful fetching of the created Guide
        $response = $this->runApp('GET', '/guides/' . $lastInsertedId);
        $this->assertEquals(200, $response->getStatusCode());

        $guide = json_decode((string) $response->getBody());
        $this->assertNotNull($guide->secure_id);
        $this->assertEquals($guideArray['firstName'], $guide->first_name);
        $this->assertEquals($guideArray['middleName'], $guide->middle_name);
        $this->assertEquals($guideArray['lastName'], $guide->last_name);
        $this->assertEquals(new \DateTime($guideArray['birthDate']), new \DateTime($guide->birth_date));
        $this->assertNotNull($guide->email);
        $this->assertEquals($guideArray['address'], $guide->address);
        $this->assertEquals($guideArray['affiliation'], $guide->affiliation);
        $this->assertEquals($guideArray['jobTitle'], $guide->job_title);
        $this->assertEquals($guideArray['country'], $guide->country);
        $this->assertEquals($guideArray['education'], $guide->education);
        $this->assertEquals($guideArray['phone'], $guide->phone);
        $this->assertEquals($guideArray['imagePath'], $guide->image_path);
        $this->assertEquals($guideArray['additionalInfo'], $guide->additional_info);
        $this->assertEquals($guideArray['position'], $guide->position);
        $this->assertEquals($guideArray['description'], $guide->description);
        $this->assertEquals($guideArray['type'], $guide->type);
        $this->assertEquals($guideArray['rank'], $guide->rank);
        $this->assertEquals($guideArray['status'], $guide->status);

        // Let us test 404 status code
        $response = $this->runApp('GET', '/guides/' . ($lastInsertedId + 1));
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for deleting a specific Guide
     */
    public function testDeleteSpecificGuide()
    {
        $lastInsertedId = $this->insertGuidesToDB(1);

        // Check that Guide is in the DB
        $guide = self::$guide;
        $this->assertNotFalse($guide->find($lastInsertedId));

        // Let us test successful deleting of the created Guide
        $response = $this->runApp('DELETE', '/guides/' . $lastInsertedId);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertFalse($guide->find($lastInsertedId));

        // Let us test 404 status code
        $response = $this->runApp('DELETE', '/guides/' . $lastInsertedId);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for creating a Guide
     */
    public function testCreateGuide()
    {
        $guideArray = $this->getObjectArray();

        $queryString = http_build_query($guideArray);

        // Let's test successful creating of a Guide
        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        // Let's use Location Header for fetching just created Guide
        $guide = $this->runApp('GET', $response->getHeaders()['Location'][0]);
        $guide = json_decode((string) $guide->getBody());

        $this->assertNotNull($guide->secure_id);
        $this->assertEquals($guideArray['firstName'], $guide->first_name);
        $this->assertEquals($guideArray['middleName'], $guide->middle_name);
        $this->assertEquals($guideArray['lastName'], $guide->last_name);
        $this->assertEquals(new \DateTime($guideArray['birthDate']), new \DateTime($guide->birth_date));
        $this->assertNotNull($guide->email);
        $this->assertEquals($guideArray['address'], $guide->address);
        $this->assertEquals($guideArray['affiliation'], $guide->affiliation);
        $this->assertEquals($guideArray['jobTitle'], $guide->job_title);
        $this->assertEquals($guideArray['country'], $guide->country);
        $this->assertEquals($guideArray['education'], $guide->education);
        $this->assertEquals($guideArray['phone'], $guide->phone);
        $this->assertEquals($guideArray['imagePath'], $guide->image_path);
        $this->assertEquals($guideArray['additionalInfo'], $guide->additional_info);
        $this->assertEquals($guideArray['position'], $guide->position);
        $this->assertEquals($guideArray['description'], $guide->description);
        $this->assertEquals($guideArray['type'], $guide->type);
        $this->assertEquals($guideArray['rank'], $guide->rank);
        $this->assertEquals($guideArray['status'], $guide->status);

        // Let's test if duplication of the resource is not allowen
        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(409, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for updating a Guide
     */
    public function testUpdateGuide()
    {
        $guideArray = $this->getObjectArray();

        $lastInsertedId = $this->insertGuidesToDB(1);

        // Check that Guide in the DB
        $guide = self::$guide;
        $guide->find($lastInsertedId);
        $this->assertNotFalse($guide);
        $this->assertNotNull($guide->getSecureId());
        $this->assertEquals($guideArray['firstName'], $guide->getFirstName());
        $this->assertEquals($guideArray['middleName'], $guide->getMiddleName());
        $this->assertEquals($guideArray['lastName'], $guide->getLastName());
        $this->assertEquals(new \DateTime($guideArray['birthDate']), new \DateTime($guide->getBirthDate()));
        $this->assertNotNull($guide->getEmail());
        $this->assertEquals($guideArray['address'], $guide->getAddress());
        $this->assertEquals($guideArray['affiliation'], $guide->getAffiliation());
        $this->assertEquals($guideArray['jobTitle'], $guide->getJobTitle());
        $this->assertEquals($guideArray['country'], $guide->getCountry());
        $this->assertEquals($guideArray['education'], $guide->getEducation());
        $this->assertEquals($guideArray['phone'], $guide->getPhone());
        $this->assertEquals($guideArray['imagePath'], $guide->getImagePath());
        $this->assertEquals($guideArray['additionalInfo'], $guide->getAdditionalInfo());
        $this->assertEquals($guideArray['position'], $guide->getPosition());
        $this->assertEquals($guideArray['description'], $guide->getDescription());
        $this->assertEquals($guideArray['type'], $guide->getType());
        $this->assertEquals($guideArray['rank'], $guide->getRank());
        $this->assertEquals($guideArray['status'], $guide->getStatus());

        $guideUpdateArray = [
            'status' => '4',
            'firstName' => 'Շառլ',
            'lastName' => 'Քոուի',
            'middleName' => 'սըր',
            'imagePath' => 'images/cow.jpeg',
            'phone' => '099-000000',
            'country' => 'ARM',
            'description' => 'Edy4ZgjEGM',
            'secureId' => '395QRcwDVrIHM8k4EyD3',
            'rank' => '5',
            'additionalInfo' => 'add 2',
            'affiliation' => 'Ազգային ժողով',
            'address' => 'Կոմիտաս 6, բն. 29',
            'birthDate' => '1957/12/04',
            'position' => '4',
            'education' => 'PhD',
            'type' => '1',
            'email' => 'charlcow@oxford.ai',
            'jobTitle' => 'Ջուր բռնող',
        ];

        $queryString = http_build_query($guideUpdateArray);

        // Let's test updating of a Guide which doesn't exist
        $response = $this->runApp('PUT', '/guides/' . ($lastInsertedId + 1) . '?' . $queryString);
        $this->assertEquals(404, $response->getStatusCode());

        // Let's test successful updating of the Guide
        $response = $this->runApp('PUT', "/guides/$lastInsertedId?" . $queryString);
        $this->assertEquals(204, $response->getStatusCode());

        // Check that Guide's fields are updated
        $guide->find($lastInsertedId);
        $this->assertNotNull($guide->getSecureId());
        $this->assertEquals($guideUpdateArray['firstName'], $guide->getFirstName());
        $this->assertEquals($guideUpdateArray['middleName'], $guide->getMiddleName());
        $this->assertEquals($guideUpdateArray['lastName'], $guide->getLastName());
        $this->assertEquals(new \DateTime($guideUpdateArray['birthDate']), new \DateTime($guide->getBirthDate()));
        $this->assertNotNull($guide->getEmail());
        $this->assertEquals($guideUpdateArray['address'], $guide->getAddress());
        $this->assertEquals($guideUpdateArray['affiliation'], $guide->getAffiliation());
        $this->assertEquals($guideUpdateArray['jobTitle'], $guide->getJobTitle());
        $this->assertEquals($guideUpdateArray['country'], $guide->getCountry());
        $this->assertEquals($guideUpdateArray['education'], $guide->getEducation());
        $this->assertEquals($guideUpdateArray['phone'], $guide->getPhone());
        $this->assertEquals($guideUpdateArray['imagePath'], $guide->getImagePath());
        $this->assertEquals($guideUpdateArray['additionalInfo'], $guide->getAdditionalInfo());
        $this->assertEquals($guideUpdateArray['position'], $guide->getPosition());
        $this->assertEquals($guideUpdateArray['description'], $guide->getDescription());
        $this->assertEquals($guideUpdateArray['type'], $guide->getType());
        $this->assertEquals($guideUpdateArray['rank'], $guide->getRank());
        $this->assertEquals($guideUpdateArray['status'], $guide->getStatus());
    }

    /**
     * Tests the endpoints for Guide Language associations
     */
    public function testGuideLanguageAssociations()
    {
        $guideArray = $this->getObjectArray();

        $languageOneArray = [
            'status' => '5',
            'description' => 'A Romance language that originated in the Iberian Peninsula and today has over 483 million native speakers, mainly in Spain and the Americas.',
            'secureId' => '4mq8jkMvfI9FZ0CMZdjR',
            'rank' => '5',
            'additional' => 'español',
            'type' => '2',
            'name' => 'spanish',
        ];

        $languageTwoArray = [
            'status' => '2',
            'description' => 'A Western Romance language originating in the Iberian Peninsula. It is the sole official language of Portugal, Brazil, Cape Verde, Guinea-Bissau, Mozambique, Angola and São Tomé and Príncipe.',
            'secureId' => 'fH0GsX7H7ONNJioqX4r9',
            'rank' => '3',
            'additional' => '[spanish]',
            'type' => '1',
            'name' => 'portuguese',
        ];

        // Let's test successful creating of a Guide
        $queryString = http_build_query($guideArray);

        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $guideId = str_replace('/guides/', '', $locationHeader);

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

        // Check that Guide and Languages obtained expected ids
        $this->assertEquals(1, $guideId);
        $this->assertEquals(1, $languageOneId);
        $this->assertEquals(2, $languageTwoId);

        // Fetch Languages associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/languages');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check that there is no associations with the Guide
        $this->assertEmpty($res);

        // Let us associate the first Language with the Guide
        $response = $this->runApp('POST', '/guides/' . $guideId . '/languages/' . $languageOneId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Languages associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/languages');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Language's associations for the Guide
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertEquals($languageOneArray['name'], $res[0]->name);
        $this->assertEquals($languageOneId, $res[0]->language_id);

        // Let us associate the second Language with the Guide
        $response = $this->runApp('POST', '/guides/' . $guideId . '/languages/' . $languageTwoId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Languages associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/languages');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Language's associations for the Guide
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
        $this->assertEquals($languageOneArray['name'], $res[0]->name);
        $this->assertEquals($languageOneId, $res[0]->language_id);
        $this->assertEquals($languageTwoArray['name'], $res[1]->name);
        $this->assertEquals($languageTwoId, $res[1]->language_id);

        // Let us delete associations for the Guide
        $response = $this->runApp('DELETE', '/guides/' . $guideId . '/languages');
        $this->assertEquals(204, $response->getStatusCode());

        // Fetch Languages associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/languages');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Language's associations for the Guide
        $this->assertEmpty($res);
    }

    /**
     * Tests the endpoints for Language Guide associations
     */
    public function testLanguageGuideAssociations()
    {
        $languageArray = [
            'status' => '5',
            'description' => 'A Romance language that originated in the Iberian Peninsula and today has over 483 million native speakers, mainly in Spain and the Americas.',
            'secureId' => '4mq8jkMvfI9FZ0CMZdjR',
            'rank' => '5',
            'additional' => 'español',
            'type' => '2',
            'name' => 'spanish',
        ];

        $guideOneArray = $this->getObjectArray();

        $guideTwoArray = [
            'status' => '4',
            'firstName' => 'Շառլ',
            'lastName' => 'Քոուի',
            'middleName' => 'սըր',
            'imagePath' => 'images/cow.jpeg',
            'phone' => '099-000000',
            'country' => 'ARM',
            'description' => 'Edy4ZgjEGM',
            'secureId' => '395QRcwDVrIHM8k4EyD3',
            'rank' => '5',
            'additionalInfo' => 'add 2',
            'affiliation' => 'Ազգային ժողով',
            'address' => 'Կոմիտաս 6, բն. 29',
            'birthDate' => '1957/12/04',
            'position' => '4',
            'education' => 'PhD',
            'type' => '1',
            'email' => 'charlcow@oxford.ai',
            'jobTitle' => 'Ջուր բռնող',
        ];

        // Let's test successful creating of a Language
        $queryString = http_build_query($languageArray);

        $response = $this->runApp('POST', '/languages?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $languageId = str_replace('/languages/', '', $locationHeader);

        // Let's test successful creating of the first Guide
        $queryString = http_build_query($guideOneArray);

        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $guideOneId = str_replace('/guides/', '', $locationHeader);

        // Let's test successful creating of the second Guide
        $queryString = http_build_query($guideTwoArray);

        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $guideTwoId = str_replace('/guides/', '', $locationHeader);

        // Check that Language and Guides obtained expected ids
        $this->assertEquals(1, $languageId);
        $this->assertEquals(1, $guideOneId);
        $this->assertEquals(2, $guideTwoId);

        // Fetch Guides associated with the Language
        $response = $this->runApp('GET', '/languages/' . $languageId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check that there is no associations with the Language
        $this->assertEmpty($res);

        // Let us associate the first Guide with the Language
        $response = $this->runApp('POST', '/guides/' . $guideOneId . '/languages/' . $languageId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Guides associated with the Language
        $response = $this->runApp('GET', '/languages/' . $languageId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Guide's associations for the Language
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertEquals($guideOneArray['jobTitle'], $res[0]->job_title);
        $this->assertEquals($guideOneId, $res[0]->guide_id);

        // Let us associate the second Guide with the Language
        $response = $this->runApp('POST', '/guides/' . $guideTwoId . '/languages/' . $languageId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Guides associated with the Language
        $response = $this->runApp('GET', '/languages/' . $languageId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Guide's associations for the Language
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
        $this->assertEquals($guideOneArray['jobTitle'], $res[0]->job_title);
        $this->assertEquals($guideOneId, $res[0]->guide_id);
        $this->assertEquals($guideTwoArray['jobTitle'], $res[1]->job_title);
        $this->assertEquals($guideTwoId, $res[1]->guide_id);

        // Let us delete associations for the Language
        $response = $this->runApp('DELETE', '/languages/' . $languageId . '/guides');
        $this->assertEquals(204, $response->getStatusCode());

        // Fetch Guides associated with the Language
        $response = $this->runApp('GET', '/languages/' . $languageId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Guide's associations for the Language
        $this->assertEmpty($res);
    }

    /**
     * Tests the endpoints for Guide Excursion associations
     */
    public function testGuideExcursionAssociations()
    {
        $guideArray = $this->getObjectArray();

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
            'rank' => '4',
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
            'rank' => '2',
            'country' => 'AM',
            'verifyStartTimeInHours' => '2',
            'excursionStartTime' => '09:00:00',
            'excursionEndTime' => '09:45:00',
            'additionalInfo' => 'an old confilct between ',
            'description' => 'A tourist from France',
        ];

        // Let's test successful creating of a Guide
        $queryString = http_build_query($guideArray);

        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $guideId = str_replace('/guides/', '', $locationHeader);

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

        // Check that Guide and Excursions obtained expected ids
        $this->assertEquals(1, $guideId);
        $this->assertEquals(1, $excursionOneId);
        $this->assertEquals(2, $excursionTwoId);

        // Fetch Excursions associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check that there is no associations with the Guide
        $this->assertEmpty($res);

        // Let us associate the first Excursion with the Guide
        $response = $this->runApp('POST', '/guides/' . $guideId . '/excursions/' . $excursionOneId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Excursions associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Excursion's associations for the Guide
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertEquals($excursionOneArray['description'], $res[0]->description);
        $this->assertEquals($excursionOneId, $res[0]->excursion_id);

        // Let us associate the second Excursion with the Guide
        $response = $this->runApp('POST', '/guides/' . $guideId . '/excursions/' . $excursionTwoId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Excursions associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Excursion's associations for the Guide
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
        $this->assertEquals($excursionOneArray['description'], $res[0]->description);
        $this->assertEquals($excursionOneId, $res[0]->excursion_id);
        $this->assertEquals($excursionTwoArray['description'], $res[1]->description);
        $this->assertEquals($excursionTwoId, $res[1]->excursion_id);

        // Let us delete associations for the Guide
        $response = $this->runApp('DELETE', '/guides/' . $guideId . '/excursions');
        $this->assertEquals(204, $response->getStatusCode());

        // Fetch Excursions associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/excursions');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Excursion's associations for the Guide
        $this->assertEmpty($res);
    }

    /**
     * Tests the endpoints for Excursion Guide associations
     */
    public function testExcursionGuideAssociations()
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
            'rank' => '4',
            'country' => 'zPChPK2FbR',
            'verifyStartTimeInHours' => '3',
            'excursionStartTime' => '15:40:00',
            'excursionEndTime' => '17:00:00',
            'additionalInfo' => 'no reason for changing',
            'description' => 'Group of 50 tourists from Angola',
        ];

        $guideOneArray = $this->getObjectArray();

        $guideTwoArray = [
            'status' => '4',
            'firstName' => 'Շառլ',
            'lastName' => 'Քոուի',
            'middleName' => 'սըր',
            'imagePath' => 'images/cow.jpeg',
            'phone' => '099-000000',
            'country' => 'ARM',
            'description' => 'Edy4ZgjEGM',
            'secureId' => '395QRcwDVrIHM8k4EyD3',
            'rank' => '5',
            'additionalInfo' => 'add 2',
            'affiliation' => 'Ազգային ժողով',
            'address' => 'Կոմիտաս 6, բն. 29',
            'birthDate' => '1957/12/04',
            'position' => '4',
            'education' => 'PhD',
            'type' => '1',
            'email' => 'charlcow@oxford.ai',
            'jobTitle' => 'Ջուր բռնող',
        ];

        // Let's test successful creating of a Excursion
        $queryString = http_build_query($excursionArray);

        $response = $this->runApp('POST', '/excursions?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $excursionId = str_replace('/excursions/', '', $locationHeader);

        // Let's test successful creating of the first Guide
        $queryString = http_build_query($guideOneArray);

        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $guideOneId = str_replace('/guides/', '', $locationHeader);

        // Let's test successful creating of the second Guide
        $queryString = http_build_query($guideTwoArray);

        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $guideTwoId = str_replace('/guides/', '', $locationHeader);

        // Check that Excursion and Guides obtained expected ids
        $this->assertEquals(1, $excursionId);
        $this->assertEquals(1, $guideOneId);
        $this->assertEquals(2, $guideTwoId);

        // Fetch Guides associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check that there is no associations with the Excursion
        $this->assertEmpty($res);

        // Let us associate the first Guide with the Excursion
        $response = $this->runApp('POST', '/guides/' . $guideOneId . '/excursions/' . $excursionId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Guides associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Guide's associations for the Excursion
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertEquals($guideOneArray['jobTitle'], $res[0]->job_title);
        $this->assertEquals($guideOneId, $res[0]->guide_id);

        // Let us associate the second Guide with the Excursion
        $response = $this->runApp('POST', '/guides/' . $guideTwoId . '/excursions/' . $excursionId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Guides associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Guide's associations for the Excursion
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
        $this->assertEquals($guideOneArray['jobTitle'], $res[0]->job_title);
        $this->assertEquals($guideOneId, $res[0]->guide_id);
        $this->assertEquals($guideTwoArray['jobTitle'], $res[1]->job_title);
        $this->assertEquals($guideTwoId, $res[1]->guide_id);

        // Let us delete associations for the Excursion
        $response = $this->runApp('DELETE', '/excursions/' . $excursionId . '/guides');
        $this->assertEquals(204, $response->getStatusCode());

        // Fetch Guides associated with the Excursion
        $response = $this->runApp('GET', '/excursions/' . $excursionId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Guide's associations for the Excursion
        $this->assertEmpty($res);
    }

    /**
     * Tests the endpoints for Guide Appearance associations
     */
    public function testGuideAppearanceAssociations()
    {
        $guideArray = $this->getObjectArray();

        $appearanceOneArray = [
            'status' => '1',
            'appearanceEndDatetime' => '2020/10/06 00:00:00',
            'appearanceStartDatetime' => '2020/05/04 12:30:00',
            'secureId' => 'P5pG1b6KAWp9WE3G1Uzh',
            'rank' => '3',
            'reason' => 'Բանկում հանդիպում',
            'mode' => '1',
            'type' => '1',
        ];

        $appearanceTwoArray = [
            'status' => '1',
            'appearanceEndDatetime' => '2020/11/05 23:59:59',
            'appearanceStartDatetime' => '2020/05/04 14:40:00',
            'secureId' => 'DavXwArUNPQ12HK9EILF',
            'rank' => '2',
            'reason' => 'Արձակուրդ',
            'mode' => '1',
            'type' => '2',
        ];

        // Let's test successful creating of a Guide
        $queryString = http_build_query($guideArray);

        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $guideId = str_replace('/guides/', '', $locationHeader);

        // Let's test successful creating of the first Appearance
        $queryString = http_build_query($appearanceOneArray);

        $response = $this->runApp('POST', '/appearances?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $appearanceOneId = str_replace('/appearances/', '', $locationHeader);

        // Let's test successful creating of the second Appearance
        $queryString = http_build_query($appearanceTwoArray);

        $response = $this->runApp('POST', '/appearances?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $appearanceTwoId = str_replace('/appearances/', '', $locationHeader);

        // Check that Guide and Appearances obtained expected ids
        $this->assertEquals(1, $guideId);
        $this->assertEquals(1, $appearanceOneId);
        $this->assertEquals(2, $appearanceTwoId);

        // Fetch Appearances associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/appearances');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check that there is no associations with the Guide
        $this->assertEmpty($res);

        // Let us associate the first Appearance with the Guide
        $response = $this->runApp('POST', '/guides/' . $guideId . '/appearances/' . $appearanceOneId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Appearances associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/appearances');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Appearance's associations for the Guide
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertEquals($appearanceOneArray['type'], $res[0]->type);
        $this->assertEquals($appearanceOneId, $res[0]->appearance_id);

        // Let us associate the second Appearance with the Guide
        $response = $this->runApp('POST', '/guides/' . $guideId . '/appearances/' . $appearanceTwoId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Appearances associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/appearances');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Appearance's associations for the Guide
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
        $this->assertEquals($appearanceOneArray['type'], $res[0]->type);
        $this->assertEquals($appearanceOneId, $res[0]->appearance_id);
        $this->assertEquals($appearanceTwoArray['type'], $res[1]->type);
        $this->assertEquals($appearanceTwoId, $res[1]->appearance_id);

        // Let us delete associations for the Guide
        $response = $this->runApp('DELETE', '/guides/' . $guideId . '/appearances');
        $this->assertEquals(204, $response->getStatusCode());

        // Fetch Appearances associated with the Guide
        $response = $this->runApp('GET', '/guides/' . $guideId . '/appearances');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Appearance's associations for the Guide
        $this->assertEmpty($res);
    }

    /**
     * Tests the endpoints for Appearance Guide associations
     */
    public function testAppearanceGuideAssociations()
    {
        $appearanceArray = [
            'status' => '1',
            'appearanceEndDatetime' => '2020/10/06 00:00:00',
            'appearanceStartDatetime' => '2020/05/04 12:30:00',
            'secureId' => 'P5pG1b6KAWp9WE3G1Uzh',
            'rank' => '3',
            'reason' => 'Բանկում հանդիպում',
            'mode' => '1',
            'type' => '1',
        ];

        $guideOneArray = $this->getObjectArray();

        $guideTwoArray = [
            'status' => '4',
            'firstName' => 'Շառլ',
            'lastName' => 'Քոուի',
            'middleName' => 'սըր',
            'imagePath' => 'images/cow.jpeg',
            'phone' => '099-000000',
            'country' => 'ARM',
            'description' => 'Edy4ZgjEGM',
            'secureId' => '395QRcwDVrIHM8k4EyD3',
            'rank' => '5',
            'additionalInfo' => 'add 2',
            'affiliation' => 'Ազգային ժողով',
            'address' => 'Կոմիտաս 6, բն. 29',
            'birthDate' => '1957/12/04',
            'position' => '4',
            'education' => 'PhD',
            'type' => '1',
            'email' => 'charlcow@oxford.ai',
            'jobTitle' => 'Ջուր բռնող',
        ];

        // Let's test successful creating of a Appearance
        $queryString = http_build_query($appearanceArray);

        $response = $this->runApp('POST', '/appearances?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $appearanceId = str_replace('/appearances/', '', $locationHeader);

        // Let's test successful creating of the first Guide
        $queryString = http_build_query($guideOneArray);

        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $guideOneId = str_replace('/guides/', '', $locationHeader);

        // Let's test successful creating of the second Guide
        $queryString = http_build_query($guideTwoArray);

        $response = $this->runApp('POST', '/guides?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        $locationHeader = $response->getHeaders()['Location'][0];
        $guideTwoId = str_replace('/guides/', '', $locationHeader);

        // Check that Appearance and Guides obtained expected ids
        $this->assertEquals(1, $appearanceId);
        $this->assertEquals(1, $guideOneId);
        $this->assertEquals(2, $guideTwoId);

        // Fetch Guides associated with the Appearance
        $response = $this->runApp('GET', '/appearances/' . $appearanceId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check that there is no associations with the Appearance
        $this->assertEmpty($res);

        // Let us associate the first Guide with the Appearance
        $response = $this->runApp('POST', '/guides/' . $guideOneId . '/appearances/' . $appearanceId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Guides associated with the Appearance
        $response = $this->runApp('GET', '/appearances/' . $appearanceId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Guide's associations for the Appearance
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertEquals($guideOneArray['jobTitle'], $res[0]->job_title);
        $this->assertEquals($guideOneId, $res[0]->guide_id);

        // Let us associate the second Guide with the Appearance
        $response = $this->runApp('POST', '/guides/' . $guideTwoId . '/appearances/' . $appearanceId);
        $this->assertEquals(201, $response->getStatusCode());

        // Fetch Guides associated with the Appearance
        $response = $this->runApp('GET', '/appearances/' . $appearanceId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Guide's associations for the Appearance
        $this->assertNotEmpty($res);
        $this->assertCount(2, $res);
        $this->assertEquals($guideOneArray['jobTitle'], $res[0]->job_title);
        $this->assertEquals($guideOneId, $res[0]->guide_id);
        $this->assertEquals($guideTwoArray['jobTitle'], $res[1]->job_title);
        $this->assertEquals($guideTwoId, $res[1]->guide_id);

        // Let us delete associations for the Appearance
        $response = $this->runApp('DELETE', '/appearances/' . $appearanceId . '/guides');
        $this->assertEquals(204, $response->getStatusCode());

        // Fetch Guides associated with the Appearance
        $response = $this->runApp('GET', '/appearances/' . $appearanceId . '/guides');
        $this->assertEquals(200, $response->getStatusCode());
        $res = json_decode((string) $response->getBody());

        // Check Guide's associations for the Appearance
        $this->assertEmpty($res);
    }
}
