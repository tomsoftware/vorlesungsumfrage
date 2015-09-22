<?PHP

include_once('config.php');
include_once(INCLUDE_DIR .'clmysql.php');
include_once(INCLUDE_DIR .'cltemplate.php');
include_once(INCLUDE_DIR .'tools.php');
include_once(INCLUDE_DIR .'vote_session.php');


//////////////////////////////////////////////////////////////////////////////////////////
// Main
/////////////////////////////////////////////////////////////////////////////////////////

//- Datenbank
$myDB = new clMySQL();

//- Ausgabe Template
$myTmp = new clTemplate('vote_new.txt');


//- hier komme alle Fehler rein
$ErrorList = array();


$inVar_newName = trim(GetAllVar('name', ''));
$inVar_newComment = trim(GetAllVar('comment', ''));
$inVar_newDate = trim(GetAllVar('date', date('d.m.Y')));
$inVar_use_vid = (trim(GetAllVar('usevid', ''))+0);
$inVar_cmd = strtolower(trim(GetAllVar('cmd', '')));




$myTmp->AddVar('USERNAME',  GetvoteUsername());
$myTmp->AddVar('USERNAME_STATUS', GetUserStatusStr());


$myTmp->AddVar('SID', GetvoteSID());
$myTmp->AddVar('CH', GetvoteChallenge());

//- Da kommt mal die neue Vote_ID rein
$new_v_id=0;


$myTmp->Write('head');

$myTmp->Write('HEAD_INFO');

