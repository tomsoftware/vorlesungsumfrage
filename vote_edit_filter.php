<?PHP


include_once('config.php');
include_once(INCLUDE_DIR .'clmysql.php');
include_once(INCLUDE_DIR .'cltemplate.php');
include_once(INCLUDE_DIR .'tools.php');
include_once(INCLUDE_DIR .'vote_session.php');

// ------------------------------------------------------------------ //





// ------------------------------------------------------------------ //
// ------------------------------------------------------------------ //
// ------------------------------------------------------------------ //
$ErrorList = array();


$myTmp = new clTemplate('vote_edit_filter.txt');

$myDB = new clMySQL();


$inVar_FilterID = (GetAllVar('filter_id', -1)+0);
$inVar_Depend_Item_ID = (GetAllVar('dep_id', -1)+0);

$inVar_Command = strtolower(trim(GetAllVar('cmd', '')));
$inVar_Edit = (GetAllVar('edit', 0)+0);



$myTmp->AddVar('USERNAME',  GetvoteUsername());
$myTmp->AddVar('USERNAME_STATUS', GetUserStatusStr());
$myTmp->AddVar('is_edit', $inVar_Edit);

$myTmp->AddVar('SID', GetvoteSID());
$myTmp->AddVar('CH', GetvoteChallenge());


$myTmp->AddVar('FILTER_ID', $inVar_FilterID);
$myTmp->AddVar('Depend_Item_ID', $inVar_Depend_Item_ID);


$myTmp->Write('HEAD');


//- allgemeine Daten abfragen
$myDB->Execute('SELECT vote_Filter_ID AS F01, vote_Filter_Name AS F02, vote_Filter_Destination_List_Index AS F03, vote_Filter_Depending_List_Index AS F04 FROM vote_filter WHERE vote_Filter_ID='. $inVar_FilterID);



if (!$myDB->eof())
{
  $DB_Filter_ID = $myDB->Fields('F01')+0;
  $DB_Filter_Name = $myDB->Fields('F02');
  $DB_Dest_List_ID = $myDB->Fields('F03')+0;
  $DB_Depend_List_ID = $myDB->Fields('F04')+0;



  //- alle möglichen List-Item-IDs abfragen die möglich sind
  $DB_All_List_Item_Arr = array();
  $myDB->Execute('SELECT vote_List_Item_ID AS F01 FROM vote_list_item WHERE vote_List_Index='. $DB_Dest_List_ID);

  while(!$myDB->eof())
  {
    $DB_All_List_Item_Arr[$myDB->fields('F01')] = true;
    $myDB->MoveNext();
  }


  //- Die Variable "$inVar_Depend_Item_ID" sicher machen... d.h. prüfen ob es die wirklich gibt
  $inVar_Depend_Item_ID = ($myDB->GetOneValueByQuery('SELECT vote_List_Item_ID FROM vote_list_item WHERE ((vote_List_Index='. ($DB_Depend_List_ID+0) .') AND (vote_List_Item_ID='. ($inVar_Depend_Item_ID+0) .'))')+0);




  if ($DB_Depend_List_ID>0)
  {
    //- Info: $inVar_Depend_Item_ID wurde schon geprüft!
    $inVar_Depend_Item_ID_SQL = ' = '. $inVar_Depend_Item_ID .' ';
    $inVar_Depend_Item_ID_Insert = ($inVar_Depend_Item_ID +0);
  }
  else
  {
    $inVar_Depend_Item_ID_SQL = ' is null ';
    $inVar_Depend_Item_ID_Insert = 'NULL';
  }
}
else
{
  $DB_Filter_ID = -1;
  $DB_Filter_Name = '[unbekannt]';
  $DB_Dest_List_ID = -1;
  $DB_Depend_List_ID = -1;

  $inVar_Depend_Item_ID_Insert = '-1';
  $inVar_Depend_Item_ID_SQL = ' = -1 ';

  $ErrorList[]='Der Filter mit der ID: "'. $inVar_FilterID .'" wurde nicht gefunden!';
}



// ---------------------- Speichern ---------------------------- //
if (count($ErrorList)<1)
{
  if ($inVar_Command=='save')
  {
    $inVar_new_filter = GetPostVar('filter', array());

    if(is_array($inVar_new_filter))
    {
      
      $myDB->DoQuery('DELETE FROM vote_filter_item WHERE ((vote_Filter_Item_Filter_Index='. $DB_Filter_ID  .') AND (Depend_List_Item_Index'.  $inVar_Depend_Item_ID_SQL .'))');


      foreach($inVar_new_filter as $Item_ID => $Item_val)
      {
	if ($Item_ID>0)
	{
	  //- Prüfen ob die ID auch richtig ist... gehört sie zur Liste [vote_filter].[vote_Filter_Depending_List_Index]
	  if (array_key_exists($Item_ID, $DB_All_List_Item_Arr))
	  {
            $myDB->DoQuery('INSERT INTO vote_filter_item(vote_Filter_Item_Name, vote_Filter_Item_Filter_Index, Depend_List_Item_Index, Show_List_Item_Index) VALUES ('. $myDB->GetSaveStr($Item_val) .', '. $DB_Filter_ID .', '. $inVar_Depend_Item_ID_Insert .', '. ($Item_ID+0) .')');
	  }
	}
	else
	{
	  $ErrorList[] ='Interner Fehler: Ein gewähltes Feld passt nicht zu diesem Filter!';
	}
      }

    }
    else
    {
      $ErrorList[] ='Interner Fehler: Array erwartet für "filter"!';
    }

  }
}

// ---------------------- Kopf-Ausgabe ---------------------------- //

