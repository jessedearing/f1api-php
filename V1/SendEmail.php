<?php
$path = dirname(__FILE__) . '\\';
include_once ($path . '..\includes\db_client.php');
require_once ($path . 'AppConfig.php');
require_once ($path . 'Util.php');
require_once ($path . 'F1Api.php');

class SendEmail {

    public function SendValidationEmail($firstname, $lastname, $eMail, $facebookid) {
        $db = new DB_Access();
        $apiConsumer = new F1Api();
        $found_individuals = $apiConsumer->peopleSearch($firstname, $lastname, $eMail);

        $guid = Util::getGuid();

        if (sizeof($found_individuals) >0){
            $db->SaveUserActivationCode($facebookid, $eMail, $guid, $found_individuals[0]);
        }
        else {
            $db->SaveUserActivationCode($facebookid, $eMail, $guid);
        }             

        // Email Initialize
        ini_set("SMTP",AppConfig::$SMTP );
        ini_set('sendmail_from', AppConfig::$FromAddress);

        $Name = AppConfig::$SenderName; //senders name
        $email = AppConfig::$FromAddress; //senders e-mail adress
        $recipient = $eMail; //recipient
        $mail_body = "Please click the following link to validate your e-mail address.\n\n".AppConfig::$app_url.'/validateemail.php?code='.$guid;
        $subject = AppConfig::$app_name . ': E-mail Validation'; //subject
        $header = "From: ". $Name . " <" . $email . ">\r\n"; //optional headerfields

        mail($recipient, $subject, $mail_body, $header); //mail command :)
    }
}

// B. Find if there is already an individual with the same name and e-mail address
// B 1) TODO: Get the e-mail address from the UI Screen
//            Get first name and last name from the Facebook API
//$firstname = "Jas";
//$lastname = "singh";
//$eMail = "jsingh@fellowshiptech.com";

// TODO: Get the facebook user ID from Facebook API
//$facebookid= 1;

/*
// Initialize
// A. TODO: Get the access_token and token_secret from the database
$access_token = '61344b1a-0e88-4ccc-8854-cc6219d83642';
$token_secret = '7e01bf18-cc44-4332-b40e-dc4ae501098a';
// B. Create a OAuthAPIClient object and initialize it
$apiConsumer = new OAuthAPIClient(AppConfig::$f1_base_url, AppConfig::$f1_default_church_code, AppConfig::$f1_key, AppConfig::$f1_secret);
$apiConsumer->setPathsFromConfig();
// This is important. Set the access_token and the token_secret from step A
$apiConsumer->init_AccessToken($access_token, $token_secret);

// B. Find if there is already an individual with the same name and e-mail address
// B 1) TODO: Get the e-mail address from the UI Screen
//            Get first name and last name from the Facebook API
$firstname = "Jas";
$lastname = "singh";
$eMail = "jsingh@fellowshiptech.com";

// B 2) Call API to perform the search
$requestUrl = $apiConsumer->getBaseUrl().AppConfig::$f1_people_search.'?searchFor='.$lastname.','.$firstname.'&communication='.$eMail;;
$search_results = $apiConsumer->doRequest($requestUrl);
$json_array = json_decode(strstr($search_results, '{"results":{'), true);

// Results found?
if (isset($json_array['results']['person'])) {
    $json_length = count($json_array['results']['person']);
}

// Array to store Individual Id of the person matching the search criteria
$found_individuals = array();
// Loop through each found person
for ($i = 0; $i < $json_length; $i++) {
    $x = $json_array['results']['person'][$i];
    $first_name = $x['firstName'];
    $last_name = $x['lastName'];
    $marital = $x['maritalStatus'];
    $gender = $x['gender'];
    $birthdate = $x['dateOfBirth'];
    $household = $x['householdMemberType']['value'];
    $person_id = $x['@id'];
    $h = '';
    $h .=	$first_name;
    $h .=	$last_name;
    $h .=	$marital;
    $h .=	$gender;
    $h .=	$household ;
    $h .=	$birthdate;

    // echo $h;
    // Get the Communication(Email) value for this person, and find it it matches
    $commPath = str_replace("{id}", $person_id, AppConfig::$f1_people_communications);
    $requestUrl = $apiConsumer->getBaseUrl().$commPath;
    $search_results = $apiConsumer->doRequest($requestUrl);
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
        // echo 'Type: '.$commType_name.', ID: '.$commType_Id.', Email: '.$email;
    }
}
*/

// echo "Found: ".sizeof($found_individuals);
/*/* <pre><?php print_r($json_array); ?></pre>*/

// TODO: Insert record into the DB
/*
$db = mssql_connect(AppConfig::$db_ip, AppConfig::$db_user, AppConfig::$db_pass);
if (!$link) {
    die('Could not connect: ' . mssql_get_last_message());
}
if (!mssql_select_db(AppConfig::$db_name, $db)) {
    die('Could not connect to database: ' . mssql_get_last_message());
}
$results = mssql_query($query, $db);
*
 */

// Send out email




?>