if (GetvoteIsAdmin())
{

  if (($inVar_cmd=='add') || ($inVar_cmd=='check'))
  {
    $db_newdate = $myDB->GetSaveDBDate($inVar_newDate);

    if (strlen($inVar_newName)<4) $ErrorList[]='Bitte geben Sie einen Namen für die neue Umfrage an!';
    if (strlen($db_newdate)<4) $ErrorList[]='Bitte geben Sie ein Datum im Format [tag.monat.jahr] ein!';


    if (count($ErrorList)==0)
    {
      $old_id = ($myDB->GetOneValueByQuery('SELECT vote_ID AS F01 FROM vote WHERE vote_Name='. $myDB->GetSaveStr($inVar_newName))+0);
      if ($old_id>0) $ErrorList[]='Der Name der Umfrage existiert schon!';
    }


    if (count($ErrorList)==0)
    {

      if ($inVar_cmd=='add') 
      {
 
        $myDB->DoQuery('INSERT INTO vote(vote_Name, vote_Comment, vote_Create_Date) VALUES ('. $myDB->GetSaveStr($inVar_newName) .', '. $myDB->GetSaveStr($inVar_newComment) .', '. $db_newdate .') ');

        $new_v_id = $myDB->GetInsertId();

        if ($new_v_id<1) $ErrorList[]='Beim anlegen der Umfrage ist ein fehler aufgetreten!';


        

	if (($inVar_use_vid>0) && ($new_v_id>0))
	{

	  $felder = array();

	  $Query  = "SELECT \n";
	  $Query .= "  vote_Field_ID			AS F01, \n";
	  $Query .= "  vote_Index			AS F02, \n";
	  $Query .= "  vote_Field_Name			AS F03, \n";
	  $Query .= "  vote_Field_Comment		AS F04, \n";
	  $Query .= "  vote_Field_Type			AS F05, \n";
	  $Query .= "  vote_Field_isNecessary		AS F06, \n";
	  $Query .= "  vote_Field_Auswertung_Group	AS F07, \n";
	  $Query .= "  vote_Field_Auswertung_GroupBy	AS F08, \n";
	  $Query .= "  vote_Field_KeepAfterSaving	AS F09, \n";
	  $Query .= "  vote_Field_Min			AS F10, \n";
	  $Query .= "  vote_Field_Max			AS F11, \n";
	  $Query .= "  vote_Field_Sortpos		AS F12, \n";
	  $Query .= "  vote_Field_Data_Fieldname	AS F13, \n";
	  $Query .= "  vote_Field_List_Index		AS F14, \n";
	  $Query .= "  vote_Field_Filter_Index		AS F15, \n";
	  $Query .= "  vote_Field_Filter_Depending_Field_Index	AS F16  \n";
	  $Query .= "FROM vote_field \n";
	  $Query .= "WHERE vote_Index=". $inVar_use_vid;



          $myDB->Execute($Query);
	  while(!$myDB->eof())
	  {
	    $f =array();
	    $f_id=$myDB->fields('F01')+0;


	    $ins =array();
	    $ins['vote_Index']= array('str', $new_v_id);

	    $ins['vote_Field_Name']= array('str', $myDB->fields('F03'));
	    $ins['vote_Field_Comment']= array('str', $myDB->fields('F04'));

	    $ins['vote_Field_Type']=		array('num', $myDB->fields('F05'));
	    $ins['vote_Field_isNecessary']=	array('num', $myDB->fields('F06'));
	    $ins['vote_Field_Auswertung_Group']=	array('num', $myDB->fields('F07'));
	    $ins['vote_Field_Auswertung_GroupBy']=array('num', $myDB->fields('F08'));
	    $ins['vote_Field_KeepAfterSaving']=	array('num', $myDB->fields('F09'));
	    $ins['vote_Field_Min']=		array('num', $myDB->fields('F10'));
	    $ins['vote_Field_Max']=		array('num', $myDB->fields('F11'));
	    $ins['vote_Field_Sortpos']=		array('num', $myDB->fields('F12'));
	    if (trim($myDB->fields('F13'))!='')
	    {
	      $ins['vote_Field_Data_Fieldname']=	array('str', $myDB->fields('F13'));
	    }
	    $ins['vote_Field_List_Index']=	array('num', $myDB->fields('F14'));
	    $ins['vote_Field_Filter_Index']=	array('num', $myDB->fields('F15'));

	  

	    $f['new_f_id'] = $myDB->ExecuteInsertArray('vote_field', $ins);
	    $f['f_id'] = $myDB->fields('F01')+0;
	    $f['depending_id'] = $myDB->fields('F16')+0;

	    $felder[$f_id]=$f;

	    $myDB->moveNext();
	  }


	  //- alle abhänigkeiten auf die neuen Felder verlinken!
	  foreach($felder as $f)
	  {
	    if ($f['depending_id']>0)
	    {
	      $myDB->DoQuery('UPDATE vote_field SET vote_Field_Filter_Depending_Field_Index='. ($felder[$f['depending_id']]['new_f_id']+0) .' WHERE vote_Field_ID='. ($f['new_f_id']+0) );
	    }  
	  }
	}
      }
    }
  }

  $myTmp->AddVar('new_vid', $new_v_id);
  $myTmp->AddVar('new_date', $inVar_newDate);
  $myTmp->AddVar('new_comment', $inVar_newComment);
  $myTmp->AddVar('new_name', $inVar_newName);
  $myTmp->AddVar('usevid', $inVar_use_vid);
  $myTmp->AddVar('VORLAGE_NAME', $myDB->GetOneValueByQuery('SELECT vote_Name AS F01 FROM vote WHERE vote_ID='. ($inVar_use_vid+0)) );

  $myTmp->AddVarHTML('VOTE_LIST', GetIDList($myDB, 'SELECT vote_Name AS F01, vote_ID AS F02 FROM vote ORDER BY vote_Create_Date DESC, vote_Name', 'F02', 'F01', $inVar_use_vid ));


  if ($new_v_id>0)
  {
    $myTmp->Write('NEW_DONE');
  }
  else
  {
    if (($inVar_cmd=='check') && (count($ErrorList)==0))
    {
      $myTmp->Write('NEW_CHECK');
    }
    else
    {
      $myTmp->Write('NEW_ENTER');
    }
  }

}
else
{
  $ErrorList[] = 'Ihnen fehlen die Rechte um eine neue Umfrage anzulegen!';
}



// ************************************************************************************** //
if (count($ErrorList)>0)
{
  $myTmp->AddVarHTML('ERROR_LIST', '<ul><li>&nbsp;'. implode('</li><li>&nbsp;', $ErrorList) . '</li></ul>');
  $myTmp->Write('ERROR');
  $ErrorList = array();
}



$myTmp->Write('feet');



?>