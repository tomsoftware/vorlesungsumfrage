<?PHP


include_once('config.php');


if (!isset($GLOBALS['MySQLConnection'])) $GLOBALS['MySQLConnection']=null;
if (!isset($GLOBALS['MySQLQueryCount'])) $GLOBALS['MySQLQueryCount']=0;
if (!isset($GLOBALS['MySQLTransErrorCount'])) $GLOBALS['MySQLTransErrorCount']=0;
if (!isset($GLOBALS['MySQLTransactionCount'])) $GLOBALS['MySQLTransactionCount']=0;


// ----------------------------------------------------------------------------- //
class clMySQL
{
  var $ConID;
  var $AKDB;
  var $MyAkRec;
  var $quietMode;

  // ----------------------------------------------------------------------------- //
  function clMySQL($DBName=MYSQL_DATABASE_NAME, $quietMode=false)
  {
    if ($DBName=='') $DBName=MYSQL_DATABASE_NAME;

    $this->quietMode = $quietMode;
    $this->MyAkRec = false;
    $this->AKDB = '';

    if (!extension_loaded('mysqli')) 
    {
      if (!dl('mysqli.so'))
      {
        echo '<b>error loading Modul mysqli</b>';
      }
    } 


    // RegShutdown($this, "release");

    if ($GLOBALS['MySQLConnection']!=null)
    {
      $this->ConID=$GLOBALS['MySQLConnection'];
    }
    else
    {
      if (!$this->quietMode)
      {

	$this->ConID = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS);

	if ($this->ConID==false) MyErrorHandler(E_USER_ERROR, '[clMySQL.php->clMySQL()] Konnte nicht zum MySQL-Server verbinden');
      }
      else
      {

	$this->ConID = @mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS);


	if ($this->ConID==false) return false;
      }
    }

    if ($this->ConID!==false)
    {

      if (!mysqli_set_charset($this->ConID, 'latin1'))
      {
	MyErrorHandler(E_USER_ERROR, '[clMySQL.php->clMySQL()] set_charset("latin1") fail! ');
      }

      if (!mysqli_select_db($this->ConID, $DBName))
      {
	MyErrorHandler(E_USER_ERROR, '[clMySQL.php->clMySQL()] Datenbank '. $DBName .' nicht gefunden.');
      }


      $this->AKDB=$DBName;
    }

  }


  // ----------------------------------------------------------------------------- //
  // ----Führt einen Query aus (Rückgabe: True/False)   -------------------------- //
  // ----------------------------------------------------------------------------- //
  function DoQuery($Query)
  {
    if (!$this->ConID) return false;

    $GLOBALS['MySQLQueryCount']++;

    if (mysqli_query($this->ConID, $Query) ==false)
    {
	$GLOBALS['MySQLTransErrorCount']++;
	MyErrorHandler(E_USER_WARNING, '[clMySQL.php->DoQuery()]: '. mysqli_error($this->ConID) ,__FILE__,__LINE__,  $Query );
	return false;
    }
    return true;

  }

  // ----------------------------------------------------------------------------- //
  // ----Führt einen Query aus (Rückgabe: True/False)   -------------------------- //
  // ----------------------------------------------------------------------------- //
  function TryQuery($Query)
  {
    if (!$this->ConID) return false;

    $GLOBALS['MySQLQueryCount']++;

    if (mysqli_query($this->ConID, $Query) ==false)
    {
	$GLOBALS['MySQLTransErrorCount']++;
	return false;
    }
    return true;

  }



  // ----------------------------------------------------------------------------- //
  function release()
  {
    if ($this->ConID)
    {
      //- mysqli_close ($this->ConID); //-- don't need for mysqli_pconnect
      $this->ConID =false;
    }
  }


  // ----------------------------------------------------------------------------- //
  function IsTable($TableName)
  {  
    if ($this->ConID)
    {
      $TableName = strtolower($TableName);

      $result = mysqli_list_tables($this->ConID, $this->AKDB);
      
      while ($row = mysqli_fetch_row($result))
      {
	if ($TableName == strtolower($row[0]))
	{
	  mysqli_free_result($result);
	  return true;
	}
      }

      mysqli_free_result($result);
    }
    return false;
  }

  // ----------------------------------------------------------------------------- //
  function Execute($Query)
  {
    $this->MyAkRec=false;

    if ($this->ConID)
    {
      $GLOBALS['MySQLQueryCount']++;

      $this->result = mysqli_query($this->ConID, $Query);

      if ($this->result==NULL)
      {
	$GLOBALS['MySQLTransErrorCount']++;
        MyErrorHandler(E_USER_WARNING, '[clMySQL.php->Execute()]: '. mysqli_error($this->ConID) ,__FILE__,__LINE__,  $Query );
	$this->result=false;
      }

      $this->MoveNext();
      return true;

     }
   }


  // ----------------------------------------------------------------------------- //
  // ----Gibt einen wert aus einer SQL Abfrage zurück    ------------------------- //
  // ----------------------------------------------------------------------------- //
  function GetOneValueByQuery($Query,$Field = 0, $default = null)
  {
    if (!$this->ConID) return null;

    $GLOBALS['MySQLQueryCount']++;

    $result = mysqli_query($this->ConID, $Query);

    if ($result==NULL)
    {
      $GLOBALS['MySQLTransErrorCount']++;
      MyErrorHandler(E_USER_WARNING, '[clMySQL.php->GetOneValueByQuery()]: '. mysqli_error($this->ConID) ,__FILE__,__LINE__,  $Query );
      return $default;
    }

    if ($Rec_Arr=mysqli_fetch_row($result))
    {
      mysqli_free_result($result);

      return $Rec_Arr[$Field];
    }
    else
    {
      mysqli_free_result($result);
      return $default;
    }
  }



  // ----------------------------------------------------------------------------- //
  // ----Gibt nur den ersten Record aus einer SQL Abfrage zurück ----------------- //
  // ----------------------------------------------------------------------------- //
  function GetOneRecordByQuery($Query)
  {
    if (!$this->ConID) return null;

    $GLOBALS['MySQLQueryCount']++;

    $result = mysqli_query($this->ConID, $Query);

    if ($result==NULL)
    {
      $GLOBALS['MySQLTransErrorCount']++;
      MyErrorHandler(E_USER_WARNING, '[clMySQL.php->GetOneValueByQuery()]: '. mysqli_error($this->ConID) ,__FILE__,__LINE__,  $Query );
      return null;
    }

    if ($Rec_Arr= mysqli_fetch_assoc($result))
    {
      mysqli_free_result($result);
      return $Rec_Arr;
    }
    else
    {
      mysqli_free_result($result);
      return array();
    }
  }


  //*****************************************************************//
  function MoveNext()
  {
    if ($this->result==NULL) return false;

    $this->MyAkRec = mysqli_fetch_assoc($this->result);
  }

  //*****************************************************************//
  function Fields($FieldName)
  {
    if ($this->MyAkRec) return $this->MyAkRec[$FieldName];
    return false;
  }

  //*****************************************************************//
  function GetRecArray()
  {
    if ($this->MyAkRec) return $this->MyAkRec;
    return false;
  }


  //////////////////////////////////////////////////////////////////////////////////////////
  // Liefert eine Liste als Array
  /////////////////////////////////////////////////////////////////////////////////////////
  function GetArrayByQuery($Query, $IDFieldName, $ValueFieldName, $SelectID=-1)
  {
    $tmp=array();

    $result = mysqli_query($this->ConID, $Query);

    if ($result==NULL)
    {
      $GLOBALS['MySQLTransErrorCount']++;
      MyErrorHandler(E_USER_WARNING, '[clMySQL.php->GetArrayByQuery()]: '. mysqli_error($this->ConID) ,__FILE__,__LINE__,  $Query );
      return array();
    }
 

    $row=mysqli_fetch_assoc($result);
    if ($row)
    {
      //- Schluessel/Feldnamen suchen und zuordnen
      $row_keys = array_keys($row);
      $IDFieldIndex = array_search($IDFieldName, $row_keys);
      $ValueFieldIndex = array_search($ValueFieldName, $row_keys );


      if (($IDFieldIndex===false) || ($ValueFieldIndex==false))
      {
	MyErrorHandler(E_USER_WARNING, '[clMySQL.php->GetArrayByQuery()]: DB-Fieldname ['. $IDFieldName .','. $ValueFieldName .'] not found!! ',__FILE__,__LINE__,  $Query );
	return array();
      }


      $row=array_values($row);

      //- Alle Daten auslesen
      while($row)
      {
	$id = $row[$IDFieldIndex];
	$new['id'] = $id;
	$new['value'] = $row[$ValueFieldIndex];
	$new['selected'] =  ($id==$SelectID);
	
	$tmp[] = $new;
	
	$row=mysqli_fetch_row($result);
      }
    }

    mysqli_free_result($result);
    return $tmp;
  }





  //*****************************************************************//
  function eof()
  {
    if ($this->MyAkRec) return false;
    return true;
  }

  //*****************************************************************//
  function RecordCount()
  {
    if ($this->result==NULL) return -1;

    return mysqli_num_rows($this->result);
  }

  //*****************************************************************//
  function GetInsertId()
  {
    if (!$this->ConID) return 0;
    return mysqli_insert_id($this->ConID);
  }



  //*****************************************************************//
  //* Beispiel für startTransaction():
  //*  $dbA->startTransaction();
  //*  $dbA->doQuery('INSERT ...');	(1)
  //*
  //*  $dbB->startTransaction();
  //*  $dbB->doQuery('UPDATE ...');	(2)
  //*  $dbB->Execute('SELECT ...');	(3)
  //*  $dbB->endTransaction();
  //*
  //*  $dbA->doQuery('UPDATE ...');	(4)
  //*  $dbA->endTransaction();
  //*
  //* Sobald in (1), (2), (3) oder (4) ein Fehler auftritt werden ALLE
  //*  Anweisungen (1), (2) und (4) wieder rückgänig gemacht!
  //*****************************************************************//
  function startTransaction()
  {
    if (!$this->ConID) return false;

    if (mysqli_query($this->ConID, 'START TRANSACTION') ==false)
    {
	MyErrorHandler(E_USER_WARNING, '[clMySQL.php->startTransaction()]: '. mysqli_error($this->ConID) ,__FILE__,__LINE__,  'START TRANSACTION' );
	return false;
    }

    //- nur auf Null setzen wenn dies die Erste/einzige Transaktion ist
    //-  so ist es möglich verschachtelte Transactionen durch zu führen
    if ($GLOBALS['MySQLTransactionCount']==0) $GLOBALS['MySQLTransErrorCount']=0;
    $GLOBALS['MySQLTransactionCount']++;

    return true;
  }


  //*****************************************************************//
  function endTransaction($forceRollback=false)
  {
    if (!$this->ConID) return false;

    $Query ='';    

    if (($GLOBALS['MySQLTransErrorCount']>0) || ($forceRollback))
    {
      MyErrorHandler(E_USER_WARNING, '[clMySQL.php->endTransaction()] Auto-ROLLBACK' ,__FILE__,__LINE__, $_REQUEST);
      $Query ='ROLLBACK';
    }
    else
    {
      $Query ='COMMIT';
    }

    if (mysqli_query($this->ConID, $Query) ==false)
    {
	MyErrorHandler(E_USER_WARNING, '[clMySQL.php->endTransaction()]: '. mysqli_error($this->ConID) ,__FILE__,__LINE__,  $Query );
	return false;
    }

    $GLOBALS['MySQLTransactionCount']--;
    if ($GLOBALS['MySQLTransactionCount']<0) $GLOBALS['MySQLTransactionCount']=0;
    return true;
  }



  //*****************************************************************//
  //** Wandelt ein Datum-Zeit-Str zu einem für diese Datenbank	   **//
  //*****************************************************************//
  function GetSaveDBDate($StrDate, $default='')  //-- Not supported yet
  {
    $Tag ='x';
    $Monat ='x';
    $Jahr = 'x';

    $StrDate=trim($StrDate);

    if ($StrDate=='') return $default;
    $time ='';

    if (strpos($StrDate,'/',0)>0) 
    {
      $StrDate=strtr($StrDate , ' ', '/');
      $listArr = explode('/', $StrDate, 4);
      
      switch (count($listArr))
      {
	case 3:
	  list($Monat, $Tag, $Jahr)=$listArr;
	  break;
	case 4:
	  list($Monat, $Tag, $Jahr, $time)=$listArr;
	  break;
      }
    }
    elseif (strpos($StrDate,'.',0)>0)
    {
      $StrDate=strtr($StrDate , ' ', '.');
      $listArr = explode('.', $StrDate, 4);
      switch (count($listArr))
      {
	case 3:
	  list($Tag, $Monat, $Jahr)=$listArr;
	  break;
	case 4:
	  list($Tag, $Monat, $Jahr, $time)=$listArr;
	  break;
      }
    }
    elseif (strpos($StrDate,'-',0)>0)
    {
      $StrDate=strtr($StrDate , ' ', '-');
      $listArr = explode('-', $StrDate, 4);
      switch (count($listArr))
      {
	case 3:
	  list($Jahr, $Monat, $Tag)=$listArr;
	  break;
	case 4:
	  list($Jahr, $Monat, $Tag , $time)=$listArr;
	  break;
      }
    }
    else return $default;

    

    if (!is_numeric($Tag) || !is_numeric($Monat) || !is_numeric($Jahr)) return $default;
    if (!checkdate($Monat, $Tag, $Jahr)) return $default;
    if ((strlen($Jahr)!=2) && (strlen($Jahr)!=4)) return $default;



    $d = mktime(0,0,0, $Monat, $Tag , $Jahr );


    if ($time=='')
    {
      return "'". date('Y-m-d' ,$d) ."'";
    }
    else
    {
      return "'". date('Y-m-d' ,$d) .' '. $time ."'";
    }
  }


  //*****************************************************************//
  //** Gibt eine String so zurück das man ihn in der SQL abfrage verwenden kann	   **//
  //*****************************************************************//
  function GetSaveStr($inStr, $maxLen=0)
  {
    if ($maxLen<1) return "'". addslashes ($inStr) ."'";

    return "'". addslashes (substr($inStr,0,$maxLen)) ."'";
    
  }


  //*****************************************************************//
  function ExecuteInsertArray($TableName, $insertArray, $ID_FieldName='') //- 'ID_FieldName' wird für MySQL nicht gebraucht!
  {

    if (!$this->ConID) return -1;

    $Query1='';
    $Query2='';

    foreach ($insertArray as $FName => $FValue)
    {
      $Query1 .= ', '. $FName;

      $maxlen = -1;
      if (isset($FValue[2])) $maxlen=($FValue[2]+0);

      switch(strtolower($FValue[0]))
      {
	case 'str':
	  if ($maxlen>0)
	  {
	    $Query2 .= ', '. $this->GetSaveStr(substr($FValue[1],0,$maxlen));
	  }
	  else
	  {
	    $Query2 .= ', '. $this->GetSaveStr($FValue[1]);
	  }
	  break;

	case 'num':
	  if (is_numeric($FValue[1]))
	  {
	    $Query2 .= ', '. $FValue[1] .'';
	  }
	  else
	  {
	    $Query2 .= ', null';
	  }
	  break;
	  
	case 'date':
	  $tmp =$this->GetSaveDBDate($FValue[1]);
	  if ($tmp!='') 
	  {
	    $Query2 .= ', '. $tmp;
	  }
	  else
	  {
	    $Query2 .= ', null';
	  }
	  break;

	case 'func':
	  $Query2 .= ', '. $FValue[1];
	  break;
      }
    }


    $Query = 'INSERT INTO '. $TableName . '('.  substr($Query1,1) .') VALUES ('. substr($Query2,1) .')';


    if (!$this->ConID) return false;

    $GLOBALS['MySQLQueryCount']++;


    if (@mysqli_query($this->ConID, $Query) == false)
    {
      $GLOBALS['MySQLTransErrorCount']++;
      MyErrorHandler(E_USER_WARNING, '[clMySQL.php->ExecuteInsertArray()]: '. mysqli_error($this->ConID) ,__FILE__,__LINE__,  $Query, $this->quietMode );
      return -1;
    }


    return mysqli_insert_id($this->ConID);
  }


}

?>