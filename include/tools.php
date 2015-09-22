<?PHP

// -------------------------------------------------------------------------- //
// -------- Sucht nach einer Datei in den verschiedenen Ordnern ------------- //
// -------- (Sprachverzeichnis / Userdir) 			------------- //
// -------------------------------------------------------------------------- //
function Find_Language_File($FileName, $SubDir = '')
{
  if ($SubDir!='') $SubDir .= '/';


  if (is_file($GLOBALS['akdir'] . $GLOBALS['Work_Space_Folder'] .'/'.  $GLOBALS['template_SubFolder'] .'/'. $SubDir . $FileName)) 
  {
    return $GLOBALS['akdir'] . $GLOBALS['Work_Space_Folder'] .'/'.  $GLOBALS['template_SubFolder'] .'/'. $SubDir;
  }

  if (is_file($GLOBALS['akdir'] . $GLOBALS['Work_Space_Folder'] .'/'.  $GLOBALS['Default_template_SubFolder'] .'/'. $SubDir . $FileName)) 
  {
    return $GLOBALS['akdir'] . $GLOBALS['Work_Space_Folder'] .'/'.  $GLOBALS['Default_template_SubFolder'] .'/'. $SubDir;
  }

  if (is_file($GLOBALS['akdir'] . $GLOBALS['Default_Work_Space_Folder'] .'/'.  $GLOBALS['template_SubFolder'] .'/'. $SubDir . $FileName)) 
  {
    return $GLOBALS['akdir'] . $GLOBALS['Default_Work_Space_Folder'] .'/'.  $GLOBALS['template_SubFolder'] .'/'. $SubDir;
  }

  if (is_file($GLOBALS['akdir'] . $GLOBALS['Default_Work_Space_Folder'] .'/'.  $GLOBALS['Default_template_SubFolder'] .'/'. $SubDir . $FileName)) 
  {
    return $GLOBALS['akdir'] . $GLOBALS['Default_Work_Space_Folder'] .'/'.  $GLOBALS['Default_template_SubFolder'] .'/'. $SubDir;
  }

  return '';
}

// -------------------------------------------------------------------------- //
// -------- Prüfen den Charset ------------------------ //
// -------------------------------------------------------------------------- //
function DecodeRequestCharset($inVar)
{

  // From http://w3.org/International/questions/qa-forms-utf-8.html
  if (preg_match('%^(?:
          [\x09\x0A\x0D\x20-\x7E]            # ASCII
        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $inVar))
  {
    return utf8_decode($inVar);
  }
  else
  {
    return $inVar;
  }
}

// -------------------------------------------------------------------------- //
// -------- Gibt den Wert einer Post Variable zurück ------------------------ //
// -------------------------------------------------------------------------- //
function GetPostVar($VarName, $DefaultVal="", $Find="", $ReplaceWith="")
{
  if (isset($_POST[$VarName]))
  {
    $temp = $_POST[$VarName];
    if (!is_Array($temp))
    {	
      $temp = stripcslashes(DecodeRequestCharset($temp));

      if ($Find!='')
      {
        $temp = strtr($temp,$Find,$ReplaceWith);
      }
    }
  }
  else
  {
    return $DefaultVal;
  }

  return $temp;

}

// -------------------------------------------------------------------------- //
// -------- Gibt den Wert einer Get Variable zurück ------------------------ //
// -------------------------------------------------------------------------- //
function GetGetVar($VarName, $DefaultVal="", $Find="", $ReplaceWith="")
{
  if (isset($_GET[$VarName]))
  {
    $temp = $_GET[$VarName];
    if (!is_Array($temp))
    {
      $temp = stripcslashes(DecodeRequestCharset($temp));

      if ($Find!='')
      {
        $temp = strtr($temp,$Find,$ReplaceWith);
      }
    }
  }
  else
  {
    return $DefaultVal;
  }

  return $temp;
}


