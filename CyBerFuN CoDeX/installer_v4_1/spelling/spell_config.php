<?php
  // --------------------------------------------------------------------
  // phpSpell Configuration
  //
  // This is (c)Copyright 2002-2008, Team phpSpell.
  // --------------------------------------------------------------------

  define ('PHPSPELL_CONFIG', true);
  define ('PHPSPELL_VERSION', '1.06r');
  define ('LINE_FEED', "\n");

  // ----------------------------------------------
  // Set Default php file extensions
  // If you are using phpbb or nuke this will be
  // CHANGED by the extension.inc file!
  // So changing it here won't matter in that case
  // ----------------------------------------------
  $phpEx = 'php';

  // ----------------------------------------------
  // Set Default Database Engine
  // If you are using phpbb thiw will Need to be 'PHPBB'
  // If this is Hivemail; then set it to 'Hivemail'
  // if this is PHPNuke then set it to phpnuke
  //
  // If you are attempting to integrate yourself into
  // a product then use 'MySQL' and change the parameters
  // ----------------------------------------------
  // ----- Uncomment which ever line you need -----
  // ----------------------------------------------
  //$Spell_Config['DB_Type'] = 'PHPBB3';
//  $Spell_Config['DB_Type'] = 'Hivemail';
    $Spell_Config['DB_Type'] = 'MySQL';
