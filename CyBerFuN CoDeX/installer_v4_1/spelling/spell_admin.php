<?php
  // --------------------------------------------------------------------
  // phpSpell Dictionary Installation
  //
  // This is (c)Copyright 2002-2008, Team phpSpell.
  // --------------------------------------------------------------------

  define ("IN_SPELL_ADMIN", true);

  error_reporting(E_ALL);
  ini_set('display_errors',true);


  $start_time = time();
  $safe_mode = (bool) ini_get("safe_mode");

  // Override Safe Mode
  if (isset($HTTP_POST_VARS["SM"]) || isset($_REQUEST['SM'])) $safe_mode = true;
  if ($safe_mode == false) @set_time_limit(6000);
  $exec_time = ini_get("max_execution_time")-2;

  // Override Exec Time
  if (isset($_REQUEST["SM"]) || isset($HTTP_POST_VARS['SM'])) {
    if ($exec_time > 28) $exec_time = 28;
  }

  // Include Spell Configuration
  include "spell_config.php";
  if (!defined("PHPSPELL_CONFIG")) exit;

  if ($Spell_Config["DB_MODULE"] == "pspell") {
    echo "<font size=+2><b>You do not need to run this!</b></font>";
    exit;
  }

  // Output Page header
  echo "<html><head><link rel=\"stylesheet\" href=\"".$Spell_Config["CSS"]."\" type=\"text/css\"></head>";
  if ($safe_mode || $exec_time < 5998) {
    echo "<body bgcolor=\"#E5E5E5\" onload=\"sfmform.submit();\">";
  } else {
    echo "<body bgcolor=\"#E5E5E5\">";
  }

  // Language Support
  if ($Spell_Config["Default_Language"] == "") {
    echo "Configuration file is missing language setting.<br>Please set \$Spell_Config[\"Default_Language\"] to your language in the <b>spell_config.php</b> file.";
    exit;
  }

  if (isset($_REQUEST["CL"])) $CL = $_REQUEST["CL"];
  else if (isset($HTTP_POST_VARS["CL"])) $CL = $HTTP_POST_VARS["CL"];
  else $CL = 0;

  if (!isset($Spell_Config["Languages_Supported"][$CL])) $Current_Language = $Spell_Config["Default_Language"];
  else $Current_Language = $Spell_Config["Languages_Supported"][$CL];
  include ("spell_".$Current_Language.".".$phpEx);
  $valid_charlist = $Language_Character_List;



  // Globalize variables
  if (isset($_REQUEST["Install_Dictionary"])) $Install_Dictionary = $_REQUEST["Install_Dictionary"];
  else if (isset($HTTP_POST_VARS["Install_Dictionary"])) $Install_Dictionary = $HTTP_POST_VARS["Install_Dictionary"];
  if (isset($_REQUEST["Clear_Dictionary"])) $Clear_Dictionary = $_REQUEST["Clear_Dictionary"];
  else if (isset($HTTP_POST_VARS["Clear_Dictionary"])) $Clear_Dictionary = $HTTP_POST_VARS["Clear_Dictionary"];
  if (isset($_REQUEST["New_Word_To_Add"])) $New_Word_To_Add = $_REQUEST["New_Word_To_Add"];
  else if (isset($HTTP_POST_VARS["New_Word_To_Add"])) $New_Word_To_Add = $HTTP_POST_VARS["New_Word_To_Add"];
  if (isset($_REQUEST["Offset"])) $Offset = $_REQUEST["Offset"];
  else if (isset($HTTP_POST_VARS["Offset"])) $Offset = $HTTP_POST_VARS["Offset"];
  if (isset($_REQUEST["WP"])) $WP = $_REQUEST["WP"];
  else if (isset($HTTP_POST_VARS["WP"])) $WP = $HTTP_POST_VARS["WP"];
  if (isset($_REQUEST["WA"])) $WA = $_REQUEST["WA"];
  else if (isset($HTTP_POST_VARS["WA"])) $WA = $HTTP_POST_VARS["WA"];
  if (isset($_REQUEST["SM"])) $SM = $_REQUEST["SM"];
  else if (isset($HTTP_POST_VARS["SM"])) $SM = $HTTP_POST_VARS["SM"];


  if (isset($Install_Dictionary) && $Install_Dictionary == "NONE") unset($Install_Dictionary);
  if (!isset($Install_Dictionary) && !isset($Clear_Dictionary) && !isset($New_Word_To_Add)) {
?>
  <script LANGUAGE="javascript">
  <!--
     Submitted=false;
     function submitform(link)
     {
       if (Submitted) return (false);
       link.value = "Procesessing...";
       Submitted = true;
       return (true);
     }
  // -->
  </script>
                        <h1>SpellChecker Dictionary Maintenance
                <?php
                  if ($safe_mode) {
                    echo "(Safe Mode - $exec_time secs)";
                  } else if ($exec_time < 5998) {
                    echo "(Max time - $exec_time secs)";
                  }
                ?>
                </h1>
                <p><span class="genmed">Here you can manage the dictionaries on your forums. This will allow you to add new languages and words to your database easily.</span></p>
                    <form name="DictForm" method="post">
                    <table cellpadding="3" cellspacing="1" width="100%" class="forumline">
                    <tr>
                        <th colspan="2" class="thHead">Import Words Lists</th></tr>
                    <tr>
                        <td class="row1">Dictionary file to add:</td>
                        <td class="row2"><input type="hidden" name="formtype" value="1">&nbsp;<select name="Install_Dictionary" class="Text">
                          <option value="NONE">Choose Dictionary...</option>
                            <?php
                                if ($dir = @opendir(".")) {
                                  while (($file = readdir($dir)) !== false) {
                                    $pos=strpos(strtolower($file), ".dic") ;
                                    if ($pos==true) echo "<option value='$file'>$file</option>";
                                  }
                                  closedir($dir);
                                }
                            ?>
                      </select></td>
                    </tr>
                    <tr>
                        <td class="row1">Select Language:</td>
                        <td class="row2">&nbsp;<?php Do_Languages(); ?></td>
                    </tr>
                    <tr>
                        <td class="row1">Clear Existing Dictionary:</td>
                        <td class="row2"><input type="checkbox" name="Clear_Dictionary"></td>
                    </tr>
                  <tr>
                        <td class="row1">Force Safe Mode:</td>
                        <td class="row2"><input type="checkbox" name="SM"></td>
                   </tr>
                   <tr>
                        <td colspan="2" class="catBottom" align="center"><input class="mainoption" type="submit" Value="Submit" onclick="return(submitform(this));"></td>
                  </tr>
                </table>
              </form>

                <form name="WordForm" method="post">
                <table cellpadding="3" cellspacing="1" width="100%" class="forumline">
                      <tr>
                        <th colspan="2" class="thHead">Add New Word</th>
                   </tr>
                   <tr>
                        <td class="row1">Word to add:</td>
                        <td class="row2"><input type="hidden" name="formtype" value="2"><input class="post" type="text" name="New_Word_To_Add" value=""></td>
                   </tr>
                   <tr>
                        <td class="row1">Language:</td>
                        <td class="row2">&nbsp;<?php Do_Languages(); ?></td>
                   </tr>
                   <tr>
                        <td colspan="2" class="catBottom" align="center"><input class="mainoption" type="submit" Value="Add Word"></td>
                   </tr>
                </table>
                </form>

<?php
   } else {
    echo "<span style=\"font:8pt verdana\">";
   }

