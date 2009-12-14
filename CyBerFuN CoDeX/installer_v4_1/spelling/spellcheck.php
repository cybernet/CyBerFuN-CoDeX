<?php
  // --------------------------------------------------------------------
  // phpSpell 1.06r (beta) Spelling Engine
  //
  // This is (c)Copyright 2002-2008, Team phpSpell.
  // --------------------------------------------------------------------

  // Ok, lets give us 5 Minutes total
  // the end user probably won't even have the patience for 5 minutes
  @set_time_limit(300);

  $mtime = microtime();
  $mtime = explode(' ',$mtime);
  $mtime = $mtime[1] + $mtime[0];

  include 'spell_config.php';
  if (!defined('PHPSPELL_CONFIG')) exit;

  $starttime = $mtime;



  // For Newer versions of PHP which don't globalize the variables
  // We want these globalized.  :)
  if (isset($_REQUEST['inputtext'])) $Document = $_REQUEST['inputtext'];
  else if (isset($HTTP_GET_VARS['inputtext'])) $Document = $HTTP_GET_VARS['inputtext'];
  else if (isset($HTTP_POST_VARS['inputtext'])) $Document = $HTTP_POST_VARS['inputtext'];

  if (isset($_REQUEST['Suggest'])) $Suggest = $_REQUEST['Suggest'];
  else if (isset($HTTP_GET_VARS['Suggest'])) $Suggest = $HTTP_GET_VARS['Suggest'];
  else if (isset($HTTP_POST_VARS['Suggest'])) $Suggest = $HTTP_POST_VARS['Suggest'];

  if (isset($_COOKIE['SpellLearned'])) $SpellLearned = urldecode($_COOKIE['SpellLearned']);
  else if (isset($HTTP_COOKIE_VARS['SpellLearned'])) $SpellLearned = urldecode($HTTP_COOKIE_VARS['SpellLearned']);

  if (isset($_COOKIE['SpellSettings'])) {
    $User_Settings = explode(',',$_COOKIE['SpellSettings']);
  } else if (isset($HTTP_COOKIE_VARS['SpellSettings'])) {
    $User_Settings = explode(',',$HTTP_COOKIE_VARS['SpellSettings']);
  } else {
    $User_Settings = array(-1,   // Language (Not Set / Default)
                           -1,   // Levenshire Distance
                           -1,   // Theasures Site
                           -1);   // Dictionary Site
  }

  // Set Levenshtein Distance for this user
  if ($User_Settings[1] >= 0 && $Spell_Config['Max_User_Levenshtein_Distance'] >= $User_Settings[1]) {
    $Spell_Config['Levenshtein_Distance'] = $User_Settings[1];
  }

  // Language Support
  if ($Spell_Config['Default_Language'] == '') {
    echo 'Configuration file is missing language setting.<br>Please set \$Spell_Config[\'Default_Language\'] to your language in your configuration.';
    exit;
  }
  if (!isset($Spell_Config['Languages_Supported'][$User_Settings[0]])) $Current_Language = $Spell_Config['Default_Language'];
  else $Current_Language = $Spell_Config['Languages_Supported'][$User_Settings[0]];

  include_once ('spell_'.$Current_Language.'.'.$phpEx);
  $valid_charlist = $Language_Character_List;

  // Setup Document Variables
  $Encode_Type = 0;
  if (isset($Document))  {
//    echo '<!-- '.$Document.' -->';
    $Document = Language_Decode($Document);
    $Document = html_entity_remove($Document);
//    $New = Language_Lower($Document);
//    echo '<!-- '.$New.' -->';
  } else {
    $Document = ' Error: Unable to spell check at this time.';
  }

  // Setup Suggest Word
  if (isset($Suggest)) {
    $Encode_Type = 0;
    $Suggest = Language_Decode($Suggest);
    $Suggest = html_entity_remove($Suggest);
    $Document = ' '.$Suggest;
  }

  // Remove first '.' character that is used so that browser doesn't delete enters
  // Add Trailing Space for End of Document Check clearing of Words.
  $Document = stripslashes(substr($Document,1)).' ';

  // Setup Spell Learned (Cookie Variable for what words the user has learned)
  if (!isset($SpellLearned)) $SpellLearned = '';
  if ($Encode_Type > 0) {
    $SpellLearned = Language_Decode($SpellLearned);
  }

  // Set up Skipped Words
  $Skipped_Words = ','.$Language_Common_Words.','.strtolower(addslashes($SpellLearned)).',';

  // Globals
  $wc_count = 0;   // Word Count
  $bw_count = 0;   // Bad Words
  $lb_count = 0;   // Line Breaks

  $corrected_words = '';      // Corrected Word Code
  $lb_words = '';             // Line Break Word Code
  $Fixed_Words_Table=array(); // Fixed Words Table

  // Start the Scan of the Document for valid words
  $Browser = 'Opera/6';
  if (isset($_SERVER['HTTP_USER_AGENT'])) $Browser = $_SERVER['HTTP_USER_AGENT'];
  else if (isset($HTTP_SERVER_VARS['HTTP_USER_AGENT'])) $Browser = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];


  echo '<html><head><title>Spellcheck '.PHPSPELL_VERSION.'</title>';
