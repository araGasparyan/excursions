<?php
/**
 * Tests for Initiator endpoints
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

namespace Tests;

use LinesC\Model\Initiator;

class InitiatorTest extends BaseTestCase
{
    /**
     * Initiator object
     *
     * @var \LinesC\Model\Initiator
     */
    protected static $initiator;

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
        self::$initiator = new Initiator(self::$db);
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
            'website' => 'http://website.am',
            'status' => '4',
            'name' => 'Հյուր սերվիս',
            'secureId' => 'RkDLmpwFRCPCyx6UXELr',
            'rank' => '1',
            'additionalInfo' => 'A big tour agency in Armenia',
            'phone' => '010-10-99-888,094-10-99-888',
            'address' => 'Թումանյան 23, բն. 18',
            'identifier' => '100',
            'type' => '4',
            'email' => 'hyur@mail.am',
        ];
    }

    /**
     * Inserts Initiators to db with given number
     *
     * @var int
     *
     * @return int
     */
    protected function insertInitiatorsToDB(int $initiatorsCount)
    {
        $initiator = self::$initiator;
        $initiatorArray = $this->getObjectArray();

        for ($i = 1; $i <= $initiatorsCount; ++$i) {
            $dateTime = new \DateTime();

            $initiator->setWebsite($initiatorArray['website']);
            $initiator->setStatus($initiatorArray['status']);
            $initiator->setName($initiatorArray['name']);
            $initiator->setSecureId(generateSecureId());
            $initiator->setRank($initiatorArray['rank']);
            $initiator->setAdditionalInfo($initiatorArray['additionalInfo']);
            $initiator->setPhone($initiatorArray['phone']);
            $initiator->setAddress($initiatorArray['address']);
            $initiator->setIdentifier($initiatorArray['identifier']);
            $initiator->setType($initiatorArray['type']);
            $initiator->setEmail($initiatorArray['email']);
            $initiator->setCreatedDate($dateTime);
            $initiator->setUpdatedDate($dateTime);

            $initiator->insert();
        }

        return self::$db->lastInsertId();
    }

    /**
     * Tests the endpoint for fetching all the Initiators
     */
    public function testGetInitiators()
    {
        $page = 4;
        $perPage = 3;
        $initiatorsCount = 20;

        $initiatorArray = $this->getObjectArray();

        $queryString = http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);

        $this->insertInitiatorsToDB($initiatorsCount);

        $response = $this->runApp('GET', '/initiators?' . $queryString);
        $this->assertEquals(200, $response->getStatusCode());

        // Let us test pagination firstly
        $resp = json_decode((string) $response->getBody());
        $meta = $resp->meta;

        $this->assertEquals($page, $meta->page);
        $this->assertEquals($perPage, $meta->perPage);
        $this->assertEquals(1, $meta->first_page);
        $this->assertEquals($page - 1, $meta->prev_page);
        $this->assertEquals($page + 1, $meta->next_page);
        $this->assertEquals(ceil($initiatorsCount / $perPage), $meta->last_page);
        $this->assertEquals($initiatorsCount, $meta->total);

        // Let us continue with testing data
        $initiator = $resp->data[0];
        $this->assertNotNull($initiator->secure_id);
        $this->assertEquals($initiatorArray['name'], $initiator->name);
        $this->assertEquals($initiatorArray['address'], $initiator->address);
        $this->assertEquals($initiatorArray['email'], $initiator->email);
        $this->assertEquals($initiatorArray['phone'], $initiator->phone);
        $this->assertEquals($initiatorArray['website'], $initiator->website);
        $this->assertEquals($initiatorArray['additionalInfo'], $initiator->additional_info);
        $this->assertEquals($initiatorArray['identifier'], $initiator->identifier);
        $this->assertEquals($initiatorArray['type'], $initiator->type);
        $this->assertEquals($initiatorArray['rank'], $initiator->rank);
        $this->assertEquals($initiatorArray['status'], $initiator->status);
    }

    /**
     * Tests the endpoint for fetching a specific Initiator
     */
    public function testGetSpecificInitiator()
    {
        $lastInsertedId = $this->insertInitiatorsToDB(1);
        $initiatorArray = $this->getObjectArray();

        // Let us test successful fetching of the created Initiator
        $response = $this->runApp('GET', '/initiators/' . $lastInsertedId);
        $this->assertEquals(200, $response->getStatusCode());

        $initiator = json_decode((string) $response->getBody());
        $this->assertNotNull($initiator->secure_id);
        $this->assertEquals($initiatorArray['name'], $initiator->name);
        $this->assertEquals($initiatorArray['address'], $initiator->address);
        $this->assertEquals($initiatorArray['email'], $initiator->email);
        $this->assertEquals($initiatorArray['phone'], $initiator->phone);
        $this->assertEquals($initiatorArray['website'], $initiator->website);
        $this->assertEquals($initiatorArray['additionalInfo'], $initiator->additional_info);
        $this->assertEquals($initiatorArray['identifier'], $initiator->identifier);
        $this->assertEquals($initiatorArray['type'], $initiator->type);
        $this->assertEquals($initiatorArray['rank'], $initiator->rank);
        $this->assertEquals($initiatorArray['status'], $initiator->status);

        // Let us test 404 status code
        $response = $this->runApp('GET', '/initiators/' . ($lastInsertedId + 1));
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for deleting a specific Initiator
     */
    public function testDeleteSpecificInitiator()
    {
        $lastInsertedId = $this->insertInitiatorsToDB(1);

        // Check that Initiator is in the DB
        $initiator = self::$initiator;
        $this->assertNotFalse($initiator->find($lastInsertedId));

        // Let us test successful deleting of the created Initiator
        $response = $this->runApp('DELETE', '/initiators/' . $lastInsertedId);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertFalse($initiator->find($lastInsertedId));

        // Let us test 404 status code
        $response = $this->runApp('DELETE', '/initiators/' . $lastInsertedId);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for creating a Initiator
     */
    public function testCreateInitiator()
    {
        $initiatorArray = $this->getObjectArray();

        $queryString = http_build_query($initiatorArray);

        // Let's test successful creating of a Initiator
        $response = $this->runApp('POST', '/initiators?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        // Let's use Location Header for fetching just created Initiator
        $initiator = $this->runApp('GET', $response->getHeaders()['Location'][0]);
        $initiator = json_decode((string) $initiator->getBody());

        $this->assertNotNull($initiator->secure_id);
        $this->assertEquals($initiatorArray['name'], $initiator->name);
        $this->assertEquals($initiatorArray['address'], $initiator->address);
        $this->assertEquals($initiatorArray['email'], $initiator->email);
        $this->assertEquals($initiatorArray['phone'], $initiator->phone);
        $this->assertEquals($initiatorArray['website'], $initiator->website);
        $this->assertEquals($initiatorArray['additionalInfo'], $initiator->additional_info);
        $this->assertEquals($initiatorArray['identifier'], $initiator->identifier);
        $this->assertEquals($initiatorArray['type'], $initiator->type);
        $this->assertEquals($initiatorArray['rank'], $initiator->rank);
        $this->assertEquals($initiatorArray['status'], $initiator->status);

    }

    /**
     * Tests the endpoint for updating a Initiator
     */
    public function testUpdateInitiator()
    {
        $initiatorArray = $this->getObjectArray();

        $lastInsertedId = $this->insertInitiatorsToDB(1);

        // Check that Initiator in the DB
        $initiator = self::$initiator;
        $initiator->find($lastInsertedId);
        $this->assertNotFalse($initiator);
        $this->assertNotNull($initiator->getSecureId());
        $this->assertEquals($initiatorArray['name'], $initiator->getName());
        $this->assertEquals($initiatorArray['address'], $initiator->getAddress());
        $this->assertEquals($initiatorArray['email'], $initiator->getEmail());
        $this->assertEquals($initiatorArray['phone'], $initiator->getPhone());
        $this->assertEquals($initiatorArray['website'], $initiator->getWebsite());
        $this->assertEquals($initiatorArray['additionalInfo'], $initiator->getAdditionalInfo());
        $this->assertEquals($initiatorArray['identifier'], $initiator->getIdentifier());
        $this->assertEquals($initiatorArray['type'], $initiator->getType());
        $this->assertEquals($initiatorArray['rank'], $initiator->getRank());
        $this->assertEquals($initiatorArray['status'], $initiator->getStatus());

        $initiatorUpdateArray = [
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

        $queryString = http_build_query($initiatorUpdateArray);

        // Let's test updating of a Initiator which doesn't exist
        $response = $this->runApp('PUT', '/initiators/' . ($lastInsertedId + 1) . '?' . $queryString);
        $this->assertEquals(404, $response->getStatusCode());

        // Let's test successful updating of the Initiator
        $response = $this->runApp('PUT', "/initiators/$lastInsertedId?" . $queryString);
        $this->assertEquals(204, $response->getStatusCode());

        // Check that Initiator's fields are updated
        $initiator->find($lastInsertedId);
        $this->assertNotNull($initiator->getSecureId());
        $this->assertEquals($initiatorUpdateArray['name'], $initiator->getName());
        $this->assertEquals($initiatorUpdateArray['address'], $initiator->getAddress());
        $this->assertEquals($initiatorUpdateArray['email'], $initiator->getEmail());
        $this->assertEquals($initiatorUpdateArray['phone'], $initiator->getPhone());
        $this->assertEquals($initiatorUpdateArray['website'], $initiator->getWebsite());
        $this->assertEquals($initiatorUpdateArray['additionalInfo'], $initiator->getAdditionalInfo());
        $this->assertEquals($initiatorUpdateArray['identifier'], $initiator->getIdentifier());
        $this->assertEquals($initiatorUpdateArray['type'], $initiator->getType());
        $this->assertEquals($initiatorUpdateArray['rank'], $initiator->getRank());
        $this->assertEquals($initiatorUpdateArray['status'], $initiator->getStatus());
    }
}