$words_added=0;
$words_processed=0;

function Do_Languages()
{
  global $Spell_Config;
  if (Count($Spell_Config["Languages_Supported"]) == 1) {
    echo "<input type=\"hidden\" name=\"CL\" value=\"0\">";
    echo $Spell_Config["Languages_Supported"][0];
  }
  else {
    echo "<select name=\"CL\">";
    for ($i=0;$i<count($Spell_Config["Languages_Supported"]);$i++) {
      echo "<option value=\"$i\">".$Spell_Config["Languages_Supported"][$i];
    }
    echo "</select>";
  }
}

function Add_Word($word_to_add)
{
  global $words_added, $words_processed, $safe_mode;

  $word_to_add = strtolower($word_to_add);

  if (DB_Check_Word($word_to_add)) return (false);

  $tr_word_to_add = Translate_Word($word_to_add);
  $metacode = Word_Sound_Function($tr_word_to_add);

  DB_Add_Word($word_to_add, $metacode);
  $words_added++;
}

function Install_Dictionary($Dictionary, $Dictionary_Offset=0)
{
  global $words_processed;
  global $start_time, $safe_mode, $exec_time;

  $last_time = 0;

  // Create the Table
  if ($Dictionary_Offset == 0) {
    DB_Create_Table();
  }

  // Open the File
  $FileSize = filesize($Dictionary);
  $fp = fopen($Dictionary,"r");
  if (!$fp) {
    message_die(CRITICAL_ERROR, "Unable to open dictionary file: ".$Dictionary);
  }
  if ($Dictionary_Offset != 0) fseek($fp, $Dictionary_Offset);
  while (!feof($fp)) {
    $data = trim(fgets($fp, 4096));
    if ($data != "") {
      $words_processed++;
      Add_Word($data);
      $end_time = time() - $start_time;
      if ($end_time > $last_time) {
       $loc = ftell($fp);
       $Percent = round(($loc / $FileSize)*100, 0);
       echo "Processed: $words_processed... (".$Percent."%)<br>";
       flush();
      }
      $last_time = $end_time;

      if ($end_time > $exec_time) { // && $safe_mode) {
        $loc = ftell($fp);
        fclose($fp);
        SafeMode_Script($Dictionary, $loc);
      }
    }
  }
  fclose($fp);
}

