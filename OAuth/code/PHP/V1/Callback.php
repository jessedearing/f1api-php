<?php
$path = dirname(__FILE__) . '\\';

require_once($path . '..\DB\db_client.php');

$churchcode = $_GET['churchcode'];
$churchid = $_GET['churchid'];
$facebookid = $_GET['facebookid'];

//insert the secret and the key.
$db = new DB_Access();

$db->SaveChurchAuthInfo($churchid, $churchcode, $_GET['oauth_token']);
$db->AddAdminUser($facebookid, $churchid)

?>
<script language="javascript">
    function but_onclick() {
        top.location = "http://apps.facebook.com/ftoutreach/index.php?churchcode=<?=$churchcode?>&churchid=<?=$churchid?>"
    }
</script>
<div style="border: 1px solid #d4dae8; width: 350px; margin-left: auto; margin-right: auto;">
    <h4>You have successfully logged in!<h4><br />
    <input type="button" value="Go to FTOutreach" id="goto" onclick="but_onclick()">
</div>

