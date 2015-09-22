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



//////////////////////////////////////////////////////////////////////////////////////////
// Main
/////////////////////////////////////////////////////////////////////////////////////////

//- Datenbank
$myDB = new clMySQL();


//- Ausgabe Template
$myTmp = new clTemplate('vote_status.txt');



//- hier komme alle Fehler rein
$ErrorList = array();

//- Übergebene Werte einlesen
$inVar_VID = (GetAllVar('vid', -1)+0);
$inVar_Edit = (GetAllVar('edit', 0)+0);
$inVar_Command = strtolower(trim(GetAllVar('cmd', '')));
$inVar_add_filter_group = GetAllVar('add_filter_group', '');




if ( $inVar_VID<1) 
{
  $ErrorList[] = 'Es muss zuerst eine Umfrage ausgewählt werden';
}


//- einfache Werte in das Template schreiben
$myTmp->AddVar('VOTE_ID',  $inVar_VID);
$myTmp->AddVar('USERNAME',  GetvoteUsername());
$myTmp->AddVar('USERNAME_STATUS', GetUserStatusStr());


$myTmp->AddVar('SID', GetvoteSID());
$myTmp->AddVar('CH', GetvoteChallenge());


//- Template Bereich 'HEAD' ausgeben
$myTmp->Write('HEAD');



// ************************************************************************************** //
// ** Feld-Infos aus DB laden
// ************************************************************************************** //

//- Gennerelle Informationen über die Umfrage
$myDB->Execute('SELECT vote_Name AS F01, vote_Comment AS F02 FROM vote WHERE vote_ID='. $inVar_VID);

$myTmp->AddVar('VOTE_NAME', $myDB->Fields('F01'));
$myTmp->AddVar('vote_Comment', $myDB->Fields('F02'));
$myTmp->Write('VOTE_HEAD');