//  if (isset($Meta_Language)) echo '<meta http-equiv="Content-Type" content="text/html; charset='.$Meta_Language.'">';
  echo '<script language="javascript"><!--'.LINE_FEED;
  $Count = count($Language_Javascript);
  for ($i=0;$i<$Count;$i++) {
     echo 'parent.Language_Text['.$i.'] = "'.$Language_Javascript[$i].'";'.LINE_FEED;
  }

  if (isset($Suggest)) {
    Suggest_Word($Suggest);
    echo $corrected_words;
    echo LINE_FEED.'--></script>'.LINE_FEED;
    echo '</head>';
    echo '<body bgcolor="#ffffff" onload="parent.Run_Suggestion();"><nobr>';
  } else if (isset($Dictionary)) {
    //
  } else if (isset($Thesaurus)) {
    //
  } else {
    Scan_Document($Document);
    if (strpos($Browser, 'Opera/6') !== false) {
      echo 'function OGC() {return (document.forms(0).inputtext);}'.LINE_FEED;
      $Not_Opera = '';
    } else {
      $Not_Opera = 'visibility:hidden;';
    }

    echo 'parent.Bad_Word_Count='.$bw_count.';'.LINE_FEED;
    echo 'parent.Scanned_Words='.$wc_count.';'.LINE_FEED;
    echo 'parent.Line_Break_Count='.$lb_count.';'.LINE_FEED.LINE_FEED;

    // Output the data the way the Spell checking script expects it
    echo $lb_words;
    echo $corrected_words;
    echo LINE_FEED.'--></script>'.LINE_FEED;
    echo '</head>';
    echo '<body bgcolor="#1F7FB0" onload="parent.Run_Spellchecker();">';
    $data = sprintf($Language_Text[0], $wc_count, $bw_count);
    echo '<nobr><table width=100% border=0 cellpadding=0 cellspacing=0 style="color:#ffffff;font-size : 11px; font-family: Verdana, Arial;"><tr><td>'.$data.'</td><td align=right>'.PHPSPELL_VERSION.'</td></tr></table>';
    if (strpos($Browser, 'Opera/6') !== false) {
      echo '<br><br>';
    }
    echo '<form name="iform"><textarea cols=1 rows=1 name=inputtext style="font:verdana;color:blue;'.$Not_Opera.'"> </textarea></form>';
  }


  $mtime = microtime();
  $mtime = explode(' ',$mtime);
  $mtime = $mtime[1] + $mtime[0];
  $endtime = $mtime;
  $totaltime = ($endtime - $starttime);

  echo LINE_FEED.'<!-- Total Time: '.$totaltime.' --></nobr></body></html>';
  exit;
  // --------------------
  // End Output & Script
  // --------------------

// -------------------------------------
// Functions
// -------------------------------------


