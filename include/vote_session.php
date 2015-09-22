<?PHP

include_once('./config.php');
include_once(INCLUDE_DIR .'clmysql.php');
include_once(INCLUDE_DIR .'tools.php');
include_once(INCLUDE_DIR .'clconfig.php');


$GLOBALS['vote_session_manager_info'] = array();
$GLOBALS['vote_session_manager_info']['login'] = false;


$GLOBALS['vote_session_manager_info']['config'] = new clConfig();


// ------------------------------------------------------------------ //
function GetvoteUsername()
{
  if ($GLOBALS['vote_session_manager_info']['login']) return $GLOBALS['vote_session_manager_info']['user'];
  return '';
}

    

// ------------------------------------------------------------------ //
function GetvoteSID()
{
  if ($GLOBALS['vote_session_manager_info']['login']) return $GLOBALS['vote_session_manager_info']['sid'];
  return '';
}


// ------------------------------------------------------------------ //
function GetvoteChallenge()
{
  if ($GLOBALS['vote_session_manager_info']['login']) return $GLOBALS['vote_session_manager_info']['ch'];
  return '';
}

// ------------------------------------------------------------------ //
function GetvoteIsAdmin()
{
  if ($GLOBALS['vote_session_manager_info']['login']) return $GLOBALS['vote_session_manager_info']['is_admin'];
  return false;
}

// ------------------------------------------------------------------ //
function GetvoteIsMod()
{
  if ($GLOBALS['vote_session_manager_info']['login']) return (($GLOBALS['vote_session_manager_info']['is_mod']) || ($GLOBALS['vote_session_manager_info']['is_admin']));
  return false;
}

// ------------------------------------------------------------------ //
function GetvoteIsUser()
{
  if ($GLOBALS['vote_session_manager_info']['login']) return true;
  return false;
}


// ------------------------------------------------------------------ //
function GetUserStatusStr()
{
  if ($GLOBALS['vote_session_manager_info']['login'])
  {
    if ( $GLOBALS['vote_session_manager_info']['is_admin'] ) return 'Admin';
    if ( $GLOBALS['vote_session_manager_info']['is_mod'] ) return 'Moderator';
    return 'Benutzer';
  }
  return 'Gast';
}


// ------------------------------------------------------------------ //
function _init_vote_session_manager()
{

  $myDB = new clMySQL();

  $session_id = (GetAllVar('_sid', '')+0);
  $session_challenge = strtolower(trim(GetAllVar('_ch', '')));
  $session_create = (GetAllVar('create_session', -1)+0);


  $session_id = ($myDB->GetOneValueByQuery('SELECT vote_session_ID FROM vote_session WHERE ((vote_session_ID='. $session_id .') AND (vote_session_timestamp>DATE_ADD(now(), INTERVAL -1 DAY) )  AND (vote_session_challenge='. $myDB->GetSaveStr($session_challenge) .')) ') +0);

  if ($session_create==1)
  {
    $session_username = GetPostVar('username', '');
    $session_psw = strtolower(trim(GetPostVar('userpsw', '')));
    $session_challenge = GetRndText(20);

    $is_mod = 0;
    $is_admin = 0;

    $cfg = $GLOBALS['vote_session_manager_info']['config'];

    if ($session_psw == strtolower(trim($cfg->getVar('mod_psw')))) $is_mod=1;
    if ($session_psw == strtolower(trim($cfg->getVar('admin_psw')))) $is_admin=1;


    if ($session_id>0)
    {
      $myDB->DoQuery('UPDATE vote_session SET vote_session_isAdmin='. $is_admin .' , vote_session_isModerator='. $is_mod .' WHERE vote_session_ID='. $session_id);
    }
    else
    {
      $myDB->DoQuery('INSERT INTO vote_session(vote_session_timestamp, vote_session_username, vote_session_challenge, vote_session_isAdmin, vote_session_isModerator) VALUES (now(), '. $myDB->GetSaveStr($session_username) .', '. $myDB->GetSaveStr($session_challenge) .', '. $is_admin .', '. $is_mod .');');
      $session_id = $myDB->GetInsertId();
    }    
  }

  $myDB->Execute('SELECT vote_session_username AS F01, vote_session_challenge AS F02, vote_session_isAdmin AS F03, vote_session_isModerator AS F04 FROM vote_session WHERE  (vote_session_ID='. $session_id .')');

  if (!$myDB->eof())
  {
    $GLOBALS['vote_session_manager_info']['login'] = true;
    $GLOBALS['vote_session_manager_info']['sid'] = ($session_id+0);
    $GLOBALS['vote_session_manager_info']['user'] = $myDB->fields('F01');
    $GLOBALS['vote_session_manager_info']['ch'] = $myDB->fields('F02');
    $GLOBALS['vote_session_manager_info']['is_admin'] = (($myDB->fields('F03')+0)>0);
    $GLOBALS['vote_session_manager_info']['is_mod'] = (($myDB->fields('F04')+0)>0);
  }

}  


_init_vote_session_manager();





?>