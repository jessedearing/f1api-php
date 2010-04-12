<?php
require_once 'AppConfig.php';
require_once 'OAuthAPIClient.php';
require_once 'Person.php';

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of F1Api
 *
 * @author jsingh
 */
class F1Api {
    private $apiConsumer;
    private $baseUrl;
    public function __construct($facebookid=0) {
        // A. TODO: Get the access_token and token_secret from the database
        //$access_token = '61344b1a-0e88-4ccc-8854-cc6219d83642';
        //$token_secret = '7e01bf18-cc44-4332-b40e-dc4ae501098a';

        $db = new DB_Access();
        $results = $db->GetAuthTokenByUserID($facebookid);
        $row = mssql_fetch_row($results);

        $access_token = $row[2];
        $token_secret = $row[3];
        // B. Create a OAuthAPIClient object and initialize it
        $this->apiConsumer = new OAuthAPIClient(AppConfig::$f1_base_url, AppConfig::$f1_default_church_code, AppConfig::$f1_key, AppConfig::$f1_secret);
        $this->apiConsumer->setPathsFromConfig();
        // This is important. Set the access_token and the token_secret from step A
        $this->apiConsumer->init_AccessToken($access_token, $token_secret);
        $this->baseUrl = $this->apiConsumer->getBaseUrl();
    }

    /***************************START- CORE API Functions********************************/
    public function addHousehold($householdFirstName, $householdLastName){
        $householdName = $householdFirstName.' '.$householdLastName;
        $date = date("Y-m-d");
        // FOR SOME REASON< XML DOESNT WORK. USE JSON INSTEAD
        $requestBody = <<<EOD
    {"household":{"@id":"","@uri":"","@oldID":"","@hCode":"","householdName":"$householdName","householdSortName":"$householdLastName","householdFirstName":"$householdFirstName","lastSecurityAuthorization":null,"lastActivityDate":null,"createdDate":null,"lastUpdatedDate":null}}
EOD;
        $requestUrl = $this->baseUrl.AppConfig::$f1_household_create;
        $result = $this->apiConsumer->postRequest($requestUrl, $requestBody);
        $json_string = json_decode($result, true);
        return $json_string["household"]["@id"];
    }