function Do_Check_Word($word_to_check, $word_location)
{
  global $DB_TablePrefix, $Spelling_DB, $DB_TableName;
  global $wc_count, $corrected_words, $bw_count, $lb_count, $lb_words;
  global $Spell_Config;
  global $Fixed_Words_Table, $Skipped_Words, $Document;

  $wc_count++;

  // Check Common Word List & SpellLearned List
  if (strpos($Skipped_Words, ','.$word_to_check.',') !== false) return (false);

  // Check to see if we already spell checked this word
  if (isset($Fixed_Words_Table[$word_to_check])) {
    $word_length = strlen($word_to_check);
    $original_word_to_check = substr($Document, $word_location, $word_length);
    $corrected_words .= 'parent.Bad_Words['.$bw_count.'] = new parent.Add_Word('.$word_location.','.$word_length.',"'.Language_Encode($original_word_to_check).'"';
    $corrected_words .= $Fixed_Words_Table[$word_to_check];
    $corrected_words .= ');'.LINE_FEED;
    $bw_count++;
    return (false);
  }

  // Search for word in Main table
  if (DB_Check_Word($word_to_check)) {
    $Skipped_Words .= $word_to_check.',';
    return (false);
  }


  // Drop ('s) or just (s) and see if we find a match
  $last_char = substr($word_to_check, -1);
  if ($Spell_Config['Enable_Drop_S_Support'] && $last_char == 's') {

    $last_char = substr($word_to_check, -2, 1);
    if ($last_char == '\'') {
      $tr_word_to_check = substr($word_to_check, 0, -2);
    } else {
      $tr_word_to_check = substr($word_to_check, 0, -1);
    }
    if ($last_char != 's') {  // Ignore Double ss words
      if (DB_Check_Word($tr_word_to_check)) {
        $Skipped_Words .= $word_to_check.',';
        return (false);
      }
    }
  }

  // Word Not found -- now find matches
  $tr_word_to_check = Translate_Word($word_to_check);
  $word_sound = Word_Sound_Function($tr_word_to_check);

  $word_length = strlen($word_to_check);

  // Create The (Suggestions) Word List
  $original_word_to_check = substr($Document, $word_location, $word_length);
//  $original_word_to_check = $word_to_check;
  $corrected_words .= 'parent.Bad_Words['.$bw_count.'] = new parent.Add_Word('.$word_location.','.$word_length.',"'.Language_Encode($original_word_to_check).'"';

  // Check Case
  $Word_Is_Case = 0;
  if (strtolower($original_word_to_check) == $original_word_to_check) $Word_Is_Case = 1;
  else if (strtoupper($original_word_to_check) == $original_word_to_check) $Word_Is_Case = 2;
  else if ($original_word_to_check{0} == strtoupper($original_word_to_check{0})) $Word_Is_Case = 3;

  // Sorting of Words
  $Correct_Word_Array = array();
  $Correct_Word_Tag = array();
  for ($i=0;$i<$Spell_Config['Levenshtein_Distance'];$i++) {
     $Correct_Word_Array[$i] = '';
     $Correct_Word_Tag[$i] = 0;
  }

  // Add Bad word to Count
  $bw_count++;

  $Good_Word_Array = DB_Get_Suggestions($word_sound, $word_to_check);
  $Count = count($Good_Word_Array);
  $Corrected_Word_Count = 0;
  for ($i=0;$i<$Count;$i++) {
    $TR_Fetched_Word = Translate_Word($Good_Word_Array[$i]);
    $Lev_Distance = levenshtein($tr_word_to_check, $TR_Fetched_Word);
    if ($Lev_Distance < $Spell_Config['Levenshtein_Distance']) {
      $Corrected_Word_Count++;
      if ($Word_Is_Case == 1) $Good_Word_Array[$i] = strtolower($Good_Word_Array[$i]);
      else if ($Word_Is_Case == 2) $Good_Word_Array[$i] = strtoupper($Good_Word_Array[$i]);
      else if ($Word_Is_Case == 3)  $Good_Word_Array[$i] = ucfirst($Good_Word_Array[$i]);
      $Correct_Word_Array[$Lev_Distance] .= ',"'.$Good_Word_Array[$i].'"';
    }
  }

  // Off by One Searching
  if ($Spell_Config['Off_By_One_Search'] == 2 || ($Spell_Config['Off_By_One_Search'] == 1 && $Corrected_Word_Count == 0)) {
    $Good_Word_Array = DB_Get_OBO_Suggestions($word_to_check);
    $Count = count($Good_Word_Array);
    for ($i=0;$i<$Count;$i++) {
      $TR_Fetched_Word = Translate_Word($Good_Word_Array[$i]);
      $Lev_Distance = levenshtein($tr_word_to_check, $TR_Fetched_Word);
      if ($Lev_Distance < $Spell_Config['Levenshtein_Distance']) {
        if ($Word_Is_Case == 1) $Good_Word_Array[$i] = strtoupper($Good_Word_Array[$i]);
        else if ($Word_Is_Case == 2)  $Good_Word_Array[$i] = ucfirst($Good_Word_Array[$i]);
        $Correct_Word_Array[$Lev_Distance] .= ',"'.$Good_Word_Array[$i].'"';
      }
    }
  }

  // Add words to list
  $Corrected_Word_List = '';
  for ($i=0;$i<$Spell_Config['Levenshtein_Distance'];$i++) {
    $Corrected_Word_List .= $Correct_Word_Array[$i];
  }
  $Corrected_Word_List = Language_Encode($Corrected_Word_List);
  $corrected_words .= $Corrected_Word_List . ');'.LINE_FEED;
  $Fixed_Words_Table[$word_to_check] = $Corrected_Word_List;

  return (true);
}

