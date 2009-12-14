<?php
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

if (get_user_class() < UC_SYSOP)
hacker_dork("htaccessor - Nosey Cunt !");
stdhead();
if (isset($_REQUEST['submit']))
{
//export the form submits to global variables
while (isset($_REQUEST)&&(list($k,$v)=each($_REQUEST)))
{ if ($v) { $$k=$v; } } 

$output="";
$options="";
$cgi_hand="";
if (isset($opt_execCGI))
 {
  $options.=" +execCGI"; 
  if (isset($handle_cgi)) { $cgi_hand.=" cgi"; }
  if (isset($handle_pl)) { $cgi_hand.=" pl"; }
  if (isset($handle_exe)) { $cgi_hand.=" exe"; }
  if (isset($handle_sh)) { $cgi_hand.=" sh"; }
  if (isset($cgi_hand)) {
	$output.="\nAddHandler cgi-script $cgi_hand"; 
 	}
 };

if (isset($opt_include))
 { $options.=" +Includes"; }
else
{
if (isset($opt_includeNOEXEC))
 { $options.=" +IncludeNOEXEC"; }
}

if (isset($opt_FollowSymLinks))
 { $options.=" +FollowSymLinks"; }

if (isset($opt_FollowSymLinksIfOwnerMatch))
 { $options.=" +FollowSymLinksIfOwnerMatch"; }

if (isset($opt_indexes))
 { $options.=" +Indexes"; }

if (isset($opt_multiview))
 { $options.=" +MultiViews";}

if (isset($auth_name)||isset($auth_user)||isset($auth_group))
 { $output.="\nAuthType Basic"; }

if (isset($auth_name))
 { $output.="\nAuthName \"$auth_name\""; }

if (isset($auth_user))
 {  $output.="\nAuthUserFile $auth_userpath"; }

if (isset($auth_group))
 {  $output.="\nAuthGroupFile $auth_userpath"; }

if (isset($auth_denyall))
 { $output.="\nOrder allow,deny"; }
else
 { $output.="\nOrder deny,allow"; }

if (isset($satisfy_any))
 { $output.="\nSatisfy Any"; }

if (isset($auth_valid_user))
 { $output.="\nRequire valid-user"; }

if (isset($auth_allow_users))
 { $output.="\nRequire user $auth_allow_users"; }

if (isset($auth_allow_groups))
 { $output.="\nRequire group $auth_allow_groups"; }

if (isset($auth_allow_ip))
 { $output.="\nAllow from $auth_allow_ip"; }

if (isset($auth_deny_ip))
 { $output.="\nDeny from $auth_deny_ip"; }

if (isset($mime_types))
 {
  if (is_array($mime_types))
	{
	while (list($k,$v)=each($mime_types))
	{
	 $output.="\nAddType $v";
	}
	} else
	{
  $output.="\nAddType $mime_types";
	}
 }

 if (isset($opt_includeNOEXEC)||isset($opt_include))
 { 
 if (isset($opt_include_ext))
 { $output.="\nAddType text/html $opt_include_ext\nAddHandler server-parsed $opt_include_ext";};
 };

 if (isset($protect)) {
   $output.="\n<Files .htaccess .htpasswd .htuser .htgroups $protect_files>";
   $output.="\norder allow,deny\ndeny from all\n</Files>"; 
 }


 if (isset($redirect)) {
  $output.="\nRedirect permanent /$redirect_file $redirect_url";
 }

 if (isset($force_ssl))
 {
  $output.="\n<IfModule !mod_ssl.c>";
  $output.="\nRedirect permanent / https://$force_ssl_domain/";
  $output.="\n</IfModule>";

 }

 if (isset($no_index)) {
  $output.="\nIndexIgnore */*";
 }

 if (isset($cache))
 {
  $output.="\nExpiresActive on\nExpiresDefault ";
  if (isset($cache_server))
  { $output.="M"; }
  else
  { $output.="A"; }
  $output.=$cachelength;
 }

 if (isset($check_media_referrer)) { $modrewrite="true"; }
 if (isset($failed_redirect))	{ $modrewrite="true"; }
 if (isset($user_dir)) { $modrewrite="true"; }
 if (isset($timed_pages)) { $modrewrite="true"; }
 if (isset($block_harvesters)) { $modrewrite="true"; }
 if (isset($rewrite_browser_page)) { $modrewrite="true"; }
 if (isset($remap_script)&&isset($remap_folder)) { $modrewrite="true"; }

 if (isset($modrewrite)&&($modrewrite!="false"))
 {
   $output.="\nRewriteEngine  on";

   if (isset($check_media_referrer)) {
	$output.="\n".'RewriteCond %{HTTP_REFERER} !^$';
	$output.="\n".'RewriteCond %{HTTP_REFERER} !^http://(www\.)?'.$referrer_domain.'/.*$ [NC]';
	$output.="\n".'RewriteRule \.(gif|jpg|png|mp3|mpg|avi|mov)$ - [F]  ';
  }

 if (isset($failed_redirect))
	{
	$output.="\n".'RewriteCond   %{REQUEST_URI} !-U';
	$output.="\n".'RewriteRule   ^(.+)          http://'.$failed_redirect_server.'/$1';
 	}

 if (isset($user_dir)) {
	$user_domain=str_replace('.','\.',$user_domain);
	$output.="\n".'RewriteCond   %{HTTP_HOST}                 ^www\.[^.]+\.'.$user_domain.'$';
	$output.="\n".'RewriteRule   ^(.+)                        %{HTTP_HOST}$1          [C]';
	$output.="\n".'RewriteRule   ^www\.([^.]+)\.'.$user_domain.'(.*) /'.$user_dir_path.'$1$2';
	}

 if (isset($timed_pages))
	{
	$timed_page=str_replace('.','\.',$timed_page);
	$output.="\n".'RewriteCond   %{TIME_HOUR}%{TIME_MIN} >'.$timed_page_start;
	$output.="\n".'RewriteCond   %{TIME_HOUR}%{TIME_MIN} <'.$timed_page_end;
	$output.="\n".'RewriteRule   ^'.$timed_page.'$	'.$timed_page_day;
	$output.="\n".'RewriteRule   ^'.$timed_page.'$	'.$timed_page_night;
	}
 if (isset($block_harvesters)) {
  $output.="\nRewriteCond %{HTTP_USER_AGENT} Wget [OR] ";
  $output.="\nRewriteCond %{HTTP_USER_AGENT} CherryPickerSE [OR] ";
  $output.="\nRewriteCond %{HTTP_USER_AGENT} CherryPickerElite [OR] ";
  $output.="\nRewriteCond %{HTTP_USER_AGENT} EmailCollector [OR] ";
  $output.="\nRewriteCond %{HTTP_USER_AGENT} EmailSiphon [OR] ";
  $output.="\nRewriteCond %{HTTP_USER_AGENT} EmailWolf [OR] ";
  $output.="\nRewriteCond %{HTTP_USER_AGENT} ExtractorPro ";
  $output.="\nRewriteRule ^.*$ $block_doc [L]";
 }

   if (isset($rewrite_browser_page))
     { //rewrite browser pages
       $rw_page='^'.str_replace('.','\.',$rewrite_browser_page).'$';
       if (isset($geoip_country))
	 {
	   $output.="\nRewriteCond %{ENV:GEOIP_COUNTRY_CODE} $geoip_country [NC]";
	   $output.="\nRewriteRule $rw_page $geoip_page [L]\n";
	 }
       if (isset($rewrite_browser_page_ns))
	 {
	   $output.="\n".'RewriteCond %{HTTP_USER_AGENT}  ^Mozilla/[345].*Gecko*';
	   $output.="\nRewriteRule $rw_page $rewrite_browser_page_ns [L]\n";
	 }
       if (isset($rewrite_browser_page_ie))
	 {
	   $output.="\n".'RewriteCond %{HTTP_USER_AGENT}  ^Mozilla/[345].*MSIE*';
	   $output.="\nRewriteRule $rw_page $rewrite_browser_page_ie [L]\n";
	 }
       if (isset($rewrite_browser_page_lynx))
	 {
	   $output.="\n".'RewriteCond %{HTTP_USER_AGENT}  ^Mozilla/[12].* [OR]';
	   $output.="\n".'RewriteCond %{HTTP_USER_AGENT}  ^Lynx/*';
	   $output.="\nRewriteRule $rw_page $rewrite_browser_page_lynx [L]\n";
	 }

       if (isset($rewrite_browser_page_default))
	 {
	   $output.="\nRewriteRule $rw_page $rewrite_browser_page_default [L]\n";
	}

     }
	if (isset($remap_script)&&isset($remap_folder))
	{
	   $output.="\nRewriteRule $remap_folder(.*) /$remap_script$1 [PT]";
	}
 }
 if (isset($error_400)) { $output.="\nErrorDocument 400 $error_400"; }
 if (isset($error_401)) { $output.="\nErrorDocument 401 $error_401"; }
 if (isset($error_403)) { $output.="\nErrorDocument 403 $error_403"; }
 if (isset($error_404)) { $output.="\nErrorDocument 404 $error_404"; }
 if (isset($error_500)) { $output.="\nErrorDocument 500 $error_500"; }
 if (isset($default_page)) { $output.="\nDirectoryIndex $default_page"; }

 if ($options) { $output="Options $options\n".$output; }
begin_main_frame();
?>
<h3>Your .htaccess file contents</h3>
<p>Copy the lines below and paste them into your .htaccess file</p>
<textarea cols=80 rows=20><?=$output;?></textarea>
<?};?>

