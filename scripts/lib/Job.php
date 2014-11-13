<?php

/**
 * Job - contains data about a job and has the ability to convert this data into JSON.
 * Every agent should return an instance of this class when scraping a html file.
 */
class Job {

    public  $autoTrim = true;
    private $careerId = 6;     // int
    private $companyId = 6732;    // int
    private $currencyId = 5;    // int
    private $userId = 30882;    // int
    private $jobTypeId = 1;     // int
    private $responsibilityId = -1;   // int
    private $statusId = 1;     // int
    private $isClosed = 0;     // int
    private $foreignId;      // string
    private $description;     // string
    private $expireDate;     // string
    private $foreignUrl;     // string
    private $picture;      // string
    private $salaryMax = 0;     // double
    private $salaryMin = 0;     // double
    private $jobIndustry;     // array (assoc)
    private $jobRouting;     // array (assoc)
    private $jobTitle;      // array (assoc)
    private $jobLocations = array();  // array
    private $jobPersons = array();   // array
    private $technicalSupport = array(); // array
    // Validation rules.

    private $validationRules = array(
        'careerId' => 'numeric',
        'companyId' => 'numeric',
        'currencyId' => 'numeric',
        'userId' => 'numeric',
        'jobTypeId' => 'numeric',
        'responsibilityId' => 'numeric',
        'statusId' => 'numeric',
        'salaryMax' => 'numeric',
        'salaryMin' => 'numeric'
    );

    public function Job() {
        $this->setJobIndustry(27);
    }

    public function setCareerId($careerId) {
        $this->validateProperty($careerId, 'careerId');
        $this->careerId = $careerId;
    }

    public function getCareerId() {
        return $this->careerId;
    }

    public function setCompanyId($companyId) {
        //$this->validateProperty($companyId, 'companyId');
        $this->companyId = $companyId;
    }

    public function getCompanyId() {
        return $this->companyId;
    }

    public function setCurrencyId($currencyId) {
        $this->validateProperty($currencyId, 'currencyId');
        $this->currencyId = $currencyId;
    }

    public function getCurrencyId() {
        return $this->currencyId;
    }

