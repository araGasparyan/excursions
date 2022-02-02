<?php
/**
 * Tests for Appearance endpoints
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

namespace Tests;

use LinesC\Model\Appearance;

class AppearanceTest extends BaseTestCase
{
    /**
     * Appearance object
     *
     * @var \LinesC\Model\Appearance
     */
    protected static $appearance;

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
        self::$appearance = new Appearance(self::$db);
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
            'appearanceEndDatetime' => '2020/10/06 00:00:00',
            'appearanceStartDatetime' => '2020/05/04 12:30:00',
            'secureId' => 'P5pG1b6KAWp9WE3G1Uzh',
            'rank' => '3',
            'reason' => 'Բանկում հանդիպում',
            'mode' => '1',
            'type' => '1',
        ];
    }

    /**
     * Inserts Appearances to db with given number
     *
     * @var int
     *
     * @return int
     */
    protected function insertAppearancesToDB(int $appearancesCount)
    {
        $appearance = self::$appearance;
        $appearanceArray = $this->getObjectArray();

        for ($i = 1; $i <= $appearancesCount; ++$i) {
            $dateTime = new \DateTime();

            $appearance->setStatus($appearanceArray['status']);
            $appearance->setAppearanceEndDatetime(new \DateTime($appearanceArray['appearanceEndDatetime']));
            $appearance->setAppearanceStartDatetime(new \DateTime($appearanceArray['appearanceStartDatetime']));
            $appearance->setSecureId(generateSecureId());
            $appearance->setRank($appearanceArray['rank']);
            $appearance->setReason($appearanceArray['reason']);
            $appearance->setMode($appearanceArray['mode']);
            $appearance->setType($appearanceArray['type']);
            $appearance->setCreatedDate($dateTime);
            $appearance->setUpdatedDate($dateTime);

            $appearance->insert();
        }

        return self::$db->lastInsertId();
    }

    /**
     * Tests the endpoint for fetching all the Appearances
     */
    public function testGetAppearances()
    {
        $page = 4;
        $perPage = 3;
        $appearancesCount = 20;

        $appearanceArray = $this->getObjectArray();

        $queryString = http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);

        $this->insertAppearancesToDB($appearancesCount);

        $response = $this->runApp('GET', '/appearances?' . $queryString);
        $this->assertEquals(200, $response->getStatusCode());

        // Let us test pagination firstly
        $resp = json_decode((string) $response->getBody());
        $meta = $resp->meta;

        $this->assertEquals($page, $meta->page);
        $this->assertEquals($perPage, $meta->perPage);
        $this->assertEquals(1, $meta->first_page);
        $this->assertEquals($page - 1, $meta->prev_page);
        $this->assertEquals($page + 1, $meta->next_page);
        $this->assertEquals(ceil($appearancesCount / $perPage), $meta->last_page);
        $this->assertEquals($appearancesCount, $meta->total);

        // Let us continue with testing data
        $appearance = $resp->data[0];
        $this->assertNotNull($appearance->secure_id);
        $this->assertEquals($appearanceArray['mode'], $appearance->mode);
        $this->assertEquals($appearanceArray['reason'], $appearance->reason);
        $this->assertEquals(new \DateTime($appearanceArray['appearanceStartDatetime']), new \DateTime($appearance->appearance_start_datetime));
        $this->assertEquals(new \DateTime($appearanceArray['appearanceEndDatetime']), new \DateTime($appearance->appearance_end_datetime));
        $this->assertEquals($appearanceArray['type'], $appearance->type);
        $this->assertEquals($appearanceArray['rank'], $appearance->rank);
        $this->assertEquals($appearanceArray['status'], $appearance->status);
    }

    /**
     * Tests the endpoint for fetching a specific Appearance
     */
    public function testGetSpecificAppearance()
    {
        $lastInsertedId = $this->insertAppearancesToDB(1);
        $appearanceArray = $this->getObjectArray();

        // Let us test successful fetching of the created Appearance
        $response = $this->runApp('GET', '/appearances/' . $lastInsertedId);
        $this->assertEquals(200, $response->getStatusCode());

        $appearance = json_decode((string) $response->getBody());
        $this->assertNotNull($appearance->secure_id);
        $this->assertEquals($appearanceArray['mode'], $appearance->mode);
        $this->assertEquals($appearanceArray['reason'], $appearance->reason);
        $this->assertEquals(new \DateTime($appearanceArray['appearanceStartDatetime']), new \DateTime($appearance->appearance_start_datetime));
        $this->assertEquals(new \DateTime($appearanceArray['appearanceEndDatetime']), new \DateTime($appearance->appearance_end_datetime));
        $this->assertEquals($appearanceArray['type'], $appearance->type);
        $this->assertEquals($appearanceArray['rank'], $appearance->rank);
        $this->assertEquals($appearanceArray['status'], $appearance->status);

        // Let us test 404 status code
        $response = $this->runApp('GET', '/appearances/' . ($lastInsertedId + 1));
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for deleting a specific Appearance
     */
    public function testDeleteSpecificAppearance()
    {
        $lastInsertedId = $this->insertAppearancesToDB(1);

        // Check that Appearance is in the DB
        $appearance = self::$appearance;
        $this->assertNotFalse($appearance->find($lastInsertedId));

        // Let us test successful deleting of the created Appearance
        $response = $this->runApp('DELETE', '/appearances/' . $lastInsertedId);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertFalse($appearance->find($lastInsertedId));

        // Let us test 404 status code
        $response = $this->runApp('DELETE', '/appearances/' . $lastInsertedId);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests the endpoint for creating a Appearance
     */
    public function testCreateAppearance()
    {
        $appearanceArray = $this->getObjectArray();

        $queryString = http_build_query($appearanceArray);

        // Let's test successful creating of a Appearance
        $response = $this->runApp('POST', '/appearances?' . $queryString);
        $this->assertEquals(201, $response->getStatusCode());

        // Let's use Location Header for fetching just created Appearance
        $appearance = $this->runApp('GET', $response->getHeaders()['Location'][0]);
        $appearance = json_decode((string) $appearance->getBody());

        $this->assertNotNull($appearance->secure_id);
        $this->assertEquals($appearanceArray['mode'], $appearance->mode);
        $this->assertEquals($appearanceArray['reason'], $appearance->reason);
        $this->assertEquals(new \DateTime($appearanceArray['appearanceStartDatetime']), new \DateTime($appearance->appearance_start_datetime));
        $this->assertEquals(new \DateTime($appearanceArray['appearanceEndDatetime']), new \DateTime($appearance->appearance_end_datetime));
        $this->assertEquals($appearanceArray['type'], $appearance->type);
        $this->assertEquals($appearanceArray['rank'], $appearance->rank);
        $this->assertEquals($appearanceArray['status'], $appearance->status);

    }

    /**
     * Tests the endpoint for updating a Appearance
     */
    public function testUpdateAppearance()
    {
        $appearanceArray = $this->getObjectArray();

        $lastInsertedId = $this->insertAppearancesToDB(1);

        // Check that Appearance in the DB
        $appearance = self::$appearance;
        $appearance->find($lastInsertedId);
        $this->assertNotFalse($appearance);
        $this->assertNotNull($appearance->getSecureId());
        $this->assertEquals($appearanceArray['mode'], $appearance->getMode());
        $this->assertEquals($appearanceArray['reason'], $appearance->getReason());
        $this->assertEquals(new \DateTime($appearanceArray['appearanceStartDatetime']), new \DateTime($appearance->getAppearanceStartDatetime()));
        $this->assertEquals(new \DateTime($appearanceArray['appearanceEndDatetime']), new \DateTime($appearance->getAppearanceEndDatetime()));
        $this->assertEquals($appearanceArray['type'], $appearance->getType());
        $this->assertEquals($appearanceArray['rank'], $appearance->getRank());
        $this->assertEquals($appearanceArray['status'], $appearance->getStatus());

        $appearanceUpdateArray = [
            'status' => '1',
            'appearanceEndDatetime' => '2020/11/05 23:59:59',
            'appearanceStartDatetime' => '2020/05/04 14:40:00',
            'secureId' => 'DavXwArUNPQ12HK9EILF',
            'rank' => '2',
            'reason' => 'Արձակուրդ',
            'mode' => '1',
            'type' => '2',
        ];

        $queryString = http_build_query($appearanceUpdateArray);

        // Let's test updating of a Appearance which doesn't exist
        $response = $this->runApp('PUT', '/appearances/' . ($lastInsertedId + 1) . '?' . $queryString);
        $this->assertEquals(404, $response->getStatusCode());

        // Let's test successful updating of the Appearance
        $response = $this->runApp('PUT', "/appearances/$lastInsertedId?" . $queryString);
        $this->assertEquals(204, $response->getStatusCode());

        // Check that Appearance's fields are updated
        $appearance->find($lastInsertedId);
        $this->assertNotNull($appearance->getSecureId());
        $this->assertEquals($appearanceUpdateArray['mode'], $appearance->getMode());
        $this->assertEquals($appearanceUpdateArray['reason'], $appearance->getReason());
        $this->assertEquals(new \DateTime($appearanceUpdateArray['appearanceStartDatetime']), new \DateTime($appearance->getAppearanceStartDatetime()));
        $this->assertEquals(new \DateTime($appearanceUpdateArray['appearanceEndDatetime']), new \DateTime($appearance->getAppearanceEndDatetime()));
        $this->assertEquals($appearanceUpdateArray['type'], $appearance->getType());
        $this->assertEquals($appearanceUpdateArray['rank'], $appearance->getRank());
        $this->assertEquals($appearanceUpdateArray['status'], $appearance->getStatus());
    }
}
