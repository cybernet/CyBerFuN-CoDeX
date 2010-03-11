// --------------------------------------------------------------------
// phpSpell Javascript
// v1.06r
//
// This is (c)Copyright 2002-2008, Team phpSpell.
// --------------------------------------------------------------------
// It is highly recommended that you do NOT modify this file at ALL!
// Language Translation is done in the spell_(language).php file
// --------------------------------------------------------------------

// Will be a User Setting Shortly
SpellSettings = 1;  // 0 = Show incorrect word in dialog
                    // 1 = Show first correct word in dialog (if any).

// PHP Extension
phpEx = "php";

// Detect the Browser
var ie4 = (document.all) ? 1:0;
var ns6=(navigator.userAgent.indexOf('Netscape6') > -1) ? 1:0;
var ns7=(navigator.userAgent.indexOf('Netscape7') > -1) ? 1:0;

// Find out if we are using Mozilla (pre)1.0
var gecko = (navigator.userAgent.indexOf('Gecko') > -1) ? 1:0;
var mz0=((navigator.userAgent.indexOf('rv:0.') > -1) && gecko) ? 1:0;
var mz1=((navigator.userAgent.indexOf('rv:1.') > -1) && gecko) ? 1:0;
var mz = mz1 | mz0;

var op6=(navigator.userAgent.indexOf('Opera/6') > -1) ? 1:0;
var op7=(navigator.userAgent.indexOf('Opera/7') > -1) ? 1 : (navigator.userAgent.indexOf('Opera 7') > -1) ? 1:0;
var op72=(navigator.userAgent.indexOf('Opera/7.2') > -1) ? 1 : (navigator.userAgent.indexOf('Opera 7.2') > -1) ? 1:(navigator.userAgent.indexOf('Opera/7.5') > -1) ? 1 : (navigator.userAgent.indexOf('Opera 7.5') > -1) ? 1:0;
var ns4=(navigator.userAgent.indexOf('Mozilla/4.7') > -1) ? 1:0;
var sf=(navigator.userAgent.indexOf('Safari') > -1) ? 1:0;
var sf3 = 0
if (sf) {
  if (navigator.userAgent.indexOf('Safari/5') > -1) {
    sf3 = 1;
  }
  if (navigator.userAgent.indexOf('Safari/6') > -1) {
    sf3 = 1;
  }
  
}

var macintosh=(navigator.userAgent.indexOf('Mac') > -1) ? 1:0;


// Opera; disable IE support
if (op7 || op6) {
  ie4 = 0;
}
// Safari Supposed to support Mozilla Dom objects???
if (sf) {
  ie4 = 0;
  gecko = 1;
}
// Disable Specific Mozilla Support if Netscape 6
if (ns6) mz = 0;

// Defaults
var Suggestion=new Object();
var Bad_Words=new Object();
var AddLineBreak=new Array();
var Word_Offset=0;
var Current_Word=0;
var Last_Location=0;
var Last_SizeOfWord=0;
var Bad_Word_Count=3;
var Scanned_Words=5;
var Last_Selected_Word=-1;
var Corrected_Output="";
var Line_Break_Count=0;
var BrowserFirstTime=1;
var Opera_iOption_Frame=null;
var Opera_iSpellMain_Frame=null;

// Styles
var Title_Body_Color = "#1F7FB0";
var Title_Style = "color:#ffffff;font-size : 11px; font-family: Verdana;";