    public function addPerson($person) {
        /*
        $requestBody = <<<EOD
{"person":{"@id":"","@uri":"","@oldID":"","@iCode":"","@householdID":"$householdId","@oldHouseholdID":"","title":null,"salutation":null,"prefix":null,"firstName":"$firstName","lastName":"$lastName","suffix":null,"middleName":null,"goesByName":null,"formerName":null,"gender":null,"dateOfBirth":null,"maritalStatus":null,"householdMemberType":{"@id":"$householdMemberTypeId","@uri":"https://ftapiair.staging.fellowshiponeapi.com/v1/People/HouseholdMemberTypes/1","name":"Head"},"isAuthorized":"true","status":{"@id":"$statusId","@uri":"","name":null,"comment":null,"date":null,"subStatus":{"@id":"","@uri":"","name":null}},"occupation":{"@id":"","@uri":"","name":null,"description":null},"employer":null,"school":{"@id":"","@uri":"","name":null},"denomination":{"@id":"","@uri":"","name":null},"formerChurch":null,"barCode":null,"memberEnvelopeCode":null,"defaultTagComment":null,"weblink":{"userID":null,"passwordHint":null,"passwordAnswer":null},"solicit":null,"thank":null,"firstRecord":null,"lastMatchDate":null,"createdDate":null,"lastUpdatedDate":null}}
EOD;
         */

        $requestBody = <<<EOD
{"person":{"@id":"","@uri":"","@oldID":"","@iCode":"","@householdID":"$person->householdId","@oldHouseholdID":"","title":"$person->title","salutation":"$person->salutation","prefix":"$person->prefix","firstName":"$person->firstName","lastName":"$person->lastName","suffix":"$person->suffix","middleName":"$person->middleName","goesByName":"$person->goesByName","formerName":"$person->formerChurch","gender":"$person->gender","dateOfBirth":"$person->dateOfBirth","maritalStatus":"$person->maritalStatus","householdMemberType":{"@id":"$person->householdMemberTypeId","@uri":"https://ftapiair.staging.fellowshiponeapi.com/v1/People/HouseholdMemberTypes/1","name":"Head"},"isAuthorized":"true","status":{"@id":"$person->status","@uri":"","name":null,"comment":null,"date":null,"subStatus":{"@id":"$person->subStatus","@uri":"","name":null}},"occupation":{"@id":"$person->occupation","@uri":"","name":null,"description":null},"employer":"$person->employer","school":{"@id":"$person->school","@uri":"","name":null},"denomination":{"@id":"$person->denomination","@uri":"","name":null},"formerChurch":"$person->formerChurch","barCode":"$person->barCode","memberEnvelopeCode":"$person->memberEnvelopeCode","defaultTagComment":"$person->defaultTagComment","weblink":{"userID":"$person->weblinkUserID","passwordHint":"$person->passwordHint","passwordAnswer":"$person->passwordAnswer"},"solicit":"$person->solicit","thank":"$person->thank","firstRecord":null,"lastMatchDate":null,"createdDate":null,"lastUpdatedDate":null}}
EOD;

        // USING CURL
        $requestUrl = $this->baseUrl.AppConfig::$f1_people_create;
        $result = $this->apiConsumer->postRequest($requestUrl, $requestBody);
        $json_string = json_decode($result, true);
        $person_id =  $json_string["person"]["@id"];
        // print 'PersonID '.$persond_id;

        // Addresses
        if(count($person->addresses) > 0) {
            $addresses = $person->addresses;
            for($i=0; $i<count($addresses); $i++) {
                $address = $addresses[$i];
                $address->individualID = $person_id;
                $this->addAddress($address);
            }
        }
        // Communications
        if(count($person->communications) > 0) {
            $communications = $person->communications;
            for($i=0; $i<count($communications); $i++) {
                $communication = $communications[$i];
                $communication->individualID = $person_id;
                $this->addCommunication($communication);
            }
        }
        //print 'New person id is: '.$person_id;
        return $this->getPerson($person_id);
    }

    /*
     * Creates a Communication on the Individual level
     * @communication Communication object.
     * IndividualID is a required field
     */
    public function addCommunication($communication) {
         $requestBody = <<<EOD
        {"communication": {
		"@id": "",
		"@uri": "",
		"household": {
			"@id": "$communication->householdID",
			"@uri": ""
		},
		"person": {
			"@id": "$communication->individualID",
			"@uri": ""
		},
		"communicationType": {
			"@id": "$communication->communicationTypeID",
			"@uri": "",
			"name": null
		},
		"communicationGeneralType": null,
		"communicationValue": "$communication->communicationValue",
		"searchCommunicationValue": null,
		"listed": "true",
		"communicationComment": null,
		"createdDate": null,
		"lastUpdatedDate": null
	}}
EOD;
        $requestUrl = $this->baseUrl.AppConfig::$f1_people_communications;
        $requestUrl = str_replace("{id}", $communication->individualID, $requestUrl);
        return $this->apiConsumer->postRequest($requestUrl, $requestBody);
    }