//  $Spell_Config['DB_Type'] = 'PHPNuke';
//  $Spell_Config['DB_Type'] = 'PSPELL';
//  $Spell_Config['DB_Type'] = 'Invision';
//  $Spell_Config['DB_Type'] = 'vBulletin';
//  $Spell_Config['DB_Type'] = 'phpMail';
//  $Spell_Config['DB_Type'] = 'SMF';

  // ----------------------------------------------
  // You only need to change only for Native MySQL
  // support! Otherwise you are waisting your time
  // as the other built in supported methods
  // already sets these up.
  // ----------------------------------------------
    $Spell_Config["DB_Username"] = "mindless_mind";
    $Spell_Config["DB_Password"] = "embassy1";
    $Spell_Config["DB_Database"] = "mindless_mindless";
    $Spell_Config["DB_Host"] = "localhost";

  // ----------------------------------------------
  // If IE gives a javascript error on the word:
  // CAFé
  // then enable this since your server is forcing
  // UTF-8 document mode back
  // ----------------------------------------------
  $Spell_Config['IE_UTF_Encode'] = false;

  // ----------------------------------------------
  // Set to Default Language
  // ----------------------------------------------
  $Spell_Config['Default_Language'] = 'English';

  // ----------------------------------------------
  // Set to the languages you support.  Each one
  // Must have a spell_Language.php template.
  // ----------------------------------------------
  $Spell_Config['Languages_Supported'] = array('English', 'Russian', 'German', 'Polish');

  // ----------------------------------------------
  // Enabled will auto recheck the word without the
  // ('s) or (s) if the word has no match normally
  // ----------------------------------------------
  // If you are using another language other than
  // English you might have to disable this
  // ----------------------------------------------
  $Spell_Config['Enable_Drop_S_Support'] = true;

   // ------------------------
  // Globals
  //
  // If you allow this code
  // -----------------------
  $Spell_Config['USE_BBCODE'] = true;
  $Spell_Config['USE_HTML'] = false;

  // -----------------------------------------------------------------
  // The number of changes between words for being accepted as
  // a possible spelling match.  The Larger the number, the more words
  // that show up in the spelling list.  However the larger the number
  // the less likely that the word is to be the match.  :-)
  // -----------------------------------------------------------------
  $Spell_Config['Levenshtein_Distance'] = 3;

  // ---------------------------------------------------------------------
  // This is to override the ability for users to change there Levenshtein
  // distance, Zero (0) - means the user can pick any number (including 0)
  // Otherwise if they pick 5 and you have 4 as the max, they will get 4.
  // ---------------------------------------------------------------------
  $Spell_Config['Max_User_Levenshtein_Distance'] = 0;

  // -----------------------------------------------------------------
  // Enables / disables Off by One letter Searching (Always)
  // It will auto-enable for any word that no matches show up on in
  // the normal search method.
  // This search method is quite a bit slower than the normal search
  // -----------------------------------------------------------------
  // 0 = Disabled Completely
  // 1 = Disabled, except for no matches
  // 2 = Enabled always
  // -----------------------------------------------------------------
  $Spell_Config['Off_By_One_Search'] = 0;

  // ------------------------------------------------
  // HTML Tags. (You need opening & closing versions)
  // ------------------------------------------------
  $Spell_Config['HTML_Tags'] = array('<a','</a','<img','</img','<b','</b','<center','</center','<i','</i','<u','</u','<list','</list','<font','</font');

  // -------------------------------------------
  // BBCode Tags
  // -------------------------------------------
  $Spell_Config['BBCODE_Tags'] = array('[b','[/b','[url','[/url',array('[img','[/img]'),'[i','[/i','[color','[/color','[size','[/size','[u','[/u','[list','[/list',array('[quote','[/quote]'),array('[code','[/code]'));

  // ------------------------------------------------------
  // These words + the rest of the sentance are skipped
  // until a space, return, or open bracket occurs
  // (i.e. 'www.phpbb.com/forum/message?a=1' would be
  // completely skipped.  (No point is spellchecking URL's)
  // ------------------------------------------------------
  $Spell_Config['Skip_Word_Tags'] = array('http://','www.');

  // ------------------------------------------------
  // Put your list of Symbols Word you wish to ignore
  // in this list.  These are typically smilies that
  // have letters in them - IT IS CASE SENSITIVE!
  //
  // DO NOT PUT JUST LETTERS!!!  If it is ':smile:' the
  // word 'smile' is a valid word, so there is no
  // point is adding it to the symbol table
  // ------------------------------------------------
  $Spell_Config['Symbol_Tags'] = array(';-D',':-d',':-P',':-p',':p',':P');


  // ------------------------------------------------
  // This is how many characters to force a word wrap
  // After (i.e. so that long urls get broken in the
  // spell checker windo)
  // ------------------------------------------------
  $Spell_Config['Insert_Word_Wrap'] = 46;

  // -----------------------------------------------------
  // Unless you know exactly what you are doing
  // DO NOT CHANGE ANYTHING below here.
  // -----------------------------------------------------
   $Spell_Config['CSS'] = 'spelladmin.css';
                                             

  // -----------------------------------------------------
  // Verify we have something setup
  // -----------------------------------------------------
  if (!isset($Spell_Config['DB_Type'])) {
    die ('You must choose a Database Type in the Spell Configuration file.');
  }



  // ------------------------------
  // Setup to use phpbb's 3 DB Engine
  // ------------------------------
  if ($Spell_Config['DB_Type'] == 'PHPBB3') {
    $Spell_Config['PHPBB_ROOT_PATH'] = '../';
    $Spell_Config['PHPBB_Load_Smilies'] = true;
    $Spell_Config['CSS'] = '../templates/subSilver/subSilver.css';
    include 'spell_phpbb3.php';
  }


  // ------------------------------
  // Setup to use phpbb's DB Engine
  // ------------------------------
  if ($Spell_Config['DB_Type'] == 'PHPBB' || $Spell_Config['DB_Type'] == 'PHPNuke') {
    $Spell_Config['PHPBB_ROOT_PATH'] = '../';
    $Spell_Config['PHPBB_Load_Smilies'] = true;
    $Spell_Config['CSS'] = '../templates/subSilver/subSilver.css';
    include 'spell_phpbb.php';
  }


  // ---------------------------------
  // Setup to use Hivemail's DB Engine
  // ---------------------------------

  if ($Spell_Config['DB_Type'] == 'Hivemail') {
    include 'spell_hivemail.php';
  }

  // -----------------------------------
  // Setup to use native MySQL DB Engine
  // -----------------------------------
  if ($Spell_Config['DB_Type'] == 'MySQL' || $Spell_Config['DB_Type'] == 'SMF') {
    include 'spell_MySQL.php';
  }

  if ($Spell_Config['DB_Type'] == 'PSPELL') {
    // PSpell Needs to load the Language File First
    if (!isset($Spell_Config['Languages_Supported'][$User_Settings[0]])) $Current_Language = $Spell_Config['Default_Language'];
    else $Current_Language = $Spell_Config['Languages_Supported'][$User_Settings[0]];
    include_once ('spell_'.$Current_Language.'.'.$phpEx);
    include 'spell_pspell.php';
  }

  if ($Spell_Config['DB_Type'] == 'Invision')
  {
    include '../conf_global.php';

    $Spell_Config['DB_Username'] = $INFO['sql_user'];
    $Spell_Config['DB_Password'] = $INFO['sql_pass'];
    $Spell_Config['DB_Database'] = $INFO['sql_database'];;
    $Spell_Config['DB_Host'] = $INFO['sql_host'];;
    $table_prefix = $INFO['sql_tbl_prefix'];
    $Spell_Config['DB_MODULE'] = 'Invision';

     include 'spell_MySQL.php';
  }

   if ($Spell_Config['DB_Type'] == 'vBulletin')
  {
    include '../config.php';

    $Spell_Config['DB_Username'] = $dbusername;
    $Spell_Config['DB_Password'] = $dbpassword;
    $Spell_Config['DB_Database'] = $dbname;
    $Spell_Config['DB_Host'] = $servername;
    $table_prefix = @$tableprefix;  // vB 3+
    $Spell_Config['DB_MODULE'] = 'vBulletin';

     include 'spell_MySQL.php';
  }


  if ($Spell_Config['DB_Type'] == 'phpMail')
  {
     $data = file('../mysqlinfo.cgi');
     foreach ($data as $line) {
       if (strpos($line, '=') > 0) {
         $line_array = explode('=', $line);
         $line_array[0] = trim(@$line_array[0]);
         $line_array[1] = trim(@$line_array[1]);
         $pos = strpos($line_array[1], ';');
         if ($pos !== false) $line_array[1] = substr($line_array[1], 0, $pos);
         if ($line_array[0] == 'dbname') $Spell_Config['DB_Database'] = $line_array[1];
         if ($line_array[0] == 'dbhost') $Spell_Config['DB_Host'] = $line_array[1];
         if ($line_array[0] == 'dbusername') $Spell_Config['DB_Username'] = $line_array[1];
         if ($line_array[0] == 'dbpassword') $Spell_Config['DB_Password'] = $line_array[1];
       }
     }
     $Spell_Config['DB_MODULE'] = 'phpMail';
     include 'spell_MySQL.php';
  }

  if (!isset($Spell_Config['DB_MODULE'])) {
    die ('You must choose a Database Type in the Spell Configuration file.');
  }

?>