<form method="post" action="<?=$_SERVER["PHP_SELF"]?>">
<table><tr><td>
<table border=1>
<tr><td colspan=3><h4>Default Page</h4>What page to load if the user doesn't specify any (usually index.html or index.php)</td></tr>
<tr><td>Directory Index</td><td><input type=text name=default_page></td>
    <td>Can specify multiple in a list (ie index.php index.html index.htm default.htm) </td></tr>
</table>
<table border=1><tr><th colspan=3><h3>Options</h3></th></tr>
<tr>
<td width=50% align=right>execute CGI programs</td>
<td align=left> <input type=checkbox name="opt_execCGI" value="false">
<table>
 <tr><th colspan=2><h4>File Extensions</h4></th></tr>
 <tr><td>.cgi</td>
 	<td><input type=checkbox name="handle_cgi" value="false"></td>
 </tr>
 <tr><td>.pl</td>
 	<td><input type=checkbox name="handle_pl" value="false"></td>
 </tr>
 <tr><td>.exe</td>
 	<td><input type=checkbox name="handle_exe" value="false"></td>
 </tr>  
 <tr><td>.sh</td>
 	<td><input type=checkbox name="handle_sh" value="false"></td>
 </tr>
 </table>
</td>
<td>Execution of CGI scripts using mod_cgi is permitted.</td>
</tr>