    /*
     * Updates a Communication on the Individual level
     * @communication Communication object.
     * IndividualID and CommunicationID are required on the object
     */
    public function updateCommunication($communication) {
         $requestBody = <<<EOD
        {"communication": {
		"@id": "$communication->communicationId",
		"@uri": "",
		"household": {
			"@id": "$communication->householdID",
			"@uri": ""
		},
		"person": {
			"@id": "$communication->individualID",
			"@uri": ""
		},
		"communicationType": {
			"@id": "$communication->communicationTypeID",
			"@uri": "",
			"name": null
		},
		"communicationGeneralType": null,
		"communicationValue": "$communication->communicationValue",
		"searchCommunicationValue": null,
		"listed": "true",
		"communicationComment": null,
		"createdDate": null,
		"lastUpdatedDate": null
	}}
EOD;
        $requestUrl = $this->baseUrl.AppConfig::$f1_people_communications_update;
        $requestUrl = str_replace("{id}", $communication->communicationId, $requestUrl);
        $requestUrl = str_replace("{personID}", $communication->individualID, $requestUrl);
        return $this->apiConsumer->postRequest($requestUrl, $requestBody);
    }

    /*
     * Creates an Address on the Individual level
     * @communication Communication object.
     * IndividualID is a required field
     */
    public function addAddress($address) {
        $requestBody = <<<EOD
        {"address": {
		"@id": "",
		"@uri": "",
		"household": {
			"@id": "$address->householdID",
			"@uri": ""
		},
		"person": {
			"@id": "$address->individualID",
			"@uri": ""
		},
		"addressType": {
			"@id": "$address->addressTypeID",
			"@uri": "",
			"name": null
		},
		"address1": "$address->address1",
		"address2": "$address->address2",
		"address3": "$address->address3",
		"city": "$address->city",
		"postalCode": "$address->postalCode",
		"county": "$address->county",
		"country": "$address->country",
		"stProvince": "$address->stProvince",
		"carrierRoute": "$address->carrierRoute",
		"deliveryPoint": "$address->deliveryPoint",
		"addressDate": null,
		"addressComment": null,
		"uspsVerified": "false",
		"addressVerifiedDate": null,
		"lastVerificationAttemptDate": null,
		"createdDate": null,
		"lastUpdatedDate": null
	}}

EOD;
        $requestUrl = $this->baseUrl.AppConfig::$f1_people_address;
        $requestUrl = str_replace("{personID}", $address->individualID, $requestUrl);
        return $this->apiConsumer->postRequest($requestUrl, $requestBody);
    }

    /*
     * Updates an Address on the Individual level
     * @communication Communication object.
     * IndividualID is a required field
     */
    public function updateAddress($address) {
        $requestBody = <<<EOD
        {"address": {
		"@id": "$address->addressID",
		"@uri": "",
		"household": {
			"@id": "$address->householdID",
			"@uri": ""
		},
		"person": {
			"@id": "$address->individualID",
			"@uri": ""
		},
		"addressType": {
			"@id": "$address->addressTypeID",
			"@uri": "",
			"name": null
		},
		"address1": "$address->address1",
		"address2": "$address->address2",
		"address3": "$address->address3",
		"city": "$address->city",
		"postalCode": "$address->postalCode",
		"county": "$address->county",
		"country": "$address->country",
		"stProvince": "$address->stProvince",
		"carrierRoute": "$address->carrierRoute",
		"deliveryPoint": "$address->deliveryPoint",
		"addressDate": null,
		"addressComment": null,
		"uspsVerified": "false",
		"addressVerifiedDate": null,
		"lastVerificationAttemptDate": null,
		"createdDate": null,
		"lastUpdatedDate": null
	}}

EOD;
        $requestUrl = $this->baseUrl.AppConfig::$f1_people_address_update;
        $requestUrl = str_replace("{personID}", $address->individualID, $requestUrl);
        $requestUrl = str_replace("{id}", $address->addressID, $requestUrl);
        return $this->apiConsumer->postRequest($requestUrl, $requestBody);
    }

    /*
     * Gets a person for EDIT. Use this method to get a person meant to be updated.
     * If you want to just view the person, use showPerson
     */
    public function getPerson($personId, $extraData = false) {
        $requestUrl = $this->baseUrl.AppConfig::$f1_people_edit;
        $requestUrl = str_replace("{id}", $personId, $requestUrl);
        $response = $this->apiConsumer->doRequest($requestUrl);
        $person = $this->populatePerson($response);
        if($extraData) {
            $person->addresses = $this->getAddresses($personId);
            $person->communications = $this->getCommunications($personId);
        }
        return $person;
    }

