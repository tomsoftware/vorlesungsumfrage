<?PHP


// Dieses Programm erstellt ein eine Baumstruktur die Daten welche dann ausgegeben werden können
//
//
// $Daten:
//   <$GROUP_BY_ID_1$>
//     <COUNT />
//     <INFO>
//       <$GROUP_BY_VALUE$ (1) />
//       <$GROUP_BY_VALUE$ (2) />
//       <$GROUP_BY_VALUE$ (n) />
//     </INFO>
//     <DATA>
//       <$VALUE$ (1) />
//       <$VALUE$ (2) />
//       <$VALUE$ (n) />
//     </DATA>
//     <SUB>
//	 <$GROUP_BY_ID_2$>
//	   <COUNT />
//	   <INFO>
//	     <$GROUP_BY_VALUE$ (1) />
//	     <$GROUP_BY_VALUE$ (2) />
//	     <$GROUP_BY_VALUE$ (n) />
//	   </INFO>
//	   <DATA>
//	     <$VALUE$ (1) />
//	     <$VALUE$ (2) />
//	     <$VALUE$ (n) />
//	   </DATA>
//     </INFO>
//	 </$GROUP_BY_ID_2$>
//     </SUB>
//   </$GROUP_BY_ID_1$>



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
function addArrayVal(& $Array, $NewKey, $NewVal)
{
  if (isset($Array[$NewKey][$NewVal]))
  {
    $Array[$NewKey][$NewVal]++;
  }
  else
  {
    $Array[$NewKey][$NewVal]=1;
  }
}

// ------------------------------------------------------------------ //
function GetArrayAvg(& $Array)
{
  $s=0;
  $c=0;

  foreach($Array as $i => $v)
  {
    if ($i>0)
    {
      $c += $v;
      $s +=  ($i * $v);
    }
  }


  if ($c==0) return 0;
  return $s / $c;
}

// ------------------------------------------------------------------ //
function TmpCreateHisto(& $myTmp, & $Array)
{
  $myTmp->write('FIELD_HISTOGRAMM_HEAD');
  
  $max=0;

  foreach($Array as $v) if($v['v']>$max) $max = $v['v'];

  if ($max==0) $max=1;

  foreach($Array as  $v)
  {
    $myTmp->AddVar('HISO_VAL', $v['v'] .'');
    $myTmp->AddVar('HISO_MASS', $v['q'] .'');
    $myTmp->AddVar('HISO_VAL_SCAL_TO_50',  number_format($v['v'] * 50 / $max,0) .'');
    $myTmp->AddVar('HISO_VAL_SCAL_TO_100',  number_format($v['v'] * 100 / $max,0) .'');

    $myTmp->AddVar('HISO_NAME', $v['n'], ' ');
    $myTmp->write('FIELD_HISTOGRAMM_ITEM');
  }

  $myTmp->write('FIELD_HISTOGRAMM_FEET');
}

// ------------------------------------------------------------------ //
function TmpCreateHisto_H(& $myTmp, & $Array)
{
  $myTmp->write('FIELD_HISTOGRAMM_H_HEAD');
  
  $max=0;

  foreach($Array as $v) if($v['v']>$max) $max = $v['v'];

  if ($max==0) $max=1;

  foreach($Array as  $v)
  {
    $myTmp->AddVar('HISO_VAL', $v['v'] .'');
    $myTmp->AddVar('HISO_MASS', $v['q'] .'');
    $myTmp->AddVar('HISO_VAL_SCAL_TO_50',  number_format($v['v'] * 50 / $max,0) .'');
    $myTmp->AddVar('HISO_VAL_SCAL_TO_100',  number_format($v['v'] * 100 / $max,0) .'');

    $myTmp->AddVar('HISO_NAME', $v['n'], ' ');
    $myTmp->write('FIELD_HISTOGRAMM_H_ITEM');
  }

  $myTmp->write('FIELD_HISTOGRAMM_H_FEET');
}


