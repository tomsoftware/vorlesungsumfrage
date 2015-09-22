<?PHP

if (!defined('CONFIG_LOAD_SUCCESSFUL')) die('No config loaded!');

// --------------------------------------------------------- //
// ------- clTemplate Ausgabe von Template Datein ---------- //
// ------- 				    v.3.1 ---------- //
// - If Anweisung: $? Wert > 5 $  $Wert$ ist größer als 5  $?else$ $Wert$ ist kleiner oder gleich 5 $?$
// --------------------------------------------------------- //

class clTemplate
{
  var $FileName;
  var $FileBuffer=array();
  var $FileBufferCount;
  var $tmpVars=array(); 
  var $GoToArray = array();
  var $OutBuffer;
  var $IsFirstHeader;
  var $formateHTML;

  // --------------------------------------------------------- //
  function clTemplate($FileName='')
  {
    $this->formateHTML = true;
    $this->FileName ='';
    $this->FileBuffer = array();
    $this->GoToArray = array();
    $this->FileBufferCount = 0;
    $this->OutBuffer ='';
    $this->IsFirstHeader=true;
    $this->replace_search = array();
    $this->replace_replace = array();


    if ($FileName!='') $this->SetFileName($FileName);
    $this->DellVars();
  }

  // --------------------------------------------------------- //
  function DellVars()
  {
    $this->tmpVars=array();

    //- Standard Variablen setzen
    $this->AddVar('THIS', basename($_SERVER['PHP_SELF']));
    $this->AddVar('THIS_URL', basename($_SERVER['PHP_SELF']) . '?'. $_SERVER['QUERY_STRING']);
    if (isset($GLOBALS['IMAGE_HTML_PATH'])) $this->AddVar('IMAGE_HTML_PATH', $GLOBALS['IMAGE_HTML_PATH']);
    if (isset($GLOBALS['STATIC_HTML_PATH'])) 
    {
      $this->AddVar('STATIC_HTML_PATH', $GLOBALS['STATIC_HTML_PATH']);
      if (isset($GLOBALS['CSS_HTML_FILE']))
      {
        $this->AddVar('CSS_HTML_FILE', $GLOBALS['STATIC_HTML_PATH'] .'style/'. $GLOBALS['CSS_HTML_FILE']);
	$this->AddVar('STATIC_CSS_FILE', $GLOBALS['STATIC_HTML_PATH'] .'style/new_'. $GLOBALS['CSS_HTML_FILE']);
      }

    }

    if (isset($GLOBALS['UseLanguage'])) $this->AddVar('USE_LANGUAGE', $GLOBALS['UseLanguage']);

    if (isset($GLOBALS['APPLICATION_COPYRIGHT'])) $this->AddVar('APPLICATION_COPYRIGHT', $GLOBALS['APPLICATION_COPYRIGHT']);
    if (defined('APPLICATIONS_VERSION')) $this->AddVar('APPLICATIONS_VERSION', APPLICATIONS_VERSION);
    $this->AddVar('NOW_DATE', date('d.m.Y'));
    $this->AddVar('NOW_TIME',date('H:i'));

  }

  // --------------------------------------------------------- //
  function SetFileName($FileName)
  {
    $tmpPath = Find_Language_File($FileName, 'template');

    //- einen Pfad setzen nicht dass der mir eine Datei ausließt die im ROOT Ordner liegt!
    if ($tmpPath!='')
    {
      $this->FileName=$FileName;
      $this->FileBuffer = explode('$',file_get_contents($tmpPath . $FileName));
      $this->FileBufferCount = count($this->FileBuffer);

      if (($this->FileBufferCount & 1)==0)
      {
	$this->FileBuffer[]='';
	echo "FEHLER in Template: falsche Anzahl an $\n";
      }


      $this->formateHTML = true;
      $this->replace_search = array();
      $this->replace_replace = array();

    }
    else
    {
      echo '[clTemplate->SetFileName()] Internal Error! File "'. $FileName .'" not found! Output impossible!';
    }

  }


