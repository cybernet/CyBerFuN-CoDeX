<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/include/bittorrent.php");
dbconn();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Avatar maker</title>
<link rel="stylesheet" href="css/colorpicker.css" type="text/css" />
<link rel="stylesheet" href="css/fancycheckbox.css" type="text/css" />
<script type="text/javascript" src="js/fancycheckbox.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/colorpicker.js"></script>
<script type="text/javascript">
	$(document).ready(function () { 
		$('#colorSelector').ColorPicker({
			color : "#"+$('#bgColor').val()+"",
			onChange: function (hsb, hex, rgb) { 
			$('#bgColor').val(hex); 
			update();
		} });
		$('#colorSelector1').ColorPicker({
			color : "#"+$('#fColor').val()+"",
			onChange: function (hsb, hex, rgb) {
			 $('#fColor').val(hex); 
			 update(); } });
		$('#colorSelector2').ColorPicker({
			color : "#"+$('#bColor').val()+"",
			onChange: function (hsb, hex, rgb) {
			 $('#bColor').val(hex); 
			 update(); } });
			 
		get_vars();
		get_drp(true);
		crir.init();
	});
	
	function update() {
		
		var user = $('#user').val();
		var bColor = $('#bColor').val();
		var fColor = $('#fColor').val();
		var bgColor = $('#bgColor').val();
		var smile = $('#smile').val();
		var pack = $('#pack').val();
		var font = $('#font').val();
		var showuser = $('#suser').is(':checked') == 1 ? 1 : 0;
		var line = new Array(3);
		var drp = new Array(3);
		for(i=1;i<=3;i++)
		{
			 line[i] = $("#line"+i).val();
			  drp[i] = $("#drp"+i).val();
		}	
		$(".loader").fadeIn();
		$.post('save.php',{user:user,bColor:bColor,bgColor:bgColor,fColor:fColor,smile:smile,pack:pack,font:font,line1:line[1],line2:line[2],line3:line[3],drp1:drp[1],drp2:drp[2],drp3:drp[3],showuser:showuser}, function (r) {
			if(r == 1) {
				$("#preview").attr('src','avatar.php?user='+user+'&r='+Math.random());
				get_drp(true);
				$("#preview").bind("load",function () {
					$(".loader").fadeOut();
				});
			}
		});
	}
	function get_vars() {
		var user = $('#user').val();
		$.post('save.php',{action:'load',user:user},function(r) {
			 $('#bColor').val(r.bColor);
			 $('#fColor').val(r.fontColor);
			 $('#bgColor').val(r.bgColor);
			 $('#smile').val(r.smile);	
			 $('#font').val(r.font);
			 $('#pack').val(r.pack);
		},'json');
	}
	function get_drp(firstrun){
		var user = $('#user').val();
		var foo = firstrun == true ? 1 : 0;
		var drp1 = 0+parseInt($('#drp1').val());
		var drp2 = 0+parseInt($('#drp2').val());
		var drp3 = 0+parseInt($('#drp3').val());
		$.post("drp.php",{user:user,firstrun:foo,drp1:drp1,drp2:drp2,drp3:drp3}, function (r) {
			$('#drp1').empty().append(r.op1)
			$('#drp2').empty().append(r.op2)
			$('#drp3').empty().append(r.op3)
			$('#line1').empty().val(r.line1)
			$('#line2').empty().val(r.line2)
			$('#line3').empty().val(r.line3)
			if(r.showuser == 1)
				$('#suser').attr('checked','checked');
			else 
				$('#suser').removeAttr('checked');
		}, 'json');
	}
	function change_label(id,value){
		var labels = new Array(5);
		labels[1] = 'Posts';
		labels[2] = 'Download & Upload';
		labels[3] = 'Irc idle';
		labels[4] = 'Reputation';
		labels[5] = 'Geo';
		labels[6] = 'Comments';
		labels[7] = 'Agent';
		labels[8] = 'Profile hits';
		labels[9] = 'Online';
			$('#'+id).val(labels[value]);
	}
