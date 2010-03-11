<?php
  // --------------------------------------------------------------------
  // phpSpell Language Template
  //
  // This is (c)Copyright 2002-2008, Team phpSpell.
  // --------------------------------------------------------------------

  // --------------------------
  // Table Name
  // --------------------------
  $DB_TableName=$table_prefix.'spelling_words';

  // Language Text
  $Language_Text = array('Scanned %d words.    Found %d words to be corrected.');
  $Language_Javascript = array('Checking Document...','No misspellings found','OK','Cancel','Spell Check Completed','Correct','All','Ignore','Learn','Suggest','Definition','Thesaurus','Word correction','No Suggestions');

  // Prefix Database name for MSSQL tables
//  if ($dbms == "mssql") {
//    $DB_TableName = $dbname.".".$DB_TableName;
//  }

  // ---------------------------------------
  // PSPELL Support - Use English Dictionary
  // ---------------------------------------
  $Spell_Config['PSPELL_LANGUAGE'] = 'en';
  // --------------------------------------------------------------------
  // Example translation table:
  //     $Translation_Table = array("А", "Ж", "З");
  //     $Replacement_Table = array("a", "an", "sth");
  //     $Language_Translation_Character_List = "АЖЗ";
  // --------------------------------------------------------------------
  // for every "А" it finds in a word it will replace it with a "a"
  // for every "З" it finds it will replace it with a "sth"
  // for every "Ж" it finds it will replace it with a "an"
  // --------------------------------------------------------------------
  // Put the character(s) to be translated into the Translation_Table
  // Put the replacement character(s) into the replacement table
  // --------------------------------------------------------------------
  // The replacement string should be equivelent to the ENGLISH PHONETIC
  // sound.  So if you were to take a word with "А" in it; how would you
  // phonetically spell the word in english.  If the "А" sounds like a "A"
  // in english then "A" would be the replacement character.
  // If it sounds like "th" then you would use "th" as the characters.
  // always replace Larger groups first.  (i.e. if "сс" sounds differently
  // than "с" then in the translation table you would have the "сс" listed
  // before the "с".  So that way when it would replaced the "сс" before it
  // replaced it twice with "с".
  // --------------------------------------------------------------------
  // Any letters you do not translate will be IGNORED for
  // when it attempts to find spelling matches!!!
  // --------------------------------------------------------------------
  $Translation_Table = array();
  $Replacement_Table = array();

  // --------------------------------------------------------------------
  // Put the list of valid characters in your language in this list
  // --------------------------------------------------------------------
  $Language_Character_List = "abcdefghijklmnopqrstuvwxyz'";
  $Language_Common_Words = ',the,is,was,be,are,were,been,being,am,of,and,a,an,in,inside,to,have,has,had,having,he,him,his,it,its,i,me,my,to,they,their,not,no,for,you,your,she,her,with,on,that,these,this,those,do,did,does,done,doing,we,us,our,by,at,but,from,as,which,or,will,said,say,says,saying,would,what,there,if,can,who,whose,so,go,gone,went,goes,more,other,another,one,see,saw,seen,seeing,know,knew,known,knows,knowing,there,';

  // --------------------------------------------------------------------
  // Translation function
  // --------------------------------------------------------------------
  function Translate_Word($Word) {
    return ($Word);
  }

  // --------------------------------------------------------------------
  // Phonetic work function
  // --------------------------------------------------------------------
  function Word_Sound_Function($Word) {
    return (metaphone($Word));
  }


  function Language_Decode(&$Data)
  {
    // MS Internet Explorer Hack -- IE sends utf8-unicode for upper (ascii 128+) characters
     if (strpos(@$_SERVER['HTTP_USER_AGENT'], 'MSIE') > 0 || strpos(@$_SERVER['ALL_HTTP'], 'MSIE') > 0) {
       if (function_exists('utf8_decode')) $Data = utf8_decode($Data);
     }
     return ($Data);
  }

  function Language_Encode(&$Data)
  {
    global $Spell_Config;
    if (!$Spell_Config['IE_UTF_Encode']) return ($Data);
     if (strpos(@$_SERVER['HTTP_USER_AGENT'], 'MSIE') > 0 || strpos(@$_SERVER['ALL_HTTP'], 'MSIE') > 0) {
       if (function_exists('utf8_encode')) $Data = utf8_encode($Data);
     }
    return ($Data);
  }

  function Language_Lower(&$Data)
  {
    return(strtolower($Data));
  }

  function Language_Upper(&$Data)
  {
    return(strtoupper($Data));
  }

?>