  // --------------------------------------------------------- //
  function AddVarHTML($Name, $Value, $ifEmptyValue='')
  {
    $c = substr ($Name,0,1);

    if (($c!=':') && ($c!='@') && ($c!='!') && ($c!='?'))
    {
      if ($Value=='')
      {
	if (($this->formateHTML) && ($ifEmptyValue == ' '))
	{
	  $this->tmpVars[strtoupper($Name)] = $ifEmptyValue;
	}
	else
	{
	  $this->tmpVars[strtoupper($Name)] = $ifEmptyValue;
	}
      }
      else
      {
	$this->tmpVars[strtoupper($Name)] = $Value;
      }
    }
    else 
    {
      echo '[clTemplate->AddVar] Variablen mit ":" oder "@" sind nicht zulässig!';
    }
  }


  // --------------------------------------------------------- //
  function AddVar($Name, $Value, $ifEmptyValue='', $HTMLBR = true)
  {
    $c = substr ($Name,0,1);

    if (($c!=':') && ($c!='@') && ($c!='!'))
    {
      if($this->formateHTML)
      {
        if ($Value=='')
        {
	  if ($ifEmptyValue==' ')
	  {
	    $this->tmpVars[strtoupper($Name)] = '&nbsp;';
	  }
	  else
	  {
	    $this->tmpVars[strtoupper($Name)] = $ifEmptyValue;
	  }
        }
	else if ($HTMLBR)
        {
	  $this->tmpVars[strtoupper($Name)] = str_replace("\n",'<br />', htmlentities($Value));
        }
        else
        {
	  $this->tmpVars[strtoupper($Name)] = htmlentities($Value);
        }
      }
      else
      {
	if ($Value=='')
	{
	  $this->tmpVars[strtoupper($Name)] = $ifEmptyValue;
	}
	else
	{
	  $this->tmpVars[strtoupper($Name)] = str_replace($this->replace_search, $this->replace_replace, $Value);
	}	
      }
    }
    else 
    {
      echo '[clTemplate->AddVar] Variablen mit ":" oder "@" sind nicht zulässig!';
    }
  }


  // --------------------------------------------------------- //
  function flush()
  {
    echo $this->OutBuffer;
    $this->OutBuffer='';
  }