// ------------------------------------------------------------------ //
function TmpCreateHistoDirect(& $myTmp, & $Array)
{
  $myTmp->write('FIELD_HISTOGRAMM_HEAD');
  
  $max=0;

  foreach($Array as $v) if($v>$max) $max = $v;

  if ($max==0) $max=1;

  foreach($Array as $i => $v)
  {
    $myTmp->AddVar('HISO_VAL', $v .'');
    $myTmp->AddVar('HISO_VAL_SCAL_TO_50',  number_format($v * 50 / $max,0) .'');


    $myTmp->AddVar('HISO_NAME', $i, ' ');
    $myTmp->write('FIELD_HISTOGRAMM_ITEM');
  }


  $myTmp->write('FIELD_HISTOGRAMM_FEET');
}



// ------------------------------------------------------------------ //
function OutGroupInfos(& $root, $deep)
{
  global $fields, $vote_list_item, $vote_list, $myTmp;


  $myTmp->Write('GROUP_HEAD_HEAD');

  foreach($root['info'] as $Data_Fieldname => $item )
  {
    $myTmp->AddVar('GROUP_HEAD_NAME', $fields[$Data_Fieldname]['name'] );

    if ($item != '')
    {
      $myTmp->AddVar('GROUP_HEAD_VALUE', $vote_list_item[$item]['n']);
    }
    else
    {
      $myTmp->AddVar('GROUP_HEAD_VALUE','[Keine Angabe]');
    }

    $myTmp->Write('GROUP_HEAD_ITEM');
  }

  $myTmp->Write('GROUP_HEAD_FEET');


  $myTmp->Write('GROUP_FIELDS_HEAD');

  foreach($fields as $Data_Fieldname => $item)
  {
    if ((!$item['groupby']) && ($item['group']==$deep))
    {

      if (isnumber($Data_Fieldname))
      {

      }
      else
      {
	if (isset($root['data'][$Data_Fieldname]))
	{
          $item = $root['data'][$Data_Fieldname];

          $myTmp->AddVar('FIELD_NAME', $fields[$Data_Fieldname]['name']);
          $myTmp->AddVar('FIELD_COMMENT', $fields[$Data_Fieldname]['comment']);

	  if ($fields[$Data_Fieldname]['lid']>0)
	  {
            $myTmp->AddVar('FIELD_EINHEIT', $vote_list[ $fields[$Data_Fieldname]['lid'] ]['einheit'] );
            $myTmp->AddVar('FIELD_EINHEIT2', $vote_list[ $fields[$Data_Fieldname]['lid'] ]['einheit2'] );
	  }
	  else
	  {
	    $myTmp->AddVar('FIELD_EINHEIT','');
            $myTmp->AddVar('FIELD_EINHEIT2','');
	  }


	  //- maximal und minimalen Wert von skala
	  $hist_max = -1;
	  $hist_max_name = '';
	  $hist_min = 1;
	  $hist_min_name = '';

 	  if ($fields[$Data_Fieldname]['lid']>0)
	  {

	    foreach($vote_list[ $fields[$Data_Fieldname]['lid'] ] as $v) 
	    {
	      if (is_array($v))
	      {
		$i = ($v['v']+0);

		if($i>$hist_max) 
		{
		  $hist_max = $i;
		  $hist_max_name = $v['n'];
	        }

	        if($i<$hist_min)
	        {
		  $hist_min = $i;
		  $hist_min_name = $v['n'];
		}
	      }
	    }
	  }


          $myTmp->AddVar('FIELD_MAX', number_format($hist_max ,0).'' );
          $myTmp->AddVar('FIELD_MIN', number_format($hist_min ,0).'' );

          $myTmp->AddVar('FIELD_MAX_NAME', $hist_max_name );
          $myTmp->AddVar('FIELD_MIN_NAME', $hist_min_name );


          $type = $fields[$Data_Fieldname]['type'];

          switch($type)
          {

	    /////////////////////////////////////
	    case FIELD_TYPE_NUMBER:

	      ksort($item);
	      $avg = GetArrayAvg($item); 

	      $myTmp->AddVar('FIELD_AVG', number_format($avg ,1,',','') );
	      $myTmp->AddVar('FIELD_AVG_VAL', number_format($avg ,1) );
	      $myTmp->AddVar('FIELD_AVG_SCAL_TO_50', '' );
	      $myTmp->Write('FIELD_TYPE_NUMBER_HEAD');

	      TmpCreateHistoDirect($myTmp, $item);

	      $myTmp->Write('FIELD_TYPE_NUMBER_FEET');
	      break;



	    /////////////////////////////////////
	    case FIELD_TYPE_STRING:

	      $myTmp->Write('FIELD_TYPE_STRING_HEAD');
	  
	      foreach($item as $val_id => $val )
	      {
	        $myTmp->AddVar('FIELD_VALUE',  $val_id);
	        $myTmp->AddVar('FIELD_VALUE_COUNT',  $val);
	        $myTmp->Write('FIELD_TYPE_STRING_ITEM');
	      }
  
	      $myTmp->Write('FIELD_TYPE_STRING_FEET');
	      break;


	    /////////////////////////////////////
	    case FIELD_TYPE_RADIOLIST:
	    case FIELD_TYPE_RADIOLIST_NOTEXT:



 
	      $list = $vote_list[ $fields[$Data_Fieldname]['lid'] ];
	      $l_out = array();
	      $l_sum =0;
	      $l_count =0;
	      $l_max = 1;

	      $one_is_ok = false;

	      foreach($list as $val_id => $val )
	      {
		if ($val['v']!=='')
		{
		  if (isnumber($val['v'])) if ($val['v'] > $l_max) $l_max=$val['v'];
		}

	        if (isnumber($val_id))
		{
	          if (isset($item[$val_id] ) )
	          {
		    if ($val['v']!=='')
		    {
		      if (isnumber($val['v'])) $one_is_ok=true;

	              $l_out[] = array('n'=> $val['n'], 'v'=> $item[$val_id], 'q'=> $val['v']);
		      
		      if (isnumber($val['v']))
		      {
		        $l_sum += $item[$val_id] * $val['v'];
		        $l_count += $item[$val_id];
		      }
		    }
	          }
	          else
	          {
	            if ($type==FIELD_TYPE_RADIOLIST_NOTEXT) $l_out[] = array('n'=> $val['n'], 'v'=> 0, 'q'=> $val['v']);
		  }
	        }
	      }

	      if (($l_count>0) && ($one_is_ok))
	      {
	        $myTmp->AddVar('FIELD_AVG',  number_format($l_sum / $l_count, 1, ',', '') , 'keine Angabe');
	        $myTmp->AddVar('FIELD_AVG_VAL',  number_format($l_sum / $l_count, 1),'');
		$myTmp->AddVar('FIELD_AVG_SCAL_TO_50',  number_format($l_sum / $l_count /  $l_max * 50, 0), '');
	      }
	      else
	      {
	        $myTmp->AddVar('FIELD_AVG', 'keine Angabe');
	        $myTmp->AddVar('FIELD_AVG_VAL', '');
		$myTmp->AddVar('FIELD_AVG_SCAL_TO_50',  '');
	      }

	      $myTmp->Write('FIELD_TYPE_OPTION_HEAD');


	      if ($type==FIELD_TYPE_RADIOLIST_NOTEXT)
	      {
	        TmpCreateHisto($myTmp, $l_out);
	      }
	      else
	      {
	        TmpCreateHisto_H($myTmp, $l_out);
	      }


	      $myTmp->Write('FIELD_TYPE_OPTION_FEET');
	      break;

	    case FIELD_TYPE_FILTER_COMBOLIST:
	    case FIELD_TYPE_COMBOLIST:
	      break;
	  }

	}
      }
    }
  }


  $myTmp->Write('GROUP_FIELDS_FEET');

  if ($deep==1)
  {
    if (array_key_exists('sub', $root))
    {
      foreach($root['sub'] as $item)
      {
        $myTmp->Write('GROUP_SUB_HEAD');
        OutGroupInfos($item, $item['group']);
        $myTmp->Write('GROUP_SUB_FEET');
      }
    }

  }






}

