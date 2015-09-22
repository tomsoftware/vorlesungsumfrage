

  //////////////////////////////////////////////////////////
  function OpenWindow(dest_adress, new_width, new_height)
  {
    //- einen eindeutigen Fensternamen generieren
    var target_name = "editlist"+ Math.floor(Math.random() * 10000 );

    if (!new_width) new_width = 600;
    if (!new_height) new_height = 600;


    var new_left = (screen.width - new_width) / 2;
    var new_top = (screen.height - new_height) / 2;

    //- leeres-Fensert öffnen
    fenster=window.open("about:blank", target_name , "width="+ new_width +",height="+ new_height +",status=yes,scrollbars=yes,resizable=yes,left="+ new_left +",top="+ new_top);


    //- in das geöffnete Fenster das Formular "form_edit_list" schicken
    document.form_edit_list.target = target_name;
    document.form_edit_list.action = dest_adress;
    document.form_edit_list.submit();

    //- das Fenster in den vordergrund!
    fenster.focus();

  }



  //////////////////////////////////////////////////////////
  function myMaxLen(OBin,MaxCount,OBout_name)
  {
    var i=0;
    i=(MaxCount - OBin.value.length);

    OBout=document.getElementById(OBout_name);

    if (i>=0)
    {
      OBout.innerHTML = "(noch " + i + " Zeichen)";
    }
    else
    {
      OBout.innerHTML = "(Eingabe zu lang!)";
    }
  }


  //////////////////////////////////////////////////////////
  function selectRadio(rObj, newValue)
  {
    for (var i=0; i<rObj.length; i++) 
    {
      if (rObj[i].value==newValue)
      {
        rObj[i].checked = true;
      }
      else
      {
        rObj[i].checked = false;
      }
    }
  }

  //////////////////////////////////////////////////////////
  function selectNextRadio(rObj, curValue)
  {
    var doNext = false;
    var doneNext = false;

    for (var i=0; i<rObj.length; i++) 
    {
      if (doNext)
      {
        selectline(rObj[i].value, 1);
        rObj[i].checked = true;
        doNext = false;
        doneNext = true;
      }
      else
      {
        if (rObj[i].value==curValue)
        {
          doNext = true;
        }

        rObj[i].checked = false;
      }
    }

    if (!doneNext)
    {
      if (old_line_nr>0)
      {
        document.getElementById("tr_" + old_line_nr).className="vote_line";
      }
      old_line_nr=-1;
    }
  }

  //////////////////////////////////////////////////////////
  function GetObjectY(ob)
  {
    if (ob)
    {
      if (ob.offsetTop)
      {

        var curtop = 0;
        if (ob.offsetParent)
        {
	  do
	  {
	    curtop += ob.offsetTop;
	  }

	  while (ob = ob.offsetParent);
        }

        return curtop;
      }

      if (ob.y) return ob.y;
    }
    return 0;
  }

  //////////////////////////////////////////////////////////
  function GetScrollY()
  {
    if (window.pageYOffset) return window.pageYOffset;
    return 0;
  }

  //////////////////////////////////////////////////////////
  function GetScrollX()
  {
    if (window.pageXOffset) return window.pageXOffset;
    return 0;
  }


  //////////////////////////////////////////////////////////
  function GetInnerHeight()
  {
    if (window.innerHeight) return window.innerHeight;
    if (document.documentElement) if (document.documentElement.clientHeight) return document.documentElement.clientHeight;
    if (document.body.clientHeight) return document.body.clientHeight;

    return 0;
  }

  //////////////////////////////////////////////////////////
  function selectline(line_nr)
  {
    var tr_ob;
    var y=0;


    if (old_line_nr>0)
    {
      document.getElementById("tr_" + old_line_nr).className="vote_line";
    }

    tr_ob=document.getElementById("tr_" + line_nr);

    if (tr_ob)
    {
      //- Hintergrundfarbe für auswahl setzen
      tr_ob.className="vote_line_sel";
    }



    selectRadio(document.form_vote.line_radio, line_nr);

    if (line_nr != old_line_nr)
    {

      if (tr_ob)
      {
        //-bild scrollen
        y=GetObjectY(tr_ob) - (GetInnerHeight() /2) ;

        if ((y>0) && (GetInnerHeight()>0))
        {
	  if (y!=GetScrollY())
	  {
            //- kurz warten damit das Event richtig ausgeführt wird: z.B. auswahl des Radios
            window.setTimeout("window.scrollTo("+ GetScrollX() +", "+ y +");", 200);
	  }
        
        }
      }



      old_line_nr = line_nr;

      var akOB = document.form_vote["value["+ line_nr +"]"];


      if (type_arr["a_" + line_nr]==4)
      {
        if (akOB[0].focus) akOB[0].focus();
      }
      else if ((type_arr["a_"+ line_nr]==2) || (type_arr["a_"+ line_nr]==1))
      {
        if (akOB.select) akOB.select();
      }
      else if ((type_arr["a_"+ line_nr]==3))
      {
        if (akOB.focus) akOB.focus();
      }
      else
      {
        if (akOB.focus) akOB.focus();
      }

    }

    old_line_nr = line_nr;
  }

  //////////////////////////////////////////////////////////

  function watchKeyPress(event)
  {
    var keynum =-1;
    var hitkey=0;

    event = event || window.event;


    if (event)
    {
      hitkey = event.keyCode;
      if (!hitkey) hitkey=event.which;

      if (hitkey==10)
      {
        return false;
      }

      if (hitkey==13)
      {
        //- unterdrücken das Opera Tabs wächselt
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();

        selectNextRadio(document.form_vote.line_radio, old_line_nr);
        return false;
      }


      if (old_line_nr>0)
      {
        if (type_arr["a_"+ old_line_nr]==4)
        {
    keynum = hitkey-49;
    var rObj=document.form_vote["value["+ old_line_nr +"]"];
    var ok_sel = false;
    
    for (var i=0; i<rObj.length; i++) 
    {
      if (i==keynum)
      {
        rObj[i].checked = true;
        ok_sel=true;
      }
      else
      {
        rObj[i].checked = false;
      }
    }
    

    if (ok_sel)
    {
      //- unterdrücken das Opera Tabs wächselt
      if (event.stopPropagation) event.stopPropagation();
      if (event.preventDefault) event.preventDefault();

      selectNextRadio(document.form_vote.line_radio, old_line_nr);

            return false;
    }
        }


        if (type_arr["a_"+ old_line_nr]==2)
        {
    keynum = hitkey;

    if ((keynum>31) && ((keynum<48) || (keynum>57)))
    {
            //- unterdrücken das Opera Tabs wächselt
      if (event.stopPropagation) event.stopPropagation();
      if (event.preventDefault) event.preventDefault();

      return false;
    }
        }
      }
    }

  }

  //////////////////////////////////////////////////////////
  function GetCurrentDepValue(line_nr)
  {
    if (depending_arr["a_"+ line_nr])
    {
      var dep_id = depending_arr["a_"+ line_nr];

      if (document.form_vote["value["+ dep_id +"]"])
      {
        var dep_list = document.form_vote["value["+ dep_id +"]"];

        if (dep_list)
        {
          if (dep_list.options) return dep_list.options[dep_list.selectedIndex].value;
        }
      }
    }

    return -1;
  }

  //////////////////////////////////////////////////////////
  function doRefreshs(line_nr)
  {
    var item;
    var do_line_nr=0;
    var do_save_list_id=0;
    var list_obj;
    var i=0;
    var n=0;
    var newList=Array();
    var ak_dep_Id = 0;


    if (depending_arr)
    {
      list_obj =  document.form_vote["value["+ line_nr +"]"];
      if (!list_obj) return;

      ak_dep_Id = list_obj.options[list_obj.selectedIndex].value;
      if (!ak_dep_Id) return;
     //  if (ak_dep_Id<1) return;

      for (item in depending_arr)
      {
        if (depending_arr[item]==line_nr)
        {
    do_line_nr = item.substr(2, 99);

    list_obj = document.form_vote["value["+ do_line_nr +"]"];

    newList = filter_arr[item]["a_"+ ak_dep_Id];

    do_save_list_id = list_obj.options[list_obj.selectedIndex].value;



    for(i=list_obj.options.length; i>0; i--)
    {
      list_obj.options[(i-1)]= null;
    }


    if (newList)
    {
      n=1;

      if (newList.length>0)
      {
        list_obj.options[0] = new Option("- Bitte wählen -", -1);

        for(i=0; i<newList.length; i += 3)
        {

          if (newList[i+2]!='')
          {
            list_obj.options[n] = new Option(newList[i+1] + " ( " + newList[i+2] +" )", newList[i]);
          }
          else
          {
            list_obj.options[n] = new Option(newList[i+1], newList[i]);
          }    

          if (newList[i] == do_save_list_id) list_obj.selectedIndex = n;

          n++;
        }

        if (list_obj.options.length==2) list_obj.selectedIndex=1;
       
      }
      else
      {
        list_obj.options[0] = new Option("- keine Auswahl -", -1);
      }
    }
    else
    {
      list_obj.options[0] = new Option("- keine Auswahl -", -1);
    }
        }
      }
    }
  }
