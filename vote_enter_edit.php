<?PHP

if (!defined('vote_enter_edit')) die('This is no standalon-script... it"s just one part!');


  //- achtung: Dieses Programm erfordert eine spezielle "Umgeben" die durch das Programm "vote_enter.php" erzeugt wird

  $inVar_new_name = trim(GetAllVar('new_name', ''));
  $inVar_new_comment = trim(GetAllVar('new_comment', ''));
  $inVar_new_type = trim(GetAllVar('new_type', -1))+0;
  $inVar_edit_field_id = trim(GetAllVar('field_id', -1))+0;
  $inVar_new_dep_field_id = trim(GetAllVar('new_dep_id', ''));
  $inVar_new_list_id=-1;
  $inVar_new_filter_id = -1 ;

  //- Die Listen-ID und die Filter ID sehen beide in ""inVar_new_list_text""
  //-   sie sind aber mit dem prefix "l_" oder "f_" gekennzeichnet
  //-   => trennen!
  if (substr($inVar_new_list_text,0,2)=='l_')
  {
    $inVar_new_list_id = (substr($inVar_new_list_text,2,99)+0);
  }
  else if (substr($inVar_new_list_text,0,2)=='f_')
  {
    $inVar_new_filter_id = (substr($inVar_new_list_text,2,99)+0);

    $Query  = 'SELECT ';
    $Query .= '	vote_Filter_Name			AS F01, ';
    $Query .= '	vote_Filter_Destination_List_Index	AS F02, ';
    $Query .= '	vote_Filter_Depending_List_Index	AS F03, ';
    $Query .= '	vote_Filter_allowEdit			AS F04 ';
    $Query .= 'FROM vote_filter ';
    $Query .= 'WHERE vote_Filter_ID='.  $inVar_new_filter_id;

    $myDB->Execute($Query);

    if (!$myDB->eof())
    {
      $inVar_new_list_id = $myDB->Fields('F02')+0;
      $DB_Filter_Name = $myDB->Fields('F01');
      $DB_Dep_List_ID = $myDB->Fields('F03')+0;
      $DB_Filter_edit = $myDB->Fields('F04')+0;

      if ($DB_Dep_List_ID>0)
      {
        //- überprüfen ob das gewähjlte Dep-Feld auch passen kann:
        $DB_Dep_Field_List_ID = $myDB->GetOneValueByQuery('SELECT vote_Field_List_Index AS F01 FROM vote_field WHERE ((vote_Index='. $inVar_VID .') AND (vote_field_ID='. $inVar_new_dep_field_id .'))');

        if ($DB_Dep_Field_List_ID!=$DB_Dep_List_ID)
        {
	  $ErrorList[]='Falsche Abhänigkeit! Sie müssen als Abhänigkeit ein Feld wählen das zu dem Filter "'. $DB_Filter_Name .'" passt!';
        }
      }
      else
      {
	$inVar_new_dep_field_id=0;
      }
    }
    else
    {
      $ErrorList[]='Interner Fehler: Der Filter wurde nicht gefunden!';
    }
  }


  //- werte prüfen
  if (!array_key_exists($inVar_new_type, $Field_Type_is_List)) $inVar_new_type=-1;
  $inVar_new_list_id = ($myDB->GetOneValueByQuery('SELECT vote_List_ID FROM vote_list WHERE vote_List_ID='. $inVar_new_list_id) +0);
  $tmp_count = ($myDB->GetOneValueByQuery('SELECT count(*) FROM vote_data WHERE vote_Index='. $inVar_VID)+0);


  //- --------------------------------------------- //
  if ($inVar_Command=='move_up')
  {
    $old_Sort_Pos = ($myDB->GetOneValueByQuery('SELECT vote_Field_Sortpos AS F01 FROM vote_field WHERE ((vote_Index='. $inVar_VID .') AND (vote_Field_ID='. $inVar_edit_field_id .'));'))+0;
    $new_Sort_Pos = -1;
    $new_Sort_Pos_Field_ID = -1;

    $myDB->Execute('SELECT vote_Field_ID as F01, vote_Field_Sortpos AS F02 FROM vote_field WHERE ((vote_Index='. $inVar_VID .') AND (vote_Field_Sortpos<'. $old_Sort_Pos .')) ORDER BY vote_Field_Sortpos DESC;');
  
    if (!$myDB->eof())
    {
          $new_Sort_Pos_Field_ID = ($myDB->Fields('F01')+0);
          $new_Sort_Pos = ($myDB->Fields('F02')+0);
      }
  }

  //- --------------------------------------------- //
  if ($inVar_Command=='move_down')
  {
    $old_Sort_Pos = ($myDB->GetOneValueByQuery('SELECT vote_Field_Sortpos AS F01 FROM vote_field WHERE ((vote_Index='. $inVar_VID .') AND (vote_Field_ID='. $inVar_edit_field_id .'));'))+0;
    $new_Sort_Pos = -1;
    $new_Sort_Pos_Field_ID = -1;

    $myDB->Execute('SELECT vote_Field_ID as F01, vote_Field_Sortpos AS F02 FROM vote_field WHERE ((vote_Index='. $inVar_VID .') AND (vote_Field_Sortpos>'. $old_Sort_Pos .')) ORDER BY vote_Field_Sortpos ASC;');


    if (!$myDB->eof())
    {
          $new_Sort_Pos_Field_ID = ($myDB->Fields('F01')+0);
          $new_Sort_Pos = ($myDB->Fields('F02')+0);
    }

  }


  //- --------------------------------------------- //
  if (($inVar_Command=='move_up') || ($inVar_Command=='move_down'))
  {
    //- wenn keine Fehler aufgetreten sind dann können wir speichern
    if (count($ErrorList)<1)
    {
      if ($new_Sort_Pos_Field_ID>0)
      {
        //- sort-positionen tauschen

        if ($new_Sort_Pos!=$old_Sort_Pos)
        {
          $myDB->DoQuery('UPDATE vote_field SET vote_Field_Sortpos='. $old_Sort_Pos .' WHERE ((vote_Field_ID='.  $new_Sort_Pos_Field_ID .') AND (vote_Index='. $inVar_VID .'))');    
	  $myDB->DoQuery('UPDATE vote_field SET vote_Field_Sortpos='. $new_Sort_Pos .' WHERE ((vote_Field_ID='.  $inVar_edit_field_id .') AND (vote_Index='. $inVar_VID .'))');
        }        
      }
    }
  }



  //- --------------------------------------------- //
  if ($inVar_Command=='delete')
  {
     if ($tmp_count>0) $ErrorList[] = 'Es können nur Umfragen bearbeitet werden die noch keine Wert enthalten! - Sorry!';

    //- wenn keine Fehler aufgetreten sind dann können wir speichern
    if (count($ErrorList)<1)
    {
      $myDB->DoQuery('DELETE FROM vote_field WHERE ((vote_Field_ID='.  $inVar_edit_field_id .') AND (vote_Index='. $inVar_VID .'))');
    }
  }


  //- --------------------------------------------- //
  if ($inVar_Command=='add_field')
  {
    if ($tmp_count>0) $ErrorList[] = 'Es können nur Umfragen bearbeitet werden die noch keine Wert enthalten! - Sorry!';

    if ($inVar_new_type>0) 
    {
          if (($Field_Type_is_List[$inVar_new_type]) && ($inVar_new_list_id<1)) $ErrorList[] = 'Sie müssen eine Liste wählen!';
          if ((!$Field_Type_is_List[$inVar_new_type]) && ($inVar_new_list_id>0)) $ErrorList[] = 'Bei diesem Typ kann/darf keine Liste gewählt werden!';
	  if (($Field_Type_is_Filter[$inVar_new_type]) && ($inVar_new_filter_id<1)) $ErrorList[] = 'Bei diesem Typ müssen Sie auch einen Filter wählen!';
	  if ((!$Field_Type_is_Filter[$inVar_new_type]) && ($inVar_new_filter_id>0)) $ErrorList[] = 'Bei diesem Typ darf kein Filter gewählt werden!';
    }
    else
    {
          $ErrorList[] = 'Sie müssen einen Typen aus der Liste wählen';
    }    



    //- wenn keine Fehler aufgetreten sind dann können wir speichern
    if (count($ErrorList)<1)
    {
      //- nächste Sortierposition un Datenfeld-Namen auslesen
      $ak_max_N=1;
      $ak_max_S=1;
      $min_N=9999;
      $min_S=9999;
      $DBSortPos=1;

  
      $myDB->Execute('SELECT vote_Field_Data_Fieldname AS F01, vote_Field_Sortpos as F02 FROM vote_field WHERE ((vote_Index='. $inVar_VID .')) ORDER BY vote_Field_Data_Fieldname');
      while(!$myDB->eof())
      {
        //- Sortpos
        $tmp = strtolower(trim($myDB->Fields('F02')))+0;
        if (($tmp>0) && ($tmp>=$DBSortPos)) $DBSortPos = ($tmp +1);

        //- Daten-Feld
        $tmp = strtolower(trim($myDB->Fields('F01')));
        if ($tmp!='')
        {
          $tmp_type = substr($tmp,0,2);
          $tmp_val = (substr($tmp,2,2)+0);



          if ($tmp_val<1) $ErrorList[] = 'Fehler in Datenbank! Datenfeld [vote_Field_Data_Fieldname] hat ungültigen Wert ('. $tmp .')!';

          if ($tmp_type == 'n_')
          {
            if (($ak_max_N<$tmp_val) && ($min_N>$ak_max_N)) $min_N=$ak_max_N;
            $ak_max_N=$tmp_val+1;
          }
          else if ($tmp_type == 's_')
          {
            if (($ak_max_S<$tmp_val) && ($min_S>$ak_max_S)) $min_S=$ak_max_S;
            $ak_max_S=$tmp_val+1;
          }
          else
          {
            $ErrorList[] = 'Fehler in Datenbank! Datenfeld [vote_Field_Data_Fieldname] hat ungültigen Wert ('. $tmp .')!';
          }
        }

        $myDB->MoveNext();
      }


      if ($min_N>=9999) $min_N=$ak_max_N;
      if ($min_S>=9999) $min_S=$ak_max_S;

      switch ($Field_Type_DataType[$inVar_new_type])
      {
        case 0:
          $DB_Data_Fieldname = 'NULL';
          break;
        case 1:
          if ($min_N>COUNT_OF_NUMBER_FIELDS)  $ErrorList[] = 'Es können keine weiteren Numerischen Felder in die Umfrage aufgenommen werden! (Datenbank muss angepasst werden)';
          $DB_Data_Fieldname = $myDB->GetSaveStr('N_'. substr('00'. $min_N, -2, 2));
          break;
        case 2:
          if ($min_N>COUNT_OF_NUMBER_FIELDS) $ErrorList[] = 'Es können keine weiteren Text-Felder in die Umfrage aufgenommen werden! (Datenbank muss angepasst werden)';        
          $DB_Data_Fieldname = $myDB->GetSaveStr('S_'. substr('00'. $min_S, -2, 2));
          break;
        default:
          $ErrorList[] = 'Falscher Feld-DB-Type!';
          break;
      }


      //- wenn keine Fehler aufgetreten sind dann können wir speichern
      if (count($ErrorList)<1)
      {
        $Query  = "INSERT INTO vote_field \n";
        $Query .= "        (vote_Index, vote_Field_Name, vote_Field_Comment, vote_Field_Type, vote_Field_List_Index, vote_Field_Sortpos, vote_Field_Data_Fieldname, vote_Field_Filter_Index, vote_Field_Filter_Depending_Field_Index) \n";
        $Query .= "VALUES( \n";
        $Query .=         $inVar_VID .", \n";
        $Query .=         $myDB->GetSaveStr($inVar_new_name) .", \n";
        $Query .=         $myDB->GetSaveStr($inVar_new_comment) .", \n";
        $Query .=         $inVar_new_type .", \n";
        $Query .=         (($inVar_new_list_id<1)?'NULL':$inVar_new_list_id) .", \n";
        $Query .=         $DBSortPos .", \n";
        $Query .=         $DB_Data_Fieldname .", \n";
        $Query .=         (($inVar_new_filter_id<1)?'NULL':$inVar_new_filter_id) .", \n";
        $Query .=         (($inVar_new_dep_field_id<1)?'NULL':$inVar_new_dep_field_id);
        $Query .= ");";

        $myDB->DoQuery($Query);
      }
    }
  }



?>