    public function getAddresses($personId) {
        $addresses = array();
        $requestUrl = $this->baseUrl.AppConfig::$f1_people_address;
        $requestUrl = str_replace("{personID}", $personId, $requestUrl);
        $response = $this->apiConsumer->doRequest($requestUrl);
        $json_array = json_decode(strstr($response, '{"addresses":{'), true);
        if (isset($json_array['addresses']['address'])) {
            $json_length = count($json_array['addresses']['address']);
        }
        for ($i = 0; $i < $json_length; $i++) {
            $x = $json_array['addresses']['address'][$i];
            $addresses[] = $this->populateAddressFromJsonObject($x);
        }
        return $addresses;
    }

    public function getCommunications($personId) {
        $communications = array();
        $requestUrl = $this->baseUrl.AppConfig::$f1_people_communications;
        $requestUrl = str_replace("{id}", $personId, $requestUrl);
        $response = $this->apiConsumer->doRequest($requestUrl);

        $comm_json_array = json_decode(strstr($response, '{"communications":{'), true);
        if (isset($comm_json_array['communications']['communication'])) {
            $comm_json_length = count($comm_json_array['communications']['communication']);
        }
        for ($i = 0; $i < $comm_json_length; $i++) {
            $x = $comm_json_array['communications']['communication'][$i];
            $communications[] = $this->populateCommunicationFromJsonObject($x);
        }
        return $communications;
    }
    
    public function getOccupations() {
        $returnUrl = $this->baseUrl.AppConfig::$f1_people_occupations;
        return $this->apiConsumer->doRequest($returnUrl);
    }

    public function getAllOccupations($results) {
        $json_array = json_decode(strstr($results, '{"occupations":{'), true);
        return $json_array;
    }

    /*
     * Gets a person to show/view a person. DO NOT use this method if you want to subsequently use the person
     * object to update. Use getPerson instead
     */
    public function showPerson($personId) {
        $requestUrl = $this->baseUrl.AppConfig::$f1_people_show;
        $requestUrl = str_replace("{id}", $personId, $requestUrl);
        return $this->apiConsumer->doRequest($requestUrl);
    }

