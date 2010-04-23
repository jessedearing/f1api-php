<?php
    require_once 'AppConfig.php';
    require_once 'OAuthAPIClient.php';
               
    $apiConsumer = new OAuthAPIClient(AppConfig::$f1_base_url, AppConfig::$f1_default_church_code, AppConfig::$f1_key, AppConfig::$f1_secret);
    $apiConsumer->setRequestTokenPath( AppConfig::$f1_requesttoken_path ) ;
    $apiConsumer->setAccessTokenPath( AppConfig::$f1_accesstoken_path );
    $apiConsumer->setAuthPath( AppConfig::$f1_auth_path );
    $apiConsumer->setCallback(AppConfig::$callbackURI );
    // $data = $apiConsumer->getRequestToken();
    //$data = $apiConsumer->AuthenticateUser();
    $result = $apiConsumer->addHousehold("Jas", "FB", AppConfig::$f1_household_create);
    // Print information about the Household
    $household_id =  $result["household"]["@id"];
    print $household_id;
    /*
    $apiConsumer = new OAuthClient(AppConfig::$f1_key, AppConfig::$f1_secret);
    $data = $apiConsumer->getRequestToken(AppConfig::$f1_base_url.AppConfig::$f1_requesttoken_path);
    print $data;
    */
?>