$myTmp->AddVar('FILTER_NAME',  $DB_Filter_Name);
$myTmp->AddVar('DESTINATION_LIST_ID', $DB_Dest_List_ID);
$myTmp->AddVar('DEPENDING_LIST_ID', $DB_Depend_List_ID);

$myTmp->Write('HEAD_INFO');


// ************************************************************************************** //
if (count($ErrorList)>0)
{
  $myTmp->AddVarHTML('ERROR_LIST', '<ul><li>&nbsp;'. implode('</li><li>&nbsp;', $ErrorList) . '</li></ul>');
  $myTmp->Write('ERROR');
  $ErrorList = array();
}


// ---------------------- Ausgabe ---------------------------- //
if ($DB_Filter_ID>0)
{

  $myTmp->Write('LIST_HEAD');

  
  if ($DB_Depend_List_ID>0)
  {
    $myTmp->AddVar('DEP_LIST_NAME', $myDB->GetOneValueByQuery('SELECT vote_list_name AS F01 FROM vote_list WHERE vote_list_id ='. $DB_Depend_List_ID) );
    $myTmp->AddVarHTML('DEP_LIST_LIST', GetIDList($myDB, 'SELECT vote_list_item_ID AS F01, vote_list_item_name AS F02 FROM vote_list_item WHERE vote_List_Index='. $DB_Depend_List_ID .' ORDER BY vote_list_item_sortpos, vote_list_item_name, vote_list_item_value','F01', 'F02', $inVar_Depend_Item_ID));


    $myTmp->Write('LIST_SELECT_DEP');
  }


  //- Fortfahren wenn:
  //--- keine dep-ID benötigt wird
  //--- eine dep-ID gewählt wurde
  if (($DB_Depend_List_ID<1) || ($inVar_Depend_Item_ID>0))
  {

    $Query  = "SELECT \n";
    $Query .= "	vote_List_Item_ID	AS F01, \n";
    $Query .= "	vote_List_Item_Name	AS F02, \n";
    $Query .= "	vote_List_Item_Value	AS F03, \n";
    $Query .= "	vote_List_Item_Sortpos	AS F04, \n";
    $Query .= "	vote_Filter_Item_Name	AS F05, \n";
    $Query .= "	vote_Filter_Item_ID	AS F06, \n";
    $Query .= "	vote_Filter_Item_Name	AS F07, \n";
    $Query .= "	Depend_List_Item_Index	AS F08  \n";
    $Query .= "FROM vote_list_item  \n";
    $Query .= "LEFT JOIN vote_filter_item ON ((vote_list_item.vote_List_Item_ID = vote_filter_item.Show_List_Item_Index) AND (vote_filter_item.Depend_List_Item_Index ". $inVar_Depend_Item_ID_SQL .")) \n";
    $Query .= "WHERE vote_List_Index=". $DB_Dest_List_ID ." \n";
    $Query .= "ORDER BY vote_List_Item_Sortpos, vote_List_Item_Name, vote_List_Item_Value \n";


  
    $myDB->Execute($Query);
  
    $FilterArr = array();
    $HTMLList = '';

    while(!$myDB->eof())
    {
      $item = array();
      $item['l_id'] = ($myDB->Fields('F01')+0);
      $item['f_id'] = ($myDB->Fields('F06')+0);
      $item['name'] = $myDB->Fields('F02'); 
      $item['value'] = $myDB->Fields('F03');
      $item['f_name'] = $myDB->Fields('F05');


      $FilterArr[$myDB->Fields('F01')] =$item;

      if ($myDB->Fields('F03') !='')
      { 
        $HTMLList .= '<option value="'. $myDB->Fields('F01') .'">'. htmlentities($myDB->Fields('F02')) .' - '. htmlentities($myDB->Fields('F03')) .'</option>';
      }
      else
      { 
        $HTMLList .= '<option value="'. $myDB->Fields('F01') .'">'. htmlentities($myDB->Fields('F02')) .'</option>';
      }
  
      $myDB->MoveNext();
    }

    $myTmp->AddVarHTML('NOTUSED_LIST_LIST', $HTMLList);

    $myTmp->Write('LIST_EDIT_LIST');



    //- Javascript Daten ausgeben ----


    $myTmp->Write('SET_JAVASCRIPT_DATA_HEAD');

    foreach($FilterArr as $item)
    {
      $myTmp->AddVarHTML('LIST_ITEM_NAME', addslashes($item['name']));
      $myTmp->AddVar('FILTER_ITEM_ID', $item['f_id']);
      $myTmp->AddVar('LIST_ITEM_ID', $item['l_id']);
      $myTmp->AddVar('LIST_ITEM_VALUE', $item['value']);
      $myTmp->AddVar('FILTER_ITEM_NAME', $item['f_name']);

      if ($item['f_id']>0)
      {
        $myTmp->Write('SET_JAVASCRIPT_DATA_ITEM_ADD');
      }
      else
      {
        $myTmp->Write('SET_JAVASCRIPT_DATA_ITEM');
      }
    } 

    $myTmp->Write('SET_JAVASCRIPT_DATA_FEET');
  }
  else
  {
    $ErrorList[] ='Wählen Sie bitte eine Wert aus der "Abhänigkeitsliste"!';
  }
}



// ************************************************************************************** //
if (count($ErrorList)>0)
{
  $myTmp->AddVarHTML('ERROR_LIST', '<ul><li>&nbsp;'. implode('</li><li>&nbsp;', $ErrorList) . '</li></ul>');
  $myTmp->Write('ERROR');
}


$myTmp->Write('FEET');


?>