    public function updatePerson($person) {
        // AppConfig::$simulateRequest = 1;

        $requestBody = <<<EOD
{"person":{"@id":"$person->id","@uri":"","@oldID":"","@iCode":"","@householdID":"$person->householdId","@oldHouseholdID":"","title":"$person->title","salutation":"$person->salutation","prefix":"$person->prefix","firstName":"$person->firstName","lastName":"$person->lastName","suffix":"$person->suffix","middleName":"$person->middleName","goesByName":"$person->goesByName","formerName":"$person->formerName","gender":"$person->gender","dateOfBirth":"$person->dateOfBirth","maritalStatus":"$person->maritalStatus","householdMemberType":{"@id":"$person->householdMemberTypeId","@uri":"https://ftapiair.staging.fellowshiponeapi.com/v1/People/HouseholdMemberTypes/1","name":"Head"},"isAuthorized":"true","status":{"@id":"$person->status","@uri":"","name":null,"comment":null,"date":null,"subStatus":{"@id":"$person->subStatus","@uri":"","name":null}},"occupation":{"@id":"$person->occupation","@uri":"","name":null,"description":null},"employer":"$person->employer","school":{"@id":"$person->school","@uri":"","name":null},"denomination":{"@id":"$person->denomination","@uri":"","name":null},"formerChurch":"$person->formerChurch","barCode":"$person->barCode","memberEnvelopeCode":"$person->memberEnvelopeCode","defaultTagComment":"$person->defaultTagComment","weblink":{"userID":"$person->weblinkUserID","passwordHint":"$person->passwordHint","passwordAnswer":"$person->passwordAnswer"},"solicit":"$person->solicit","thank":"$person->thank","firstRecord":"$person->firstRecord","lastMatchDate":"$person->lastMatchDate","createdDate":"$person->createdDate","lastUpdatedDate":"$person->lastUpdatedDate"}}
EOD;

/*
$requestBody = <<<EOD
{"person":{"@id":"$person->id","@uri":"","@oldID":"","@iCode":"","@householdID":"$person->householdId","@oldHouseholdID":"","title":null,"salutation":null,"prefix":null,"firstName":"$person->firstName","lastName":"$person->lastName","suffix":null,"middleName":null,"goesByName":null,"formerName":null,"gender":null,"dateOfBirth":null,"maritalStatus":null,"householdMemberType":{"@id":"$person->householdMemberTypeId","@uri":"https://ftapiair.staging.fellowshiponeapi.com/v1/People/HouseholdMemberTypes/1","name":"Head"},"isAuthorized":"true","status":{"@id":"$person->status","@uri":"","name":null,"comment":null,"date":null,"subStatus":{"@id":"","@uri":"","name":null}},"occupation":{"@id":"","@uri":"","name":null,"description":null},"employer":null,"school":{"@id":"","@uri":"","name":null},"denomination":{"@id":"","@uri":"","name":null},"formerChurch":null,"barCode":null,"memberEnvelopeCode":null,"defaultTagComment":null,"weblink":{"userID":null,"passwordHint":null,"passwordAnswer":null},"solicit":null,"thank":null,"firstRecord":null,"lastMatchDate":null,"createdDate":null,"lastUpdatedDate":null}}
EOD;*/


        // print $requestBody;
        if($person->entityState != EntityState::$UNMODIFIED) {
            $requestUrl = $this->baseUrl.AppConfig::$f1_people_update;
            $requestUrl = str_replace("{id}", $person->id, $requestUrl);
            $requestUrl .= '.json';
            $this->apiConsumer->postRequest($requestUrl, $requestBody);
        }

        // Addresses
        if(count($person->addresses) > 0) {
            $addresses = $person->addresses;
            for($i=0; $i<count($addresses); $i++) {
                $address = $addresses[$i];
                $address->individualID = $person->id;
                if($address->entityState != EntityState::$UNMODIFIED) {
                    if($address->entityState == EntityState::$CREATE) {
                        $this->addAddress($address);
                    } else if($address->entityState == EntityState::$DELETE) {
                        // $this-deletedAddress($address);
                    } else {
                        $this->updateAddress($address);
                    }
                }
            }
        }
        // Communications
        if(count($person->communications) > 0) {
            $communications = $person->communications;
            for($i=0; $i<count($communications); $i++) {
                $communication = $communications[$i];
                $communication->individualID = $person->id;
                if($communication->entityState != EntityState::$UNMODIFIED) {
                    if($communication->entityState == EntityState::$CREATE) {
                        $this->addCommunication($communication);
                    } else if($communication->entityState == EntityState::$DELETE) {
                        // $this-deletedAddress($address);
                    } else {
                        $this->updateCommunication($communication);
                    }
                }
            }
        }
        return $this->getPerson($person->id);
    }

    /*
     * Populates a Person object from the raw response obtained from a EDIT or SHOW API call
     * @rawResponse response received from EDIT or SHOW request, request is json representation of a Person object
     */
    public function populatePerson($rawResponse){
        // print $response;
        $decoded_str = strstr($rawResponse, '{"person":{');
        // print $decoded_str;
        if(strlen($decoded_str) > 0) {
            $json_array = json_decode($decoded_str, true);
            $x = $json_array['person'];
            return $this->populatePersonFromJsonObject($x);
        }
        return null;
    }