// If this variable is undefined then we define it here
// Hopefully this will be defined prior (or it might be overwritten later)
if (typeof(Language_Text) == "undefined") {
  var Language_Text = new Array();
  Language_Text[0] = "Checking Document...";
  Language_Text[1] = "No misspellings found";
  Language_Text[2] = "OK";
  Language_Text[3] = "Cancel";
  Language_Text[4] = "Spell Check Completed";
  Language_Text[5] = "Correct";
  Language_Text[6] = "All";
  Language_Text[7] = "Ignore";
  Language_Text[8] = "Learn";
  Language_Text[9] = "Suggest";
  Language_Text[10] = "Definition";
  Language_Text[11] = "Thesaurus";
  Language_Text[12] = "Word correction";
  Language_Text[13] = "No Suggestions";

}
var CSS_Style="spelling.css";
if (ie4) CSS_Style="spelling-ie.css";
if (op7) CSS_Style="spelling-op7.css";
if (op6) {
  CSS_Style="spelling-op.css";
  var st="";
  for(var i = 0;i < self.document.getElementsByTagName('link').length;i++){
    var tempObj = self.document.getElementsByTagName('link').item(i);
    if((tempObj.rel)&&(tempObj.rel == 'stylesheet')){
      CSS_Style = tempObj.href;
    }
  }
}

function Add_Word(Word_Location, Word_Size, The_Bad_Word)
{
   this.Location = Word_Location;
   this.Size = Word_Size;
   this.Word = The_Bad_Word;
   this.Checked = false;
   this.Suggestions = new Array();
   this.Suggest_Count=0;
   k = Add_Word.arguments.length;
   if (k > 2) {
     for (j=3; j < k; j++) {
       this.Suggestions[this.Suggest_Count] = Add_Word.arguments[j];
       this.Suggest_Count++;
     }
   }
}

function Start_Spellchecker()
{
  if (ie4) odoc = document.ispellheader.document.open();
  if (sf) {
    var elems = document.getElementsByTagName('iframe');
    if (sf3) {
      oIframe = elems.item(0);
      odoc = oIframe.contentDocument;
    } else {
      odoc = elems.item(0).document;
    }
    odoc.open();
  }
  if (gecko && !sf) odoc = parent.ispellheader.document.open();
  if (op7) {
    var odoc = parent.ispellheader.document;
    odoc.open();
  }
  if (op6) {
    var iFrObj = self.document.getElementsByTagName('iframe').namedItem('ispellheader');
    iFrObj.open('', 'ispellheader');
    odoc = iFrObj.document;
  }
  odoc.write("<html><head><title>Start</title></head><body bgcolor='"+Title_Body_Color+"'><span style='"+Title_Style+"'>"+Language_Text[0]+"</span>");
  odoc.write("<form action='spellcheck."+phpEx+"' method='post' name='iform' id='iform' style='visibility:hidden'>");
  odoc.write("<textarea cols=1 rows=1 name=inputtext style='font:verdana;color:blue'>.");
  var LTFValue;
  if (ie4) LTFValue = parent.opener.LinkToField.value;
  if (gecko) LTFValue = top.opener.LinkToField.value;
  if (op7) LTFValue = top.opener.LinkToField.value;
  if (op6) {
    var LtF = self.opener.Opera_Get_Link();
    LTFValue = LtF.value;
  }
  if (op7) {
    LTFValue = OP7_Replace(LTFValue, "&", "&amp;");
  } else {
    LTFValue = LTFValue.replace(/&/g,"&amp;");
  }
  odoc.write(LTFValue);
  odoc.write("</textarea>");
  sentcount = LTFValue.length+1;
  odoc.write("<input type=hidden name=sentcount value='"+sentcount+"'>");
  if (!op6) {
    odoc.write("<input type='submit' name='submit' value='doccheck' style='visibility:hidden'></form>");
  } else {
    odoc.write("<input type='submit' value='doccheck' style='visibility:hidden'></form>");
    odoc.write("<scr"+"ipt language='javascript'> self.document.forms[0].submit(); </scr"+"ipt>");
  }
  odoc.write("</body></html>");
  odoc.close();
  if (ie4) {
    document.ispellheader.document.iform.submit.click();
  }
  if (gecko || op7) {
    parent.ispellheader.document.iform.submit.click();
  }
}

function Run_Suggestion()
{
    Build_Option_List(Current_Word-1, Suggestion);
    parent.Suggestion.Checked = true;
}


