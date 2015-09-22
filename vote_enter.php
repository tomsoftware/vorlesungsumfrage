<?PHP


include_once('config.php');
include_once(INCLUDE_DIR .'clmysql.php');
include_once(INCLUDE_DIR .'cltemplate.php');
include_once(INCLUDE_DIR .'tools.php');
include_once(INCLUDE_DIR .'vote_session.php');

srand ((double)microtime()*1000000);


// ------------------------------------------------------------------ //
function Fieldname_to_DBName($Fieldname)
{
  $tmp_type = strtolower(substr($Fieldname,0,2));
  $tmp_val = (substr($Fieldname,2,2)+0);


  if ($tmp_type=='n_')
  {
    if (($tmp_val<1) && ($tmp_val>COUNT_OF_NUMBER_FIELDS)) return '';
    return 'int_'. str_pad($tmp_val, 2, '0', STR_PAD_LEFT);
  }
  elseif ($tmp_type=='s_')
  {
    if (($tmp_val<1) && ($tmp_val>COUNT_OF_STRING_FIELDS)) return '';
    return 'str_'. str_pad($tmp_val, 2, '0', STR_PAD_LEFT) ;
  }

  return '';
}



/////////////////////////////////////////////////////////////////////////////////////////
//- erstellt ein Arra zu einer Liste
//-	return: [LIST_ID] = Wert(val, name, ...)
function CreateList(& $InDB, $List_Index)
{
  $tmp=array();


  $InDB->Execute('SELECT vote_List_Item_ID AS F01, vote_List_Item_Value AS F02, vote_List_Item_Name AS F03 FROM vote_list_item WHERE vote_List_Index='. ($List_Index+0) .' ORDER BY vote_List_Item_Sortpos, vote_List_Item_Name, vote_List_Item_Value');


  while (!$InDB->eof())
  {
    $item = array();
    $item['id'] = $InDB->Fields('F01');
    $item['val'] = $InDB->Fields('F02');
    $item['name'] = $InDB->Fields('F03');

    $tmp[] = $item;

    $InDB->MoveNext();

  }

  return $tmp;
}


/////////////////////////////////////////////////////////////////////////////////////////
//- erstellt ein Array mit allen Informationen zu einem Filter
//-	return: [DEPENDENT_ID][LIST_ID] = Wert(val, name, ...)
function CreateFilter(& $InDB, $Filter_Index )
{
  $tmp=array();


  $Query  = 'SELECT ';
  $Query .= '	vote_filter_item.Depend_List_Item_Index	AS F01, ';
  $Query .= '	vote_filter_item.vote_Filter_Item_Name	AS F02, ';
  $Query .= '	vote_list_item.vote_List_Item_ID	AS F03, ';
  $Query .= '	vote_list_item.vote_List_Item_Value	AS F04, ';
  $Query .= '	vote_list_item.vote_List_Item_Name	AS F05  ';
  $Query .= 'FROM vote_filter_item ';
  $Query .= 'LEFT JOIN vote_list_item ON vote_filter_item.Show_List_Item_Index = vote_list_item.vote_List_Item_ID ';
  $Query .= 'WHERE (vote_Filter_Item_Filter_Index = '. ($Filter_Index+0) .') ';
  $Query .= 'ORDER BY Depend_List_Item_Index, vote_List_Item_Sortpos, vote_List_Item_Name, vote_List_Item_Value ';


  $InDB->Execute($Query);


  while (!$InDB->eof())
  {
    $item_id = ($InDB->Fields('F03')+0);
    $item = array();
    $item['group_name'] = $InDB->Fields('F02');

    $item['id'] = $item_id;
    $item['val'] = $InDB->Fields('F04');
    $item['name'] = $InDB->Fields('F05');

    $tmp[ ($InDB->Fields('F01') +0) ][$item_id] = $item;

    
    $InDB->MoveNext();
  }

  return $tmp;
}


//////////////////////////////////////////////////////////////////////////////////////////
// Main
/////////////////////////////////////////////////////////////////////////////////////////

//- Datenbank
$myDB = new clMySQL();
$myList = new clMySQL();

//- Ausgabe Template
$myTmp = new clTemplate('vote_enter.txt');


//- hier komme alle Fehler rein
$ErrorList = array();
$InfoList = array();

//- Übergebene Werte einlesen
$inVar_VID = (GetAllVar('vid', -1)+0);
$inVar_Edit = (GetAllVar('edit', 0)+0);
$inVar_Command = strtolower(trim(GetAllVar('cmd', '')));
$inVar_Values = GetPostVar('value', array());
$inVar_reload_suppress = (GetAllVar('reload_suppress', rand (10000,99999) )+0);
$inVar_doscrollto = (trim(GetAllVar('doscrollto', 0))+0);
$inVar_session_pos = GetAllVar('session_pos',0)+0;  //- gibt die Position an wenn man Datensätze bearbeitet!

