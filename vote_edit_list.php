<?PHP


include_once('config.php');
include_once(INCLUDE_DIR .'clmysql.php');
include_once(INCLUDE_DIR .'cltemplate.php');
include_once(INCLUDE_DIR .'tools.php');
include_once(INCLUDE_DIR .'vote_session.php');

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


// ------------------------------------------------------------------ //
function checkInUse($ListItemID, $forceListID=-1)
{
  $myDB = new clMySQL();
  $ret = array();
  $ret['state'] = false;
  $ret['msg'] = 'Unknown error!';

  $ListItemID = ($ListItemID +0);

  $DB_List_ID = ($myDB->GetOneValueByQuery('SELECT vote_List_Index AS F01 FROM vote_list_item WHERE vote_List_Item_ID ='. $ListItemID)+0);

  if (($DB_List_ID<1) || (($forceListID>0) && ($forceListID!=$DB_List_ID)))
  {
    $ret['msg'] = 'Listen Eintrag '. $ListItemID .' nicht gefunden!';
    return $ret;
  }

  //- Prüfe Filter...
  $DB_Filter_ID = ($myDB->GetOneValueByQuery('SELECT vote_Filter_Item_Filter_Index FROM vote_filter_item WHERE ((Depend_List_Item_Index='. $ListItemID .') OR (Show_List_Item_Index='. $ListItemID .'))')+0);
  if ($DB_Filter_ID>0)
  {
    $DB_Filter_Name = $myDB->GetOneValueByQuery('SELECT vote_Filter_Name AS F01 FROM vote_filter WHERE (vote_Filter_ID='. $DB_Filter_ID .')');

    $ret['msg'] = 'Listen Eintrag wird von dem Filter "'. $DB_Filter_Name .'" verwendet!';
    return $ret;
  }


  //- Daten Prüfen...
  $myDB->Execute('SELECT vote_Index AS F01, vote_Field_Data_Fieldname AS F02 FROM vote_field WHERE ((vote_Field_Data_Fieldname is not null) AND (vote_Field_List_Index='. $DB_List_ID .'))');

  $checkArr = array();

  while(!$myDB->eof())
  {
    $checkArr[] = ' ( (vote_Index = '. $myDB->Fields('F01') .') AND ( '. Fieldname_to_DBName( $myDB->Fields('F02') ) .' = '. $ListItemID .')) ';

    $myDB->MoveNext();
  }


  if (count($checkArr)>0)
  {
    $DB_Count = ($myDB->GetOneValueByQuery('SELECT Count(*) FROM vote_data WHERE ('. implode(' OR ', $checkArr) .')')+0);

    if ( $DB_Count> 0) 
    {
      $ret['msg'] = 'Listen Eintrag wird bereits in '. $DB_Count .' Datensätzen verwendet!';
      return $ret;
    }
  }
  
  
  $ret['state'] = true;
  return $ret;
	

}



// ------------------------------------------------------------------ //
// ------------------------------------------------------------------ //
// ------------------------------------------------------------------ //
$ErrorList = array();


$myTmp = new clTemplate('vote_edit_list.txt');

$myDB = new clMySQL();


$inVar_VID = (GetAllVar('vid', -1)+0);
$inVar_ListID = (GetAllVar('list_id', -1)+0);
$inVar_ListItemID = (GetAllVar('list_item_id', -1)+0);
$inVar_Command = strtolower(trim(GetAllVar('cmd', '')));
$inVar_Edit = (GetAllVar('edit', 0)+0);
$inVar_doscrollto = (trim(GetAllVar('doscrollto', 0))+0);


$myTmp->AddVar('VOTE_ID',  $inVar_VID);
$myTmp->AddVar('USERNAME',  GetvoteUsername());
$myTmp->AddVar('USERNAME_STATUS', GetUserStatusStr());
$myTmp->AddVar('is_edit', $inVar_Edit);

$myTmp->AddVar('SID', GetvoteSID());
$myTmp->AddVar('CH', GetvoteChallenge());

$myTmp->AddVar('LIST_ID', $inVar_ListID);
$myTmp->AddVar('DOSCROLLTO', ''. $inVar_doscrollto);


$myTmp->Write('HEAD');


//- allgemeine Listen Informationen lesen
$myDB->Execute('SELECT vote_List_ID AS F01, vote_List_Name AS F02, vote_List_allowEdit AS F03 FROM vote_list WHERE vote_List_ID='. $inVar_ListID);


