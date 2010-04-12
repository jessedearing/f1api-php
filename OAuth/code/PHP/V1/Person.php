<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Person
 *
 * @author jsingh
 */
class EntityState {
    public static $CREATE = 1;
    public static $UPDATE = 2;
    public static $DELETE = 3;
    public static $UNMODIFIED = 4;
}
class BaseEntity {
    public $createdDate;
    public $lastUpdatedDate;
    public $entityState;
}
class Person extends BaseEntity{
    public $id;
    public $title;
    public $salutation;
    public $prefix;
    public $firstName;
    public $lastName;
    public $suffix;
    public $middleName;
    public $goesByName;
    public $formerName;
    public $gender;
    public $dateOfBirth;
    public $maritalStatus;
    public $householdMemberTypeId;
    public $householdId;
    public $isAuthorized;
    public $status; // id
    public $subStatus; //id
    public $occupation; // id
    public $employer;
    public $school ; //id
    public $denomination; //id
    public $formerChurch;
    public $barCode;
    public $memberEnvelopeCode;
    public $defaultTagComment;
    public $weblinkUserID;
    public $passwordHint;
    public $passwordAnswer;
    public $solicit;
    public $thank;
    public $firstRecord;
    public $lastMatchDate;
   
    // Array of Addresses
    public $addresses;
    // Communication
    public $communications;

}

class Address extends BaseEntity{
    public $addressID ;
    public $householdID;
    public $individualID;

    public $addressTypeID;
    public $addressTypeName;
    public $address1;
    public $address2;
    public $address3;
    public $city;
    public $postalCode;
    public $county;
    public $country;
    public $stProvince;
    public $carrierRoute;
    public $deliveryPoint;
    public $addressDate;
    public $addressComment;
    public $uspsVerified;
    public $addressVerifiedDate;
    public $lastVerificationAttemptDate;
   
}

class Communication extends BaseEntity{
    public $communicationId;
    public $householdID ;
    public $individualID;

    public $communicationTypeID;
    public $communicationTypeName;
    public $communicationGeneralType;
    public $communicationValue;
    public $listed;
    public $communicationComment;
    

}
?>