    private function populatePersonFromJsonObject($x){
        $person = new Person();
        $person->id = $x['@id'];
        $person->householdId= $x['@householdID'];
        $person->title= $x['title'];
        $person->salutation= $x['salutation'];
        $person->prefix= $x['prefix'];
        $person->firstName = $x['firstName'];
        $person->lastName= $x['lastName'];
        $person->suffix= $x['suffix'];
        $person->middleName= $x['middleName'];
        $person->goesByName= $x['goesByName'];
        $person->formerName= $x['formerName'];
        $person->gender= $x['gender'];
        $person->dateOfBirth= $x['dateOfBirth'];
        $person->maritalStatus= $x['maritalStatus'];
        $person->householdMemberTypeId= $x['householdMemberType']['@id'];
        $person->isAuthorized= $x['isAuthorized'];
        $person->status= $x['status']['@id'];
        $person->subStatus= $x['status']['subStatus']['@id'];
        $person->occupation= $x['occupation']['@id'];
        $person->employer= $x['employer'];
        $person->school = $x['school']['@id'];
        $person->denomination= $x['denomination']['@id'];
        $person->formerChurch= $x['formerChurch'];
        $person->barCode= $x['barCode'];
        $person->memberEnvelopeCode= $x['memberEnvelopeCode'];
        $person->defaultTagComment= $x['defaultTagComment'];
        $person->weblinkUserID= $x['weblink']['userID'];
        $person->passwordHint= $x['weblink']['passwordHint'];
        $person->passwordAnswer= $x['weblink']['passwordAnswer'];
        $person->solicit= $x['solicit'];
        $person->thank= $x['thank'];
        $person->firstRecord= $x['firstRecord'];
        $person->lastMatchDate= $x['lastMatchDate'];
        $person->createdDate= $x['createdDate'];
        $person->lastUpdatedDate= $x['lastUpdatedDate'];

        // Addresses
        $addresses = $x['addresses']['address'];
        // Drill through addresses
        if (count($addresses) > 0) {
            for ($i = 0; $i < count($addresses); $i++) {
                $y = $addresses[$i];
                $address = $this->populateAddressFromJsonObject($y);
                $person->addresses[] = $address;
            }
        }

        // Are there comm values?
        $communications = $x['communications']['communication'];
        // Drill through comm values.
        if (count($communications) > 0) {
            for ($j = 0; $j < count($communications); $j++) {
                $z = $communications[$j];
                $communication = $this->populateCommunicationFromJsonObject($z);
                $person->communications[] = $communication;
            }
        }
        return $person;
    }

    private function populateAddressFromJsonObject($y) {
        $address = new Address();
        $address->addressID = $y['@id'];
        $address->individualID =  $y['person']['@id'];
        $address->householdID =  $y['household']['@id'];

        $address->address1 = $y['address1'];
        $address->address2 = $y['address2'];
        $address->address3 = $y['address3'];
        $address->city = $y['city'];
        $address->stProvince = $y['stProvince'];
        $address->postalCode = $y['postalCode'];
        $address->addressTypeID = $y['addressType']['@id'];
        $address->addressTypeName = $y['addressType']['name'];
        $address->county = $y['county'];
        $address->country = $y['country'];
        $address->carrierRoute = $y['carrierRoute'];
        $address->deliveryPoint = $y['deliveryPoint'];
        $address->addressDate = $y['addressDate'];
        $address->addressComment = $y['addressComment'];
        $address->uspsVerified = $y['uspsVerified'];
        $address->addressVerifiedDate = $y['addressVerifiedDate'];
        $address->lastVerificationAttemptDate = $y['lastVerificationAttemptDate'];
        $address->createdDate = $y['createdDate'];
        $address->lastUpdatedDate = $y['lastUpdatedDate'];
        return $address;
    }