if (!$myDB->eof())
{
  $DB_List_ID = $myDB->Fields('F01')+0;
  $DB_List_Name = $myDB->Fields('F02');
  $DB_List_allowEdit = (($myDB->Fields('F03')+0)==1);


  $DB_max_sortPos = ($myDB->GetOneValueByQuery('SELECT max(vote_List_Item_Sortpos) FROM vote_list_item WHERE vote_List_Index='. $DB_List_ID)+0);
 

  $myTmp->AddVar('LIST_NAME',  $DB_List_Name);
}
else
{
  $ErrorList[] = 'eine Ungültige "list_id" wurde übergeben - Liste existiert nicht!';
  $DB_List_ID  =0;
  $DB_List_allowEdit =0;

  $myTmp->AddVar('LIST_NAME',  '[unbekannt]');

  $DB_max_sortPos=999;
}




if ((count($ErrorList)<1) && ($DB_List_ID >0 ))
{

  //-- Befehle ausführen --------------------------- //
  if ((GetvoteSID()!='') && ((GetvoteIsAdmin()) || ($DB_List_allowEdit)))
  {

    //-----------------------------
    if ($inVar_Command=='delete')
    {
      $ret = checkInUse($inVar_ListItemID, $DB_List_ID );

      if ($ret['state'])
      {
        $myDB->DoQuery('DELETE FROM vote_list_item WHERE ((vote_List_Item_ID='. $inVar_ListItemID .') AND (vote_List_Index='.  $DB_List_ID .'))');
      }
      else
      {
        $ErrorList[] = 'Der Befehl konnte nicht ausgeführt werden:';
        $ErrorList[] = $ret['msg'];
      }
    }

    //-----------------------------
    if ($inVar_Command=='add')
    {
      $inVar_newName = trim(GetAllVar('add_name', ''));
      $inVar_newValue = trim(GetAllVar('add_value', ''));

      if (($inVar_newName=='') && ($inVar_newValue=='')) $ErrorList[]='Sie müssen min. einen Bezeichnung oder ein Wert eingeben!';

      if ($inVar_newValue=='') 
      {
	$inVar_newValue='NULL';
	$inVar_newValue_select='is NULL';
      }
      else
      {
	$inVar_newValue = $inVar_newValue +0;
	$inVar_newValue_select='='. $inVar_newValue;
      }

      $inVar_newSortPos = ($myDB->GetOneValueByQuery('SELECT max(vote_List_Item_Sortpos) FROM vote_list_item WHERE vote_List_Index='. $DB_List_ID)+0);
      if ($inVar_newSortPos<999) $inVar_newSortPos++;

      //- Prüfen ob es den Wert schon gibt:
      
      $DB_In_DB_count = ($myDB->GetOneValueByQuery('SELECT count(*) FROM vote_list_item WHERE ((vote_List_Index='. $DB_List_ID .') AND (vote_List_Item_Name='. $myDB->GetSaveStr($inVar_newName) .') AND (vote_List_Item_Value '. $inVar_newValue_select .'))')+0);
      if ($DB_In_DB_count>0) $ErrorList[] = 'Es gibt bereits einen Eintrag der die gleiche Beschreibung und den gleichen Wert hat!';



      if (count($ErrorList)==0)
      {
        $Query  = 'INSERT INTO ';
        $Query .= '	vote_list_item(vote_List_Index, vote_List_Item_Name, vote_List_Item_Value, vote_List_Item_Sortpos) ';
        $Query .= 'VALUES ('. $DB_List_ID .', '. $myDB->GetSaveStr($inVar_newName) .', '. $inVar_newValue .', '. $inVar_newSortPos .') ';

        $myDB->DoQuery($Query);
      }
    }


    //-----------------------------
    if (($inVar_Command=='move_down') || ($inVar_Command=='move_up'))
    {

      $akPos = ($myDB->GetOneValueByQuery('SELECT vote_List_Item_Sortpos FROM vote_list_item WHERE vote_List_Item_ID='. $inVar_ListItemID)+0);

      if ($inVar_Command=='move_down')
      {
        $Query = 'SELECT vote_List_Item_ID AS F01, vote_List_Item_Sortpos AS F02 FROM  vote_list_item WHERE ((vote_List_Index='. $DB_List_ID .') AND (vote_List_Item_Sortpos >'. $akPos .')) ORDER BY vote_List_Item_Sortpos';
      }
      else
      {
        $Query = 'SELECT vote_List_Item_ID AS F01, vote_List_Item_Sortpos AS F02 FROM  vote_list_item WHERE ((vote_List_Index='. $DB_List_ID .') AND (vote_List_Item_Sortpos <'. $akPos .')) ORDER BY vote_List_Item_Sortpos DESC';
      }

      $myDB->Execute($Query);

      if (!$myDB->eof())
      {
	$newPos = ($myDB->Fields('F02')+0);
	$newPosID = ($myDB->Fields('F01')+0);

	$myDB->DoQuery('UPDATE vote_list_item SET vote_List_Item_Sortpos='. $newPos .' WHERE vote_List_Item_ID='. $inVar_ListItemID);
	$myDB->DoQuery('UPDATE vote_list_item SET vote_List_Item_Sortpos='. $akPos .' WHERE vote_List_Item_ID='. $newPosID);
      }
      else
      {
        $ErrorList[] = 'Umsorieren geht nicht!';
      }
    }


    //-----------------------------
    if (GetvoteIsAdmin())
    {

      //-----------------------------
      if ($inVar_Command=='edit')
      {
        $inVar_L_Name = trim(GetAllVar('edit_name', ''));
        $inVar_AllowEdit = trim(GetAllVar('edit_allow_edit', 0))+0;

        if ($inVar_AllowEdit!=1) $inVar_AllowEdit=0;
 
        if ($inVar_L_Name!='')
        {
          $myDB->DoQuery('UPDATE vote_list SET vote_List_Name='. $myDB->GetSaveStr($inVar_L_Name) .', vote_List_allowEdit='. $inVar_AllowEdit .' WHERE vote_List_ID='. $DB_List_ID);

	  $DB_List_allowEdit=($inVar_AllowEdit==1) ;


	  $myTmp->AddVar('LIST_NAME',  $inVar_L_Name);

        }
        else
        {
          $ErrorList[] = 'Die Liste muss einen Namen haben!';
        }
      }
     


      //-----------------------------
      if ($inVar_Command=='sort')
      {
        $inVar_AutoSort = trim(GetAllVar('edit_auto_sort', 0))+0;

        if ($inVar_AutoSort==0)
	{
          $myDB->DoQuery('SET @newsort=0;');
          $myDB->DoQuery('UPDATE vote_list_item SET vote_List_Item_Sortpos=@newsort := @newsort+1 WHERE vote_List_Index='. $DB_List_ID .' ORDER BY vote_List_Item_Sortpos, vote_List_Item_Name, vote_List_Item_Value');
	  $DB_max_sortPos=0;
	}
	else
	{
          $myDB->DoQuery('UPDATE vote_list_item SET vote_List_Item_Sortpos=999 WHERE vote_List_Index='. $DB_List_ID);
	  $DB_max_sortPos=999;
	}
      }

    }



  }
  else
  {
    $ErrorList[] = 'Ihnen fehlen als "'. GetUserStatusStr() .'" die Rechte für diese Aktion';
    if (!$DB_List_allowEdit) $ErrorList[] = 'Diese Liste kann nur vom Admin bearbeitet werden!';
  }
}


