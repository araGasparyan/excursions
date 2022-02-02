<?php
/**
 * Model for Appearance
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

namespace LinesC\Model;

class Appearance extends AbstractModel
{
    /**
     * Appearance Id
     *
     * @var  int
     */
    protected $appearanceId;

    /**
     * Secure Id
     *
     * @var string
     */
    protected $secureId;

    /**
     * Mode
     *
     * @var int
     */
    protected $mode;

    /**
     * Reason
     *
     * @var string
     */
    protected $reason;

    /**
     * Appearance Start Datetime
     *
     * @var string
     */
    protected $appearanceStartDatetime;

    /**
     * Appearance End Datetime
     *
     * @var string
     */
    protected $appearanceEndDatetime;

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
         return 'appearance_id';
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
        return 'appearances';
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
            'appearance_id' => 'appearanceId',
            'secure_id' => 'secureId',
            'mode' => 'mode',
            'reason' => 'reason',
            'appearance_start_datetime' => 'appearanceStartDatetime',
            'appearance_end_datetime' => 'appearanceEndDatetime',
            'type' => 'type',
            'rank' => 'rank',
            'status' => 'status',
            'appearance_created_date' => 'createdDate',
            'appearance_updated_date' => 'updatedDate',
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
     * Get the value of Mode
     *
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set the value of Mode
     *
     * @param int mode
     *
     * @return self
     */
    public function setMode(int $mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Get the value of Reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set the value of Reason
     *
     * @param string reason
     *
     * @return self
     */
    public function setReason(string $reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get the value of Appearance Start Datetime
     *
     * @return string
     */
    public function getAppearanceStartDatetime()
    {
        return $this->appearanceStartDatetime;
    }

    /**
     * Set the value of Appearance Start Datetime
     *
     * @param \DateTime appearanceStartDatetime
     *
     * @return self
     */
    public function setAppearanceStartDatetime(\DateTime $appearanceStartDatetime)
    {
        $this->appearanceStartDatetime = $appearanceStartDatetime->format('Y-m-d H:i:s');

        return $this;
    }

    /**
     * Get the value of Appearance End Datetime
     *
     * @return string
     */
    public function getAppearanceEndDatetime()
    {
        return $this->appearanceEndDatetime;
    }

    /**
     * Set the value of Appearance End Datetime
     *
     * @param \DateTime appearanceEndDatetime
     *
     * @return self
     */
    public function setAppearanceEndDatetime(\DateTime $appearanceEndDatetime)
    {
        $this->appearanceEndDatetime = $appearanceEndDatetime->format('Y-m-d H:i:s');

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
