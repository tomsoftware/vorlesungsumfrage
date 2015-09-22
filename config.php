<?PHP


  //- Pfadangaben:
  define('TEMP_FOLDER','temp/');
  define('ERROR_LOG_PATH','errors/error_');


  //- HTTP-Pfad zu Bildern
  $IMAGE_HTML_PATH = '/Fachschaft/images/';
  //- HTTP-Pfad zu anderen Statischen Objeketen (JavaScripts / SWF / HTML)
  $STATIC_HTML_PATH = '/Fachschaft/vote/static/';


  //- MySQL Verbindungsdaten
  define('MYSQL_DATABASE_NAME', 'fachschaft');
  define('MYSQL_SERVER','localhost');
  define('MYSQL_USER','fs');
  define('MYSQL_PASS','fsdbuser2008');
  define('MYSQL_PCONNECT',false);


  //--------------------------------------------------------------//


  //- ALLE Fehler anzigen
  error_reporting(E_ALL);


  //- Farbewechsel für Tabellen-Spalten
  define('COLOR_ROW_EVEN','F8F8F8');
  define('COLOR_ROW_ODD', 'EFEFEF');


  //- Version dieses Programmes
  define('APPLICATIONS_VERSION','0.0.1');
  $APPLICATION_COPYRIGHT = 'Fachschaft Physik';



  //--- Zeitzohne einstellen (seit PHP 5)---
  if (function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Berlin');

  //- Gibt das standard-Startdatum für alle Exporte an.
  define('DEFAULT_START_DATE', date('d.m.Y', mktime(0, 0, 0, date('m')- 3, date('d'), date('Y'))));
  define('MIN_START_DATE','01.01.2003');


  //- Browser so einstellen das er IMMER ALLES neu läd und nicht seinen Chach nutzt
  header('Cache-Control: no-cache, must-revalidate');  
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');    // Datum aus Vergangenheit
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); 




  //- Arbeitsverzeichnis festlegen
  $akdir = strtr(getcwd (),'\\','/');
  if (substr($akdir, -1)!='/') $akdir= $akdir . '/';



  //------------------------------------------------------
  //-- Spracheinstellung ---------------------------------
  //- Info:
  //-	Für die verschiedenen Kunden können verschiedenen Order angegeben werden in denen die Templatedateien und die 
  //-	Sprachdateien für diesen Kunden liegen. Werden die benötigeten Dateien NICHT gefunden dann wird im "default" Ordner
  //-	gesucht. Die Variabelen werden von der Funktion [clUser.php-> Init_User_Profile()] gesetzt.
  //- $Work_Space_Folder		Root-Verzeichnis in dem ALLE Spracheinstellungen gespeichert sind - kann für jeden Kunden anders sein
  //- $Default_Work_Space_Folder	Root-Verzeichnis in dem ALLE Spracheinstellungen gespeichert sind - wird verwendet wenn in $Work_Space_Folder nichts gefunden wurde
  //- $template_SubFolder		Verzeichnis unter $Work_Space_Folder in dem die Templates gespeichert werden
  //- $UseLangguage			Indikator für die Sprache ('DE' oder 'EN')  
  //- $DB_Language			Namenserweiterung in der Datenbank für den Wert in einer anderen Sprache


  $Work_Space_Folder = 'data';
  $Default_Work_Space_Folder = 'data';
  $Default_template_SubFolder = 'de';
  $UseLangguage = 'DE';
  $DB_Language='';
  $template_SubFolder ='de';



  //- Einstellungen in der Tabelle [config]
  define('CONFIG_PROGRAMM_NAME', 'vote');



  define('CONFIG_LOAD_SUCCESSFUL',true);
  define('INCLUDE_DIR', './include/');
  define('APPLICATION_FOLDER', 'applications');


  include_once(INCLUDE_DIR . 'errorhandler.php');


  //- Config von Vote
  define('COUNT_OF_NUMBER_FIELDS', 60);
  define('COUNT_OF_STRING_FIELDS', 20);

  define('FIELD_TYPE_STRING', 1);
  define('FIELD_TYPE_NUMBER', 2);
  define('FIELD_TYPE_COMBOLIST', 3);
  define('FIELD_TYPE_RADIOLIST', 4);
  define('FIELD_TYPE_COMMENT', 5);
  define('FIELD_TYPE_RADIO_HEADLINE', 6);
  define('FIELD_TYPE_RADIOLIST_NOTEXT', 7);
  define('FIELD_TYPE_FILTER_COMBOLIST', 8);


  $Field_Type_Name = array();
  $Field_Type_Name[FIELD_TYPE_COMMENT] = 'Text-Überschrift (keine Eingabe)';
  $Field_Type_Name[FIELD_TYPE_RADIO_HEADLINE] = 'Überschrift für Einfachauswahl-Tabelle (keine Eingabe)';

  $Field_Type_Name[FIELD_TYPE_STRING] = 'Eingabe: Textfeld';
  $Field_Type_Name[FIELD_TYPE_NUMBER] = 'Eingabe: Zahlenfeld';
  $Field_Type_Name[FIELD_TYPE_COMBOLIST] = 'Auswahl: Auswahlliste / Dropdown';
  $Field_Type_Name[FIELD_TYPE_RADIOLIST] = 'Auswahl: Einfachauswahl mit Text (Radiobutton)';

  $Field_Type_Name[FIELD_TYPE_RADIOLIST_NOTEXT] = 'Auswahl: Einfachauswahl-Tabelle ohne Text (Radiobutton)';
  $Field_Type_Name[FIELD_TYPE_FILTER_COMBOLIST] = 'Filter: Gefilterte Auswahlliste / Dropdown';


  $Field_Type_is_List = array();
  $Field_Type_is_List[FIELD_TYPE_COMMENT] = false;
  $Field_Type_is_List[FIELD_TYPE_STRING] = false;
  $Field_Type_is_List[FIELD_TYPE_NUMBER] = false;
  $Field_Type_is_List[FIELD_TYPE_COMBOLIST] = true;
  $Field_Type_is_List[FIELD_TYPE_RADIOLIST] = true;
  $Field_Type_is_List[FIELD_TYPE_RADIO_HEADLINE] = true;
  $Field_Type_is_List[FIELD_TYPE_RADIOLIST_NOTEXT] = true;
  $Field_Type_is_List[FIELD_TYPE_FILTER_COMBOLIST] = true;


  $Field_Type_is_Filter = array();
  $Field_Type_is_Filter[FIELD_TYPE_COMMENT] = false;
  $Field_Type_is_Filter[FIELD_TYPE_STRING] = false;
  $Field_Type_is_Filter[FIELD_TYPE_NUMBER] = false;
  $Field_Type_is_Filter[FIELD_TYPE_COMBOLIST] = false;
  $Field_Type_is_Filter[FIELD_TYPE_RADIOLIST] = false;
  $Field_Type_is_Filter[FIELD_TYPE_RADIO_HEADLINE] = false;
  $Field_Type_is_Filter[FIELD_TYPE_RADIOLIST_NOTEXT] = false;
  $Field_Type_is_Filter[FIELD_TYPE_FILTER_COMBOLIST] = true;


  $Field_Type_DataType = array();
  $Field_Type_DataType[FIELD_TYPE_COMMENT] = 0;
  $Field_Type_DataType[FIELD_TYPE_STRING] = 2;
  $Field_Type_DataType[FIELD_TYPE_NUMBER] = 1;
  $Field_Type_DataType[FIELD_TYPE_COMBOLIST] = 1;
  $Field_Type_DataType[FIELD_TYPE_RADIOLIST] = 1;
  $Field_Type_DataType[FIELD_TYPE_RADIO_HEADLINE] = 0;
  $Field_Type_DataType[FIELD_TYPE_RADIOLIST_NOTEXT] = 1;
  $Field_Type_DataType[FIELD_TYPE_FILTER_COMBOLIST] = 1;




  //------------------------------------------------------
  //-- Zeit zum Erstellen der Seite ----------------------
  list($Programm_Start_usec, $Programm_Start_sec) = explode(' ',microtime()); 

?>