// ------------------------------------------------------------------ //









/////////////////////////////////////////////////////////////////////////////////////////
// Main
/////////////////////////////////////////////////////////////////////////////////////////





//- Datenbank
$myDB = new clMySQL();

//- hier komme alle Fehler rein
$ErrorList = array();


//- Übergebene Werte einlesen
$inVar_VID = (GetAllVar('vid', -1)+0);
$inVar_GroupID = (GetAllVar('gid', -1));
$inVar_tmp = basename(trim(strtolower(GetAllVar('tmp', ''))));
$inVar_add_filter_group = trim(GetAllVar('add_filter_group', 0));


//- gruppierung expliziet ausschalten?
$doGroup = true;
if ($inVar_add_filter_group<0)
{
  $doGroup=false;
}


if ($inVar_tmp=='') $inVar_tmp='vote_report.txt';


//- Ausgabe Template
$myTmp = new clTemplate($inVar_tmp);


//- einfache Werte in das Template schreiben
$myTmp->AddVar('VOTE_ID',  $inVar_VID);
$myTmp->AddVar('USERNAME',  GetvoteUsername());
$myTmp->AddVar('USERNAME_STATUS', GetUserStatusStr());


$myTmp->AddVar('SID', GetvoteSID());
$myTmp->AddVar('CH', GetvoteChallenge());


