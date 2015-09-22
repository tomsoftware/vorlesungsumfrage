<?PHP

include_once('config.php');
include_once(INCLUDE_DIR .'clmysql.php');
include_once(INCLUDE_DIR .'cltemplate.php');
include_once(INCLUDE_DIR .'tools.php');
include_once(INCLUDE_DIR .'vote_session.php');

//////////////////////////////////////////////////////////////////////////////////////////
// Main
/////////////////////////////////////////////////////////////////////////////////////////



$myTmp = new clTemplate('vote_select.txt');
$myDB = new clMySQL();




$myTmp->AddVarHTML('VOTE_LIST', GetIDList($myDB, 'SELECT vote_Name AS F01, vote_ID AS F02 FROM vote ORDER BY vote_Create_Date DESC, vote_Name', 'F02', 'F01' ));
$myTmp->AddVar('USERNAME', GetvoteUsername());
$myTmp->AddVar('USERNAME_STATUS', GetUserStatusStr());
$myTmp->AddVar('SID', GetvoteSID());
$myTmp->AddVar('CH', GetvoteChallenge());


$myTmp->Write('HEAD');


$myTmp->Write('YOU_ARE_HEAD');

if (GetvoteSID()>0)
{
   $myTmp->Write('YOU_ARE_SESSION');
}
else
{
   $myTmp->Write('YOU_ARE_GUEST');
}

$myTmp->Write('YOU_ARE_FEET');



if (GetvoteUsername()=='')
{
  $myTmp->Write('LOGIN');
}
else
{
  $myTmp->Write('CHANGE_LOGIN');
}

$myTmp->Write('FEET_LOGIN');


$myTmp->Write('FEET');


?>