function Scan_Document(&$Document)
{
  global $Spell_Config;
  global $Browser;


  $lc_doc = $Document;
  $Add_Line_Breaks = false;
  // MSIE is the only one that has built in Line Breaking!
  // So we don't have to do line breaking
  if (strpos($Browser, 'MSIE') !== false) $Add_Line_Breaks=true;

  // Filter out Symbols (Case Sensitive)
  $Array_Count = count($Spell_Config['Symbol_Tags']);
  for ($i=0;$i<$Array_Count;$i++) {
    Clean_Document($Spell_Config['Symbol_Tags'][$i], NULL, $lc_doc);
  }

  // Lowercase the entire Document
  $lc_doc = language_lower($lc_doc);

  // Filter out BBCode
  if ($Spell_Config['USE_BBCODE']) {
    $Array_Count = count($Spell_Config['BBCODE_Tags']);
    for ($i=0;$i<$Array_Count;$i++) {
      if (is_array($Spell_Config['BBCODE_Tags'][$i])) {
        Clean_Document($Spell_Config['BBCODE_Tags'][$i][0], array($Spell_Config['BBCODE_Tags'][$i][1],']'), $lc_doc, false, 1);
      } else {
        Clean_Document($Spell_Config['BBCODE_Tags'][$i], ']', $lc_doc);
      }
    }
  }

  // Filter out html
  if ($Spell_Config['USE_HTML']) {
    $Array_Count = count($Spell_Config['HTML_Tags']);
    for ($i=0;$i<$Array_Count;$i++) {
      if (is_array($Spell_Config['HTML_Tags'][$i])) {
        Clean_Document($Spell_Config['HTML_Tags'][$i][0], array($Spell_Config['HTML_Tags'][$i][1],'>'), $lc_doc, false, 1);
      } else {
        Clean_Document($Spell_Config['HTML_Tags'][$i], '>', $lc_doc);
      }
    }
  }

  // Filter out Skip Words
  $Array_Count = count($Spell_Config['Skip_Word_Tags']);
  for ($i=0;$i<$Array_Count;$i++) {
    Clean_Document($Spell_Config['Skip_Word_Tags'][$i], array(' ', "\n", '[', '<', '"', '\'', ']', '>'), $lc_doc, $Add_Line_Breaks, 2);
  }

  // Split Words & Check them
  RX_Split_Word_Engine($lc_doc);
}