// ----------------------------------- //
if ($DB_max_sortPos==999)
{
  $myTmp->AddVarHTML('LIST_AUTO_SORT_CHECK', 'checked="checked"');
}
else
{
  $myTmp->AddVar('LIST_AUTO_SORT_CHECK', '');
}

if ($DB_List_allowEdit)
{
  $myTmp->AddVarHTML('LIST_ALLOW_EDIT_CHECK', 'checked="checked"');
}
else
{
  $myTmp->AddVarHTML('LIST_ALLOW_EDIT_CHECK', '');
}


// ************************************************************************************** //

//- HTML Kopf ausgeben
$myTmp->Write('HEAD_INFO');



// ************************************************************************************** //
if (count($ErrorList)>0)
{
  $myTmp->AddVarHTML('ERROR_LIST', '<ul><li>&nbsp;'. implode('</li><li>&nbsp;', $ErrorList) . '</li></ul>');
  $myTmp->Write('ERROR');
  $ErrorList=array();
}



if ($DB_List_ID >0 )
{
  //- Listeneinträge abfragen und ausgeben
  $myTmp->Write('LIST_HEAD');

  $Query  = 'SELECT ';
  $Query .= '	vote_List_Item_ID	AS F01, ';
  $Query .= '	vote_List_Item_Name	AS F02, ';
  $Query .= '	vote_List_Item_Value	AS F03, ';
  $Query .= '	vote_List_Item_Sortpos	AS F04  ';
  $Query .= 'FROM vote_list_item ';
  $Query .= 'WHERE vote_List_Index='. $DB_List_ID .' ';
  $Query .= 'ORDER BY vote_List_Item_Sortpos, vote_List_Item_Name, vote_List_Item_Value';

  $myDB->Execute($Query);

  while(!$myDB->eof())  
  {
    $myTmp->AddVar('ITEM_ID', $myDB->Fields('F01'));
    $myTmp->AddVar('ITEM_NAME', $myDB->Fields('F02'), '&nbsp;');
    $myTmp->AddVar('ITEM_VALUE', $myDB->Fields('F03'), '&nbsp;');
    $myTmp->AddVar('ITEM_SORTPOS', $myDB->Fields('F04'));

    $myTmp->Write('LIST_ITEM');

    $myDB->MoveNext();
  }


  $myTmp->Write('LIST_FEET');


  if (GetvoteIsAdmin())
  {
    $myTmp->Write('ADMIN');
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