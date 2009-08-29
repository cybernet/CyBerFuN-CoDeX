<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");

dbconn();
maxcoder();
// ini_set('session.use_trans_sid', '0');
// Begin the session
session_start();
// ==(time() - $_SESSION['captcha_time'] < 10) ? exit('No Spam - 10 Sec Delay - Stop Hammering !') : NULL;
$res = sql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_row($res);
if ($arr[0] >= $maxusers)
    stderr("Sorry", "The current user account limit (" . number_format($maxusers) . ") has been reached. Inactive accounts are pruned all the time, please check back again later...");

stdhead("Signup");
begin_main_frame(true);

?>
<script type="text/javascript" src="captcha/captcha.js"></script>
<!--<div align="center"><p> Note: You need cookies enabled to sign up or log in.</p></div>-->
<form method="post" action="takesignup.php">
<table align="center" border="1" cellspacing=0 cellpadding="10">
<tr><td align="right" class="heading">Desired username:</td><td align=left><input type="text" size="40" name="wantusername" /></td></tr>
<tr><td align="right" class="heading">Pick a password:</td><td align=left><input type="password" size="40" name="wantpassword" /></td></tr>
<tr><td align="right" class="heading">Enter password again:</td><td align=left><input type="password" size="40" name="passagain" /></td></tr>
<tr valign=top><td align="right" class="heading">Email address:</td><td align=left><input type="text" size="40" name="email" />
<table width=250 border=0 cellspacing=0 cellpadding=0><tr><td class=embedded><font class=small>The email address should always be valid.
Your email address won't be publicly shown anywhere.</font></td></tr>
</table>
<?php
$questions = array(
    array("id" => "1", "question" => "Mother's birthplace"),
    array("id" => "2", "question" => "Best childhood friend"),
    array("id" => "3", "question" => "Name of first pet"),
    array("id" => "4", "question" => "Favorite teacher"),
    array("id" => "5", "question" => "Favorite historical person"),
    array("id" => "6", "question" => "Grandfather's occupation")
    );

foreach($questions as $sph) {
    $passhint .= "<option value=" . $sph['id'] . ">" . $sph['question'] . "</option>\n";
}

tr("Question", "<select name=passhint>\n$passhint\n</select>", 1);

?>
                     <tr><td class=rowhead>Enter hint  answer:</td><td align=left><input type="text" size="40"  name="hintanswer" /><br/><font class=small>This answer  will be used to reset your password in case you forget  it.<br/>Make sure its something you will not  forget!</font></td></tr>


</td></tr>
  <tr>
    <td>&nbsp;</td>
    <td>
      <div id="captchaimage">
      <a href="<?php echo $_SERVER['PHP_SELF'];
?>" onclick="refreshimg(); return false;" title="Click to refresh image">
      <img class="cimage" src="captcha/GD_Security_image.php?<?php echo time();
?>" alt="Captcha image" />
      </a>
      </div>
     </td>
  </tr>
  <tr>
      <td class="rowhead">PIN:</td>
      <td>
        <input type="text" maxlength="6" name="captcha" id="captcha" onBlur="check(); return false;"/>
      </td>
  </tr>