function RX_Split_Word_Engine($Document)
{
  global $valid_charlist;

  $Reg_Expression = '/[^'.$valid_charlist.']+/';
  $Words = preg_split($Reg_Expression, $Document, -1, PREG_SPLIT_NO_EMPTY);
  $Array_Count = count($Words);

  $Words[-1] = '';
  $Loc = 0;

  // Scan all remaining words
  for ($i=0;$i<$Array_Count; $i++) {
    // Strip ' from beginning & end
    if ($Words[$i]{0} == '\'') $Words[$i] = substr($Words[$i],1);
    if (substr($Words[$i],-1) == '\'') $Words[$i] = substr($Words[$i],0,-1);
    // Weird case where a ' gets counted as a word; and then gets eaten by the above removals
    $Word_Length = strlen($Words[$i]);
    if ($Word_Length > 0) {
      // Find actual Word Locations & Check word
      $Loc = strpos($Document, $Words[$i], $Loc+strlen($Words[$i-1]));
      Add_Line_Breaks($Loc, $Word_Length);
      Do_Check_Word($Words[$i], $Loc);
    }
  }
}


function Clean_Document($Open_Tag, $Close_Tag, &$Document, $Add_Line_Breaks=false, $Special=0)
{
  global $lb_words, $lb_count;

  $Found = -1;
  $Results = array();
  $Cnt = 0;
  do {
    $Found = strpos($Document, $Open_Tag, $Found+1);

    if ($Found !== false) {
       if ($Close_Tag === NULL) {
         $Results[0] = $Found+strlen($Open_Tag);
       } else if (is_array($Close_Tag)) {
         $ArrayCount = count($Close_Tag);
         for ($i=0;$i<$ArrayCount;$i++) {
           $Results[$i] = strpos($Document, $Close_Tag[$i], $Found+1);
           // Move the lowest number to the first location (If sorted)
           if ($Results[$i] !== false) {
             $Results[$i] += strlen($Close_Tag[$i]);
             if ($Results[0] === false || ($Results[$i] < $Results[0] && $Special != 1)) $Results[0] = $Results[$i];
           }
         }
         // Special Case for THE HTTP:// & WWW
         if ($Special == 2 && $Results[0] !== false) {
           $Results[0]--;
         }
       } else {
         $Results[0] = strpos($Document, $Close_Tag, $Found+1);
         if ($Results[0] !== false) $Results[0] += strlen($Close_Tag);
       }

       if ($Results[0] !== false) {
          $Count = ($Results[0] - $Found);
          $Document = substr_replace($Document, str_repeat(' ', $Count) , $Found, $Count);

          // Line Break Code
          if ($Add_Line_Breaks)  Add_Line_Breaks($Found, $Count);
       }
    }
  } while ($Found !== false);
}

function Add_Line_Breaks($Start, $Count)
{
  global $Spell_Config;
  global $lb_words, $lb_count;

  if ($Count <= $Spell_Config['Insert_Word_Wrap']) return;
  for ($i=$Spell_Config['Insert_Word_Wrap'];$i < $Count; $i += ($Spell_Config['Insert_Word_Wrap']+1)) {
     $Offset = $Start+$i;
     $lb_words .= 'parent.AddLineBreak['.$lb_count.'] = '.$Offset.';'.LINE_FEED;
     $lb_count++;
  }
}


function Suggest_Word($Word) {
  global $corrected_words;
  Do_Check_Word($Word, 0);
  $corrected_words = str_replace('Bad_Words[0]', 'Suggestion', $corrected_words);
  if (strpos($corrected_words, 'parent.Suggestion') === false) $corrected_words .= 'parent.Suggestion = new parent.Add_Word(0,"'.$Word.'","'.$Word.'");'.LINE_FEED;
}


// Used to Remove any HTML Entities that weren't converted by the Language Conversion
// If those characters weren't converted then they aren't valid.
// Just use '*' as a filler character to keep word/character alignment
function html_entity_remove($Data)
{
  $i=0;
   while (($i = strpos($Data, '&#', $i+1)) !== false) {
     $j = strpos($Data, ';', $i);
     if ($j !== false && $j < $i+8) {
       $Data = substr($Data, 0, $i).'*'.substr($Data, $j+1);
     }
   }
   $Data = str_replace('&amp;', '&', $Data);
   return ($Data);
}


?>