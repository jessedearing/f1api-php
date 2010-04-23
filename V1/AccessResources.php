<?php
require_once 'AppConfig.php';
//require_once 'OAuthAPIClient.php';
require_once 'F1Api.php';
require_once 'Person.php';

// Get the access token here
/*
$access_token = '61344b1a-0e88-4ccc-8854-cc6219d83642';
$token_secret = '7e01bf18-cc44-4332-b40e-dc4ae501098a';
$apiConsumer = new OAuthAPIClient(AppConfig::$f1_base_url, AppConfig::$f1_default_church_code, AppConfig::$f1_key, AppConfig::$f1_secret);
$apiConsumer->setPathsFromConfig();
*/
$personId = 24643185;
//$personId = 1;
$apiConsumer = new F1Api();
/*
$decoded_str = strstr($apiConsumer->getPerson($personId), '{"person":{');
print 'decoded string:'.$decoded_str;

$json_array = json_decode($decoded_str, true);
print $json_array['person']['firstName'];
 */

//
// Get a person
//
/*
$response = $apiConsumer->getPerson($personId);
print '<br/>'."Person json:"."<br/>".$response."<br/>";

?>
<pre><?php print_r($response); ?></pre>
<?php
$person = $apiConsumer->populatePerson($response);
print "id:".$person->id.", fname:".$person->firstName.", lname:".$person->lastName.', statusId: '.$person->status.', substatus'.$person->subStatus.", householdMemberTypeId:".$person->householdMemberTypeId.AppConfig::$lineBreak;


// Update a person
// IndID=24643185&HsdID=15809155

$household_id = 15809155;
$statusId = 1;
$householdMemberTypeId = 101;
$person->householdId = $household_id;
$person->status = $statusId;
$person->householdMemberTypeId = $householdMemberTypeId;
$person->employer = "fellowship technologies";
//$person->id = $personId;
$apiConsumer->updatePerson($person);
exit;
//print 'FirstName: '.$person->firstName;
?>
<pre><?php print_r($json_array); ?></pre>
<?php
// $apiConsumer->init_AccessToken($access_token, $token_secret);

*/

//
// GET STATUSES
//
/*
$statuses = $apiConsumer->getStatuses();
//print $statuses;
$statusId = null;
$json_string = json_decode($statuses, true);
$json_length = count($json_string['statuses']['status']);
//print 'length:'.$json_length;
for ($i = 0; $i < $json_length; $i++) {
    $x = $json_string['statuses']['status'][$i];
    //print $x['@id'].', '.$x['name'];
    if(strcmp(strtolower($x['name']), "new from facebook") == 0 ) {
        $statusId = $x['@id'];
        // break;
    }
}
print 'StatusID: '.$statusId;
*/

//
// GET Address Types
//
/*
$requestUrl = $apiConsumer->getBaseUrl().AppConfig::$f1_addresstypes;
$addressTypes = $apiConsumer->doRequest($requestUrl);
$json_string = json_decode($addressTypes, true);
$json_length = count($json_string['addressTypes']['addressType']);
//print 'length:'.$json_length;
for ($i = 0; $i < $json_length; $i++) {
    $x = $json_string['addressTypes']['addressType'][$i];
    print $x['@id'].':'.$x['name']." ";
}
*/

//
// GET Communication Types
//
/*
$requestUrl = $apiConsumer->getBaseUrl().AppConfig::$f1_communicationtypes;
$commTypes = $apiConsumer->doRequest($requestUrl);
$json_string = json_decode($commTypes, true);
$json_length = count($json_string['communicationTypes']['communicationType']);
//print 'length:'.$json_length;
for ($i = 0; $i < $json_length; $i++) {
    $x = $json_string['communicationTypes']['communicationType'][$i];
    print $x['@id'].':'.$x['name']." ";
}
*/
//
// People Search
//

$firstName = "Jas";
$lastName = "singh";
$eMail = "jsingh@fellowshiptech.com";