function Run_Spellchecker()
{
  if (ie4) {
    SpellString = parent.opener.LinkToField.value;
    Corrected_Output = document.ispellheader.document.iform.inputtext;
  }
  if (gecko || op7) {
    SpellString = top.opener.LinkToField.value;
    Corrected_Output = parent.ispellheader.document.iform.inputtext;
  }
  if (op6) {
    var LtF = self.opener.Opera_Get_Link();
    SpellString = LtF.value;
    Corrected_Output = document.ispellheader.OGC();
  }

  // Replace since the submit will have cleared it.  :)
  Corrected_Output.value = SpellString;
  Build_Display_Screen(0);

  if (Bad_Word_Count > 0) Check_Next_Word(false);
  else Do_No_Bad_Words(true);
}

function Build_Display_Screen(Bad_Word_ID)
{
  var SpellString = Corrected_Output.value;
  if (op6) {
    if (Opera_iSpellMain_Frame == null) {
      Opera_iSpellMain_Frame = self.document.getElementsByTagName('iframe').namedItem('ispellcheck');
    }
    Opera_iSpellMain_Frame.open('', 'ispellcheck');
    var odoc = Opera_iSpellMain_Frame.document;
    odoc.clear();
    odoc.open();
  } else {
    var odoc = parent.ispellcheck.document;
    odoc.open();
  }
  odoc.write("<html><head><title>SpellMain</title><link rel=stylesheet href='"+CSS_Style+"' type='text/css'>");
  if (!gecko) odoc.write("<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>");
  if ((gecko || op6) && parent.Bad_Word_Count > 0)  {
    odoc.write("\n<sc"+"ript language='javascript'>\n");
    odoc.write("function Select_Bad_Word(id) {\n");
    odoc.write("  document.getElementById(id).style.color='red';\n");
    if (mz) {
       odoc.write("  document.getElementById(id).scrollIntoView(false);");
    }
    odoc.write("}\n");
    odoc.write("function Unselect_Bad_Word(id) {\n");
    odoc.write("  document.getElementById(id).style.color='black';\n");
    odoc.write("}\n");
    odoc.write("function Replace_Bad_Word(id, word) {\n");
    odoc.write("  document.getElementById(id).innerHTML=word;\n");
    odoc.write("}\n");
    odoc.write("</scr"+"ipt>\n");
  }
  
  odoc.write("</head><body bgcolor=#ffffff class='main' topmargin=0 leftmargin=0 marginheight=0 marginwidth=0>");
  odoc.write("<table border=0 width=400 cellpadding=10><tr><td class=main><span class=main>");

  // Change everything to '\r' but save the Original
  if (gecko) {
    SpellString = SpellString.replace(/\n\r/g, " \r");
    SpellString = SpellString.replace(/\n/g, " \r");
    Corrected_Output.value = SpellString;
  } else if (macintosh) {
    SpellString = SpellString.replace(/\n\r/g, "  \r");
    SpellString = SpellString.replace(/\n/g, "  \r");
    Corrected_Output.value = SpellString;
  }
  var NewSentance = "";
  var Last_Location = 0;
  for (i=0;i<parent.Bad_Word_Count;i++) {
     Location = parent.Bad_Words[i].Location;
     WordLength = parent.Bad_Words[i].Size;
     for (j=0;j<parent.Line_Break_Count;j++) {
       if (Location < parent.AddLineBreak[j]) parent.AddLineBreak[j] += 33;
     }
     if (Location > 0) NewSentance = NewSentance + SpellString.slice(Last_Location, Location);
     NewSentance = NewSentance + "|<|span id='bw"+i+"' name='bw"+i+"'>";
     NewSentance = NewSentance + SpellString.slice(Location, Location+WordLength);
     NewSentance = NewSentance + "|<|/span>";
     Last_Location = Location+WordLength;
  }
  NewSentance = NewSentance + SpellString.slice(Last_Location, SpellString.length);

  if (op7) {
    NewSentance = OP7_Replace(NewSentance, "&", "&amp;")
    NewSentance = OP7_Replace(NewSentance, "<", "&lt;");
    NewSentance = OP7_Replace(NewSentance, "|&lt;|", "<");
  } else {
    NewSentance = NewSentance.replace(/&/g,"&amp;");
    NewSentance = NewSentance.replace(/</g, "&lt;");
    NewSentance = NewSentance.replace(/\|&lt;\|/g, "<");
  }

  for (i=0;i<parent.Line_Break_Count;i++) {
    if (NewSentance.charAt(parent.AddLineBreak[i]) == "/")  parent.AddLineBreak[i]++;
    NewSentance = NewSentance.slice(0, parent.AddLineBreak[i]) + " " + NewSentance.slice(parent.AddLineBreak[i], NewSentance.length);
  }

  if (op7) {
    NewSentance = OP7_Replace(NewSentance, "\r", "<br>");
  } else {
    NewSentance = NewSentance.replace(/\r/g, "<br>");
  }

  odoc.write(NewSentance);
  odoc.write("\n</td></tr></table>");
  if ((gecko || op6) && parent.Bad_Word_Count > 0 && Bad_Word_ID < parent.Bad_Word_Count)  {
    odoc.write("<sc"+"ript language='javascript'>\n");
    odoc.write("  Select_Bad_Word('bw"+Bad_Word_ID+"');\n");
    odoc.write("</scr"+"ipt>");
  }
  odoc.write("</body></html>");
  odoc.close();

}

