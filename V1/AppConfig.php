<?php
//! Store App Specific configuration here

class AppConfig {
  // Facebook specific configuration variables
  public static $debug = "0";
  public static $simulateRequest = "0";
  public static $app_name = '<Your Values Here>';
  public static $app_url = '<Your Values Here>';
  public static $app_id = '<Your Values Here>';
  public static $api_key = '<Your Values Here>';
  public static $secret  = '<Your Values Here>';
  public static $domain_url = '<Your Values Here>';

  // For the feed forms (the popups that ask the user if they would like to
  // publish an answer), You have to create an fbml template for the content
  // in the box. You create/register the template using the feed templates console at
  // http://developers.facebook.com/tools.php
  // For more information about feed forms head to the developer wiki:
  // http://wiki.developers.facebook.com/index.php/Feed_Forms
  public static $template_bundle_id = 0;
  public static $admin_template_id = 0;

  // The base URL. The Domain
  // APIAIR Staging
  
  public static $f1_base_url = 'https://{church_code}.fellowshiponeapi.com';
  public static $f1_default_church_code = '<Your church code Here>';
  public static $f1_default_church_id = '<Your church id Here>';
  //  Staging Keys for ftapiair
  public static $f1_key = '<Your Values Here>';
  public static $f1_secret  = '<Your Values Here>';
  
    
  // APIEARTH Staging
  /*
  public static $f1_base_url = 'https://{church_code}.staging.fellowshiponeapi.com';
  public static $f1_default_church_code = '';
  //  Staging Keys for ftapiearth
  public static $f1_key = '';
  public static $f1_secret  = '';
  */

  /* DEMO -- FOR DEVELOPMENT
  public static $f1_base_url = 'http://{church_code}.api.dev.corp.local';
  public static $f1_default_church_code = '';
  // Demo keys for development
  public static $f1_key = '';
  public static $f1_secret  = '';
  */

  
  //DC -- FOR DEVELOPMENT
  /*
  public static $f1_base_url = 'http://{church_code}.api.dev.corp.local';
  public static $f1_default_church_code = '';
  // DC keys for development
  public static $f1_key = '';
  public static $f1_secret  = '';
  */

  public static $f1_requesttoken_path = "/v1/Tokens/RequestToken";
  // The path consumer requests Access token from
  // public static $f1_accesstoken_path = "/v1/PortalUser/AccessToken";
  public static $f1_accesstoken_path = "/v1/Tokens/AccessToken";
  // The path consumer redirects the user to so that user can authenticate himself on the
  // service provider side
  public static $f1_auth_path = "/v1/PortalUser/Login";

  // DEMO
  /*
  public static $f1_household_create = "/v1/Households/Create?mode=demo";
  public static $f1_people_create = "/v1/People?mode=demo";
  public static $f1_statuses_list = "/v1/People/Statuses?mode=demo";
  public static $f1_householdMemberTypes_list = "/v1/People/HouseholdMemberTypes?mode=demo";
  */

  public static $f1_household_create = "/v1/Households";
  public static $f1_household_people = "/v1/Households/{householdID}/People";
  public static $f1_people_create = "/v1/People";
  public static $f1_people_edit = "/v1/People/{id}/Edit";
  public static $f1_people_show = "/v1/People/{id} ";
  public static $f1_people_update = "/v1/People/{id}";
  public static $f1_people_occupations = "/v1/People/Occupations";
  public static $f1_statuses_list = "/v1/People/Statuses";
  public static $f1_householdMemberTypes_list = "/v1/People/HouseholdMemberTypes";
  public static $f1_people_search = "/v1/People/Search";
  public static $f1_people_address = "/v1/People/{personID}/Addresses";
  public static $f1_people_address_update = "/v1/People/{personID}/Addresses/{id}";
  public static $f1_people_communications = "/v1/People/{id}/Communications";
  public static $f1_people_communications_update = "/v1/People/{personID}/Communications/{id}";
  public static $f1_addresstypes = "/v1/Addresses/AddressTypes";
  public static $f1_communicationtypes = "/v1/Communications/CommunicationTypes";

  public static $callbackURI = "http://<Your app domain>/callback.php";
  // 1 = Includes token secret in the query string appended to the call back uri so that we have access to 
  // the token secret on the callback page. Since the request token is used to sign the request for access token, having the
  // token secret will be handy. Otherwise you will have to store the token secret in some fashion. (Session, DB etc.)
  public static $includeRequestSecretInUrl = "1";

  public static $lineBreak = "<br/>";

 //  User: rchamp
 //  Password: pa$$w0rd
 //  Church Code: ftapiair
 //  ChurchID: 502




  // Application specific configuration variables
  public static $db_ip = '<Your Values Here>';
  public static $db_user = '<Your Values Here>';
  public static $db_pass = '<Your Values Here>';
  public static $db_name = '<Your Values Here>';

  // Email Configuration
  public static $SMTP ="<Your Values Here>";
  public static $FromAddress = "<Your Values Here>"; // Who sent the e-mail
  public static $SenderName = "<Your Values Here>"; //senders name
}

?>