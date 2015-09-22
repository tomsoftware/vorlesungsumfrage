<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
  <me_ta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />


  <TITLE>Umfrage</TITLE>

  <link rel="stylesheet" type="text/css" href="static/vote.css" />

  <script src="static/vote.js" type="text/javascript"></script>

</HEAD>

<BODY style="margin:0px; padding:0px">



  
  <FORM method="post" ACTION="" name="form_vote" onSubmit="return alow_submit;"  id="form_vote">


  
	  <textarea name="value" rows="2" style="width:380px">bla bla bal!</textarea>
          <input type="text" name="value2" />
	  <input type="submit" />
  </FORM>


	<FORM method="POST" ACTION="">
	  <textarea name="value" rows="2" style="width:380px">bla bla bal!</textarea>
          <input type="text" name="value2" />
	  <input type="submit" />
	</FORM>


<?PHP

echo '<h1>V1</h1>';
if (isset($_POST['value']))
{
echo htmlentities($_POST['value']);
echo '<hr>';
echo htmlentities(utf8_decode($_POST['value']));

}

echo '<h1>V2</h1>';

if (isset($_POST['value2']))
{
echo htmlentities($_POST['value2']);
echo '<hr>';
echo htmlentities(utf8_decode($_POST['value2']));

}
?>

</BODY>
</HTML>