    public function setUserId($userId) {
        $this->validateProperty($userId, 'userId');
        $this->userId = $userId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setJobTypeId($jobTypeId) {
        $this->validateProperty($jobTypeId, 'jobTypeId');
        $this->jobTypeId = $jobTypeId;
    }

    public function getJobTypeId() {
        return $this->jobTypeId;
    }

    public function setResponsibilityId($responsibilityId) {
        $this->validateProperty($responsibilityId, 'responsibilityId');
        $this->responsibilityId = $responsibilityId;
    }

    public function getResponsibilityId() {
        $this->responsibilityId;
    }

    public function setStatusId($statusId) {
        $this->validateProperty($statusId, 'statusId');
        $this->statusId = $statusId;
    }

    public function getStatusId() {
        return $this->statusId;
    }

    public function setIsClosed($isClosed) {
        $this->validateProperty($isClosed, 'isClosed');
        $this->isClosed = $isClosed;
    }

    public function getIsClosed() {
        return $this->isClosed;
    }

    public function setForeignId($foreignId) {
        $this->validateProperty($foreignId, 'foreignId');
        $this->foreignId = $foreignId;
    }

    public function getForeignId() {
        return $this->foreignId;
    }

    public function setDescription($description) {
        $this->validateProperty($description, 'description');

        if ($this->autoTrim === true) {
            $description = trim($description);
        }

        $this->description = $description;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setExpireDate($expireDate) {
        $this->validateProperty($expireDate, 'expireDate');
        $this->expireDate = $expireDate;
    }

    public function getExpireDate() {
        return $this->expireDate;
    }

    public function setForeignUrl($foreignUrl) {
        $this->validateProperty($foreignUrl, 'foreignUrl');
        $this->foreignUrl = $foreignUrl;
    }

    public function getForeignUrl() {
        return $this->foreignUrl;
    }

    public function setPicture($picture) {
        $this->validateProperty($picture, 'picture');
        $this->picture = $picture;
    }

    public function getPicture() {
        return $this->picture;
    }

    public function setSalaryMax($salaryMax) {
        $this->validateProperty($salaryMax, 'salaryMax');
        $this->salaryMax = $salaryMax;
    }

    public function getSalaryMax() {
        return $this->salaryMax;
    }

    public function setSalaryMin($salaryMin) {
        $this->validateProperty($salaryMin, 'salaryMin');
        $this->salaryMin = $salaryMin;
    }

    public function getSalaryMin() {
        return $this->salaryMin;
    }

    public function setJobIndustry($industryId) {
        $this->validateProperty($industryId, 'industryId');
        $this->jobIndustry = array(
            'INDUSTRY_ID' => $industryId
        );
    }

    public function getJobIndustry() {
        return $this->jobIndustry;
    }

    public function setJobRouting($email = false, $url = false, $type = false) {

        $this->jobRouting = array(
            'JobRoutingEmail' => $email,
            'ROUTING_URL' => $url,
            'TYPE' => $type
        );
    }

    public function getJobRouting() {
        return $this->jobRouting;
    }

    public function setJobTitle($name) {

        if ($this->autoTrim === true) {
            $name = trim($name);
        }

        $this->jobTitle = array(
            'NAME' => $name
        );
    }

    public function getJobTitle() {
        return $this->jobTitle;
    }

    public function addJobLocation($address, $postal = false, $website = false, $regionId = 273677) {

        if (strlen(trim($address)) === 0) {
            throw new Exception('Invalid job location. Address cannot be empty.');
        }

        if (strlen(trim($regionId)) === 0) {
            throw new Exception('Invalid job location. Region ID cannot be empty.');
        }

        $jobLocation = array(
            'ADDRESS' => $address,
            'POSTAL' => $postal,
            'REGION_ID' => $regionId,
            'WEBSITE' => $website
        );

        $this->jobLocations[] = $jobLocation;
    }

    public function clearJobLocations() {
        $this->jobLocations = array();
    }

    public function addJobPerson($fax = false, $jobTitle = false, $personId = 0, $phone = false) {

        $jobPerson = array(
            'FAX' => $fax,
            'JOBTITLE' => $jobTitle,
            'PERSON_ID' => $personId,
            'PHONE' => $phone
        );

        $this->jobPersons[] = $jobPerson;
    }

    public function clearJobPersons() {
        $this->jobPersons = array();
    }

    public function addTechnicalSupport($email = false, $name = false) {

        $technicalSupport = array(
            'EMAIL' => $email,
            'NAME' => $name
        );

        $this->technicalSupport[] = $technicalSupport;
    }

    public function clearTechnicalSupport() {
        $this->technicalSupport = array();
    }

    public function toJson() {
        $data = array(
            'CAREER_ID' => $this->careerId,
            'COMPANY_ID' => $this->companyId,
            'CURRENCY_ID' => $this->currencyId,
            'DESCRIPTION' => $this->description,
            'EXPIRE_DATE' => $this->expireDate,
            'FOREIGN_ID' => $this->foreignId,
            'FOREIGN_URL' => $this->foreignUrl,
            'IS_CLOSED' => $this->isClosed,
            'JOBTYPE_ID' => $this->jobTypeId,
            'JobIndustry' => $this->jobIndustry,
            'JobLocations' => $this->jobLocations,
            'JobPersons' => $this->jobPersons,
            'JobRouting' => $this->jobRouting,
            'Jobtitle' => $this->jobTitle,
            'PICTURE' => $this->picture,
            'RESPONSIBILITY_ID' => $this->responsibilityId,
            'SALARY_MAX' => $this->salaryMax,
            'SALARY_MIN' => $this->salaryMin,
            'STATUS_ID' => $this->statusId,
            'TechnicalSupport' => $this->technicalSupport,
            'USER_ID' => $this->userId
        );

        return json_encode($data);
    }

    /**
     * Validates the stored data.
     *
     * @return bool True if valid, error message otherwise.
     */
    public function validate() {

        if (strlen(trim($this->foreignId)) === 0) {
            return 'Missing foreign ID';
        }

        if (strlen(trim($this->description)) === 0) {
            return 'Missing description';
        }

        if (count($this->jobLocations) === 0) {
            return 'Missing job location';
        }
        else {
            foreach ($this->jobLocations as $jobLocation) {
                if (strlen(trim($jobLocation['ADDRESS'])) === 0) {
                    return "Address shouldn't be empty";
                }

                if (strlen(trim($jobLocation['REGION_ID'])) === 0) {
                    return "Region ID shouldn't be empty";
                }
            }
        }

        return true;
    }

    /**
     * Validates the specified properties based on validation rules.
     *
     * @param string $value        Value
     * @param string $propertyName Property name
     *
     * @return void
     */
    private function validateProperty($value, $propertyName) {
        if (isset($this->validationRules[$propertyName])) {
            if ($this->validationRules[$propertyName] === 'numeric') {
                if ( ! is_numeric($value)) {
                    throw new Exception("Invalid value '{$value}' for property '{$propertyName}'. Expected numeric value.");
                }
            }
        }
    }

}