</script>
<style type="text/css">
body {
	background:#666666;
	color:#222;
	font-family:Verdana, Arial, Helvetica, sans-serif;
	font-size:12px;
}
.avy_body td {
	border:1px solid #333333;
	-moz-border-radius:2px;
}
.avy_body input[type='text'], select {
	border:1px #333333 solid;
	background:#CCCCCC;
	padding:3px;
	width: 130px;
	-moz-border-radius:2px;
}
.avy_body select {
	width:138px;
}
fieldset {
	border:1px solid #333333;
	padding:3px;
	-moz-border-radius:2px;
}
legend {
	border:solid #333333;
	border-width:1px 1px 3px 1px;
	-moz-border-radius:2px;
	padding:2px 4px 2px 4px;
	font-weight:bold;
}
.info {
	font-weight:bold;
	text-align:right
}
.loader {
	background:url(images/ajax-loader.gif) no-repeat;
	padding-left:36px;
	padding-top:7px;
	color:#6960EC;
	font-weight:bold;
	position:absolute;
	top:0;
	left:0;
	height:32px;
}
#colorSelector, #colorSelector2, #colorSelector3 {
	cursor: pointer;
}
</style>
</head>
<body>
<div class="loader" style="display:none;">Wait while the avatar is saved!</div>
<table width="600" border="0" align="center">
  <tr>
    <td valign="top"><fieldset style="width:170px;">
      <legend>Preview</legend>
      <div align="center">
        <input type="hidden" value="<?php echo $CURUSER["username"]?>" id="user"  />
        <img id="preview" src="avatar.php?user=<?php echo $CURUSER["username"]?>" width="150" height="190" /></div>
      </fieldset></td>
    <td valign="top"><fieldset style="width:400px;">
      <legend>Avatar body</legend>
      <table border="0" cellpadding="4" cellspacing="2" style="border-collapse:separate" align="center" class="avy_body">
        <tr>
          <td nowrap="nowrap" class="info">Background color</td>
          <td width="100%"><input type="text" id="bgColor" readonly="readonly" size="25"/>
            <img id="colorSelector" title="Select color" src="images/color_wheel.png" width="16" height="16"  /></td>
        </tr>
        <tr>
          <td nowrap="nowrap" class="info">Font color</td>
          <td width="100%"><input type="text" id="fColor" readonly="readonly" size="25"/>
            <img id="colorSelector1" title="Select color" src="images/color_wheel.png" width="16" height="16"  /></td>
        </tr>
        <tr>
          <td nowrap="nowrap" class="info">Border color</td>
          <td width="100%"><input type="text" id="bColor" readonly="readonly" size="25"/>
            <img id="colorSelector2" title="Select color" src="images/color_wheel.png" width="16" height="16" /></td>
        </tr>
        <tr>
          <td nowrap="nowrap" class="info">Smile pack</td>
          <td width="100%"><select id="pack" onchange="update();">
              <option value="1">Default</option>
              <option value="2">Blacy</option>
              <option value="3">Popo</option>
              <option value="4">Buttery</option>
            </select></td>
        </tr>
        <tr>
          <td nowrap="nowrap" class="info">Smile</td>
          <td width="100%"><select id="smile" onchange="update();">
              <option value="225">Random</option>
              <option value="1">Smile 1</option>
              <option value="2">Smile 2</option>
              <option value="3">Smile 3</option>
              <option value="4">Smile 4</option>
              <option value="5">Smile 5</option>
              <option value="6">Smile 6</option>
              <option value="7">Smile 7</option>
              <option value="8">Smile 8</option>
              <option value="9">Smile 9</option>
              <option value="10">Smile 10</option>
              <option value="11">Smile 11</option>
              <option value="12">Smile 12</option>
              <option value="13">Smile 13</option>
              <option value="14">Smile 14</option>
              <option value="15">Smile 15</option>
              <option value="16">Smile 16</option>
              <option value="17">Smile 17</option>
              <option value="18">Smile 18</option>
              <option value="19">Smile 19</option>
              <option value="20">Smile 20</option>
            </select></td>
        </tr>
        <tr>
          <td nowrap="nowrap" class="info">Font</td>
          <td width="100%"><select id="font" onchange="update();">
              <option value="1">Font 1</option>
              <option value="2">Font 2</option>
              <option value="3">Font 3</option>
            </select></td>
        </tr>
      </table>
      </fieldset></td>
  </tr>
  <tr>
    <td valign="top"><fieldset style="width:170px;">
      <legend>Final link</legend>
      <table border="0" cellpadding="4" cellspacing="2" style="border-collapse:separate" align="center" class="avy_body">
        <tr>
          <td nowrap="nowrap" class="info"><input style="width:150px;" type="text" onclick="select();" value="<?php echo $BASEURL?>/avatars/<?php echo $CURUSER["username"]?>.png" readonly="readonly"/>
          </td>
        </tr>
      </table>
      </fieldset></td>
    <td valign="top"><fieldset style="width:400px;">
      <legend>Settings</legend>
      <table border="0" cellpadding="4" cellspacing="2" style="border-collapse:separate" align="center" class="avy_body">
        <tr>
          <td nowrap="nowrap" class="info">Line 1</td>
          <td width="100%" align="center"><select id="drp1" onchange="get_drp();change_label('line1',this.value);update();">
            </select>
            <input type="text" id="line1" onchange="update();"/>
          </td>
        </tr>
        <tr>
          <td nowrap="nowrap" class="info">Line 2</td>
          <td width="100%" align="center"><select id="drp2" onchange="get_drp();change_label('line2',this.value);update();">
            </select>
            <input type="text" id="line2" onchange="update();"/>
          </td>
        </tr>
        <tr>
          <td nowrap="nowrap" class="info">Line 3</td>
          <td width="100%" align="center"><select id="drp3" onchange="get_drp();change_label('line3',this.value);update();">
            </select>
            <input type="text" id="line3" onchange="update();"/>
          </td>
        </tr>
        <tr>
          <td nowrap="nowrap" class="info">Show<br/>
            Username</td>
          <td width="100%"><label for="suser">
            <input type="checkbox" id="suser" onchange="update();" class="crirHiddenJS" />
            Tick this if you want your username on the avatar!</label></td>
        </tr>
      </table>
      </fieldset></td>
  </tr>
</table>
</body>
</html>