//- Template Bereich 'HEAD' ausgeben
$myTmp->Write('HEAD');


//- Variablen aufbereiten
if (!is_array($inVar_GroupID)) $inVar_GroupID=array($inVar_GroupID);

//- Den übergebenen Parameter in ein Array umwandeln
foreach($inVar_GroupID as $g_nr=>$g_val)
{
  $g_arr = explode('|', $g_val);

  $inVar_GroupID[$g_nr]=array();

  foreach($g_arr as $g_item)
  {
    list($fn, $fv) = explode(":", $g_item .':0');
    $fn = strtolower(trim($fn));
    $fv = ($fv+0);
    if (($fn!=='') && ($fv>0))
    {
      $inVar_GroupID[$g_nr][$fn]=$fv;
    }
  }
}



// ************************************************************************************** //
// ** Feld-Infos aus DB laden
// ************************************************************************************** //

//- Gennerelle Informationen über die Umfrage
$myDB->Execute('SELECT vote_Name AS F01, vote_Comment AS F02 FROM vote WHERE vote_ID='. $inVar_VID);

$myTmp->AddVar('VOTE_NAME', $myDB->Fields('F01'));
$myTmp->AddVar('vote_Comment', $myDB->Fields('F02'));
$myTmp->Write('VOTE_HEAD');



$fields = array();
$need_List = array();
$data_fields = array();
$groups = array();
$need_groups = array();