<td colspan="2" align="center">
    <div style="width:100%; height:130px; overflow: auto" class="quickjump">
      <table width="100%"  border="0" cellspacing="2" cellpadding="2">
        <tr>
          <td width="75%"><strong><?php echo $SITENAME ?> Rules</strong>
              <p align="left">for all Users!<br />
                  <br />
            </p></td>
          <td width="25%"><font size="-2">Copyright 2007 by<br />
            <?php echo $SITENAME ?><br />
            <br />
            </font></td>
        </tr>
        <tr>
          <td colspan="2"><h2>General rules - Breaking these rules can and will get you banned!</h2>
              <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <tbody>
                  <tr>
                    <td><ul>
                        <li>Do not defy the moderators expressed wishes!</li>
                      <li>Do not upload our torrents to other trackers! (See the <a href="faq.php#up3"><strong>FAQ</strong></a> for details.)</li>
                      <li><a name="warning" id="warning"></a>Disruptive behaviour in the forums or on the game server will result in a warning (<img src="pic/warned.gif" /> ).<br />
                        You will only get <strong>one</strong> warning! After that it's bye bye Kansas!</li>
                    </ul></td>
                  </tr>
                </tbody>
              </table>
            <h2>Downloading rules - By not following these rules you will lose download privileges!</h2>
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <tbody>
                  <tr>
                    <td><ul>
                        <li>Access to the newest torrents is conditional on a good ratio! (See the <a href="faq.php#dl8"><strong>FAQ</strong></a> for details.)</li>
                      <li>Low ratios may result in severe consequences, including banning in extreme cases.</li>
                    </ul></td>
                  </tr>
                </tbody>
              </table>
            <h2>General Forum Guidelines - Please follow these guidelines or else you might end up with a warning!</h2>
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <tbody>
                  <tr>
                    <td><ul>
                        <li>No aggressive behaviour or flaming in the forums.</li>
                      <li>No trashing of other peoples topics (i.e. SPAM).</li>
                      <li>No language other than English in the forums.</li>
                      <li>No systematic foul language (and none at all on  titles).</li>
                      <li>No links to warez or crack sites in the forums.</li>
                      <li>No requesting or posting of serials, CD keys, passwords or cracks in the forums.</li>
                      <li>No requesting if there has been no '<a href="http://www.nforce.nl/">scene</a>' release in the last 7 days.</li>
                      <li>No bumping... (All bumped threads will be deleted.)</li>
                      <li>No images larger than 800x600, and preferably web-optimised.</li>
                      <li>No double posting. If you wish to post again, and yours is the last post  in the thread please use the EDIT function, instead of posting a double.</li>
                      <li>Please ensure all questions are posted in the correct section!<br />
                        (Game questions in the Games section, Apps questions in the Apps section, etc.)</li>
                      <li>Last, please read the <a href="faq.php"><strong>FAQ</strong></a> before asking any questions!</li>
                    </ul></td>
                  </tr>
                </tbody>
              </table>
            <h2>Avatar Guidelines - Please try to follow these guidelines</h2>
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <tbody>
                  <tr>
                    <td><ul>
                        <li>The allowed formats are .gif, .jpg and .png. </li>
                      <li>Be considerate. Resize your images to a width of 150 px and a size of no more than 150 KB.  (Browsers will rescale them anyway: smaller images will be expanded and will not look good;  larger images will just waste bandwidth and CPU cycles.) For now this is just a guideline but  it will be automatically enforced in the near future. </li>
                      <li>Do not use potentially offensive material involving porn, religious material, animal / human  cruelty or ideologically charged images. Mods have wide discretion on what is acceptable.  If in doubt PM one. </li>
                    </ul></td>
                  </tr>
                </tbody>
              </table>
            <p align="left"></p></td>
        </tr>
      </table>
    </div></td></tr>
<tr>
  <td colspan="2" align="center">
<tr><td align="right" class="heading"></td><td align=left><input type=checkbox name=rulesverify value=yes> I have read the site rules page.<br>
<input type=checkbox name=faqverify value=yes> I agree to read the FAQ before asking questions.<br>
<input type=checkbox name=ageverify value=yes> I am at least 18 years old.</td></tr>
<tr><td colspan="2" align="center"><input type=submit value="Sign up! (PRESS ONLY ONCE)" style='height: 25px'></td></tr>
</table>
</form>
<div align="center"><a href="http://www.mozilla.com" />
	<img alt="Get Firefox" border="0" src="/pic/firefox.png"></a>
	<a href="http://www.utorrent.com" />
	<img alt="Get Utorrent" border="0" src="/pic/utorrent.png"></a>
	<a href="http://tbdev.net" />
	<img alt="Powered By TBDEV" border="0" src="/pic/tbdev.png"></a> </div>
<?php
end_main_frame();
stdfoot();

?>