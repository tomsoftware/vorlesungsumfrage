<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />

  <TITLE>Umfrage</TITLE>
</HEAD>
<BODY>

<FORM method="post" ACTION=""  name=""  id="">
  <input type="text" name="text" value="" />
  <input type="submit"  value="senden" />
</FORM>



<?PHP

if(isset($_POST['text']))
{
  $t = $_POST['text'];

  echo '<hr />';
  echo $t .' - '. htmlentities($t);
  echo '<hr />';
  echo utf8_decode($t) .' - '. htmlentities(utf8_decode($t));
  echo '<hr />';

  if (preg_match('%^(?:
          [\x09\x0A\x0D\x20-\x7E]            # ASCII
        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $t))
  {
    echo 'IS UTF8!'. utf8_decode($t) .' - '. htmlentities(utf8_decode($t));
  }
  else
  {
    echo 'NO UTF8! '. $t .' - '. htmlentities($t);
  }

  echo '<hr />';

  if (function_exists('mb_convert_encoding'))
  {
    $tt= mb_convert_encoding($tt, 'ISO-8859-1', 'auto');
    echo 'mb_convert_encoding: '. $tt .' - '. htmlentities($tt);
  }

  echo '<hr />';

  if (function_exists('iconv'))
  {
    $tt= iconv('ISO-8859-1','UTF-8',$t);
    echo 'iconv: '. $tt .' - '. htmlentities($tt);
  }

  echo '<hr />';
}


?>


</BODY>
</HTML>