  // --------------------------------------------------------- //
  function Write($GotoName = '', $doFlush=true)
  {
    $tmpBuffer ='';
    $IsOKBereich=($GotoName=='');
    $Count= $this->FileBufferCount;
    $akWert ='';
    $doDisplay=true;


    $GotoName=strtoupper($GotoName);

    $pos=-1;

    //- suchen ob der Einsprung-Punkt bekannt ist
    if (($GotoName!='') && (isset($this->GoToArray[$GotoName]))) 
    {
      $pos = $this->GoToArray[$GotoName];
    }
     
    for ($pos=$pos;$pos<$Count;$pos+=2)
    {
      if ($pos>-1)
      {
	//- kommt jetzt ein Bereich?
	$akWert = strtoupper(trim($this->FileBuffer[$pos]));

	if (substr($akWert,0,1)==':')
	{
	  $akWert = substr($akWert,1);

	  $this->GoToArray[$akWert] = $pos;

	  if ($IsOKBereich) break;
	  if ($akWert == $GotoName) $IsOKBereich=true;
	}
	else
	{
	  if ($IsOKBereich)
	  {

	    //- Es handelt sich um einen Befehl!
	    switch(substr($akWert,0,1))
	    {
	      case '':
		if ($doDisplay) $tmpBuffer .= '$';
		break;


	      case '@':
		//- Header Informationen werden hier geschrieben!
		if (!headers_sent())
		{
		  if ($this->IsFirstHeader)
		  {
		    //- Den Standart Header aus 'config.php' zurück setzen!
		    header('Cache-Control: ', true); 
		    header('Expires: '. gmdate('D, d M Y H:i:s') .' GMT', true); 
		    $this->IsFirstHeader=false;
		  }

		  header(substr($this->FileBuffer[$pos],1));
		}
		break;


	      case '!':
		$control = explode(':', substr($this->FileBuffer[$pos],1),2);
		if (count($control)==2)
		{

		  $control_is_yes = (stripos('|off|no|false', $control[1])<1);

		  switch (strtolower($control[0]))
		  {
		    case 'html':
			$this->formateHTML=$control_is_yes;
			break;
		    case 'replace':
			$control_list = explode(',', $control[1],2);

			if (count($control_list)==2)
			{
			  array_push($this->replace_search, $control_list[0]);
			  array_push($this->replace_replace, $control_list[1]);
			}
			break;
		  }
		}
		break;


	      case '?':
		//- Mit dieser Option können einfache IFs erstellt werden
		//- Beispiel: 
		//-   $? Wert > 5 $         $Wert$ ist größer als 5  $?$
		//- Übersetzung:
		//-   IF Wert > 5 THEN "ist $Wert$ ist größer als 5" END IF
		//- Dabei ist [Wert] eine Variable
		//- Operationen: <,>,!=,==
		//- Info: alle Lehrzeichen im Beispiel sind optional - auch richtig: $?Wert>5$$Wert$ ist größer als 5$?$

		$checkCmd = trim(substr($this->FileBuffer[$pos],1));


		if (strtolower($checkCmd)=='else')
		{
		  $doDisplay = ! $doDisplay;
		}
		else if ($checkCmd=='')
		{
		  $doDisplay=true;
		}
		else
		{
		  $doDisplay=false;


		  $checktmp = strtr($checkCmd,'<>!', '===') .'=';

		  //- Position des Operators ermitteln
		  $checkPos = strpos($checktmp, '=');
		  $checkVar = strtoupper(trim(substr($checkCmd,0,$checkPos)));

		  //- auf 2stelligen Operator prüfen
		  if (substr($checktmp, $checkPos+1, 1) == '=')
		  {
		    $checkOp = substr($checkCmd,$checkPos,2);
		    $checkPos++;
		  }
		  else
		  {
		    $checkOp = substr($checkCmd,$checkPos,1);
		  }

		  //- Sollwert
		  $checkCmp = trim(substr($checkCmd,$checkPos+1));

		  //- Wert aus Variable auslesen (Istwert)
		  $checkOK='';




		  if (isset($this->tmpVars[$checkVar])) $checkOK=$this->tmpVars[$checkVar];


		  switch($checkOp)
		  {
		      case '':
			if ($checkOK!='') $doDisplay=true;
			break;

		      case '=':
		      case '==':
			if (strcasecmp($checkOK, $checkCmp)==0) $doDisplay=true;
			break;

		      case '>':
			if (($checkOK+0)>($checkCmp+0)) $doDisplay=true;
			break;

		      case '<':
			if (($checkOK+0)<($checkCmp+0)) $doDisplay=true;
			break;

		      case '<>':
		      case '!=':
			if (strcasecmp($checkOK .'', $checkCmp .'')!=0) $doDisplay=true;
			break;

		  }
		}


	      default:
		if (isset($this->tmpVars[$akWert]))
		{
		  if ($doDisplay) $tmpBuffer .= $this->tmpVars[$akWert];
		}
		else
		{
		  //- Variable nicht vorhanden ODER spezial Befehl!
		  switch($akWert)
		  {
		    case 'RUN_TIME':  
			list($usec, $sec) = explode(' ',microtime()); 
			if ($doDisplay) $tmpBuffer .= ((float)$usec + (float)$sec - (float)$GLOBALS['Programm_Start_usec'] - (float)$GLOBALS['Programm_Start_sec']);
			break;

		    case 'DEBUG':
			print_r($this->tmpVars);
			break;

		    default:
			if (defined('SHOW_DEBUG_ERROR')) if ($doDisplay) $tmpBuffer .= '<hr>Unknown Value: '. $akWert .'<hr>';
			break;
		  }
		}
		break;
	    }
	  }
	}
      }

      //- Text nach der Variable ausgeben
      if (($IsOKBereich) && ($doDisplay)) $tmpBuffer .=  $this->FileBuffer[$pos+1];

    }
    
    if ($doFlush)
    {
      echo $this->OutBuffer;
      $this->OutBuffer='';
      echo $tmpBuffer;
    }
    else
    {
      $this->OutBuffer .= $tmpBuffer;
    }

    return $IsOKBereich;
  }

}


?>