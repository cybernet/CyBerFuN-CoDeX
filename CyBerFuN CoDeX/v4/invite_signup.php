<?php
require_once( "include/bittorrent.php" );
dbconn();

$res = mysql_query( "SELECT COUNT(*) FROM users" ) or sqlerr( __FILE__, __LINE__ );
$arr = mysql_fetch_row( $res );
if ( $arr[0] >= $invites )
    stderr( "Sorry", "The current user account limit (" . number_format( $maxusers ) . ") has been reached. Inactive accounts are pruned all the time, please check back again later..." );

stdhead( 'Signup' );

?>
<!--
<table width=500 border=1 cellspacing=0 cellpadding=10><tr><td align=left>
<h2 align=center>Proxy check</h2>
<b><font color=red>Important - please read:</font></b> We do not accept users connecting through public proxies. When you
submit the form below we will check whether any commonly used proxy ports on your computer is open. If you have a firewall it may alert of you of port
scanning activity originating from <b>69.10.142.42</b> (torrentbits.org). This is only our proxy-detector in action.
<b>The check takes up to 30 seconds to complete, please be patient.</b> The IP address we will test is <b><?= $HTTP_SERVER_VARS["REMOTE_ADDR"];
?></b>.
By proceeding with submitting the form below you grant us permission to scan certain ports on this computer.
</td></tr></table>
<p>
-->
Note: You need cookies enabled to sign up or log in.
<p>
<form method="post" action="take_invite_signup.php">
<table align="center" border="1" cellspacing=0 cellpadding="10">
<tr><td align="right" class="heading">Desired username:</td><td align=left><input type="text" size="40" name="wantusername" /></td></tr>
<tr><td align="right" class="heading">Pick a password:</td><td align=left><input type="password" size="40" name="wantpassword" /></td></tr>
<tr><td align="right" class="heading">Enter password again:</td><td align=left><input type="password" size="40" name="passagain" /></td></tr>
<tr><td align="right" class="heading">Enter invite-code:</td><td align=left><input type="text" size="40" name="invite" /></td></tr>
<tr valign=top><td align="right" class="heading">Email address:</td><td align=left><input type="text" size="40" name="email" />
<table width=250 border=0 cellspacing=0 cellpadding=0><tr><td class=embedded><font class=small>The email address must be valid.
You will receive a confirmation email which you need to respond to. The email address won't be publicly shown anywhere.</td></tr>
<td colspan="2" align="center">
    <div style="width:100%; height:130px; overflow: auto" class="quickjump">
     <table width="100%"  border="0" cellspacing="2" cellpadding="2">
        <tr>
          <td width="75%"><strong><?php echo $SITENAME?> Rules</strong>
              <p align="left">for all Users!<br />
                  <br />
            </p></td>
          <td width="25%"><font size="-2">Copyright 2007 by<br />
            <?php echo $SITENAME?><br />
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
                      <li><b>Confidentiality Agreement</b>
                      All User data held on <?php echo $SITENAME?> from Usernames, Passwords, Email address and any IP's held on the database or transmited via the peerlist<br /> are deemed confidential information and for the sole use of <?php echo $SITENAME?>. The Use of this information for anything other than use on site will be deemed to be a disclosure of confidential<br /> information. In using this information other then on site you have broken the <?php echo $SITENAME?> Confidentiality Agreement and may allow damages claims<br /> in the event of wrongful use or disclosure of confidential information other then that as specified by <?php echo $SITENAME?>. If any information gained from <?php echo $SITENAME?> is used in what <?php echo $SITENAME?> or it's user base deem as breaking this Agreement and/or a user suffers as a result in the use of this confidential information the said user will be given the user datails as<br /> to who has leaked there confidential information so as to make a claim against them. If you wish to use any Confidential User Information from the <?php echo $SITENAME?> website you must first inform a member of <?php echo $SITENAME?> staff<br /> of your intend actions and the intended use of this information.
                      Quote on legal advice
                      If a 3rd party keeps a list of all IP's they gain via there visit to a website, without permission,<br /> he breachs clause of confidentiality and makes an infringement.
                      Confidential Information shall mean all information identified by a party ("Disclosing Party") to the other party ("Receiving Party"), which,<br /> if in writing, labelled as confidential. Confidential Information shall remain the sole property of the Disclosing Party. Except for the specific rights granted by this agreement, the Receiving Party shall<br /> not use any Confidential Information of Disclosing Party for its own account. Receiving Party shall use reasonable care to protect Disclosing Party's Confidential Information. Receiving Party shall not disclose<br /> Confidential Information to any third party without the written consent of Disclosing Party (except to consultants who are bound by a written agreement with Receiving Party to maintain confidentiality). Confidential Information shall exclude information (i) available to the public other than by a breach of this Agreement;<br /> (ii) rightfully received from a third party not in breach of an obligation of confidentiality.</li>
										</li>

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

</font></td></tr></table>
</td></tr>
</td></tr>
<tr><td align="right" class="heading"></td><td align=left><input type=checkbox name=rulesverify value=yes> I have read the site rules page.<br>
<input type=checkbox name=faqverify value=yes> I agree to read the FAQ before asking questions.<br>
<input type=checkbox name=ageverify value=yes> I am at least 18 years old.</td></tr>
<tr><td colspan="2" align="center"><input type=submit value="Sign up! (PRESS ONLY ONCE)" style='height: 25px'></td></tr>
</table>
</form>
<br><br>
<center><a href="http://www.mozilla.com" />
	<img alt="Get Firefox" border="0" src="/pic/firefox.png"></a>
	<a href="http://www.utorrent.com" />
	<img alt="Get Utorrent" border="0" src="/pic/utorrent.png"></a>
	<a href="http://tbdev.net" />
	<img alt="Powered By TBDEV" border="0" src="/pic/tbdev.png"></a> </center>

<?php
stdfoot();

?>