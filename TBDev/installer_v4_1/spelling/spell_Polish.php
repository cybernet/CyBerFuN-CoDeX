<?php
  // --------------------------------------------------------------------
  // phpSpell Language Template
  //
  // This is (c)Copyright 2002-2008, Team phpSpell.
  // --------------------------------------------------------------------

  // --------------------------
  // Table Name
  // --------------------------
  $DB_TableName="polish_spelling_words";
  $Meta_Language = "iso-8859-2";

  // Language Text
  $Language_Text = array('Przeskanowano %d s³ów.    Znaleziono %d s³ów do poprawienia.');
  $Language_Javascript = array('Sprawdznie dokumentu...','Nie znaleziono','OK','Anuluj','Sprawdzanie s³ownika zakoñczone','Popraw','Wszystkie','Ignoruj','Naucz','Sugeruj','Definicja','S³ownik','Poprawienie wyrazu','Brak sugestii');

  // Prefix Database name for MSSQL tables
//  if ($dbms == "mssql") {
//    $DB_TableName = $dbname.".".$DB_TableName;
//  }

  // ---------------------------------------
  // PSPELL Support - Use English Dictionary
  // ---------------------------------------
  $Spell_Config["PSPELL_LANGUAGE"] = "pl";
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
  $Translation_Table = array();
  $Replacement_Table = array();

  // --------------------------------------------------------------------
  // Put the list of valid characters in your language in this list
  // --------------------------------------------------------------------
  $Language_Character_List = "a±bcædeêfghijkl³mnñoópqrs¶tuvwxyz¿¼'";

  // --------------------------------------------------------------------
  // Put the list of most common words in this list ",word,word,word,"
  // --------------------------------------------------------------------
  //$Language_Common_Words = ",the,is,was,be,are,were,been,being,am,of,and,a,an,in,inside,to,have,has,had,having,he,him,his,it,its,i,me,my,to,they,their,not,no,for,you,your,she,her,with,on,that,these,this,those,do,did,does,done,doing,we,us,our,by,at,but,from,as,which,or,will,said,say,says,saying,would,what,there,if,can,who,whose,so,go,gone,went,goes,more,other,another,one,see,saw,seen,seeing,know,knew,known,knows,knowing,there,";
  $Language_Common_Words = ",one,jeden,dwa,trzy,cztery,piêæ,sze¶æ,siedem,osiem,dziewiêæ,dziesiêæ,wy,jest,by³,b±d¼,s±,byli,by³y,bêd±c,jestem,z,i,w,wewnêtrzny,do,mieæ,ma,mia³,maj±c,on,jego,to,ja,mój,oni,ich,nic,nie,dla,ty,twój,ona,jej,dalej,¿e,te,tu,zrób,zrobi³y,robi,zrobiony,robi±c,mój,my,za,o,pod,przed,nasi,oko³o,oko,ale,od,kto,jako,który,albo,powiedzieæ,g³osy,mówi±c,co,tam,je¿eli,mo¿e,tak,idzie,posz³y,poszed³,wiêcej,inny,jeden,widzi,widzia³,widz±c,wiedzieæ,wiedzia³,rozpoznany,wie,wiedz±c,tam,";

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
     }    return ($Data);
  }

  function Language_Lower(&$Data)
        {
        $Data=strtr($Data, "¡ÆÊ£ÑÓ¦¯¬", "±æê³ñó¶¿¼");
        return(strtolower($Data));
        }

  function Language_Upper(&$Data)
        {
        $Data=strtr($Data, "±æê³ñó¶¿¼", "¡ÆÊ£ÑÓ¦¯¬");
        return(strtoupper($Data));
        }
?>