// -------------------------------------------------------------------------- //
// -------- Gibt den Wert einer Get oder Post Variable zurück --------------- //
// -------------------------------------------------------------------------- //
function GetAllVar($VarName, $DefaultVal="", $Find="", $ReplaceWith="")
{
  if (isset($_GET[$VarName]))
  {
    return GetGetVar($VarName, $DefaultVal, $Find, $ReplaceWith);
  }
  else
  {
    return GetPostVar($VarName, $DefaultVal, $Find, $ReplaceWith);
  }
}


// -------------------------------------------------------------------------- //
// -------- Gibt den Wert einers Cookies zurück      ------------------------ //
// -------------------------------------------------------------------------- //
function GetCookieVar($VarName, $DefaultVal="", $Find="", $ReplaceWith="")
{

  $temp = stripcslashes(DecodeRequestCharset(isset($_COOKIE[$VarName])?$_COOKIE[$VarName]:$DefaultVal));

  if ($Find!="")
  {
    $temp = strtr($temp,$Find,$ReplaceWith);
  }

  return $temp;

}


// -------------------------------------------------------------------------- //
// -------- Hängt eine Fehlermeldung an die Error.log Datei an  ------------- //
// -------------------------------------------------------------------------- //
function WriteError($ErrorStr,$FileName="?", $Line ="")
{
  $OutFile = fopen (ERROR_LOG_FILE,"a");
  fwrite($OutFile, date("Y.m.d h:i:s") .",\t[". basename($_SERVER['PHP_SELF']) ."]". $FileName .", ". $Line .",\t". $ErrorStr ."\r\n");
  fclose($OutFile);
}



// --------------------------------------------------------- //
// ---- Überprüft einen String ob er eine echte Zahl enthält --- //
// --------------------------------------------------------- //
function isNumber($StrNum)
{
  $StrNum = $StrNum .''; //- in Type-String umwandeln!
  $StrNum = trim($StrNum);

  //- Wenn ein Minus (-) oder Plus (+) vor dem Text steht dann schneiden wir das weg :-) !
  if (substr($StrNum,0,1)=='-') 
  {
    $StrNum = substr($StrNum,1);
  }
  else
  {
    if (substr($StrNum,0,1)=='+') $StrNum = substr($StrNum,1);
  }

  if ($StrNum=='') return false;

  for ($i=0;$i<strlen($StrNum);$i++)
  {
    if (strpos('00123456789', substr($StrNum,$i,1),1)===false) return false;
    if ($i>11) return false;
  }

  return true;  
}

// --------------------------------------------------------- //
// ---- Schreibt daten in den Head			 --- //
// --------------------------------------------------------- //
function add_header($NewHeaderStr)
{
  if (!headers_sent())
  {
    header($NewHeaderStr);
  }
  else
  {
    if (strtolower(substr($NewHeaderStr,0,9))!= 'location:')
    {
      print $NewHeaderStr;
    }
    else
    {
      print '<a href="'. substr($NewHeaderStr,9) .'">Bitte hier klicken um auf die gewünschte Seite zu kommen!</a>';
    }
  }
}


//////////////////////////////////////////////////////////////////////////////////////////
// Liefert eine Liste aus einem Query
/////////////////////////////////////////////////////////////////////////////////////////
function GetIDList(& $InDB, $Query, $IDFieldName,$ValueFieldName, $SelectID=-1, $Usefor_Kunden_Feld = -1)
{
  $tmp='';

  $InDB->Execute($Query);

  if ($Usefor_Kunden_Feld!=-1) $Use_Kunde = $InDB->Fields($Usefor_Kunden_Feld);

  while (!$InDB->eof())
  {
    if ($Usefor_Kunden_Feld!=-1) if ($Use_Kunde <> $InDB->Fields($Usefor_Kunden_Feld)) break; //- Da kommten die default Werte (Usefor_Kunden_Index == -1)

    $FID =$InDB->Fields($IDFieldName);

    if ($FID == $SelectID)
    {
      $tmp .='<option value="'. $FID .'" selected="selected">'. htmlentities($InDB->Fields($ValueFieldName)) .'</option>';
    }
    else
    {
      $tmp .='<option value="'. $FID .'">'. htmlentities($InDB->Fields($ValueFieldName)) .'</option>';      
    }

    $InDB->MoveNext();

  }

  return $tmp;
}



