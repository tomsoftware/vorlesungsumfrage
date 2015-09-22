<?php


if (!defined('E_STRICT')) define('E_STRICT',2048);
if (!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR',4096);


error_reporting(E_ALL | E_STRICT | E_RECOVERABLE_ERROR);


function MyErrorHandler($errno='-1', $errmsg='', $filename='[unknown]', $linenum=0, $vars='') 
{

    $dt = date("Y-m-d H:i:s (T)");
    

    $errortype = array (
                E_ERROR              => 'Fatal error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                );

    $err  = "<table border='1'>";
    $err .= "  <tr><td><b>Fehler-Type:</b></td><td><b>" . $errortype[$errno] . "</b> (" . $errno . ")</td></tr>";
    $err .= "  <tr><td><b>Fehlermeldung:</b></td><td>" . $errmsg . "</td></tr>";
    $err .= "  <tr><td><b>Datum/Zeit:</b></td><td>" . $dt . "</td></tr>";
    $err .= "  <tr><td><b>Datei:</b></td><td>" . $filename . "</td></tr>";
    $err .= "  <tr><td><b>Zeile:</b></td><td>" . $linenum . "</td></tr>";
    $err .= "  <tr><td valign='top'><b>Backtrace:</b></td><td>";


    foreach(debug_backtrace() as $call)
    {
      $err .= "    <table border='1'>";
      if(isset($call['file']))   $err .= "      <tr><td><b>Datei:</b></td><td>". $call['file']  ."&nbsp;</td></tr>";
      if(isset($call['line']))   
      {
        $f= file($call['file']);
	$err .= "      <tr><td><b>Zeile:</b></td><td>". $call['line']  ."&nbsp;</td></tr>";
	$err .= "      <tr><td><b>Code:</b></td><td>". $f[$call['line']]  ."&nbsp;</td></tr>";
      }
      if(isset($call['function']))   $err .= "      <tr><td><b>Funktion:</b></td><td>". $call['function']  ."&nbsp;</td></tr>";
      $err .= "    </table>";
    }
    $err .= "</td></tr>";
    if (!is_array($vars))
    {
      $err .= "  <tr><td><b>Variablen</b></td><td>". $vars ."</td></tr>";
    }
    $err .= "</table><br>\n\r";


    $handle=fopen(ERROR_LOG_PATH . date('Y.m.d') .'.html' , 'a');
    fwrite($handle, $err);
    fclose($handle);    


    echo '<b>'. $errortype[$errno] . '</b>: '. $errmsg .' in <b>'. basename($filename) .'</b> on line <b>'. $linenum .'</b><br />';
}

set_error_handler('MyErrorHandler');


?>