function Do_No_Bad_Words(First_Check)
{
  if (op6) {
    if (Opera_iOption_Frame==null) {
      Opera_iOption_Frame = window.document.getElementsByTagName('iframe').namedItem('ioptions');
    }
    Opera_iOption_Frame.open('', 'ioptions');
    var odoc = Opera_iOption_Frame.document;
    odoc.clear();
    odoc.open();
  } else {
    var odoc = parent.ioptions.document;
    odoc.open();
  }
  odoc.write("<html><head><link rel=stylesheet href='"+CSS_Style+"' type='text/css'>");
  odoc.write("</head>");
  odoc.write("<body class='body' style='margin:20px'>");
  odoc.write("<form id='foptions' name='foptions' method='post'>");
  odoc.write("<table border=0 cellspacing=0 cellpadding=0 align=center><tr><td class='Text'>");
  if (First_Check) {
    odoc.write("<table border=1 bgcolor=#ffffff cellpadding=0 cellspacing=0 height=78 width=105><tr><td class='text' align=center><span class='text'>"+Language_Text[1]+"</span></td></tr></table><br>");
    odoc.write("<input type='button' value='"+Language_Text[2]+"' class='clbutton' onclick='this.blur();parent.window.close();'><br>");
  } else {
    odoc.write("<table border=1 bgcolor=#ffffff cellpadding=0 cellspacing=0 height=78 width=105><tr><td class='text' align=center><span class='text'>"+Language_Text[4]+"</span></td></tr></table><br>");
    odoc.write("<input type='button' value='"+Language_Text[2]+"' class='clbutton' onclick='this.blur();parent.Return_Valid_Sentance();'><br>");
    odoc.write("<input type='button' value='"+Language_Text[3]+"' class='clbutton' onclick='parent.window.close();'>");
  }
  odoc.write("</td></tr></table></form></body></html>");
  odoc.close();
}

function Check_Next_Word(Rebuild_Output_Screen)
{
  Unselect_Word();
  found = false
  while (!found && Current_Word < Bad_Word_Count) {
    if (!Bad_Words[Current_Word].Checked) {
       if (Rebuild_Output_Screen && op6) Build_Display_Screen(Current_Word);
       Build_Option_List(Current_Word, Bad_Words[Current_Word]);
       Bad_Words[Current_Word].Checked = true;
       found=true;
    }
    Current_Word++;
  }
  if (!found) {
    if (op6) {
      if (Rebuild_Output_Screen) Build_Display_Screen(Current_Word);
      parent.setTimeout('Do_No_Bad_Words(false);', 250);
    } else {
      Do_No_Bad_Words(false);
    }
  }
}

