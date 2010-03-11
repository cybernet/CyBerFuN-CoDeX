<?php
  // --------------------------------------------------------------------
  // phpSpell Language Template
  //
  // This is (c)Copyright 2002-2008, Team phpSpell.
  // --------------------------------------------------------------------

  // --------------------------
  // Table Name
  // --------------------------
  $DB_TableName="russian_spelling_words";
  $Meta_Language = "windows-1251";

  // Language Text  (Recommend that they are converted to HTML entities - that way they should display in all browsers properly)
  $Language_Text = array('Scanned %d words.    Found %d words to be corrected.');
  $Language_Javascript = array('&#1055;&#1088;&#1086;&#1074;&#1077;&#1088;&#1082;&#1072; &#1090;&#1077;&#1082;&#1089;&#1090;&#1072;',
                               '&#1053;&#1077;&#1090; &#1086;&#1096;&#1080;&#1073;&#1086;&#1082;',
                               'OK',
                               '&#1054;&#1090;&#1084;&#1077;&#1085;&#1080;&#1090;&#1100;',
                               '&#1053;&#1077;&#1090; &#1087;&#1088;&#1077;&#1076;&#1083;&#1086;&#1078;&#1077;&#1085;&#1080;',
                               '&#1048;&#1089;&#1087;&#1088;&#1072;&#1074;&#1080;&#1090;&#1100;',
                               '&#1042;&#1089;&#1077;',
                               '&#1055;&#1088;&#1086;&#1087;&#1091;&#1089;&#1090;&#1080;&#1090;&#1100;',
                               'Learn','Suggest','Definition','Thesaurus',
                               '&#1055;&#1088;&#1072;&#1074;&#1082;&#1072; &#1089;&#1083;&#1086;&#1074;&#1072;',
                               'No Suggestions');

  // ---------------------------------------
  // PSPELL Support - Use English Dictionary
  // ---------------------------------------
  $Spell_Config["PSPELL_LANGUAGE"] = "ru";

  // --------------------------------------------------------------------
  // Example translation table:
  //     $Translation_Table = array("А", "Ж", "З");
  //     $Replacement_Table = array("a", "an", "sth");
  //     $Language_Translation_Character_List = "АЖЗ";
  // --------------------------------------------------------------------
  // for every "А" it finds in a word it will replace it with a "a"
  // for every "З" it finds it will replace it with a "sth"
  // for every "Ж" it finds it will replace it with a "Ж"
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
  $Translation_Table = array("а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х",  "ц",  "ч",  "ш",  "щ", "ы", "э", "ю", "я", "ь");
  $Replacement_Table = array("a", "b", "v", "g", "d", "e", "o", "j", "z", "i", "y", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "kh", "ts", "ch", "sh", "shch", "i", "e", "u", "a", "");

  $Language_Translation_Character_List = "абвгдеёжзийклмнопрстуфхцчшщыэюя";

  // --------------------------------------------------------------------
  // Put the list of valid characters in your language in this list
  // --------------------------------------------------------------------
  $Language_Character_List = "абвгдеёжзийклмнопрстуфхцчшщъыьэюя";

  // --------------------------------------------------------------------
  // Put the list of most common words in this list ",word,word,word,"
  // --------------------------------------------------------------------
  $Language_Common_Words = ",а,без,более,бы,был,была,были,было,быть,в,вам,вас,весь,во,вот,все,всего,всех,вы,где,да,даже,для,до,его,ее,если,есть,еще,же,за,здесь,и,из,или,им,их,к,как,ко,когда,кто,ли,либо,мне,может,мы,на,надо,наш,не,него,нее,нет,ни,них,но,ну,о,об,однако,он,она,они,оно,от,очень,по,под,при,с,со,так,также,такой,там,те,тем,то,того,тоже,той,только,том,ты,у,уже,хотя,чего,чей,чем,что,чтобы,чье,чья,эта,эти,это,я,д,е,ё,ж,з,й,л,м,н,р,т,ф,х,ц,ч,ш,щ,ы,э,ю,";

  // --------------------------------------------------------------------
  // Translation function
  // --------------------------------------------------------------------
  function Translate_Word($Word) {
    global $Translation_Table, $Replacement_Table;

    $New_Word = str_replace($Translation_Table, $Replacement_Table, $Word);
//    echo "New: $New_Word<br>";
    return ($New_Word);
  }

  // --------------------------------------------------------------------
  // Phonetic work function
  // --------------------------------------------------------------------
  function Word_Sound_Function($Word) {
    return (metaphone($Word));
  }

  // Based off of the code by bn2@ukr.net
  function Language_Decode($Data)
  {
    global $Encode_Type;
    $Output='';
    $FirstByte='';
    $MultiByte=false;
    $Counter = strlen($Data);

    $Pos1 = strpos($Data, '&#10');
    $Pos2 = strpos($Data, '&#11');
    if ($Pos1 !== false || $Pos2 !== false) {
      if ($Pos1 === false) $Pos1 = $Pos2;
      if ($Data[$Pos1+6] == ';') {
         $Encode_Type = 2;
         if (version_compare("4.3.2", phpversion(), "<=")) {
           $Output = html_entity_decode($Data, ENT_NOQUOTES, "cp1251");
         } else {
           echo "<!-- Second -->";
//           $Output = myhtml_entity_decode($Data);
         }
      }
    }

    if ($Encode_Type == 0) {
      for ($i=0;$i<$Counter;$i++) {
        $Code = ord($Data[$i]);
        if ($Code <= 127) $Output .= $Data[$i];
        else {
          if ($MultiByte) {
            $Conv_2=($FirstByte&3)*64+($Code&63);
            $Conv_1=($FirstByte>>2)&5;
            $Converted=$Conv_1*256+$Conv_2;
            if ($Converted==1025) $NewCharacter=168;
            else if ($Converted==1105) $NewCharacter=184;
            else $NewCharacter=$Converted-848;
            $Output.=chr($NewCharacter);
            $MultiByte=false;
          } else if (($Code>>5)==6) {
            $Encode_Type = 1;
            $FirstByte=$Code;
            $MultiByte=true;
          } else $Output .= $Data[$i];
        }
      }
    }
    return $Output;
  }

  function Language_Encode($Data)
  {
    global $Encode_Type;
    if ($Encode_Type == 0) return ($Data);
    $Output = '';

    if ($Encode_Type == 3) {
      $Count = strlen($Data);
      for ($i=0;$i<$Count;$i++) {
         $Byte = ord($Data[$i]);
         if ($Byte <= 127) $Output .= $Data[$i];
         else if ($Byte >= 192 && $Byte <= 239) $Output .= chr(208).chr($Byte-48);
         else if ($Byte >= 240 && $Byte <= 255) $Output .= chr(209).chr($Byte-112);
         else if ($Byte == 184) $Output .= chr(209).chr(209);
         else if ($Byte == 168) $Output .= chr(208).chr(129);
      }
    }
    if ($Encode_Type == 2 || $Encode_Type == 1) { // || $Encode_Type == 1) {
       if (version_compare("4.3.2", phpversion(), "<=")) {
         $Output = htmlentities($Data, ENT_NOQUOTES, "cp1251");
       } else {
         $Output = $Data;
//           $Output = myhtml_entity_encode($Data);
       }
    }
    return ($Output);
  }

  function Language_Upper(&$Data)
  {
     $New_Data = strtoupper($Data);
     return ($New_Data);
  }

  function Language_Lower(&$Data)
  {
     // Translate
     $New_Data = strtr($Data, 'ЧЯЁ','чяё');
     $New_Data = strtolower($New_Data);
     return ($New_Data);
  }

?>