if (count($ErrorList)<1)
{
  $need_List = array();
  $Group_fields = array();
  $field_types = array();
  $field_name = array();
  $vote_list_item = array();
  $Group_filter_list = array();

   
  $Group_filter_list[-1] ='[gruppierung deaktivieren]';


  //------ 1.) Feldinformationen lesen



  //- alle Felde-Informationen für diese Umfrage abfragen
  $Query  = "SELECT \n";
  $Query .= "	vote_Field_ID				AS F01, \n";
  $Query .= "	vote_Field_Name				AS F02, \n";
  $Query .= "	vote_Field_Comment			AS F03, \n";
  $Query .= "	vote_Field_Type				AS F04, \n";
  $Query .= "	vote_Field_Data_Fieldname		AS F05, \n";
  $Query .= "	vote_Field_List_Index			AS F06, \n";
  $Query .= "	vote_Field_Auswertung_GroupBy		AS F07, \n";
  $Query .= "	vote_Field_Auswertung_Group		AS F08  \n";
  $Query .= "FROM vote_field \n";
  $Query .= "WHERE vote_Index=". $inVar_VID ." \n";
  $Query .= "ORDER BY vote_Field_Sortpos \n";



  $myDB->Execute($Query);

  if (!$myDB->eof())
  {
    while(!$myDB->eof())
    {
      $list_id = ($myDB->fields('F06')+0);
      $type = ($myDB->fields('F04')+0);
      $doGroupBy = (($myDB->fields('F07')+0)==1);
      $GroupNr = (($myDB->fields('F08')+0)==1);
      $Data_Fieldname = Fieldname_to_DBName($myDB->fields('F05'));


      if (($list_id>0) && ($Data_Fieldname!=''))
      {
	$Group_filter_list[$Data_Fieldname] = $myDB->fields('F02');;
      }

      //- wir wollen nur Datenfelder, Felder mit gültigem Typ die Gruppierungsfelder sind und in der Tiefe 1 sitzen
      if (($Data_Fieldname!='') && ($type>0) && ($GroupNr==1) && ($doGroupBy) )
      {
	//- wenn es eine Liste ist dann merken wir uns die Listen_Nr
        if (($list_id >0) && ($Field_Type_is_List[$type])) $need_List[$list_id ] = $list_id ;

	$Group_fields[$Data_Fieldname] = $Data_Fieldname;
	$field_types[$Data_Fieldname] = $type;
	$field_name[$Data_Fieldname] = $myDB->fields('F02');

      }


      $myDB->MoveNext();
    }
  }
  else
  {
    if ( $inVar_VID>0)  $ErrorList[] = 'Zu dieser Umfrage sind keine Fragen definiert!';
  }




  //------ 2.) Listeninformationen lesen

  if (count($need_List)>0)
  {
    $Query  = "SELECT vote_List_Item_ID AS F01, vote_List_Item_Name AS F02, vote_List_Item_Value AS F03 FROM vote_list_item WHERE vote_List_Index in (". implode(', ', $need_List) .");";
  
    $myDB->Execute($Query);
    while(!$myDB->eof())
    {
      $vote_list_item[($myDB->fields('F01')+0)] = array('n'=> $myDB->fields('F02'),  'v'=> $myDB->fields('F03'));
      $myDB->MoveNext();
    }
  }



  $myTmp->AddVarHTML('LIST_ADD_GROUPS', GetHTMLList($Group_filter_list,$inVar_add_filter_group));


  //------ 3.) Report erstellen

  $Query = "SELECT count(*) AS F01, vote_Index AS F02 \n";


  foreach($Group_fields as $item)
  {
    $Query .= ", ". $item ." \n";
    
  }

  $Query .= "FROM vote_data \n";
  $Query .= "WHERE vote_Index=". ($inVar_VID+0) ." \n";

  $Query .= "GROUP BY vote_Index";

  if (count($Group_fields)>0)
  {
    $Query .= ", ". implode(',', $Group_fields) ." \n";
  }



  $myDB->Execute($Query);

  //- ""<tr><TH>...</TH></tr>"" ausgeben
  $myTmp->Write('VOTE_HEAD_HEAD');

  foreach($Group_fields as $item)
  {
    $myTmp->AddVar('GROUP_HEAD_NAME', $field_name[$item]);
    $myTmp->Write('VOTE_HEAD_GROUPS');
  }

  $myTmp->Write('VOTE_HEAD_FEET');




  $last_list_id = -1;

  while(!$myDB->eof())
  {
    $tmp_item_str='';
    $last_list_id='';


    $myTmp->AddVar('v_count', ($myDB->Fields('F01')+0));




    $G_Buffer = array();
    foreach($Group_fields as $item)
    {
      $type=$field_types[$item];

      if ($Field_Type_is_List[$type])
      {
	//- es ist eine Liste
	if (($myDB->Fields($item)+0 )>0)
	{
	  $G_Buffer[] = $vote_list_item[$myDB->Fields($item)]['n'] .' '. $vote_list_item[$myDB->Fields($item)]['v'];
	  $last_list_id .= $item .':'. $myDB->Fields($item). '|' ;
	}
	else
	{
	  $G_Buffer[] = '[k.A.]';
	}
      }
      else
      {
	//- es ist keine Liste
	$G_Buffer[] = $myDB->Fields($item);
      }
    }


    $myTmp->AddVar('last_list_id', $last_list_id);


    $myTmp->Write('VOTE_DATA_HEAD');
    foreach($G_Buffer as $G_Item)
    {
      $myTmp->AddVar('g_daten', $G_Item);
      $myTmp->Write('VOTE_DATA_GROUPS');
    }


    $myTmp->Write('VOTE_DATA_FEET');

    $myDB->MoveNext();

  }

}



// ************************************************************************************** //
if (count($ErrorList)>0)
{
  $myTmp->AddVarHTML('ERROR_LIST', '<ul><li>&nbsp;'. implode('</li><li>&nbsp;', $ErrorList) . '</li></ul>');
  $myTmp->Write('ERROR');
}


$myTmp->Write('VOTE_FEET');
$myTmp->Write('FEET');

?>