//- Für edit
$inVar_new_list_text = strtolower(trim(GetAllVar('new_list', '')));



//-- Übergebene Werte auf richtigkeit prüfen
if (!is_array($inVar_Values)) $inVar_Values=array();



//- übergebene Werte Prüfen
if ($inVar_Edit==1)
{
  if ( (!GetvoteIsAdmin() ) ) 
  {
    $ErrorList[]='Sie müssen Admin sein um diese Funktion zu nutzen!';
    $inVar_Edit=0;
  }
}


if ((GetvoteSID()<1) || (!GetvoteIsMod() && !GetvoteIsAdmin()) )
{
  $inVar_VID=0;
  $ErrorList[] = 'Sie müssen sich zu erst anmelden - Status: Moderator!';
}


if ( $inVar_VID<1) 
{
  $ErrorList[] = 'Es muss zuerst eine Umfrage ausgewählt werden';
}

if ($inVar_Command =='move2new')
{
  $inVar_reload_suppress = rand (10000,99999) ;
}


if ($inVar_reload_suppress <1)
{
  $ErrorList[] = 'Interner Fehler: die Reload-Sperre ist fehlerhaft!';
}


if ($inVar_session_pos<1) $inVar_session_pos=0;


//- einfache Werte in das Template schreiben
$myTmp->AddVar('VOTE_ID',  $inVar_VID);
$myTmp->AddVar('USERNAME',  GetvoteUsername());
$myTmp->AddVar('USERNAME_STATUS', GetUserStatusStr());
$myTmp->AddVar('reload_suppress', $inVar_reload_suppress);
$myTmp->AddVar('is_edit', $inVar_Edit);
$myTmp->AddVar('session_pos', $inVar_session_pos);


$myTmp->AddVar('SID', GetvoteSID());
$myTmp->AddVar('CH', GetvoteChallenge());
$myTmp->AddVar('DOSCROLLTO', ''. $inVar_doscrollto);