function SafeMode_Script($Dictionary, $Offset)
{
   global $words_added, $words_processed, $SM, $CL;
   echo "<br><b>Safe mode</b> is enabled or <b>Max execution time</b> is a hard coded value on this server.<br>The dictionary installation process will require you to continue several times to install all the words in this dictionary.&nbsp;&nbsp;";
   echo "</span></body>";
   echo "<form name=\"sfmform\" method=\"post\">";
   echo "<input type=\"hidden\" name=\"Install_Dictionary\" value=\"$Dictionary\">";
   echo "<input type=\"hidden\" name=\"Offset\" value=\"$Offset\">";
   echo "<input type=\"hidden\" name=\"WP\" value=\"$words_processed\">";
   echo "<input type=\"hidden\" name=\"WA\" value=\"$words_added\">";
   echo "<input type=\"hidden\" name=\"CL\" value=\"$CL\">";
   if (isset($SM)) echo "<input type=\"hidden\" name=\"SM\" value=\"1\">";
   echo "<center><input class=\"mainoption\" type=\"submit\" name=\"Continue\" value=\"Continue\"></center>";
   echo "</form>";
   echo "</html>";
   exit;
}

function Clear_Dictionary()
{
  DB_Drop_Table();
  echo "Existing Table &amp; data has been removed from your database.<br><br>";
}

// Main Routine
if (isset($New_Word_To_Add)) {
  $New_Word_To_Add = stripslashes($New_Word_To_Add);
  Add_Word($New_Word_To_Add);
  echo "<br><b><font size=+1>Word Added ($Current_Language)....</font></b><br>";
  echo "Click <a href=\"spell_admin.".$phpEx."\">Here</a> to install another language or another word.";
}
if (isset($Clear_Dictionary)) Clear_Dictionary();
if (isset($Install_Dictionary) && $Install_Dictionary != "NONE") {
  if (isset($Offset)) {
    echo "<b>Continuing Installation of $Install_Dictionary...</b><br>";
    if (isset($WP)) $words_processed = $WP;
    if (isset($WA)) $words_added = $WA;
    Install_Dictionary($Install_Dictionary, $Offset);
  } else {
    echo "<b>Installing $Install_Dictionary ($Current_Language)...</b><br>";
    Install_Dictionary($Install_Dictionary);
  }
  echo "<br><b><font size=+1>Completed Installation of $Install_Dictionary...</font></b><br>";
  echo "Processed a total of <b>$words_processed</b> words, added a total of <b>$words_added</b>.<br>";
  echo "<br>Please make sure you delete this file after all installations are complete!<br><br>";
  echo "Click <a href=\"spell_admin.".$phpEx."\">Here</a> to install another dictionary or another word.";
}

?>
</span>
</body>
</html>