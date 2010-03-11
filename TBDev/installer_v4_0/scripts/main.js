var linkset=new Array()
var checkflag = "false";
var browserName=navigator.appName;
function check(field)
{
	if (checkflag == "false")
	{
		for (i = 0; i < field.length; i++)
		{
			field[i].checked = true;
		}
		checkflag = "true";
		return l_uncheckall; 
	}
	else
	{
		for (i = 0; i < field.length; i++)
		{
			field[i].checked = false;
		}
		checkflag = "false";
		return l_checkall;
	}
};

function SetSize(obj, x_size)
{
	if (obj.offsetWidth > x_size)
	{
		obj.style.width = x_size;
	};
};

function SmileIT(smile,form,text)
{
	document.forms[form].elements[text].value = document.forms[form].elements[text].value+" "+smile+" ";
	document.forms[form].elements[text].focus();
};

function log_out()
{
	ht = document.getElementsByTagName("html");
	ht[0].style.filter = "progid:DXImageTransform.Microsoft.BasicImage(grayscale=1)";
	if (confirm(l_logout))
	{
		return true;
	}
	else
	{
		ht[0].style.filter = "";
		return false;
	}
};

function goback(to)
{
	history.back(to)
};

function jumpto(url,message)
{
	if (typeof message != "undefined")
	{
		document.getElementById("jumpto").style.display = "block"; 
	}
	window.location = url;
};

function highlight(field)
{
	field.focus();
	field.select();
};

function quote(textarea,form,quote)
{
	var area = document.forms[form].elements[textarea];
	area.value = area.value+" "+quote+" ";
	area.focus();
};

function select_deselectAll (formname, elm, group)
{
	var frm = document.forms[formname];
	
    // Loop through all elements
    for (i=0; i<frm.length; i++)
    {
        // Look for our Header Template's Checkbox
        if (elm.attributes['checkall'] != null && elm.attributes['checkall'].value == group)
        {
            if (frm.elements[i].attributes['checkme'] != null && frm.elements[i].attributes['checkme'].value == group)
              frm.elements[i].checked = elm.checked;
        }
        // Work here with the Item Template's multiple checkboxes
        else if (frm.elements[i].attributes['checkme'] != null && frm.elements[i].attributes['checkme'].value == group)
        {
            // Check if any of the checkboxes are not checked, and then uncheck top select all checkbox
            if(frm.elements[i].checked == false)
            {
                frm.elements[1].checked = false; //Uncheck main select all checkbox
            }
        }
    }
};

function ts_show(where)
{
	document.getElementById(where).style.display = 'block';
};

function ts_hide(where)
{
	document.getElementById(where).style.display = 'none';
};

function ts_open_popup(desktopURL, alternateWidth, alternateHeight, noScrollbars)
{
	if ((alternateWidth && self.screen.availWidth * 0.8 < alternateWidth) || (alternateHeight && self.screen.availHeight * 0.8 < alternateHeight))
	{
		noScrollbars = false;
		alternateWidth = Math.min(alternateWidth, self.screen.availWidth * 0.8);
		alternateHeight = Math.min(alternateHeight, self.screen.availHeight * 0.8);
	}
	else
		noScrollbars = typeof(noScrollbars) != "undefined" && noScrollbars == true;

	window.open(desktopURL, 'ts_requested_popup', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=' + (noScrollbars ? 'no' : 'yes') + ',width=' + (alternateWidth ? alternateWidth : 480) + ',height=' + (alternateHeight ? alternateHeight : 220) + ',resizable=no');

	return false;
};

function ts_focusfield(FormName,TextAreaName)
{
	var el = document.forms[FormName].elements[TextAreaName];
	if (el)
	{
		el.value = el.value;
		el.focus && el.focus();
	}
};

window.status = "Powered by TS Special Edition v4.3"