<tr>
<td align=right>include files (SSI)</td>
<td align=left> <input type=checkbox name="opt_include" value="false">
or without #exec <input type=checkbox name="opt_includeNOEXEC" value="false">
<br>file&nbspextension <input type=text name="opt_include_ext" value="shtml">
</td>
<td>Server-side includes provided by mod_include are permitted. </td>
</tr>

<tr>
<td align=right>Follow Symbolic Links</td>
<td align=left> <input type=checkbox name="opt_FollowSymLinks" value="false"></td>
<td>The server will follow symbolic links in this directory. </td>
</tr>

<tr>
<td align=right>Follow Symbolic Links if owner matches</td>
<td align=left> <input type=checkbox name="opt_SymLinksIfOwnerMatch" value="false"></td>
<td>The server will only follow symbolic links for which the target file or directory is owned by the same user id as the link. </td>
</tr>

<tr>
<td align=right>Indexes</td>
<td align=left> <input type=checkbox name="opt_indexes" value="false"></td>
<td>If a URL which maps to a directory is requested, and there is no DirectoryIndex (e.g., index.html) in that directory, then mod_autoindex will return a formatted listing of the directory. 
</td>
</tr>

<tr>
<td align=right>Content Negotiation (MultiViews)</td>
<td align=left> <input type=checkbox name="opt_multiview" value="false"></td>
<td>Content negotiated "MultiViews" are allowed using mod_negotiation. </td>
</tr>

<tr>
<td align=right>Force SSL</td>
<td align=left> <input type=checkbox name="force_ssl" value="false"><br>
SSL Domain <input type=text name="force_ssl_domain" value="www.domain.com">
</td>
<td>Force HTTP requests to redirect HTTPS</td>
</tr>
</table>

</td><td>

<table><tr><th colspan=2><h3>Authentication</h3></th></tr>

<tr><td>Deny by default</td>
 <td><input type=checkbox name="auth_denyall" value="false"></td>
</tr>

<tr><td>Require valid username</td>
 <td><input type=checkbox name="auth_valid_user" value="false"></td>
</tr>

<tr><td>All if user OR ip matches</td>
 <td><input type=checkbox name="satisfy_any" value="false"></td>
</tr>

<tr><td>Area Name</td><td>
 <input type=text name="auth_name">
</td></tr>