//------ 1.) Feldinformationen lesen
if (count($ErrorList)<1)
{

  //- alle Felde-Informationen für diese Umfrage abfragen
  $Query  = "SELECT \n";
  $Query .= "	vote_Field_ID				AS F01, \n";
  $Query .= "	vote_Field_Name				AS F02, \n";
  $Query .= "	vote_Field_Comment			AS F03, \n";
  $Query .= "	vote_Field_Type				AS F04, \n";
  $Query .= "	vote_Field_Data_Fieldname		AS F05, \n";
  $Query .= "	vote_Field_List_Index			AS F06, \n";
  $Query .= "	vote_Field_Auswertung_Group		AS F07, \n";
  $Query .= "	vote_Field_Auswertung_GroupBy		AS F08 \n";
  $Query .= "FROM vote_field \n";
  $Query .= "WHERE vote_Index=". $inVar_VID ." \n";
  $Query .= "ORDER BY vote_Field_Sortpos \n";



  $myDB->Execute($Query);

  if (!$myDB->eof())
  {
    while(!$myDB->eof())
    {
      $item = array();
      $Data_Fieldname = Fieldname_to_DBName($myDB->fields('F05'));
      $type = ($myDB->fields('F04')+0);
      $list_id = ($myDB->fields('F06')+0);
      $group_id = ($myDB->fields('F07')+0);
      $group_by = (($myDB->fields('F08')+0)==1);


      //- zusätzliche Gruppierung?
      if (($inVar_add_filter_group!='') && (strcasecmp($Data_Fieldname,$inVar_add_filter_group)==0))
      {
	$group_by=true;
	$group_id=1;
      }

      //- Gruppierung ausschalten
      if (!$doGroup)
      {
	$group_id=1;
	$group_by=false;
      }


      $item['id'] = ($myDB->fields('F01')+0);
      $item['name'] = $myDB->fields('F02');
      $item['comment'] = $myDB->fields('F03');

      $item['type'] = $type;
      $item['lid'] = $list_id;

      $item['group'] = $group_id;
      $item['groupby'] = $group_by;



      if (array_key_exists($type, $Field_Type_Name ))
      {
        $need_groups[$group_id] = $group_id;

        if ($Data_Fieldname!='')
        {
	  $fields[$Data_Fieldname]=$item;
	  $data_fields[] = $Data_Fieldname;


	  //- wenn es eine Liste ist dann merken wir uns die Listen_Nr
	  if (($list_id >0) && ($Field_Type_is_List[$type])) $need_List[$list_id ] = $list_id ;

	  if ($group_by)
	  {
	    $groups[$group_id][] = $Data_Fieldname;
	  }


        }
        else
        {
	  $fields[]=$item;
        }
      }

      $myDB->MoveNext();
    }


    //- Gruppen ohne Gruppierungsfeld sauber einfügen
    foreach($need_groups as $group_id)
    {
      if (!array_key_exists($group_id, $groups)) $groups[$group_id] = array();
    }


  }
  else
  {
    if ( $inVar_VID>0)  $ErrorList[] = 'Zu dieser Umfrage sind keine Fragen definiert!';
  }
}

if (count($data_fields)<1) $ErrorList[] = 'Zu dieser Umfrage sind keine Daten-Fragen definiert!';



//------ 2.) Listen-Einträge lesen/cachen
$vote_list_item = array();
$vote_list = array();

if (count($ErrorList)<1)
{

  if (count($need_List)>0)
  {
    //- Listeneinträge lesen

    $Query  = "SELECT vote_List_Item_ID AS F01,  vote_List_Index AS F02, vote_List_Item_Name AS F03, vote_List_Item_Value AS F04, vote_List_Item_Sortpos AS F05 FROM vote_list_item WHERE vote_List_Index in (". implode(', ', $need_List) .") ORDER BY vote_List_Index,vote_List_Item_Sortpos;";
  
    $myDB->Execute($Query);
    while(!$myDB->eof())
    {
      $vote_list_item[($myDB->fields('F01')+0)] = array('n'=> $myDB->fields('F03'),  'v'=> $myDB->fields('F04') );
      $vote_list[($myDB->fields('F02')+0)][($myDB->fields('F01')+0)] = array('n'=> $myDB->fields('F03'),  'v'=> $myDB->fields('F04') );

      $myDB->MoveNext();
    }



    //- Listen Köpfe lesen
    $Query  = "SELECT vote_List_ID AS F01, vote_List_Einheit AS F02, vote_List_Einheit2 AS F03 FROM vote_list WHERE vote_List_ID in (". implode(', ', $need_List) .");";
  
    $myDB->Execute($Query);
    while(!$myDB->eof())
    {
      $vote_list[($myDB->fields('F01')+0)]['einheit'] = $myDB->fields('F02');
      $vote_list[($myDB->fields('F01')+0)]['einheit2'] = $myDB->fields('F03');

      $myDB->MoveNext();
    }


  }

}




//------ 3.) Alle Daten einlesen

$daten = array();

