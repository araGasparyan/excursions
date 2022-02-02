<?php
/**
 * Model for Language
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

namespace LinesC\Model;

class Language extends AbstractModel
{
    const STATUS_ACTIVE = 1;
    // Rare languages are hungarian, polish as sometimes Laslo and Piruz use them
    const STATUS_RARE = 2;
    const STATUS_DEACTIVATED = 3;
    const STATUS_REMOVED = 4;

    const TYPE_ARMENIAN = 1;
    const TYPE_IRANIAN = 2;
    const TYPE_GERMANIC = 3;
    const TYPE_ROMANCE = 4;
    const TYPE_SLAVIC = 5;
    const TYPE_GENERAL = 6;

    const RANK_DEFAULT = 1;

    /**
     * Language Id
     *
     * @var  int
     */
    protected $languageId;

    /**
     * Secure Id
     *
     * @var string
     */
    protected $secureId;

    /**
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Additional
     *
     * @var string
     */
    protected $additional;

    /**
     * Description
     *
     * @var string
     */
    protected $description;

    /**
     * Type
     *
     * @var int
     */
    protected $type;

    /**
     * Rank
     *
     * @var int
     */
    protected $rank;

    /**
     * Status
     *
     * @var int
     */
    protected $status;

    /**
     * Created Date
     *
     * @var string
     */
    protected $createdDate;

    /**
     * Updated Date
     *
     * @var string
     */
    protected $updatedDate;

    /**
     * Primary key name
     *
     * @var string
     *
     * @return string
     */
    protected function primaryKey()
    {
         return 'language_id';
    }

    /**
     * Database table name for the model
     *
     * @var string
     *
     * @return string
     */
    protected function tableName()
    {
        return 'languages';
    }

    /**
     * Database fields
     *
     * @var array
     *
     * @return array
     */
    public function tableFields()
    {
        return [
            'language_id' => 'languageId',
            'secure_id' => 'secureId',
            'name' => 'name',
            'additional' => 'additional',
            'description' => 'description',
            'type' => 'type',
            'rank' => 'rank',
            'status' => 'status',
            'language_created_date' => 'createdDate',
            'language_updated_date' => 'updatedDate',
        ];
    }

    /**
     * Get the value of Secure Id
     *
     * @return string
     */
    public function getSecureId()
    {
        return $this->secureId;
    }

    /**
     * Set the value of Secure Id
     *
     * @param string secureId
     *
     * @return self
     */
    public function setSecureId(string $secureId)
    {
        $this->secureId = $secureId;

        return $this;
    }

    /**
     * Get the value of Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of Name
     *
     * @param string name
     *
     * @return self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of Additional
     *
     * @return string
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * Set the value of Additional
     *
     * @param string additional
     *
     * @return self
     */
    public function setAdditional(string $additional)
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * Get the value of Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of Description
     *
     * @param string description
     *
     * @return self
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of Type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of Type
     *
     * @param int type
     *
     * @return self
     */
    public function setType(int $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of Rank
     *
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set the value of Rank
     *
     * @param int rank
     *
     * @return self
     */
    public function setRank(int $rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get the value of Status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of Status
     *
     * @param int status
     *
     * @return self
     */
    public function setStatus(int $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of Created Date
     *
     * @return string
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set the value of Created Date
     *
     * @param \DateTime createdDate
     *
     * @return self
     */
    public function setCreatedDate(\DateTime $createdDate)
    {
        $this->createdDate = $createdDate->format('Y-m-d H:i:s');

        return $this;
    }

    /**
     * Get the value of Updated Date
     *
     * @return string
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * Set the value of Updated Date
     *
     * @param \DateTime updatedDate
     *
     * @return self
     */
    public function setUpdatedDate(\DateTime $updatedDate)
    {
        $this->updatedDate = $updatedDate->format('Y-m-d H:i:s');

        return $this;
    }

    /**
     * Checks if language's status is valid
     *
     * @param int $status
     *
     * @return bool
     */
    public static function isValidStatus(int $status)
    {
        return in_array($status, [self::STATUS_ACTIVE, self::STATUS_RARE, self::STATUS_DEACTIVATED, self::STATUS_REMOVED]);
    }

    /**
     * Get statusses of Language as an associative array
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
                    self::STATUS_ACTIVE => 'Active',
                    self::STATUS_RARE => 'Rare',
                    self::STATUS_DEACTIVATED => 'Deactivated',
                    self::STATUS_REMOVED => 'Removed',
                ];
    }

    /**
     * Checks if language's type is valid
     *
     * @param int $type
     *
     * @return bool
     */
    public static function isValidType(int $type)
    {
        return in_array($type, [self::TYPE_ARMENIAN, self::TYPE_IRANIAN, self::TYPE_GERMANIC, self::TYPE_ROMANCE, self::TYPE_SLAVIC, self::TYPE_GENERAL]);
    }

    /**
     * Get types of Language as an associative array
     *
     * @return array
     */
    public static function getTypes()
    {
        return [
                    self::TYPE_ARMENIAN => 'Armenian',
                    self::TYPE_IRANIAN => 'Iranian',
                    self::TYPE_GERMANIC => 'Germanic',
                    self::TYPE_ROMANCE => 'Romance',
                    self::TYPE_SLAVIC => 'Slavic',
                    self::TYPE_GENERAL => 'General',
                ];
    }

    /**
     * Get ranks of Language as an associative array
     *
     * @return array
     */
    public static function getRanks()
    {
        return [self::RANK_DEFAULT => 'Default'];
    }

    /**
     * Checks if language's rank is valid
     *
     * @param int $rank
     *
     * @return bool
     */
    public static function isValidRank(int $rank)
    {
        return in_array($rank, [self::RANK_DEFAULT]);
    }
}
