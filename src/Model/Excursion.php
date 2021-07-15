<?php
/**
 * Model for Excursion
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

namespace LinesC\Model;

class Excursion extends AbstractModel
{
    /**
     * Excursion Id
     *
     * @var  int
     */
    protected $excursionId;

    /**
     * Secure Id
     *
     * @var string
     */
    protected $secureId;

    /**
     * Group Members Count
     *
     * @var int
     */
    protected $groupMembersCount;

    /**
     * Expected Excursion Start Date
     *
     * @var string
     */
    protected $expectedExcursionStartDate;

    /**
     * Expected Excursion Start Time
     *
     * @var string
     */
    protected $expectedExcursionStartTime;

    /**
     * Verify Start Time In Hours
     *
     * @var int
     */
    protected $verifyStartTimeInHours;

    /**
     * Expected Duration Of Excursion
     *
     * @var int
     */
    protected $expectedDurationOfExcursion;

    /**
     * Excursion Start Date
     *
     * @var string
     */
    protected $excursionStartDate;

    /**
     * Excursion Start Time
     *
     * @var string
     */
    protected $excursionStartTime;

    /**
     * Excursion End Time
     *
     * @var string
     */
    protected $excursionEndTime;

    /**
     * Country
     *
     * @var string
     */
    protected $country;

    /**
     * Description
     *
     * @var string
     */
    protected $description;

    /**
     * Expected Group Members Count
     *
     * @var int
     */
    protected $expectedGroupMembersCount;

    /**
     * Radio Guide
     *
     * @var int
     */
    protected $radioGuide;

    /**
     * Is Free
     *
     * @var int
     */
    protected $isFree;

    /**
     * Additional Info
     *
     * @var string
     */
    protected $additionalInfo;

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
         return 'excursion_id';
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
        return 'excursions';
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
            'excursion_id' => 'excursionId',
            'secure_id' => 'secureId',
            'group_members_count' => 'groupMembersCount',
            'expected_excursion_start_date' => 'expectedExcursionStartDate',
            'expected_excursion_start_time' => 'expectedExcursionStartTime',
            'verify_start_time_in_hours' => 'verifyStartTimeInHours',
            'expected_duration_of_excursion' => 'expectedDurationOfExcursion',
            'excursion_start_date' => 'excursionStartDate',
            'excursion_start_time' => 'excursionStartTime',
            'excursion_end_time' => 'excursionEndTime',
            'country' => 'country',
            'description' => 'description',
            'expected_group_members_count' => 'expectedGroupMembersCount',
            'radio_guide' => 'radioGuide',
            'is_free' => 'isFree',
            'additional_info' => 'additionalInfo',
            'type' => 'type',
            'rank' => 'rank',
            'status' => 'status',
            'excursion_created_date' => 'createdDate',
            'excursion_updated_date' => 'updatedDate',
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
     * Get the value of Group Members Count
     *
     * @return int
     */
    public function getGroupMembersCount()
    {
        return $this->groupMembersCount;
    }

    /**
     * Set the value of Group Members Count
     *
     * @param int groupMembersCount
     *
     * @return self
     */
    public function setGroupMembersCount(int $groupMembersCount)
    {
        $this->groupMembersCount = $groupMembersCount;

        return $this;
    }

    /**
     * Get the value of Expected Excursion Start Date
     *
     * @return string
     */
    public function getExpectedExcursionStartDate()
    {
        return $this->expectedExcursionStartDate;
    }

    /**
     * Set the value of Expected Excursion Start Date
     *
     * @param \DateTime expectedExcursionStartDate
     *
     * @return self
     */
    public function setExpectedExcursionStartDate(\DateTime $expectedExcursionStartDate)
    {
        $this->expectedExcursionStartDate = $expectedExcursionStartDate->format('Y-m-d');

        return $this;
    }

    /**
     * Get the value of Expected Excursion Start Time
     *
     * @return string
     */
    public function getExpectedExcursionStartTime()
    {
        return $this->expectedExcursionStartTime;
    }

    /**
     * Set the value of Expected Excursion Start Time
     *
     * @param \DateTime expectedExcursionStartTime
     *
     * @return self
     */
    public function setExpectedExcursionStartTime(\DateTime $expectedExcursionStartTime)
    {
        $this->expectedExcursionStartTime = $expectedExcursionStartTime->format('H:i:s');

        return $this;
    }

    /**
     * Get the value of Verify Start Time In Hours
     *
     * @return int
     */
    public function getVerifyStartTimeInHours()
    {
        return $this->verifyStartTimeInHours;
    }

    /**
     * Set the value of Verify Start Time In Hours
     *
     * @param int verifyStartTimeInHours
     *
     * @return self
     */
    public function setVerifyStartTimeInHours(int $verifyStartTimeInHours)
    {
        $this->verifyStartTimeInHours = $verifyStartTimeInHours;

        return $this;
    }

    /**
     * Get the value of Expected Duration Of Excursion
     *
     * @return int
     */
    public function getExpectedDurationOfExcursion()
    {
        return $this->expectedDurationOfExcursion;
    }

    /**
     * Set the value of Expected Duration Of Excursion
     *
     * @param int expectedDurationOfExcursion
     *
     * @return self
     */
    public function setExpectedDurationOfExcursion(int $expectedDurationOfExcursion)
    {
        $this->expectedDurationOfExcursion = $expectedDurationOfExcursion;

        return $this;
    }

    /**
     * Get the value of Excursion Start Date
     *
     * @return string
     */
    public function getExcursionStartDate()
    {
        return $this->excursionStartDate;
    }

    /**
     * Set the value of Excursion Start Date
     *
     * @param \DateTime excursionStartDate
     *
     * @return self
     */
    public function setExcursionStartDate(\DateTime $excursionStartDate)
    {
        $this->excursionStartDate = $excursionStartDate->format('Y-m-d');

        return $this;
    }

    /**
     * Get the value of Excursion Start Time
     *
     * @return string
     */
    public function getExcursionStartTime()
    {
        return $this->excursionStartTime;
    }

    /**
     * Set the value of Excursion Start Time
     *
     * @param \DateTime excursionStartTime
     *
     * @return self
     */
    public function setExcursionStartTime(\DateTime $excursionStartTime)
    {
        $this->excursionStartTime = $excursionStartTime->format('H:i:s');

        return $this;
    }

    /**
     * Get the value of Excursion End Time
     *
     * @return string
     */
    public function getExcursionEndTime()
    {
        return $this->excursionEndTime;
    }

    /**
     * Set the value of Excursion End Time
     *
     * @param \DateTime excursionEndTime
     *
     * @return self
     */
    public function setExcursionEndTime(\DateTime $excursionEndTime)
    {
        $this->excursionEndTime = $excursionEndTime->format('H:i:s');

        return $this;
    }

    /**
     * Get the value of Country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set the value of Country
     *
     * @param string country
     *
     * @return self
     */
    public function setCountry(string $country)
    {
        $this->country = $country;

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
     * Get the value of Expected Group Members Count
     *
     * @return int
     */
    public function getExpectedGroupMembersCount()
    {
        return $this->expectedGroupMembersCount;
    }

    /**
     * Set the value of Expected Group Members Count
     *
     * @param int expectedGroupMembersCount
     *
     * @return self
     */
    public function setExpectedGroupMembersCount(int $expectedGroupMembersCount)
    {
        $this->expectedGroupMembersCount = $expectedGroupMembersCount;

        return $this;
    }

    /**
     * Get the value of Radio Guide
     *
     * @return int
     */
    public function getRadioGuide()
    {
        return $this->radioGuide;
    }

    /**
     * Set the value of Radio Guide
     *
     * @param int radioGuide
     *
     * @return self
     */
    public function setRadioGuide(int $radioGuide)
    {
        $this->radioGuide = $radioGuide;

        return $this;
    }

    /**
     * Get the value of Is Free
     *
     * @return int
     */
    public function getIsFree()
    {
        return $this->isFree;
    }

    /**
     * Set the value of Is Free
     *
     * @param int isFree
     *
     * @return self
     */
    public function setIsFree(int $isFree)
    {
        $this->isFree = $isFree;

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

}
