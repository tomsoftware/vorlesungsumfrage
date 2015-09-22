<?PHP


if (!defined('CONFIG_LOAD_SUCCESSFUL')) die('No config loaded!');

include_once(INCLUDE_DIR .'clmysql.php');


class clConfig
{

  var $configs;
  var $session_psw;
  var $isAdmin;

  function clConfig()
  {
    $this->configs = array();
    $this->session_psw='';
    $this->isAdmin=false;


    $mydb = new clMySQL();

    $mydb->Execute('SELECT config_name AS F01, config_value AS F02 FROM config WHERE config_programm='. $mydb->GetSaveStr(CONFIG_PROGRAMM_NAME));

    while(!$mydb->eof())
    {
      $this->configs[strtolower(trim($mydb->fields('F01')))] = $mydb->fields('F02');
      $mydb->MoveNext();
    }
  }


  function getVar($param, $default='')
  {
    $p = strtolower(trim($param));

    if (isset($this->configs[$p]))
    {
      return $this->configs[$p];
    }

    return $default;
  }


  function isAdminUser()
  {
    return $this->isAdmin;
  }


  function createSessionKey($psw, $subminuts=0)
  {

    $ts=mktime(date('H'), floor((date('i')-$subminuts)/30)*30, 0, date('m'), date('d'), date('Y'));
      
    return strtolower(md5(strtolower(trim(PASSWORD_SALT . date('dmYHi', $ts ) . $psw))));
  }


  function MakeAdminPsw($password)
  {
    $this->isAdmin=false;

    $password = strtolower(trim($password));
    $db_pasw = strtolower(trim($this->getVar('admin_psw')));


    if (strlen($password)!=32)
    {
      if ($password==$db_pasw)
      {
        $this->isAdmin=true;
      
        $this->session_psw = $this->createSessionKey($db_pasw);

	return true;
      }
    }
    else
    {
      $s1 = $this->createSessionKey($db_pasw);

      if ($password==$s1)
      {
        $this->isAdmin=true;
        $this->session_psw = $s1;

	return true;
      }
      else
      {
	$s2 = $this->createSessionKey($db_pasw, 30);

	if ($password==$s2)
	{
	  $this->isAdmin=true;
	  $this->session_psw = $s1;

	  return true;
	}
      }
    }
  }


  function GetSessionKey()
  {
    return $this->session_psw;
  }

}


?>