<tr><td>User Authentication</td>
 <td><input type=checkbox name="auth_user" value="false">
<br>path to users file <input type=text name="auth_userpath">
</td>
</tr>

<tr><td>Group Authentication</td>
 <td><input type=checkbox name="auth_group" value="false">
<br>path to groups file <input type=text name="auth_grouppath"></td>
</tr>

<tr><td>Allowed Users</td>
<td><input type=text name="auth_allow_users" value=""></td>
</tr>

<tr><td>Allowed Groups</td>
<td><input type=text name="auth_allow_groups" value=""></td>
</tr>


<tr><td>Allowed IP addresses (wildcards and names allowed)</td>
<td><input type=text name="auth_allow_ip" value=""></td>
</tr>

<tr><td>Blocked IP addresses (wildcards and names allowed)</td>
<td><input type=text name="auth_deny_ip" value=""></td>
</tr>
</table>
<hr>
<table><tr><th>Additional Mime Types</th></tr>
<tr><td>
<select name="mime_types[]" multiple=true>
<?
$fp=fopen("./mime.types","r");
if ($fp)
 { while (!feof($fp))
	{
	$line=trim(fgets($fp,4096));
	$ext=strstr($line," ");
	echo "<option value=\"$line\">$ext</option>";
	}
  fclose($fp);
 }
?>
</select>
</td>
</tr>
<tr><td><em>File extension to mime type mappings are in this format<br>
<br>
mime/type ext<br><br>
for example<br><br>
text/html html<br>
application/x-gzip gz
</em>
</td></tr>
</table>
<hr>
<table>
<tr><th colspan=2><h4>Protect System Files</h4></th>
</tr>
<tr><td>Protect .htaccess and user and group files</td><td><input type=checkbox name="protect"></td></tr>
<tr><td>Additional files to protect</td><td><input type=text name="protect_files"></td></tr>
</table>

<table>
<tr><th colspan=3><h4>File Cache Control</h4></th>
</tr>
<tr><td>Specify File Cache Time</td><td><input type=checkbox name="cache"></td><td>How often will the client/proxy refresh the file</td></tr>
<tr><td>Modification Based</td><td><input type=checkbox name="cache_server"></td><td>Expire all clients/proxies at the same time</td></tr>
<tr><td>Cache Time</td><td colspan=2>
<select name=cachelength>
<OPTION VALUE="31536000">1 Year</OPTION>
<OPTION VALUE="15768000">6 Months</OPTION>
<OPTION VALUE="78844000">3 Months</OPTION>
<OPTION VALUE="2592000">1 Month</OPTION>
<OPTION VALUE="604800" SELECTED>1 Week</OPTION>
<OPTION VALUE="86400">1 Day</OPTION>
<OPTION VALUE="3600">1 Hour</OPTION>
<OPTION VALUE="60">1 Minutes</OPTION>
</select>
</td></tr>
</table>

</td></tr>
<tr><td colspan=2>
<table border=1><tr><td colspan=3 align=center>
<h4>ModRewrite </h4></td>
</tr>
<tr>
<td>Protect Media Files</td>
<td>
On: <input type=checkbox name="check_media_referrer"><br>
Allowed Domain: <input type=text name="referrer_domain" value="yourdomain.com">
</td>
<td>Check the referrer domain for images, music, and sound files</td>
</tr>
<tr>
<td>Block E-mail Harvesters</td>
<td>
On: <input type=checkbox name="block_harvesters"><br>
Page to server: <input type=text name="block_doc" value="deny.html">
</td>
<td>Deny access to e-mail harvesting programs.</td>
</tr>

<tr>
<td>Time-Dependant Page</td>
<td>
On: <input type=checkbox name="timed_pages"><br>
Page Name : <input type=text name="timed_page" value="page.html"><br>
Daytime Starts : <input type=text name="timed_page_start" value="0600"><br>
Daytime Ends   : <input type=next name="timed_page_end" value="1800"><br>
Daytime Page  : <input type=text name="timed_page_day" value="page.day.html"><br>
Nighttime Page : <input type=text name="timed_page_night" value="page.night.html">
</td>
<td>Serve pages depending on time of day</td>
</tr>

<tr>
<td>Virtual DNS to Folder</th>
<td>
On: <input type=checkbox name="user_dir"><br>
Base Domain: <input type=text name="user_domain" value="domain.com">
Path to Folders: <input type=text name="user_dir_path" value="home">
</td>
<td>Rewrite Virtual Subdomains to subfolders.  Ie: rewrite www.foo.domain.com to www.domain.com/subdomains/foo.  Useful for virtual user domains.</td>
</tr>