/*
$firstName = "Klien";
$lastName = "Kelly";
$eMail = "kleinke49@gmail.com";*/
/*
$found_individuals = $apiConsumer->peopleSearch($firstName, $lastName, $eMail);
print 'Found '.count($found_individuals).' individuals';

if(count($found_individuals) > 0) {
    for($i=0; $i<count($found_individuals); $i++) {
        $person = $found_individuals[$i];
        //print $person->firstName.$person->lastName;
        if(strtolower($person->firstName) == strtolower($firstName) && strtolower($person->lastName) == strtolower($lastName)) {
            //print 'Hiii';
            $communications = $person->communications;
            if(count($communications) > 0){
                for($j=0; $j<count($communications); $j++) {
                    $communication = $communications[$j];
                    if(strtolower($communication->communicationValue) == strtolower($eMail)){
                        $found_individual = $person;
                    }
                }
            }
        }
    }
}

?>
<pre><?php print_r($found_individual); ?></pre>
<?php
*/
// People Search
// '&mode=demo'.
//','.$firstName.
/*
$requestUrl = $apiConsumer->getBaseUrl().AppConfig::$f1_people_search.'?searchFor='.$lastName.','.$firstName.'&communication='.$eMail;;
// print $requestUrl;
$httpHeaders = array(
        "Accept: application/json",
        "Content-type: application/json"); // This is important. This tells the API that the incoming request data is in form of json
// $search_results = $apiConsumer->sendRequest("GET", $requestUrl, $httpHeaders, "", 200);
$search_results = $apiConsumer->doRequest($requestUrl);
$json_array = json_decode(strstr($search_results, '{"results":{'), true);

// Results found?
if (isset($json_array['results']['person'])) {
    $json_length = count($json_array['results']['person']);
}


for ($i = 0; $i < $json_length; $i++) {
    $x = $json_array['results']['person'][$i];

    $first_name = $x['firstName'];
    $individual_id = $x['@id'];
    $last_name = $x['lastName'];
    $marital = $x['maritalStatus'];
    $gender = $x['gender'];
    $birthdate = $x['dateOfBirth'];
    $household = $x['householdMemberType']['value'];

    $h = '';
    $h .=	$individual_id;
    $h .=	$first_name;
    $h .=	$last_name;
    $h .=	$marital;
    $h .=	$gender;
    $h .=	$household ;
    $h .=	$birthdate;

    echo $h;
}
*/

//
// GET HOUSEHOLD MEMBER TYPES
//
        /*
        $memberTypes = $apiConsumer->getHouseholdMemberTypes();
        // print $memberTypes;

        $householdMemberTypeId = null;
        $json_string = json_decode($memberTypes, true);
        $json_length = count($json_string['householdMemberTypes']['householdMemberType']);
        for ($i = 0; $i < $json_length; $i++) {
            $x = $json_string['householdMemberTypes']['householdMemberType'][$i];
            // print $x['@id'].', '.$x['value'];
            if(strcmp(strtolower($x['name']), "visitor") == 0 ) {
                $householdMemberTypeId = $x['@id'];
                break;
            }
        }
        print 'HouseholdMemberTypeID '.$householdMemberTypeId;
        */
// exit;

//
// CREATE A HOUSEHOLD
//
/*
        $result = $apiConsumer->addHousehold("Test", "FB", AppConfig::$f1_household_create);
        // Print information about the Household
        $json_string = json_decode($result, true);
        $household_id =  $json_string["household"]["@id"];
        print 'HouseholdID: '.$household_id;
        exit;
 */

//
// CREATE A Person
//

$household_id = 15809208;
// $statusId = 1;// Member
$statusId = 15609; // New from Facebook
$householdMemberTypeId = 101; // visitor
//
// Get a person
//
/*
$personId = 24643475;
$get_person = $apiConsumer->getPerson($personId);
print 'Get person'.$get_person->firstName.':END';
*/

// Create a person with no Address or Comm values
/*
$person = new Person();
$person->firstName = 'Test';
$person->lastName = 'FB'.date( "d/m/Y", time() );
$person->gender = 'Male';
$person->householdMemberTypeId = 101;
$person->status = $statusId;
$person->householdId = $household_id;
$person->maritalStatus = "Married";
$created_person = $apiConsumer->addPerson($person);
?>
<pre><?php print_r($created_person); ?></pre>
<?php
*/

// Create a person with 1 Address with no Comm values
/*
$addressTypeId = 1; //1, Primary 2, Secondary 3, College 4, Vacation 5, Business 6, Org 7, Previous 8, Statement 101, Mail Returned Incorrect
$person = new Person();
$person->firstName = 'Test';
$person->goesByName = "1 Address, no Comm";
$person->lastName = 'FB'.date( "d/m/Y", time() );
$person->gender = 'Male';
$person->householdMemberTypeId = 101;
$person->status = $statusId;
$person->householdId = $household_id;
$person->maritalStatus = "Married";
$address = new Address();
$address->address1 = "6363 N State Hwy 161";
$address->address2 = "Suite 200";
$address->city = "Irving";
$address->stProvince = "Texas";
$address->country = "USA";
$address->county = "Dallas";
$address->addressTypeID = $addressTypeId;
$person->addresses[] = $address;
$created_person = $apiConsumer->addPerson($person);
?>
<pre><?php print_r($created_person); ?></pre>
<?php
*/

// Create a person with no address and 1 Comm value
/*
$communicationTypeId = 1;//1:Home Phone 2:Work Phone 3:Mobile Phone 4:Email 12:Alternate Email 13:Vacation Phone 14:Pager 15:Children Phone 101:Fax 102:Web Address 103:Previous Phone 106:CR Safe Phone 126:School Phone 127:Work Email 128:School Email 129:IM Address 133:Alternate Phone 138:Emergency Phone
$person = new Person();
$person->firstName = 'Test';
$person->goesByName = "No Address, 1 Phone";
$person->lastName = 'FB'.date( "d/m/Y", time() );
$person->gender = 'Male';
$person->householdMemberTypeId = 101;
$person->status = $statusId;
$person->householdId = $household_id;
$person->maritalStatus = "Married";
$comm = new Communication();
$comm->communicationTypeID = 1; // Home Phone
$comm->communicationValue = "1234567890";
$person->communications[] = $comm;
$created_person = $apiConsumer->addPerson($person);
?>
<pre><?php print_r($created_person); ?></pre>
<?php
*/