    private function populateCommunicationFromJsonObject($z) {
        $communication = new Communication();
        $communication->communicationId = $z['@id'];
        $communication->individualID =  $z['person']['@id'];
        $communication->householdID =  $z['household']['@id'];

        $communication->communicationGeneralType = $z['communicationGeneralType'];
        $communication->communicationTypeID = $z['communicationType']['@id'];
        $communication->communicationTypeName = $z['communicationType']['name'];
        $communication->communicationValue = $z['communicationValue'];
        $communication->listed = $z['listed'];
        $communication->communicationComment = $z['communicationComment'];
        $communication->createdDate = $z['createdDate'];
        $communication->lastUpdatedDate = $z['lastUpdatedDate'];
        return $communication;
    }

    public function getStatuses() {
        $requestUrl = $this->baseUrl.AppConfig::$f1_statuses_list;
        return $this->apiConsumer->doRequest($requestUrl);
    }

    public function getHouseholdMemberTypes() {
        $requestUrl = $this->baseUrl.AppConfig::$f1_householdMemberTypes_list;
        return $this->apiConsumer->doRequest($requestUrl);
    }

    public function peopleSearch($firstName, $lastName, $eMail) {
        // Array to store Individual Id of the person matching the search criteria
        $found_individuals = array();

        //Call API to perform the search
        $requestUrl = $this->baseUrl.AppConfig::$f1_people_search.'?searchFor='.$lastName.','.$firstName.'&communication='.$eMail.'&Include=addresses,communications';
        $search_results = $this->apiConsumer->doRequest($requestUrl);
        $json_array = json_decode(strstr($search_results, '{"results":{'), true);

        // Results found?
        if (isset($json_array['results']['person'])) {
            $json_length = count($json_array['results']['person']);
        }


        // Loop through each found person
        for ($i = 0; $i < $json_length; $i++) {
            $x = $json_array['results']['person'][$i];
            $person = $this->populatePersonFromJsonObject($x);
            $found_individuals[] = $person;
            /*
            $first_name = $x['firstName'];
            $last_name = $x['lastName'];
            $marital = $x['maritalStatus'];
            $gender = $x['gender'];
            $birthdate = $x['dateOfBirth'];
            $household = $x['householdMemberType']['value'];
            $person_id = $x['@id'];
           
            // Get the Communication(Email) value for this person, and find it it matches
            $commPath = str_replace("{id}", $person_id, AppConfig::$f1_people_communications);
            $requestUrl = $this->baseUrl.$commPath;
            $search_results = $this->apiConsumer->doRequest($requestUrl);
            $json_array = json_decode(strstr($search_results, '{"communications":{'), true);

            // Results found?
            if (isset($json_array['communications']['communication'])) {
                $json_length = count($json_array['communications']['communication']);
            }

            for ($i = 0; $i < $json_length; $i++) {
                $x = $json_array['communications']['communication'][$i];
                $commType_name = $x['communicationType']['name'];
                $commType_Id = $x['communicationType']['@id'];
                $comm_Email = $x['communicationValue'];
                if(($commType_Id == 4 || $commType_Id == 12) && $comm_Email == $eMail) { // If this is an Email or Alternate Email and the email matches the search criteria
                    $found_individuals[] =  $person_id;
                }
            }
            */
        }
        return $found_individuals;
    }
    /***************************END- CORE API Functions**********************************/

    /***************************BEGIN- Helper Functions***********************************/

    public function getBaseUrl() {
        return  $this->baseUrl;
    }

    /*
     * Make a request using HTTP GET
     */
    public function doRequest($requestURL, $nonOAuthHeader = array("Accept: application/json"), $successHttpCode = 200) {
        return $this->apiConsumer->doRequest($requestURL, $nonOAuthHeader, $successHttpCode );
    }

    /*
     * Make a request using HTTP Post
     */
    public function postRequest($requestURL, $requestBody = "", $nonOAuthHeader = array("Accept: application/json",  "Content-type: application/json"), $successHttpCode = 201){
        return $this->apiConsumer->postRequest($requestURL, $requestBody, $nonOAuthHeader,  $successHttpCode );
    }
    /***************************END- Helper Functions*************************************/
}
?>