if (count($ErrorList)<1)
{
  $Query  = "SELECT \n";
  $Query .= "	vote_data_ID AS FID, \n";
  $Query .= "	". implode(", \n	", $data_fields) ." \n";
  $Query .= "FROM vote_data \n" ;
  $Query .= "WHERE \n";
  $Query .= "( \n";
  $Query .= "  (vote_Index=". $inVar_VID .") ";


  $is_group_select=false;

  foreach($inVar_GroupID as $g_val)
  {
    $Query_arr = array();

    foreach($groups[1] as $g_id=>$g_item)
    {
      if(isset($g_val[strtolower($g_item)]))
      {
	$Query_arr[] = $g_item .'='. ($g_val[strtolower($g_item)]+0);
      }
    }
    if (count($Query_arr)>0)
    {
      if (!$is_group_select)
      {
        $Query .= "AND\n  (\n    (". implode(' AND ', $Query_arr) .") ";
      }
      else
      {
        $Query .= "OR\n    (". implode(' AND ', $Query_arr) .") ";
      }

      $is_group_select=true;
    }
  }


  if ($is_group_select)
  {
    $Query .= "\n  )";
  }
  $Query .= "\n) \n";



  $myDB->Execute($Query);


  while(!$myDB->eof())
  {
    //- Gruppen-Werte lesen
    $ak_group_val = array();
    $ak_group_info = array();

    foreach($groups as $group_id => $item)
    {
      if (count($item)==0)
      {
	$ak_group_val[$group_id] = '';
        $ak_group_info[$group_id]=array();
      }
      else
      {

	foreach($item as $Data_Fieldname)
	{
	  $v = $myDB->Fields($Data_Fieldname);

	  $ak_group_info[$group_id][$Data_Fieldname] = $v;


	  if (isset($ak_group_val[$group_id]))
	  {
	    $ak_group_val[$group_id] .= '-' . $v;
	  }
	  else
	  {
	    $ak_group_val[$group_id] = $v;
	  }
	}
      }
    }


    foreach($fields as $Data_Fieldname => $item)
    {
	$group_id = $item['group'];


	if ((!isnumber($Data_Fieldname)) && (!$item['groupby']))
	{
          $v = $myDB->Fields($Data_Fieldname);

	  //- keine NULL oder leere Zeichenfolgen berücksichtigen
	  if (($v!='') || ($v===0))
	  {
	    if ($group_id==0)
	    {
	      //- das ist ein Globales Element
	      $daten['group'] = $group_id;
	      $daten['info'] = $ak_group_info[0];
	      addArrayVal($daten['data'], $Data_Fieldname, $v);
	    }
	    else if ($group_id==1)
	    {
	      //- das ist ein Root Element
	      $daten['sub'][ $ak_group_val[1] ]['info'] = $ak_group_info[1];
	      $daten['sub'][ $ak_group_val[1] ]['group'] =1;

	      addArrayVal($daten['sub'][ $ak_group_val[1] ]['data'], $Data_Fieldname, $v);
	    }
	    else
	    {
	      //- Das ist ein unterobjekt
	      $daten['sub'][ $ak_group_val[1] ]['sub'][$ak_group_val[ $group_id] ]['info'] = $ak_group_info[$group_id];
	      $daten['sub'][ $ak_group_val[1] ]['sub'][$ak_group_val[ $group_id] ]['group'] = $group_id;
	      addArrayVal($daten['sub'][ $ak_group_val[1] ]['sub'][ $ak_group_val[$group_id] ]['data'], $Data_Fieldname, $v);
	    }
	  }

	}

    }




    $myDB->MoveNext();
  }

}

//------ 4.) Ausgabe 
if (array_key_exists('sub', $daten))
{
  foreach($daten['sub'] as $root)
  {
    $myTmp->Write('GROUP_HEAD');
  
    OutGroupInfos($root, 1);

    $myTmp->Write('GROUP_FEET');
  }
}





$myTmp->Write('VOTE_FEET');


// echo '<pre>';
// print_r($daten);
// echo '</pre>';

$myTmp->Write('FEET');
?>