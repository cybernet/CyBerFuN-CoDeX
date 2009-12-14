<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
        <title>Spell Check Diagnostics</title>
        <style>
        .Text        { font-size : 11px; font-family: Verdana}
        .Title       { font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif; font-size : 22px; font-weight : bold; text-decoration :: none; line-height : 120%; color : #006699; }
        </style>
</head>
<body bgcolor="#FFFFFF">
<?php
  // --------------------------------------------------------------------
  // phpSpell Diagnostics
  //
  // This is (c)Copyright 2003-2008, Team phpSpell.
  // --------------------------------------------------------------------
  error_reporting(E_ALL);
  ini_set('display_errors',true);

  define("IN_SPELL_DIAGS", true);

  $start_time = time();
  $safe_mode = (bool) ini_get("safe_mode");
  $max_time = ini_get("max_execution_time");
  @set_time_limit(6000);
  $new_time = ini_get("max_execution_time");
  if ($new_time == 6000) $Install_overrideable = true;
  else $Install_overrideable = false;
  @set_time_limit(500);
  $new_time = ini_get("max_execution_time");
  if ($new_time == 500) $Spell_overrideable = true;
  else $Spell_overrideable = false;

  include "spell_config.php";

  // Multi Language Support
  if ($Spell_Config["Default_Language"] == "") {
    echo "Configuration file is missing language setting.<br>Please set \$Spell_Config[\"Default_Language\"] to your language in your configuration.";
    exit;
  }

  include_once ("spell_".$Spell_Config["Default_Language"].".php");
  $valid_charlist = $Language_Character_List;

  echo "<font size=+1><u><b>phpSpell Diagnostics ".PHPSPELL_VERSION."</b></u></font><br><br>";
  echo "<table border=1 cellpadding=2 cellspacing=0>";
  echo "<tr><td>Module:</td><td>".$Spell_Config["DB_MODULE"]."</td></tr>";
  echo "<tr><td nowrap>Current PHP version:</td><td>".phpversion()."</td></tr>";
  if (isset($_SERVER)) {
    echo "<tr><td>Server:</td><td>".$_SERVER["SERVER_SOFTWARE"]."</td></tr>";
    echo "<tr><td>Script Path:</td><td>".$_SERVER["PHP_SELF"]."</td></tr>";
  } else {
    echo "<tr><td>Server:</td><td>".$HTTP_SERVER_VARS["SERVER_SOFTWARE"]."</td></tr>";
    echo "<tr><td>Script Path:</td><td>".$HTTP_SERVER_VARS["PHP_SELF"]."</td></tr>";
  }
  if (isset($phpbb_root_path)) echo "<tr><td>PHP Root Path:</td><td>".$phpbb_root_path."</td></tr>";
  echo "<tr><td>utf8_decode support:</td><td>";
  if (function_exists('utf8_decode')) echo "Enabled.</td></tr>";
  else echo "Disabled.</td></tr>";
  echo "<tr><td>Magic Quotes:</td><td>";
  if (get_magic_quotes_gpc()) echo "On.</td></tr>";
  else echo "Off</td></tr>";
  echo "<tr><td>Safe mode:</td><td>";
  if ($safe_mode) echo "Enabled.</td></tr>";
  else echo "Disabled.</td></tr>";
  echo "<tr><td>Default Max Time:</td><td>$max_time seconds (";
  if ($Install_overrideable) echo "T/";
  else echo "F/";
  if ($Spell_overrideable) echo "T)</td></tr>";
  else echo "F)</td></tr>";
  echo "</td></tr>";
  echo "<tr><td>Database:</td><td>".$dbms."</td></tr>";
  echo "<tr><td>&nbsp;&nbsp;using table:</td><td>".$DB_TableName;
  if ($table_prefix != "") echo " - (Prefix: ".$table_prefix.")";
  echo "</td></tr>";
  echo "<tr><td nowrap>Languages supported:</td><td>".Display_List($Spell_Config["Languages_Supported"])." - Default: ".$Spell_Config["Default_Language"]."</td></tr>";
  echo "<tr><td>Drop S Support:</td><td>";
  if ($Spell_Config["Enable_Drop_S_Support"]) echo "Enabled.";
  else echo "Disabled.";
  echo "</td></tr>";

  echo "<tr><td>BBCODE:</td><td>";
  if ($Spell_Config["USE_BBCODE"]) echo "Enabled.";
  else echo "Disabled.";
  echo "</td></tr><tr><td>HTML:</td><td>";
  if ($Spell_Config["USE_HTML"]) echo "Enabled.";
  else echo "Disabled.";
  echo "</td></tr>";
  echo "<tr><td>Levenshtein Distance:</td><td>".$Spell_Config["Levenshtein_Distance"]." / ".$Spell_Config["Max_User_Levenshtein_Distance"]."</td></tr>";
  echo "<tr><td>Valid Characters:</td><td>".$valid_charlist."</td></tr>";
  echo "<tr><td>Valid HTML Tags:</td><td>".Display_List($Spell_Config["HTML_Tags"])."</td></tr>";
  echo "<tr><td>Valid BBCode Tags:</td><td>".Display_List($Spell_Config["BBCODE_Tags"])."</td></tr>";
  echo "<tr><td>Skip words:</td><td>".Display_List($Spell_Config["Skip_Word_Tags"])."</td></tr>";
  echo "<tr><td>Load Smilies:</td><td>";
  if (isset($Spell_Config["PHPBB_Load_Smilies"]) && $Spell_Config["PHPBB_Load_Smilies"]) echo "Enabled.</td></tr>";
  else echo "Disabled.</td></tr>";
  echo "<tr><td>Symbol Tags:</td><td>".Display_List($Spell_Config["Symbol_Tags"])."</td></tr>";
  echo "<tr><td>Word Wrap at:</td><td>".$Spell_Config["Insert_Word_Wrap"]." characters.</td></tr>";
  echo "<tr><td>Skip Tags:</td><td>".Display_List($Spell_Config["Skip_Word_Tags"])."</td></tr>";
  echo "<tr><td>Dictionaries found in<br>install directory:</td><td>";
  $found = 0;
    if ($dir = @opendir(".")) {
       while (($file = readdir($dir)) !== false) {
         $pos=strpos(strtolower($file), ".dic") ;
         if ($pos !== false) {
           $found++;
           echo $file."<br>";
         }
       }
       closedir($dir);
    }
  if ($found == 0) echo "None.<br>";
  echo "</td></tr>";
  echo "<tr><td>Words in DB:</td><td>";
  echo DB_Get_Word_Count();
  echo "</td></tr>";

  echo "</table><br><br>";

  echo "<font size=+1><b><u>Testing PHP / Spell Support...</u></b></font><br>";

  // Please note beginer is supposed to be wrong!
  $Word_Array = array("agenda","approach","beginer","zebra","special","zymurgy");

  echo "<table border=1 cellpadding=2 cellspacing=0>";
  echo "<th>Word</th><th>Translation</th><th>Metaphone</th><th>Lookup</th><th>Count</th>";
  for ($i=0;$i<count($Word_Array);$i++) {
    $Word = $Word_Array[$i];
    echo "<tr>";
    Test_Word($Word);
    echo "</tr>";
  }
  echo "</table>";

function Display_List($List)
{
  $Cnt = count($List);
  $Len=0;
  $Data = "";
  for ($i=0;$i < $Cnt; $i++)
  {
    if (is_array($List[$i])) {
      for ($j=0;$j<count($List[$i]);$j++) {
        if ($Len > 0) {
          if ($j > 0) $Data .= "|";
          else $Data .= ", ";
        }
        $Data .= htmlentities($List[$i][$j]);
        $Len += strlen($List[$i][$j]);
      }
    } else {
      if ($Len > 0) $Data .= ", ";
      $Data .= htmlentities($List[$i]);
      $Len += strlen($List[$i]);
    }
  }
  return ($Data);
}

function Test_Word($word_to_test)
{
  global $DB_TableName, $Spell_Config;
  global $Spelling_DB;

  echo "<td>".$word_to_test."</td><td>";


  $tr_word_to_test = Translate_Word($word_to_test);
  $word_metaphone = Word_Sound_Function($tr_word_to_test);
  echo $tr_word_to_test."</td><td>".$word_metaphone."</td><td>";

  if (DB_Check_Word($word_to_test)) echo "Found.";
  else echo "NOT IN DB.";

  echo "</td><td>";
  $Good_Word_Array = DB_Get_Suggestions($word_metaphone, $word_to_test);
  $Count = count($Good_Word_Array);
  echo $Count."</td></tr><tr><td>Suggestions:</td><td colspan=5>";
  for ($i=0;$i<$Count;$i++) {
    $Word = $Good_Word_Array[$i];
    $TR_Fetched_Word = Translate_Word($Word);
    if ($i > 0) echo ", ";
    $Lev_Distance = levenshtein($tr_word_to_test, $TR_Fetched_Word);
    if ($Lev_Distance < $Spell_Config["Levenshtein_Distance"]) echo "<b>";
    else echo "(";
    echo $Word;
    if ($Lev_Distance < $Spell_Config["Levenshtein_Distance"]) echo "</b>";
    else echo ")";
  }
  echo "</td></tr><tr><td colspan=6>&nbsp;</td>";

}

?>
</body>
</html>