function Select_Word(The_Current_Word_ID)
{
  Last_Selected_Word = The_Current_Word_ID;
  Element = "bw"+The_Current_Word_ID;

  if (ie4 || op7) {
     parent.ispellcheck[Element].style.color="red";
  }
  if (ie4) {
     parent.ispellcheck[Element].scrollIntoView(false);
     parent.ispellcheck.document.body.scrollLeft = 0;
  }
  if (gecko) {
    if (parent.BrowserFirstTime == 0) {
      top.ispellcheck.Select_Bad_Word(Element);
    } else {
      parent.BrowserFirstTime=0;
    }
  }
  if (op6) {
    if (parent.BrowserFirstTime == 0) {
      document.ispellcheck.Select_Bad_Word(Element);
    } else {
      parent.BrowserFirstTime = 0;
    }
  }
}

function Unselect_Word()
{
  if (Last_Selected_Word == -1) return;
  Element = "bw"+Last_Selected_Word;

  if (ie4 || op7) parent.ispellcheck[Element].style.color="black";
  if (gecko) top.ispellcheck.Unselect_Bad_Word(Element);
  if (op6) document.ispellcheck.Unselect_Bad_Word(Element);
  Last_Selected_Word = -1;
}

function Select_Word_Direct(Location, SizeOfWord)
{
  Range = parent.ispellcheck.ispellpreview.createTextRange();
  Range.move("character", Location+Word_Offset);
  Range.moveEnd("character", SizeOfWord);
  Range.select();
  Range.scrollIntoView(false);
}

