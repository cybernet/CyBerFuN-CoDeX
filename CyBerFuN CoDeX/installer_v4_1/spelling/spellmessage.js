// --------------------------------------------------------------------
// phpSpell Javascript (For the PHPBB Message Document)
//
// This is (c)Copyright 2003, Team phpSpell.
// --------------------------------------------------------------------
        var ie4 = (document.all) ? 1:0;
        var gecko=(navigator.userAgent.indexOf('Gecko') > -1) ? 1:0;
        var op6=(navigator.userAgent.indexOf('Opera/6') > -1) ? 1:0;
        var op7=(navigator.userAgent.indexOf('Opera/7') > -1) ? 1 : (navigator.userAgent.indexOf('Opera 7') > -1) ? 1:0;
        var ns4=(navigator.userAgent.indexOf('Mozilla/4.7') > -1) ? 1:0;
        var sf=(navigator.userAgent.indexOf('Safari') > -1) ? 1:0;
        if (op7) ie4 = 0;
        if (sf) {
          ie4 = 0;
          gecko = 1;
        }


        var LinkToField = "";

        function openspell()
        {
          height = 391;
          width = 555;
          if (ie4) LinkToField = self.compose.body;
          if (gecko) {
            LinkToField = parent.document.compose.body;
            height = height + 6;
          }
          if (op6) {
             LinkToField = document.forms[0].body;
             height = height + 10;
             width = width + 10;
          }
          if (op7) LinkToField = document.forms[0].body;

          if (!(op6 || gecko || ie4 || op7)) {
            alert("phpSpell only supports one of the following browsers:\nOpera 6+, Netscape 6+, Mozilla 1+, Internet Explorer 4+, Safari");
          } else {
            if (LinkToField.value.length == 0) return;
            directory = "spelling/";
            k = openspell.arguments.length;
            if (k == 1) directory = "";
            win1=window.open(directory+"phpSpell.html","spellcheckwin",'resizable=no,width='+width+',height='+height);
            if (win1.opener == null) win1.opener = self;
          }
          return (false);
        }

        function Opera_Get_Link() {
          return (LinkToField);
        }