// create a person with address and comm values
/*
$communicationTypeId = 1;//1:Home Phone 2:Work Phone 3:Mobile Phone 4:Email 12:Alternate Email 13:Vacation Phone 14:Pager 15:Children Phone 101:Fax 102:Web Address 103:Previous Phone 106:CR Safe Phone 126:School Phone 127:Work Email 128:School Email 129:IM Address 133:Alternate Phone 138:Emergency Phone
$person = new Person();
$person->firstName = 'Test';
$person->goesByName = "2Address, email";
$person->lastName = 'FB'.date( "d/m/Y", time() );
$person->gender = 'Male';
$person->householdMemberTypeId = 101;
$person->status = $statusId;
$person->householdId = $household_id;
$person->maritalStatus = "Married";

$address = new Address();
$address->address1 = "6363 N State Hwy 161";
$address->address2 = "Suite 200";
$address->city = "Irving";
$address->stProvince = "Texas";
$address->country = "USA";
$address->county = "Dallas";
$address->addressTypeID = 5; // Business
$person->addresses[] = $address;

$address = new Address();
$address->address1 = "5200 N State Hwy 161";
$address->address2 = "Suite 200";
$address->city = "Irving";
$address->stProvince = "Texas";
$address->country = "USA";
$address->county = "Dallas";
$address->addressTypeID = 1; // Primary
$person->addresses[] = $address;

$comm = new Communication();
$comm->communicationTypeID = 1; // Home Phone
$comm->communicationValue = "1234567890";
$person->communications[] = $comm;


$comm = new Communication();
$comm->communicationTypeID = 4; // Home Phone
$comm->communicationValue = "test@fellowshiptech.com";
$person->communications[] = $comm;

$created_person = $apiConsumer->addPerson($person);
?>
<pre><?php print_r($created_person); ?></pre>
<?php
*/

// Update a person but not any address or comm values
/*
$personId = 24643475;
$person = $apiConsumer->getPerson($personId, true);
// updste the person now
$person->firstName = "Test";
$person->lastName = 'FB'.date( "d/m/Y", time() );
$person->maritalStatus = "Married";
// since we dont want to update the addresses and comm values, set the entity state to unchanged
if(count($person->addresses) > 0) {
    $addresses = $person->addresses;
    for($i=0; $i<count($addresses); $i++) {
        $address = $addresses[$i];
        $address->entityState = EntityState::$UNMODIFIED;
        // update the address just to make sure that it doesnt get updated
        $address->address1 = "";
        print 'Changed Address';
    }
}
// Communications
if(count($person->communications) > 0) {
    $communications = $person->communications;
    for($i=0; $i<count($communications); $i++) {
        $communication = $communications[$i];
        $communication->entityState = EntityState::$UNMODIFIED;
        $communication->communicationValue = "";
    }
}
$updated_person = $apiConsumer->updatePerson($person);
?>
<pre><?php print_r($updated_person); ?></pre>
<?php
*/

// update a person and address and comm values
/*
$personId = 24643475;
$person = $apiConsumer->getPerson($personId, true);
// updste the person now
$person->firstName = "TestU";
$person->lastName = 'FB'.date( "d/m/Y", time() );
$person->maritalStatus = "Married";
// since we dont want to update the addresses and comm values, set the entity state to unchanged
if(count($person->addresses) > 0) {
    $addresses = $person->addresses;
    for($i=0; $i<count($addresses); $i++) {
        $address = $addresses[$i];
        // update the address
        $address->address1 = "UPDATED";
    }
}
// Communications
if(count($person->communications) > 0) {
    $communications = $person->communications;
    for($i=0; $i<count($communications); $i++) {
        $communication = $communications[$i];
        $communication->communicationValue .= "U";
    }
}
$updated_person = $apiConsumer->updatePerson($person);
?>
<pre><?php print_r($updated_person); ?></pre>
<?php
*/


//
// Get Household Members
//
$household_id = 15809155;
$requestUrl = $apiConsumer->getBaseUrl().AppConfig::$f1_household_people;
$requestUrl = str_replace("{householdID}", $household_id, $requestUrl);
$result = $apiConsumer->doRequest($requestUrl);

$json_array = json_decode(strstr($result, '{"people":{'), true);

// Results found?
if (isset($json_array['people']['person'])) {
    $json_length = count($json_array['people']['person']);

}

for ($i = 0; $i < $json_length; $i++) {
    $x = $json_array['people']['person'][$i];

    $first_name = $x['firstName'];
    $last_name = $x['lastName'];
    $marital = $x['maritalStatus'];
    $gender = $x['gender'];
    $birthdate = $x['dateOfBirth'];

    $h = '';
    $h .=	$first_name.', ';
    $h .=	$last_name.', ';
    $h .=	$marital.', ';
    $h .=	$gender.', ';
    $h .=	$birthdate;

    print $h;

}

?>