function Build_Option_List(The_Current_Word_ID, The_Current_Word)
{
  Select_Word(The_Current_Word_ID);
  if (op6) {
    if (Opera_iOption_Frame==null) {
      Opera_iOption_Frame = window.document.getElementsByTagName('iframe').namedItem('ioptions');
    }
    Opera_iOption_Frame.open('', 'ioptions');
    var odoc = Opera_iOption_Frame.document;
    odoc.clear();
    odoc.open();
  } else {
    var odoc = parent.ioptions.document;
    odoc.clear();
    odoc.open();
  }
  odoc.write("<html><head><link rel=stylesheet href='"+CSS_Style+"' type='text/css'>");
  if (!gecko) odoc.write("<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>");
  odoc.write("<title>option</title></head>");
  if (op7) {
    odoc.write("<body class='body' style='margin:0px'>");
  } else {
    odoc.write("<body class='body' style='margin:20px'>");
  }
  odoc.write("<table border=0 cellspacing=0 cellpadding=0 align=center><tr><td class='Text'><nobr>");
  odoc.write("<form class='Text' id='cword' name='cword' method='post' action='spellcheck."+phpEx+"' target='ioptions' onSubmit='parent.Do_Default_Enter_Action("+The_Current_Word_ID+", document.cword.word.value);'>");
  odoc.write("<span class='Text'>"+Language_Text[12]+"<br></span>");
  if (SpellSettings == 1 && The_Current_Word.Suggest_Count > 0) {
    odoc.write("<input name='word' id='word' type='input' value=\""+The_Current_Word.Suggestions[0]+"\" class='cltext' style='background-color:#ffffff;color:#000000'><br><br>");
  } else {
    odoc.write("<input name='word' id='word' type='input' value=\""+The_Current_Word.Word+"\" class='cltext' style='background-color:#ffffff;color:#000000'><br><br>");
  }
  if (The_Current_Word.Suggest_Count > 0) {
    odoc.write("<select name='swords' size=6 class='slct' ondblclick='this.blur();parent.Correct_Word("+The_Current_Word_ID+", document.cword.word.value, true);' onchange='document.cword.word.value=this.value;' style='font-size:7pt;font-family:Verdana,Arial,Helvetica;'>");
    k = The_Current_Word.Suggest_Count;
    for (i=0;i<k;i++) {
      odoc.write("<option value=\""+The_Current_Word.Suggestions[i]+"\">"+The_Current_Word.Suggestions[i]+"</option>");
    }
    odoc.write("</select>");
    if (op6) odoc.write("<br><br>");
  } else {
    odoc.write("<table border=1 bgcolor=#ffffff cellpadding=0 cellspacing=0 height=78 width=105><tr><td class='text' align=center><span class='text'>"+Language_Text[13]+"</span></td></tr></table>");
    if (op6) odoc.write("<br>");
  }
  odoc.write("</form><form id='qb' name='qb' method='post' action='spellcheck."+phpEx+"' target='ioptions'>");
  odoc.write("<input type='button' value='"+Language_Text[5]+"' class='clbuttonsmall' onclick='this.blur();parent.Correct_Word("+The_Current_Word_ID+",document.cword.word.value, true);'>");
  odoc.write("<input type='button' value='"+Language_Text[6]+"' class='clbuttonall' onclick='this.blur();parent.Correct_All_Words("+The_Current_Word_ID+",document.cword.word.value);'><br>");
  odoc.write("<input type='button' value='"+Language_Text[7]+"' class='clbuttonsmall' onclick='this.blur();parent.Ignore_Word("+The_Current_Word_ID+");'>");
  odoc.write("<input type='button' value='"+Language_Text[6]+"' class='clbuttonall' onclick='this.blur();parent.Ignore_All_Words("+The_Current_Word_ID+");'><br>");
  if (!op6) {
    odoc.write("<input type='button' value='"+Language_Text[8]+"' class='clbutton' onclick='this.blur();parent.Learn_Word("+The_Current_Word_ID+");'><br>");
    odoc.write("<input type='button' value='"+Language_Text[9]+"' class='clbutton' onclick='this.blur();parent.Suggest_Word("+The_Current_Word_ID+",document.cword.word.value);'><br>");
  }
  odoc.write("<input type='button' value='"+Language_Text[10]+"' class='clbuttonhalf' onclick='this.blur();parent.Open_Definition(document.cword.word.value);'>");
  odoc.write("<input type='button' value='"+Language_Text[11]+"' class='clbuttonhalf' onclick='this.blur();parent.Open_Thesaurus(document.cword.word.value);'><br>");

  odoc.write("<br><input type='button' value='"+Language_Text[2]+"' class='clbutton' onclick='this.blur();parent.Return_Valid_Sentance();'><br>");
  odoc.write("<input type='button' value='"+Language_Text[3]+"' class='clbutton' onclick='parent.window.close();'>");
  odoc.write("</form></nobr></td></tr></table>");
  odoc.write("<form id='foptions' name='foptions' method='post' action='spellcheck."+phpEx+"' target='ioptions'>");
  odoc.write("<input type='hidden' name='Suggest' value=''>");
  odoc.write("<input type='hidden' name='bw' value='"+The_Current_Word_ID+"'>");
  odoc.write("</form></body></html>");
  odoc.close();

}

function Do_Default_Enter_Action(Word_ID, Word)
{
  Correct_Word(Word_ID, Word, true);
  return (false);
}

function Open_Definition(The_Word)
{
   window.open("http://www.m-w.com/cgi-bin/dictionary?book=Dictionary&va="+The_Word, "dictionary", "width=630,resizable=yes,scrollbars=yes,height=500");
}

function Open_Thesaurus(The_Word)
{
   window.open("http://www.m-w.com/cgi-bin/thesaurus?book=Thesaurus&va="+The_Word, "dictionary", "width=630,resizable=yes,scrollbars=yes,height=500");
}

