<?php
  // --------------------------------------------------------------------
  // phpSpell Language Template
  //
  // This is (c)Copyright 2002-2008, Team phpSpell.
  // --------------------------------------------------------------------

  // --------------------------
  // Table Name
  // --------------------------
  $DB_TableName=$table_prefix."spelling_words";

  // Language Text
  $Language_Text = array('Suche nach %d Wörtern.    %d zu korrigierende Wörter gefunden.');
  $Language_Javascript = array('Bitte Warten','Keine Störungen gefunden...', 'OK','Abbrechen','Rechtschreibprüfung abgeschlossen','Korrigieren', 'Alle','Ignorieren','Hinzufügen','Vorschlagen', 'Definition','Thesaurus','Ändern in:','Keine Vorschläge');

  // Prefix Database name for MSSQL tables
//  if ($dbms == "mssql") {
//    $DB_TableName = $dbname.".".$DB_TableName;
//  }

  // ---------------------------------------
  // PSPELL Support - Use German Dictionary
  // ---------------------------------------
  $Spell_Config["PSPELL_LANGUAGE"] = "de";
  // --------------------------------------------------------------------
  // Example translation table:
  //     $Translation_Table = array("À", "Æ", "Ç");
  //     $Replacement_Table = array("a", "an", "sth");
  //     $Language_Translation_Character_List = "ÀÆÇ";
  // --------------------------------------------------------------------
  // for every "À" it finds in a word it will replace it with a "a"
  // for every "Ç" it finds it will replace it with a "sth"
  // for every "Æ" it finds it will replace it with a "an"
  // --------------------------------------------------------------------
  // Put the character(s) to be translated into the Translation_Table
  // Put the replacement character(s) into the replacement table
  // --------------------------------------------------------------------
  // The replacement string should be equivelent to the ENGLISH PHONETIC
  // sound.  So if you were to take a word with "À" in it; how would you
  // phonetically spell the word in english.  If the "À" sounds like a "A"
  // in english then "A" would be the replacement character.
  // If it sounds like "th" then you would use "th" as the characters.
  // always replace Larger groups first.  (i.e. if "ññ" sounds differently
  // than "ñ" then in the translation table you would have the "ññ" listed
  // before the "ñ".  So that way when it would replaced the "ññ" before it
  // replaced it twice with "ñ".
  // --------------------------------------------------------------------
  // Any letters you do not translate will be IGNORED for
  // when it attempts to find spelling matches!!!
  // --------------------------------------------------------------------
  //$Translation_Table = array("Ä", "ä", "Ö", "ö", "Ü"," ü", "ß");
  //$Replacement_Table = array("Ae", "ae", "Öe", "ö", "Üe", "ü", "ss");
  $Translation_Table = array();
  $Replacement_Table = array();

  // --------------------------------------------------------------------
  // Put the list of valid characters in your language in this list
  // --------------------------------------------------------------------
  $Language_Character_List = "abcdefghijklmnopqrstuvwxyzäöüßÄÖÜ'";
  $Language_Common_Words = "der,die,das,ist,war,sein,sind,waren,bin,von,vom,und,ein,eine,einer,innen,zu,zum,haben,hat,habe,hatten,er,sie,es,seiner,seine,seines,ich,mein,mir,mich,wir,unser,unsere,euer,eures,ihnen,nicht,nein,für,du,deins,ihrs,mit,auf,dieses,dies,jeses,tun,tat,getan,bei,beim,aber,leider,jedoch,von,als,oder,wird,sagen,sagte,sage,würde,würdest,was,dort,hier,wenn,kann,wer,wessen,so,gehen,geht,gegangen,mehr,anders,andere,eins,sehen,sah,gesehen,wissen,weiß,wußte";

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