//- Template Bereich 'HEAD' ausgeben
$myTmp->Write('HEAD');



    //------ Edit-Befehle-Ausführen-----
    // ************************************************************************************** //
    if (($inVar_Edit==1) && (GetvoteIsAdmin()) )
    {
      define('vote_enter_edit', true);
      include('vote_enter_edit.php');

    }


    // ************************************************************************************** //
    // ** Feld-Infos aus DB laden
    // ************************************************************************************** //

    //- Gennerelle Informationen über die Umfrage
    $myDB->Execute('SELECT vote_Name AS F01, vote_Comment AS F02 FROM vote WHERE vote_ID='. $inVar_VID);

    $myTmp->AddVar('VOTE_NAME', $myDB->Fields('F01'));
    $myTmp->AddVar('vote_Comment', $myDB->Fields('F02'));


    $Daten = array();	//- alle Feldinformationen
    $Listen = array();	//- alle listen = [LIST_ID][LIST_ITEM_ID]
    $Filter = array();	//- alle Filter = [FILTER_ID][DEPENDENT_VALUE][LIST_ITEM_ID]
    $SaveData = array(); //- sichere Werte zum Speichern


    //- nur eine Temp-Var für bessere Laufzeit
    $do_SaveData = false;
    $do_UpdateData = false;
    if ($inVar_Command=='save') $do_SaveData=true;


    //- alle Felde-Informationen für diese Umfrage abfragen
    $Query  = "SELECT \n";
    $Query .= "	vote_Field_ID				AS F01, \n";
    $Query .= "	vote_Field_Name				AS F02, \n";
    $Query .= "	vote_Field_Comment			AS F03, \n";
    $Query .= "	vote_Field_Type				AS F04, \n";
    $Query .= "	vote_Field_Min				AS F05, \n";
    $Query .= "	vote_Field_Max				AS F06, \n";
    $Query .= "	vote_Field_Data_Fieldname		AS F08, \n";
    $Query .= "	vote_Field_List_Index			AS F09, \n";
    $Query .= "	vote_Field_Filter_Index			AS F10, \n";
    $Query .= "	vote_Field_Filter_Depending_Field_Index	AS F11, \n";
    $Query .= "	vote_Field_isNecessary			AS F12, \n";
    $Query .= "	Vote_List_allowEdit			AS F13, \n";
    $Query .= "	Vote_Filter_allowEdit			AS F14, \n";
    $Query .= "	vote_Field_KeepAfterSaving		AS F15  \n";
    $Query .= "FROM vote_field \n";
    $Query .= "	LEFT JOIN vote_list ON vote_field.vote_Field_List_Index=vote_list.vote_List_ID \n";
    $Query .= "	LEFT JOIN vote_filter ON vote_field.vote_Field_Filter_Index=vote_filter.vote_Filter_ID \n";
    $Query .= "WHERE vote_Index=". $inVar_VID ." \n";
    $Query .= "ORDER BY vote_Field_Sortpos \n";



    $myDB->Execute($Query);

    if (!$myDB->eof())
    {
      while(!$myDB->eof())
      {
 	//- Feld-Informationen in Array $Item schreiben und dann an Array $Daten anhängen 
        $Item = array();
  	$Item_ID  = ($myDB->Fields('F01')+0);
	$Item_Type = ($myDB->Fields('F04')+0);
	$Item_List_ID = ($myDB->Fields('F09')+0);
  	$Item_Filter_ID = ($myDB->Fields('F10')+0);


        $Item['ID'] = $Item_ID;
        $Item['Name'] = $myDB->Fields('F02');
        $Item['Comment'] = $myDB->Fields('F03');
        $Item['min'] = $myDB->Fields('F05')+0;
        $Item['max'] = $myDB->Fields('F06')+0;
        $Item['item_type'] = $Item_Type;
        $Item['list_ID'] = $Item_List_ID;
        $Item['filter_ID'] = $Item_Filter_ID;
        $Item['depending_ID'] = $myDB->Fields('F11')+0;
        $Item['data_field'] = $myDB->Fields('F08');
        $Item['isNecessary'] = ($myDB->Fields('F12')+0);
	$Item['allowEdit'] = ( (($myDB->Fields('F13')+0)==1) || (($myDB->Fields('F14')+0)==1) );
	$Item['keepAfterSaving'] = (($myDB->Fields('F15')+0)==1);


	//- die übergebenen Werte (aus der abgesendeten Form) auch einfügen
	if (array_key_exists($Item_ID, $inVar_Values))
	{
          $Item['set'] = trim(stripcslashes(DecodeRequestCharset( $inVar_Values[$Item_ID] )));
	}
	else
	{
          $Item['set'] = '';
	}

	//- Min und max Werte auf richtigkeit prüfen
	if ($Item['min']>$Item['max'])
	{
	  $i = $Item['min'];
	  $Item['min']=$Item['max'];
	  $Item['max'] = $i;
	}

	//- Benötigte Filter laden...
	if ($Field_Type_is_Filter [ $Item_Type ] )
	{
	  if (!array_key_exists($Item_Filter_ID , $Filter))
	  {
	    $Filter[$Item_Filter_ID] = CreateFilter( $myList, $Item_Filter_ID );
	  }

	} //- Benötigte Listen laden
	else if (  $Field_Type_is_List[ $Item_Type ]   )
        {
	  if (!array_key_exists($Item_List_ID , $Listen))
	  {
	    $Listen[ $Item_List_ID ] = CreateList( $myList, $Item_List_ID );
	  }
        }


	//- Feld-Infos zusammenfügen/anhengen	
        $Daten[$Item_ID] = $Item;


	//-- Prüfen ob die eingabe gültig sind und ob es in die DB muss
	if (($do_SaveData) && ( $Field_Type_DataType[ $Item['item_type'] ]>0 ))
	{

	  //- Prüfen ob die übergebenen Daten richtig sind
	  if (array_key_exists($Item['ID'], $inVar_Values))
	  {
	    //- Dieser Wert =>$tmp wurde gewählt bzw. eingegeben
	    //$tmp=trim(utf8_decode($inVar_Values[$Item['ID']]));
	    $tmp=trim(stripcslashes(DecodeRequestCharset($inVar_Values[$Item['ID']])));
	
	    //- einzelne Datentypen prüfen
	    switch ( $Item['item_type'] )
	    {


	      case FIELD_TYPE_STRING:

		$l = strlen($tmp);

		if ($Item['min']<1) $Item['min'] =0;
		if (($Item['max']<1) || ($Item['max']>255)) $Item['max']= 255;

		if (($l>=$Item['min']) && ($l<=$Item['max'])) 
		{
		  $SaveData[ $Item['data_field'] ] = $myDB->GetSaveStr($tmp);
		}
		else
		{
		  $ErrorList[] = 'Das Feld "'. $Item['Name'] .'" muss min. '. $Item['min'] .' und max. '. $Item['max'] .' Zeichen lang sein!';
		}

		break;



	      case FIELD_TYPE_NUMBER:

		if ($tmp=='')
		{
		  if ($Item['isNecessary']) $ErrorList[] = 'Das Feld "'. $Item['Name'] .'" ist ein Pflichtfeld';
		}
		else
		{
		  $tmp = ($tmp+0);
		  if (($tmp>=$Item['min']) && ($tmp<=$Item['max'])) 
		  {
		    $SaveData[ $Item['data_field'] ] = ($tmp + 0);
		  }
		  else
		  {
		    $ErrorList[] = 'Das Feld "'. $Item['Name'] .'" muss im Bereich von min. '. $Item['min'] .' und max. '. $Item['max'] .' sein!';
		  }
		}

		break;



	      case FIELD_TYPE_COMBOLIST:
	      case FIELD_TYPE_RADIOLIST:
	      case FIELD_TYPE_RADIOLIST_NOTEXT:
	      case FIELD_TYPE_FILTER_COMBOLIST:
	      case FIELD_TYPE_FILTER_COMBOLIST:
		$tmp = ($tmp+0);
		if ($tmp>0)
		{
		  $SaveData[ $Item['data_field'] ] = ($tmp + 0);
		}
		else
		{
		  if ($Item['isNecessary']) $ErrorList[] = 'Das Feld "'. $Item['Name'] .'" ist ein Pflichtfeld';
		}

		break;

	    }


          }
	  else if ($Item['isNecessary']==1 )
	  {
	    $ErrorList[] = 'Das Feld "'. $Item['Name'] .'" ist ein Pflichtfeld';
          }

	}


        $myDB->MoveNext();
      }



      // *********************** speichern *************************************************** //
      if ($do_SaveData)
      {
        if ($inVar_Edit==1)
	{
	  $ErrorList[] = 'Im "Edit"-Modus kann man keine Datensätze speichern!';
	}



	if ($inVar_reload_suppress>0) 
	{
	  $DB_Reload_Data_ID = ($myDB->GetOneValueByQuery('SELECT vote_data_ID FROM vote_data WHERE vote_Index = '. $inVar_VID .' AND vote_Session_Index='. GetvoteSID() .' AND vote_data_reload_suppress='. $inVar_reload_suppress)+0);
	  if ($DB_Reload_Data_ID>0)
	  {
	    if ($inVar_session_pos<1)
	    {
	      $ErrorList[] = 'Dieser Datensatz wurde schon gespeichert (DB-ID: '. $DB_Reload_Data_ID .') - Reload Sperre!';
	      $InfoList[] = 'Verwenden Sie den Navigator rechts oben um einen Datensatz zu wählen oder einen neuen anzulegen';
	    }
	    else
	    {
	      $InfoList[] = 'Der Datensatz wird geändert!';
	      $do_UpdateData=true;
	    }
	  }

	}
	else
	{
	  $ErrorList[] = 'Dieser Datensatz wurde schon gespeichert (Sperr-Code fehlt)!';
	  $InfoList[] = 'Verwenden Sie den Navigator rechts oben um einen Datensatz zu wählen oder einen neuen anzulegen';
	}
	


	if ( count($ErrorList)<1 )
	{
	  if ($do_UpdateData)
	  {
	    $Query = 'UPDATE vote_data SET ';
	  }
	  else
	  {
	    $Query = 'INSERT INTO vote_data SET ';
	  }


	  foreach($SaveData as $index => $Item)
	  {
	    if ($index=='') $ErrorList[] ='Interner Fehler: Datenfelde ohne Wert in "vote_Field_Data_Fieldname" ';

	    $tmp_type = strtolower(substr($index,0,2));
	    $tmp_val = (substr($index,2,2)+0);

	    if ($tmp_type=='n_')
	    {
	      if (($tmp_val<1) && ($tmp_val>COUNT_OF_NUMBER_FIELDS)) $ErrorList[] ='Interner Fehler: Feldindex außerhalb des Gültigen Bereiches: '. $index;
	      $Query .= 'int_'. str_pad($tmp_val, 2, '0', STR_PAD_LEFT) .' = '. $Item .', ';
	    }
	    elseif ($tmp_type=='s_')
	    {
	      if (($tmp_val<1) && ($tmp_val>COUNT_OF_STRING_FIELDS)) $ErrorList[] ='Interner Fehler: Feldindex außerhalb des Gültigen Bereiches: '. $index;
	      $Query .= 'str_'. str_pad($tmp_val, 2, '0', STR_PAD_LEFT) .' = '. $Item .', ';
	    }
	    else
	    {
	      $ErrorList[] ='Interner Fehler: Unbekannter Datentyp: '. $index;
	    }
	 
	  }




	  if ($do_UpdateData)
	  {
	    //- Füg ich nur ein damit der SQL Syntaktisch richtig ist -> logisch ist das 100% überflüssig
	    $Query .= 'vote_Index = '. $inVar_VID .' ';

	    //- nur den Datensatz updaten den wir gerade sehen!
	    $Query .= 'WHERE ((vote_data_ID='. ($DB_Reload_Data_ID+0) .') AND (vote_Index = '. $inVar_VID .')) ';
	  }
	  else
	  {

	    $Query .= 'vote_Index = '. $inVar_VID .', ';
	    $Query .= 'vote_Session_Index = '. GetvoteSID() .', ';
	    $Query .= 'vote_data_timestamp = now(), ';
	    $Query .= 'vote_data_reload_suppress = '. $inVar_reload_suppress;
	  }


          if ( count($ErrorList)<1 )
	  {
	    $myDB->DoQuery($Query);

	    //- neue Reload-Sperre setzen
	    $inVar_reload_suppress = rand (10000,99999);


	    $myTmp->AddVar('reload_suppress', $inVar_reload_suppress);


	    //- alles OK ... Daten/Werte aus Anzeige löschen!
	    foreach ($Daten as $index => $Item) 
	    {
	      if (!$Daten[ $index ]['keepAfterSaving']) $Daten[ $index ]['set'] = '';
	    }

	  }
	  else
	  {
	    $ErrorList[] ='Query: '. $Query;
	  }
        }
      }

    }
    else
    {
      if ( $inVar_VID>0)  $ErrorList[] = 'Zu dieser Umfrage sind keine Fragen definiert!';
    }




    // ************************************************************************************** //

    $Vote_Count = ($myDB->GetOneValueByQuery('SELECT count(*) FROM vote_data WHERE ((vote_Index='. ($inVar_VID+0) .') AND (vote_Session_Index='. (GetvoteSID()+0) .'))')+0);


    // *********************** Datensatz bewegungen **************************************** //
    if ($inVar_Command=='move2next') 
    {
      if ($inVar_session_pos>0) $inVar_session_pos++;
    }

    //---
    if ($inVar_Command=='move2prev') 
    {
      if ($inVar_session_pos>0)
      {
        $inVar_session_pos--;
      }
      else
      {
        $inVar_session_pos=$Vote_Count;
      }   

      if ($inVar_session_pos<1) $inVar_session_pos=1;
    }


    //---
    if ($inVar_session_pos>$Vote_Count) $inVar_Command='move2new';
    if ($inVar_session_pos<0) $inVar_Command='move2new';

    //---
    if ($inVar_Command=='move2new') $inVar_session_pos=0;


    // ************************************************************************************** //
    // *************************** Alte Umfrage-Daten laden ********************************* //
    // ************************************************************************************** //
    if ($inVar_session_pos>0)
    {
      //- Alte Daten aus DB abfragen
      $Query = 'SELECT vote_data_reload_suppress AS F01, vote_data.* FROM vote_data WHERE ((vote_Index='. $inVar_VID .') AND (vote_Session_Index='. GetvoteSID() .')) ORDER BY vote_data_timestamp LIMIT '. ($inVar_session_pos-1) .', 1';

      $myDB->Execute($Query);

      //- Was gefunden?
      if (!$myDB->eof())
      {
	//- gibt es einen Wert für 'reload_suppress'?
	if ($myDB->Fields('F01')!='')
	{
	  $myTmp->AddVar('reload_suppress', $myDB->Fields('F01'));


	  $InfoList[]='Sie bearbeiten gerade einen bereits gespeicherten Datensatz!';

          $DB_Fileds = $myDB->GetRecArray();

	  foreach($Daten as $item_id =>  $Item)
	  {
	    $tmp = Fieldname_to_DBName($Item['data_field']);
	    if (array_key_exists($tmp, $DB_Fileds))
	    {
	      $Daten[$item_id]['set'] = $DB_Fileds[$tmp];
	    }
	  }
	}
	else
	{
	  //- keine Ahnung ob dieser Fall mal eintreten kann ... aber sicher ist sicher!
	  $inVar_session_pos=0;
	  $inVar_Command='move2new';
	}
      }
      else
      {
        $inVar_session_pos=0;
	$inVar_Command='move2new';
      }
    }

    if ($inVar_Command=='move2new')
    {
      $myTmp->AddVar('reload_suppress', $inVar_reload_suppress);

      foreach ($Daten as $index => $Item) 
      {
        if (!$Daten[ $index ]['keepAfterSaving']) $Daten[ $index ]['set'] = '';
      }
    }



    //- Seiten Position ausgeben
    $myTmp->AddVar('CURRENT_VOTE_COUNT', $Vote_Count .'');

    if ($inVar_session_pos>0)
    {
      $myTmp->AddVar('session_pos', $inVar_session_pos);
    }
    else
    {
      $myTmp->AddVar('session_pos', '[neu]');
    }



    // ************************************************************************************** //
    // ****************************** Ausgabe der Felder ************************************ //
    // ************************************************************************************** //
    $myTmp->Write('VOTE_HEAD');



    if (count($ErrorList)>0)
    {
      $myTmp->AddVarHTML('ERROR_LIST', '<ul><li>&nbsp;'. implode('</li><li>&nbsp;', $ErrorList) . '</li></ul>');
      $myTmp->Write('ERROR');
      $ErrorList=array();
    }


    if (count($InfoList)>0)
    {
      $myTmp->AddVarHTML('INFO_LIST', '<ul><li>&nbsp;'. implode('</li><li>&nbsp;', $InfoList) . '</li></ul>');
      $myTmp->Write('INFO');
      $InfoList=array(); 
    }


    $myTmp->Write('TYPE_HEAD_2');


    if (count($Daten)>0)
    {
      foreach($Daten as $Item)
      {
        $myTmp->AddVar('VOTE_FIELD_ID', $Item['ID']);
        $myTmp->AddVar('VOTE_FIELD_NAME', $Item['Name']);
        $myTmp->AddVar('VOTE_FIELD_Comment', $Item['Comment']);
        $myTmp->AddVar('VOTE_FIELD_MIN', $Item['min']);
        $myTmp->AddVar('VOTE_FIELD_MAX', $Item['max']);

        if ($inVar_Edit==1)
        {
          $myTmp->Write('TYPE_ITEM_HEAD_EDIT');
        }
        else
        {
          $myTmp->Write('TYPE_ITEM_HEAD');
        }
	

        switch ($Item['item_type'])
        {
	  ///////////////////////////////////////////////////
	  case FIELD_TYPE_STRING:
	    $myTmp->AddVar('SET_STRING_VALUE', $Item['set']);
	    $myTmp->Write('TYPE_STRING');
	    break;

	  ///////////////////////////////////////////////////
	  case FIELD_TYPE_NUMBER:
	    $myTmp->AddVar('SET_NUM_VALUE', ($Item['set']+0));
	    $myTmp->Write('TYPE_NUMBER');
	    break;

	  ///////////////////////////////////////////////////
	  case FIELD_TYPE_COMBOLIST:


	    $myTmp->AddVar('LIST_ID', $Item['list_ID']+0);

	    $myTmp->Write('TYPE_LIST_DD');


	    foreach($Listen[ $Item['list_ID']+0 ] as $list_item)
	    {
              $myTmp->AddVar('VOTE_LIST_ID', $list_item['id']);
              $myTmp->AddVar('VOTE_LIST_NAME', $list_item['name']);

	      if ($Item['set']==$list_item['id'])
	      {
              	$myTmp->AddVarHTML('VOTE_LIST_SELECTED', 'selected="selected"');
	      }
	      else
	      {
              	$myTmp->AddVar('VOTE_LIST_SELECTED', '');
	      }

	      $myTmp->Write('TYPE_LIST_DD_ITEM');
	    }

	    if ($Item['allowEdit'])
	    {
	      $myTmp->Write('TYPE_LIST_DD_EDIT_FEET');
	    }
	    else
	    {
	      $myTmp->Write('TYPE_LIST_DD_FEET');
	    }


	    break;

	  ///////////////////////////////////////////////////
	  case FIELD_TYPE_RADIOLIST:

	    $myTmp->Write('TYPE_LIST_OP');

	    foreach($Listen[ $Item['list_ID']+0 ] as $list_item)
	    {
              $myTmp->AddVar('VOTE_LIST_ID', $list_item['id']);
              $myTmp->AddVar('VOTE_LIST_NAME', $list_item['name']);
              $myTmp->AddVar('VOTE_LIST_VALUE', $list_item['val']);

	      if ($Item['set']==$list_item['id'])
	      {
              	$myTmp->AddVarHTML('VOTE_LIST_CHECKED', 'checked="checked"');
	      }
	      else
	      {
              	$myTmp->AddVar('VOTE_LIST_CHECKED', '');
	      }


              // $myTmp->AddVar('VOTE_LIST_CHECKED', $list_item['check']);

	      $myTmp->Write('TYPE_LIST_OP_ITEM');
	    }

	    $myTmp->Write('TYPE_LIST_OP_FEET');

	    break;


	  ///////////////////////////////////////////////////
	  case FIELD_TYPE_RADIOLIST_NOTEXT:

	    $myTmp->Write('TYPE_LIST_OP_NOTEXT');


	    foreach($Listen[ $Item['list_ID']+0 ] as $list_item)
	    {
              $myTmp->AddVar('VOTE_LIST_ID', $list_item['id']);
              $myTmp->AddVar('VOTE_LIST_NAME', $list_item['name']);
              $myTmp->AddVar('VOTE_LIST_VALUE', $list_item['val']);

	      if ($Item['set']==$list_item['id'])
	      {
              	$myTmp->AddVarHTML('VOTE_LIST_CHECKED', 'checked="checked"');
	      }
	      else
	      {
              	$myTmp->AddVar('VOTE_LIST_CHECKED', '');
	      }


	      $myTmp->Write('TYPE_LIST_OP_NOTEXT_ITEM');
	    }

	    $myTmp->Write('TYPE_LIST_OP_NOTEXT_FEET');

	    break;



	  ///////////////////////////////////////////////////
	  case FIELD_TYPE_COMMENT:
	    $myTmp->Write('TYPE_COMMENT');
	    break;


	  ///////////////////////////////////////////////////
	  case FIELD_TYPE_RADIO_HEADLINE:

	    $myTmp->Write('TYPE_LIST_OP_HEADLINE');

	    foreach($Listen[ $Item['list_ID']+0 ] as $list_item)
	    {
              $myTmp->AddVar('VOTE_LIST_ID', $list_item['id']);
              $myTmp->AddVar('VOTE_LIST_NAME', $list_item['name']);

	      $myTmp->Write('TYPE_LIST_OP_HEADLINE_ITEM');
	    }

	    $myTmp->Write('TYPE_LIST_OP_HEADLINE_FEET');

	    break;



	  ///////////////////////////////////////////////////
	  case FIELD_TYPE_FILTER_COMBOLIST:

	    $myTmp->AddVar('VOTE_DEPEND_ITEM_ID', $Item['depending_ID'] );
	    $myTmp->AddVar('LIST_ID', $Item['list_ID']+0);
	    $myTmp->AddVar('FILTER_ID', $Item['filter_ID']+0);


	    $myTmp->Write('TYPE_FILTER_DD');


	    //- Java-array ... ---------------------
	    foreach($Filter[ $Item['filter_ID']+0 ] as $filter_item_id => $filter_item)
	    {
	      $tmp ='';

	      foreach ($filter_item as $list_item )
	      {
		$tmp .= '"'. addslashes($list_item['id']) .'","'. addslashes($list_item['name']) .'","'. addslashes($list_item['group_name']) .'",';
	      }

	      $tmp = substr($tmp,0,-1);

              $myTmp->AddVar('VOTE_DEPEND_LIST_ID', $filter_item_id);
              $myTmp->AddVarHTML('VOTE_LIST_ARRAY', $tmp);


	      $myTmp->Write('TYPE_FILTER_DD_SCRIPT_ITEM');
	    }

	    $myTmp->Write('TYPE_FILTER_DD_SCRIPT_FEET');




	    //- liste...  --------------------

	    $tmp_filter_list=array();

	    if (($Item['depending_ID']+0)<1)
	    {
	      //- diese Liste hat keine abhänigkeit!
	      if (array_key_exists(0, $Filter[ $Item['filter_ID']+0] ))
	      {
	        $tmp_filter_list = $Filter[ $Item['filter_ID']+0 ][0];
	      }
	    }
	    else
	    {
	      //- frag set-wert aus der depending-Liste ab --> das ist der Wert für die Liste!
	      //- logisch oder? !

	      if (array_key_exists($Item['depending_ID'], $Daten ))
	      {
	        $dep_value = ($Daten[ $Item['depending_ID'] ]['set'])+0;

		if (array_key_exists($dep_value, $Filter[ $Item['filter_ID']+0] ))
		{
	          $tmp_filter_list = $Filter[ $Item['filter_ID']+0][ $dep_value ];
		}
	      }
	    }



	    //- liste ausgenben ...  --------------------
	    if (count($tmp_filter_list))
	    {
	      foreach ($tmp_filter_list as $list_item )
	      {
		$myTmp->AddVar('VOTE_LIST_ID', $list_item['id']);
		if ($list_item['group_name']!='')
		{
		  $myTmp->AddVar('VOTE_LIST_NAME', $list_item['name'] .' ('. $list_item['group_name'] .')');
		}
		else
		{
		  $myTmp->AddVar('VOTE_LIST_NAME', $list_item['name'] .' '. $list_item['group_name']);
		}

		if ($list_item['id'] == $Item['set'])
		{
		  $myTmp->AddVarHTML('VOTE_LIST_SELECTED', 'selected="selected"');
		}
		else
		{
		  $myTmp->AddVarHTML('VOTE_LIST_SELECTED', '');
		}
	
		$myTmp->Write('TYPE_FILTER_DD_ITEM');
	      }
	    }

	    // foreach($Filter[ $Item['filter_ID']+0 ] as $filter_item_id => $filter_item)
	    // {
	    //  $tmp ='';
	    //
	    //  foreach ($filter_item as $list_item )
	    //  {
	    //    $tmp .= '"'. addslashes($list_item['id']) .'","'. addslashes($list_item['name']) .'","'. addslashes($list_item['group_name']) .'",';
	    //  }
	    //
	    //  $tmp = substr($tmp,0,-1);
	    //
            //  $myTmp->AddVar('VOTE_DEPEND_LIST_ID', $filter_item_id);
            //  $myTmp->AddVarHTML('VOTE_LIST_ARRAY', $tmp);
	    //
	    //
	    //  $myTmp->Write('TYPE_FILTER_DD_ITEM');
	    //}



	    if ($Item['allowEdit'])
	    {
	      $myTmp->Write('TYPE_FILTER_DD_EDIT_FEET');
	    }
	    else
	    {
	      $myTmp->Write('TYPE_FILTER_DD_FEET');
	    }




	    break;




        }

        $myTmp->Write('TYPE_ITEM_FEET');
      
      
      }



      $myTmp->Write('VOTE_ENTER_FEET');
    }


    $myTmp->Write('VOTE_FEET');


    // ************************************************************************************** //
    if ($inVar_Edit==1)
    {

      //----------------------
      $Query  = 'SELECT ';
      $Query .= '	concat("l_", vote_List_ID) AS F01, ';
      $Query .= '	vote_List_Name AS F02 ';
      $Query .= 'FROM vote_list ';
      $Query .= 'ORDER BY vote_List_Name ';

      $myTmp->AddVarHTML('NEW_LIST_LIST',   GetIDList($myDB, $Query, 'F01', 'F02', $inVar_new_list_text));

      //----------------------
      $Query  = 'SELECT ';
      $Query .= '	concat("f_", vote_Filter_ID) AS F01, ';
      $Query .= '	vote_Filter_Name AS F02 ';
      $Query .= 'FROM vote_filter ';
      $Query .= 'ORDER BY vote_Filter_Name';

      $myTmp->AddVarHTML('NEW_FILTER_LIST', GetIDList($myDB, $Query, 'F01', 'F02', $inVar_new_list_text));


      //----------------------
      $myTmp->AddVarHTML('NEW_TYPE_LIST', GetHTMLList($Field_Type_Name, $inVar_new_type));


      //----------------------

      $tmp = array();
      foreach($Field_Type_DataType as $item_id => $item)
      {
	if ($item==1) $tmp[]=$item_id;
      }

      $Query  = 'SELECT ';
      $Query .= '	vote_Field_ID AS F01, ';
      $Query .= '	vote_Field_Name AS F02 ';
      $Query .= 'FROM vote_field ';
      $Query .= 'WHERE ( ';
      $Query .= '	(vote_Index='. ($inVar_VID+0) .') AND ';
      $Query .= '	(vote_Field_List_Index is not null) AND ';
      $Query .= '	(vote_Field_Type in ('. implode(',', $tmp) .')) ';
      $Query .= ')';
      $Query .= 'ORDER BY vote_Field_Sortpos, vote_Field_Name';

      $myTmp->AddVarHTML('KNOWN_VOTE_FIELDS_LIST', GetIDList($myDB, $Query, 'F01', 'F02'));


      $myTmp->Write('EDIT');
    }  //- ende Edit



// ************************************************************************************** //


if (count($InfoList)>0)
{
  $myTmp->AddVarHTML('INFO_LIST', '<ul><li>&nbsp;'. implode('</li><li>&nbsp;', $InfoList) . '</li></ul>');
  $myTmp->Write('INFO');
}


// ************************************************************************************** //
if (count($ErrorList)>0)
{
  $myTmp->AddVarHTML('ERROR_LIST', '<ul><li>&nbsp;'. implode('</li><li>&nbsp;', $ErrorList) . '</li></ul>');
  $myTmp->Write('ERROR');
}


$myTmp->Write('FEET');

?>