function Suggest_Word(The_Word_ID, The_Word)
{
  if (ie4 || op6) {
    parent.ioptions.foptions.Suggest.value=The_Word;
    parent.ioptions.foptions.bw.value = The_Word_ID;
    parent.ioptions.foptions.submit();
  }
  if (gecko || op7) {
    top.ioptions.document.foptions.Suggest.value=The_Word;
    top.ioptions.document.foptions.bw.value = The_Word_ID;
    top.ioptions.document.foptions.submit();
  }
}

function Get_Cookie(sName)
{
  var aCookie = document.cookie;
  if (aCookie == null) return (null);
  aCookie = aCookie.split("; ");
  for (var i=0; i < aCookie.length; i++)
  {
    var aCrumb = aCookie[i].split("=");
    if (sName == aCrumb[0])
      return unescape(aCrumb[1]);
  }
  return null;
}

function Set_Cookie(Cookie_Name, Cookie_Value)
{
  var Expires = new Date();
  Expires.setDate(Expires.getDate() + 365);
  var NewCookie = Cookie_Name + "=" + escape(Cookie_Value) + "; expires="+Expires.toGMTString()+";";
  document.cookie = NewCookie;
}

function Learn_Word(Word_ID)
{
  Word = Bad_Words[Word_ID].Word;
  Cookie = Get_Cookie("SpellLearned");
  if (Cookie == null) Cookie = Word;
  else Cookie = Cookie + "," + Word;
  Set_Cookie("SpellLearned",Cookie);
  Ignore_All_Words(Word_ID);
}

function Ignore_All_Words(Word_ID)
{
  Word = Bad_Words[Word_ID].Word;
  for (i=Current_Word; i<Bad_Word_Count; i++) {
    if (Bad_Words[i].Word == Word) Bad_Words[i].Checked = true;
  }
  Check_Next_Word(false);
}

function Ignore_Word(Word_ID)
{
  Check_Next_Word(false);
}

function Correct_All_Words(Word_ID, New_Word)
{
  Word = Bad_Words[Word_ID].Word;
  Correct_Word(Word_ID, New_Word, false);  // Correct this word
  for (i=Current_Word; i<Bad_Word_Count; i++) {
    if (Bad_Words[i].Word == Word) Correct_Word(i, New_Word, false);
  }
  Check_Next_Word(true);
}

function Correct_Word(Word_ID, New_Word, Do_Next_Word)
{
  OldWordLength = Bad_Words[Word_ID].Size;
  Location = Bad_Words[Word_ID].Location;
  ValidText = Corrected_Output.value;
  NewText = "";
  if (Location > 0) NewText = ValidText.slice(0, Location+Word_Offset);
  NewText = NewText + New_Word + ValidText.slice(Location+Word_Offset+OldWordLength, ValidText.length);
  Corrected_Output.value = NewText;
  Bad_Words[Word_ID].Checked = true;

  if (ie4 || op7) parent.ispellcheck['bw'+Word_ID].innerHTML=New_Word;
  if (gecko) top.ispellcheck.Replace_Bad_Word('bw'+Word_ID, New_Word);
  if (op6) {
    Bad_Words[Word_ID].Word = New_Word;
    for (i=Word_ID+1;i<Bad_Word_Count;i++) {
      Bad_Words[i].Location += (New_Word.length-OldWordLength);
    }
  } else {
    Word_Offset = Word_Offset + (New_Word.length-OldWordLength);
  }
  if (Do_Next_Word == true) Check_Next_Word(true);
  return (false);
}

function Return_Valid_Sentance()
{
  if (ie4) opener.LinkToField.value = parent.Corrected_Output.value;
  if (gecko || op7) top.opener.LinkToField.value = parent.Corrected_Output.value;
  if (op6) {
    var LtF = self.opener.Opera_Get_Link();
    LtF.value = parent.Corrected_Output.value;
  }
  parent.window.close();
}

function OP7_Replace(NewSentance, FindMe, ReplaceMe)
{
  NewSentance = NewSentance.split(FindMe).join(ReplaceMe);
  return NewSentance;
}

// ---------------------------------------------
// End Fancy Spellcheck work.  :)
