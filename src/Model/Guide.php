<?php
/**
 * Model for Guide
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

namespace LinesC\Model;

class Guide extends AbstractModel
{
    // May include more statusses for managing guide's reachibility
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVATED = 2;
    const STATUS_REMOVED = 3;
    const STATUS_VACATION = 4;
    const STATUS_PREGNANCY = 5;
    const STATUS_ILNESS = 6;
    const STATUS_UNREACHABLE = 7;
    const STATUS_ADMINISTRATOR = 8;
    const STATUS_ABSENT = 9;
    const STATUS_DOCTOR = 10;
    const STATUS_BUSY = 11;


    const TYPE_MATENADARAN_GUIDE = 1;
    const TYPE_MATENADARAN_WATCHER = 2;
    const TYPE_MATENADARAN_EMPLOYEE = 3;
    const TYPE_MATENADARAN_INTERN = 4;
    const TYPE_EXTERNAL_GUIDE = 5;
    const TYPE_GENERAL = 6;

    const RANK_DEFAULT = 1;

    const TYPE_POSITION_HALF = 2;
    const TYPE_POSITION_QUARTER = 1;
    const TYPE_POSITION_FULL = 4;
    const TYPE_POSITION_FULL_AND_HALF = 6;

    /**
     * Guide Id
     *
     * @var  int
     */
    protected $guideId;

    /**
     * Secure Id
     *
     * @var string
     */
    protected $secureId;

    /**
     * First Name
     *
     * @var string
     */
    protected $firstName;

    /**
     * Middle Name
     *
     * @var string
     */
    protected $middleName;

    /**
     * Last Name
     *
     * @var string
     */
    protected $lastName;

    /**
     * Birth Date
     *
     * @var string
     */
    protected $birthDate;

    /**
     * Email
     *
     * @var string
     */
    protected $email;

    /**
     * Address
     *
     * @var string
     */
    protected $address;

    /**
     * Affiliation
     *
     * @var string
     */
    protected $affiliation;

    /**
     * Job Title
     *
     * @var string
     */
    protected $jobTitle;

    /**
     * Country
     *
     * @var string
     */
    protected $country;

    /**
     * Education
     *
     * @var string
     */
    protected $education;

    /**
     * Phone
     *
     * @var string
     */
    protected $phone;

    /**
     * Image Path
     *
     * @var string
     */
    protected $imagePath;

    /**
     * Additional Info
     *
     * @var string
     */
    protected $additionalInfo;

    /**
     * Position
     *
     * @var int
     */
    protected $position;

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
         return 'guide_id';
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
        return 'guides';
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
            'guide_id' => 'guideId',
            'secure_id' => 'secureId',
            'first_name' => 'firstName',
            'middle_name' => 'middleName',
            'last_name' => 'lastName',
            'birth_date' => 'birthDate',
            'email' => 'email',
            'address' => 'address',
            'affiliation' => 'affiliation',
            'job_title' => 'jobTitle',
            'country' => 'country',
            'education' => 'education',
            'phone' => 'phone',
            'image_path' => 'imagePath',
            'additional_info' => 'additionalInfo',
            'position' => 'position',
            'description' => 'description',
            'type' => 'type',
            'rank' => 'rank',
            'status' => 'status',
            'guide_created_date' => 'createdDate',
            'guide_updated_date' => 'updatedDate',
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
     * Get the value of First Name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set the value of First Name
     *
     * @param string firstName
     *
     * @return self
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the value of Middle Name
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Set the value of Middle Name
     *
     * @param string middleName
     *
     * @return self
     */
    public function setMiddleName(string $middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * Get the value of Last Name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set the value of Last Name
     *
     * @param string lastName
     *
     * @return self
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get the value of Birth Date
     *
     * @return string
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set the value of Birth Date
     *
     * @param \DateTime birthDate
     *
     * @return self
     */
    public function setBirthDate(\DateTime $birthDate)
    {
        $this->birthDate = $birthDate->format('Y-m-d');

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
     * Get the value of Affiliation
     *
     * @return string
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * Set the value of Affiliation
     *
     * @param string affiliation
     *
     * @return self
     */
    public function setAffiliation(string $affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * Get the value of Job Title
     *
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * Set the value of Job Title
     *
     * @param string jobTitle
     *
     * @return self
     */
    public function setJobTitle(string $jobTitle)
    {
        $this->jobTitle = $jobTitle;

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
     * Get the value of Education
     *
     * @return string
     */
    public function getEducation()
    {
        return $this->education;
    }

    /**
     * Set the value of Education
     *
     * @param string education
     *
     * @return self
     */
    public function setEducation(string $education)
    {
        $this->education = $education;

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
     * Get the value of Image Path
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * Set the value of Image Path
     *
     * @param string imagePath
     *
     * @return self
     */
    public function setImagePath(string $imagePath)
    {
        $this->imagePath = $imagePath;

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
     * Get the value of Position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set the value of Position
     *
     * @param int position
     *
     * @return self
     */
    public function setPosition(int $position)
    {
        $this->position = $position;

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
     * Checks if guide's position is valid
     *
     * @param int $position
     *
     * @return bool
     */
    public static function isValidPosition(int $position)
    {
        return in_array($position, [self::TYPE_POSITION_QUARTER, self::TYPE_POSITION_HALF, self::TYPE_POSITION_FULL, self::TYPE_POSITION_FULL_AND_HALF]);
    }

    /**
     * Get positions of guide as an associative array
     *
     * @return array
     */
    public static function getPositions()
    {
        return [
                    self::TYPE_POSITION_QUARTER => 'Quarter',
                    self::TYPE_POSITION_HALF => 'Half',
                    self::TYPE_POSITION_FULL => 'Full',
                    self::TYPE_POSITION_FULL_AND_HALF => 'Full And Half',
                ];
    }

    /**
     * Checks if guide's status is valid
     *
     * @param int $status
     *
     * @return bool
     */
    public static function isValidStatus(int $status)
    {
        return in_array($status, [self::STATUS_ACTIVE, self::STATUS_DEACTIVATED, self::STATUS_REMOVED, self::STATUS_VACATION, self::STATUS_PREGNANCY, self::STATUS_ILNESS, self::STATUS_UNREACHABLE, self::STATUS_ADMINISTRATOR, self::STATUS_ABSENT, self::STATUS_DOCTOR, self::STATUS_BUSY]);
    }

    /**
     * Get statusses of guide as an associative array
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
                    self::STATUS_ACTIVE => 'Active',
                    self::STATUS_DEACTIVATED => 'Deactivated',
                    self::STATUS_REMOVED => 'Removed',
                    self::STATUS_VACATION => 'Vacation',
                    self::STATUS_PREGNANCY => 'Pregnancy',
                    self::STATUS_ILNESS => 'Ilness',
                    self::STATUS_UNREACHABLE => 'Unreachable',
                    self::STATUS_ADMINISTRATOR => 'Administrator',
                    self::STATUS_ABSENT => 'Absent',
                    self::STATUS_DOCTOR => 'Doctor',
                    self::STATUS_BUSY => 'Busy',
                ];
    }

    /**
     * Checks if guide's type is valid
     *
     * @param int $type
     *
     * @return bool
     */
    public static function isValidType(int $type)
    {
        return in_array($type, [self::TYPE_MATENADARAN_GUIDE, self::TYPE_MATENADARAN_WATCHER, self::TYPE_MATENADARAN_EMPLOYEE, self::TYPE_MATENADARAN_INTERN, self::TYPE_EXTERNAL_GUIDE, self::TYPE_GENERAL]);
    }

    /**
     * Get types of guide as an associative array
     *
     * @return array
     */
    public static function getTypes()
    {
        return [
                    self::TYPE_MATENADARAN_GUIDE => 'Guide',
                    self::TYPE_MATENADARAN_WATCHER => 'Watcher',
                    self::TYPE_MATENADARAN_EMPLOYEE => 'Matenadaran Employee',
                    self::TYPE_MATENADARAN_INTERN => 'Intern',
                    self::TYPE_EXTERNAL_GUIDE => 'External Guide',
                    self::TYPE_GENERAL => 'General',
                ];
    }

    /**
     * Get ranks of guide as an associative array
     *
     * @return array
     */
    public static function getRanks()
    {
        return [self::RANK_DEFAULT => 'Default'];
    }

    /**
     * Checks if guide's rank is valid
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
