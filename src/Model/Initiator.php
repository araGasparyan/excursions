<?php
/**
 * Model for Initiator
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

namespace LinesC\Model;

class Initiator extends AbstractModel
{
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVATED = 2;
    const STATUS_REMOVED = 3;

    const TYPE_ORGANIZATION_TOURISM = 1;
    const TYPE_ORGANIZATION_SCHOOL = 2;
    const TYPE_ORGANIZATION_EMBASSY = 3;
    const TYPE_ORGANIZATION_GENERAL = 4;
    const TYPE_PERSON_GUIDE = 5;
    const TYPE_PERSON_COLLEAGUE = 6;
    const TYPE_PERSON_VISITOR = 7;
    const TYPE_GENERAL = 8;

    const RANK_DEFAULT = 1;

    /**
     * Initiator Id
     *
     * @var  int
     */
    protected $initiatorId;

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
     * Address
     *
     * @var string
     */
    protected $address;

    /**
     * Email
     *
     * @var string
     */
    protected $email;

    /**
     * Phone
     *
     * @var string
     */
    protected $phone;

    /**
     * Website
     *
     * @var string
     */
    protected $website;

    /**
     * Additional Info
     *
     * @var string
     */
    protected $additionalInfo;

    /**
     * Identifier
     *
     * @var int
     */
    protected $identifier;

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
         return 'initiator_id';
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
        return 'initiators';
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
            'initiator_id' => 'initiatorId',
            'secure_id' => 'secureId',
            'name' => 'name',
            'address' => 'address',
            'email' => 'email',
            'phone' => 'phone',
            'website' => 'website',
            'additional_info' => 'additionalInfo',
            'identifier' => 'identifier',
            'type' => 'type',
            'rank' => 'rank',
            'status' => 'status',
            'initiator_created_date' => 'createdDate',
            'initiator_updated_date' => 'updatedDate',
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
     * Get the value of Address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the value of Address
     *
     * @param string address
     *
     * @return self
     */
    public function setAddress(string $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get the value of Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of Email
     *
     * @param string email
     *
     * @return self
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of Phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set the value of Phone
     *
     * @param string phone
     *
     * @return self
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get the value of Website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set the value of Website
     *
     * @param string website
     *
     * @return self
     */
    public function setWebsite(string $website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get the value of Additional Info
     *
     * @return string
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }

    /**
     * Set the value of Additional Info
     *
     * @param string additionalInfo
     *
     * @return self
     */
    public function setAdditionalInfo(string $additionalInfo)
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }

    /**
     * Get the value of Identifier
     *
     * @return int
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set the value of Identifier
     *
     * @param int identifier
     *
     * @return self
     */
    public function setIdentifier(int $identifier)
    {
        $this->identifier = $identifier;

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
     * Checks if initiator's status is valid
     *
     * @param int $status
     *
     * @return bool
     */
    public static function isValidStatus(int $status)
    {
        return in_array($status, [self::STATUS_ACTIVE, self::STATUS_DEACTIVATED, self::STATUS_REMOVED]);
    }

    /**
     * Get statusses of initiator as an associative array
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
                    self::STATUS_ACTIVE => 'Active',
                    self::STATUS_DEACTIVATED => 'Deactivated',
                    self::STATUS_REMOVED => 'Removed',
                ];
    }

    /**
     * Checks if initiator's type is valid
     *
     * @param int $type
     *
     * @return bool
     */
    public static function isValidType(int $type)
    {
        return in_array($type, [self::TYPE_ORGANIZATION_TOURISM, self::TYPE_ORGANIZATION_SCHOOL, self::TYPE_ORGANIZATION_EMBASSY, self::TYPE_ORGANIZATION_GENERAL, self::TYPE_PERSON_GUIDE, self::TYPE_PERSON_COLLEAGUE, self::TYPE_PERSON_VISITOR, self::TYPE_GENERAL]);
    }

    /**
     * Get types of initiator as an associative array
     *
     * @return array
     */
    public static function getTypes()
    {
        return [
                    self::TYPE_ORGANIZATION_TOURISM => 'Tour Agency',
                    self::TYPE_ORGANIZATION_SCHOOL => 'School',
                    self::TYPE_ORGANIZATION_EMBASSY => 'Embassy',
                    self::TYPE_ORGANIZATION_GENERAL => 'General Organization',
                    self::TYPE_PERSON_GUIDE => 'Guide',
                    self::TYPE_PERSON_COLLEAGUE => 'Colleague',
                    self::TYPE_PERSON_VISITOR => 'Visitor',
                    self::TYPE_GENERAL => 'General',
                ];
    }

    /**
     * Get ranks of initiator as an associative array
     *
     * @return array
     */
    public static function getRanks()
    {
        return [self::RANK_DEFAULT => 'Default'];
    }

    /**
     * Checks if initiator's rank is valid
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