<tr>
<td>Redirect Failing URLs To Other Webserver</th>
<td>
On: <input type=checkbox name="failed_redirect"><br>
Secondary Server: <input type=text name="failed_redirect_server" value="server2.domain.com">
</td>
<td>When a URL is invalid, or would produce an error, redirect to a secondary server.</td>
</tr>

<tr><td align=center colspan=3><h3>Rewrite Condition</h3></td></tr>
<tr><td><h3>Rewrite Page</h3>
Page Name : <input type=text name="rewrite_browser_page"><br>
Page requested in the URL
</td><td colspan=3>
<table>
<tr><td align=center colspan=3><h5>Browser Dependant Page</h5></td></tr>

<tr><td>Netscape Page </td><td><input type=text name="rewrite_browser_page_ns"></td><td>Page to use for Netscape</td></tr>
<tr><td>IE Page </td><td><input type=text name="rewrite_browser_page_ie"></td><td>Page to use for IE</td></tr>
<tr><td>Page for Lynx</td><td> <input type=text name="rewrite_browser_page_lynx"></td><td>Page to use for text mode</td></tr>
<tr><td>Default Page</td><td> <input type=text name="rewrite_browser_page_default"></td><td>Page to use for other browsers</td></tr>

<tr><td align=center colspan=3><h5>Country Specific Page</h5>
Requires the <a href="http://www.maxmind.com/app/mod_geoip">mod_geoip</a> is setup and configured on your server.  Thought the software is free, the datafiles are a commercial product.  Allows you to redirect users to specific pages depending on their country of origin.
</td></tr>
<tr><td>
Country Code
</td>
<td>
<input type=text name="geoip_country">
</td>
<td>
US = United States
GB = United Kingdom
CA = Canada
MX = Mexico
FR = France
NL = Netherlands
A1 = Anonymous
</td></tr>
<tr><td>Country Specific URL</td>
<td>
<input type=text name="geoip_page">
</td>
<td>page to redirect visitors from the country (index.us.html or index.fr.html)</td>
</tr>
</table></td></tr>
<tr><td colspan=3><h4>Map Folder To Script</h4>This trick will make it possible to run a script that has parameters in the URL.  You can make a custom home page script for your users that they can access like /login/home.html or /login/preferences.html and have them both go to login.php.</td></tr>
<tr><td>Folder Name</td><td><input type=text name="remap_folder"></td><td>Folder you will reference in your href and urls (ie login)</td></tr>
<tr><td>Script Name</td><td><input type=text name="remap_script"></td><td>Script that will be ran (ie login.php, login.cgi, or login.pl)  If you would like the rest of the path as a POST variable, do something like "login.php?page="</td></tr>


</table>
<table><tr><td>
<table>
<tr><td colspan=3><h4>Custom Error Documents</h4>Allows you to specify custom documents to serve on error conditions</td></tr>
<tr><td>Error 400</td><td><input type=text name=error_400></td>
    <td>Bad Request </td></tr>
<tr><td>Error 401</td><td><input type=text name=error_401></td>
    <td>Authentication Required</td></tr>
<tr><td>Error 403</td><td><input type=text name=error_403></td>
    <td>Forbidden</td></tr>
<tr><td>Error 404</td><td><input type=text name=error_404></td>
    <td>Not Found</td></tr>
<tr><td>Error 500</td><td><input type=text name=error_500></td>
    <td>Server Error </td></tr>
</table>
</td><td>
<table><tr><th colspan=2>Redirection</th></tr>
<tr><td colspan=2>Use this option if a document has been moved to a new url.  It will take care of automatic redirection for the user.</td></tr>
<tr><td>Redirect Moved Document</td><td><input type=checkbox name="redirect"></td>
<tr>
<td>Moved Document</td>
<td><input type=text name=redirect_file></td>
</tr>

<tr>
<td>New URL</td>
<td><input type=text name=redirect_url></td>
</tr></table>
</td></tr></table>
<br>
<center><input type=reset name=reset value="Clear Form"><input type=submit name=submit value="Generate .htaccess files"></center>
</form>
</div>
<div id="footer">
dot htaccesser provided by <a href="http://www.bitesizeinc.net/">Bite Size, Inc</a>
</div>
</div>
</body>
<?
end_main_frame();
stdfoot();
?>