//////////////////////////////////////////////////////////////////////////////////////////
// Liefert eine Liste aus einem Array
/////////////////////////////////////////////////////////////////////////////////////////
function GetHTMLList($ListArray, $SelectID=-1)
{
  $tmp='';

  foreach ($ListArray as $LID => $LValue)
  {
    if ($LID != $SelectID)
    {
      $tmp .='<option value="'. $LID .'">'. htmlentities($LValue) .'</option>';  
    }
    else
    {
      $tmp .='<option value="'. $LID .'" selected="selected">'. htmlentities($LValue) .'</option>';
    }
  }
  return $tmp;
}


//////////////////////////////////////////////////////////////////////////////////////////
// Liefert den Inhalt einer Datei als String
// - Diese Funktion ist aber PHP-Version (PHP 4 >= 4.3.0)
/////////////////////////////////////////////////////////////////////////////////////////
if (!function_exists('file_get_contents'))
{
  function file_get_contents($FileName)
  {
    $file = fopen($FileName,'r');
    $s=fread ($file, filesize ($FileName));
    fclose($file);
    return $s;
  }
}


//////////////////////////////////////////////////////////////////////////////////////////
// Gibt eine Dateigröße im optimalem Format mit Einheit zurück
/////////////////////////////////////////////////////////////////////////////////////////
function GetOptimalFilesize($FileSizeInByte)
{
  $FileSizeInByte = $FileSizeInByte +0;
  if ($FileSizeInByte<1024)		return $FileSizeInByte .' Byte';
  if ($FileSizeInByte<1048576)		return number_format($FileSizeInByte/1024, 1,',','') .' KB';
  if ($FileSizeInByte<1073741824)	return number_format($FileSizeInByte/1048576, 1,',','') .' MB';
  return number_format($FileSizeInByte/1073741824, 1,',','') .' GB';
}



//////////////////////////////////////////////////////////////////////////////////////////
// Gibt einen zufälligen Text zurück
/////////////////////////////////////////////////////////////////////////////////////////
function GetRndText($TextLen)
{
  $OutText = "";
  $RndNr = 0;

  for ($i=0; $i<$TextLen; $i++)
  {
    $RndNr=mt_rand(1,36);

    if ($RndNr>26)
    {
      $OutText .= ($RndNr-27);
    }
    else
    {
      $OutText .= chr($RndNr+64);
    }
  }
  return $OutText;
}



//////////////////////////////////////////////////////////////////////////////////////////
// Gibt ein Prüfsummen Zeichen zurück
/////////////////////////////////////////////////////////////////////////////////////////
function GetPruefsummenChar($Value)
{
  $num=0;
  $ZeichenIn = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $ZeichenOut = 'ABCDEFGHKLMNPQRSTUVWXYZ';

  for($i=0; $i<strlen($Value); $i++)
  {
    $num = ($num * 13 + stripos($ZeichenIn, $Value[$i] )) % 10000;
  }
  return $ZeichenOut[$num % strlen($ZeichenOut)];

}

//////////////////////////////////////////////////////////////////////////////////////////
// Find position of first occurrence of a case-insensitive string
/////////////////////////////////////////////////////////////////////////////////////////
if (!function_exists('stripos'))
{
  function stripos($haystack, $needle, $offset=0)
  {
    if ($offset==0)
    {
      return strrpos(strtolower($haystack), strtolower($needle));
    }
    else
    {
      return strrpos(strtolower(substr($haystack,0,-$offset)), strtolower($needle));
    }
  }
}

?>