<?php
require_once('include/bittorrent.php');
require_once('include/bbcode_functions.php');
require_once ("include/authenticate.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_SYSOP)
    hacker_dork("Admin Cp - Nosey Cunt !");

/* add your ids and uncomment this check
$allowed_ids = array(1);
if (!in_array($CURUSER['id'], $allowed_ids))
    stderr('Error', 'Access Denied!');
    */
systemcheck();    
define ('ConfigFN', 'include/config.php');
define ('TBVERSION', 'TBDEV.NET-01-06-08 InstallerV4 Beta');

if (file_exists(ConfigFN)) include_once(ConfigFN);
else {
    $pic_base_url = 'pic/';
    $SITENAME = '::InstallerV4::'; /// edit this :p
}
function utime()
{
    return (float) preg_replace('/^0?(\S+) (\S+)$/X', '$2$1', microtime());
}
$pgs = utime();
stdhead('Admin Options');
// Templates
// Options Type Templates
// current format:
// Key => array(Numeric,DisplayInput,ValidationRule,PPFilter)
// where:
// Key: is the Options Menu Type
// Numeric: dictates if the code shud be enclosed in quotes, or shud be left as is (eval possibly)
// DisplayInput: is the code for the Input box on the form
// ValidationRule: is php code that $val is passed thru, must set $ok, 0=failed 1=success
// PrePFilter: is php code that is applied before the the value is saved to the config
// PstPFilter: is php code that is applied after retrieving it from the config

// may contain:
// $key: FormName from Options Menu
// $val: Value from either config.php or DefaultValue from Options Menu
$templates = array('hidden' => array(0, '<input name="$key" type="hidden" id="$key" value="$val" size="83" maxlength="80" readonly>', null, null, null),
    'string' => array(0, '<input name="$key" type="text" id="$key" value="$val" size="83" maxlength="80">', null, null, null),
    // /
    'info' => array(0, '<textarea name="mrinfo" cols="80" rows="3" wrap="off" id="mrinfo">$val</textarea>', null, null),
    // /
    'password' => array(0, '<input name="$key" type="text" id="$key" value="$val" size="83" maxlength="80">', null, null, null),
    'path' => array(0, '<input name="$key" type="text" id="$key" value="$val" size="83" maxlength="80">', 'is_valid_path', null, null),
    'url' => array(0, '<input name="$key" type="text" id="$key" value="$val" size="83" maxlength="80">', 'is_valid_url', null, null),
    'rurl' => array(0, '<input name="$key" type="text" id="$key" value="$val" size="83" maxlength="80">', 'is_valid_rurl', null, null),
    'aurl' => array(0, '<textarea name="annurl" cols="80" rows="3" wrap="off" id="annurl">$val</textarea>', 'is_valid_urls', null, null),
    'email' => array(0, '<input name="$key" type="text" id="$keyid" value="$val" size="83" maxlength="80">', 'is_valid_email', null, null),
    'tf' => array(1, '<input name="$key" type="checkbox" value="true" $checked>', 'is_tf', null, null),
    'int' => array(1, '<input name="$key" type="text" id="$key" value="$val" size="43" maxlength="40">', 'is_numformula', null, null),
    'bytes' => array(1, '<input name="$key" type="text" id="$key" value="$val" size="43" maxlength="40">', 'is_numformula', null, null),
    'sec' => array(1, '<input name="$key" type="text" id="$key" value="$val" size="43" maxlength="40">', 'is_numformula', null, null),
    'float' => array(1, '<input name="$key" type="text" id="$key" value="$val" size="11" maxlength="8">', 'is_floatformula', null, null),
    );
// Options Menu Array
// A little more complicates
// each entry is either an string or an array
// if it's a string, than it's a Header that contains arrays below it
// the array for menu items is under this format
// DisplayName, Type, FormName, ConfigName, Description, DefaultValue
// DisplayName: Display name on the Form
// Type: The type of input expected, used in validating user/config input
// FormName: the name that appears on the form, and the variable name used in php (global)
// ConfigName: the variable name (preceded with $) or constant name (defined) in config.php
// Description: brief description displayed next/under the input button on the form
// Default/Value: Default value used if not found in config.php or on POST
$options = array('Extras',
    array('Set Torrents Free', 'tf', 'bfree', '$free_for_all', 'All Torrents Free', 'true'),
    array('Free Title', 'string', 'freetitle', '$freetitle', 'bbcodes allowed.', 'Sitewide Freeleech!'),
    array('Free Message', 'string', 'freemessage', '$freemessage', 'bbcodes allowed.', '[size=2]All torrents marked Free![/size]  :w00t:'),
    array('Set Torrents double up', 'tf', 'bdouble', '$double_for_all', 'All Torrents double up', 'false'),
    array('Double Up Title', 'string', 'doubletitle', '$doubletitle', 'bbcodes allowed.', 'Sitewide Double Upload!'),
    array('Double Up Message', 'string', 'doublemessage', '$doublemessage', 'bbcodes allowed.', '[size=2]All torrents marked Double Upload ![/size]  :w00t:'),
    array('Free Slots Expire In X Days', 'string', 'slotduration', '$slotduration', 'eg: 14 ', '14'),
    array('Enable TTL', 'tf', 'ttl', '$oldtorrents', 'Enable Time To Live?', 'false'),
    array('Torrent TTL', 'sec', 'ttorrentttl', '$torrent_ttl', 'How long do torrents live for.', '28*86400'),
    array('Enable Wait Times', 'tf', 'waiton', '$waiton', 'Enable Wait Times?', 'false'),
    array('ratio < 0.5 || gigs < 5', 'int', 'bwaittimes1', '$wait1', 'Wait Time till download is available', '12'),
    array('ratio < 0.65 || gigs < 6.5', 'int', 'bwaittimes2', '$wait2', 'Wait Time till download is available', '8'),
    array('ratio < 0.8 || gigs < 8', 'int', 'bwaittimes3', '$wait3', 'Wait Time till download is available', '4'),
    array('ratio < 0.95 || gigs < 9.5', 'int', 'bwaittimes4', '$wait4', 'Wait Time till download is available', '1'),
    array('Global torrent rules Format is Ratio:UpGigs:SeedsMax:LeechesMax:AllMax', 'string', 'bglobaltorrentrules', '$GLOBALS["TORRENT_RULES"]', 'Global slot rules', '0.5:2:10:8:18|1.01:2:30:20:50|2.01:5:40:30:70|5.01:20:50:35:85'),
    'Site Online * Offline',
    array('Site online-offline control', 'int', 'onoff', '$onoff', '1 or 0', '1'),
    array('Offline reason', 'string', 'offreason', '$reason', 'Site offline reason.', 'Server offline for updates - back soon!'),
    array('Site online-offline control - class allowed', 'int', 'class', '$class', '0 to 7', '0'),
    array('Site online-offline control - class name', 'string', 'cname', '$class_name', 'User class name', 'just for User'),
    'Auto * Users',
    array('Transfer Limit', 'bytes', 'aplimit', '$ap_limit', 'Uploaded amount for promotion', '25*1024*1024*1024'),
    array('Minimum Ratio', 'float', 'apratio', '$ap_ratio', 'Minimum ratio for promotion', '1.05'),
    array('Time Limit', 'sec', 'aptime', '$ap_time', 'Offer expires after how long a user joined', '28*86400'),
    array('Minimum Ratio', 'float', 'adratio', '$ad_ratio', 'Minimum ratio required to keep Power User', '.95'),
    // 'Category Info',
    // array('Film of the Week Cat Id','int','catid1','$catid1','Category ID For Movies','5'),
    // array('Film of the Week Cat Id','int','catid2','$catid2','Category ID For Movies','20'),
    // array('Film of the Week Cat Id','int','catid3','$catid3','Category ID For Movies','19'),
    'Site Info',
    array('TBVersion', 'hidden', 'tbv', 'TBVERSION', 'TBDevnet Versioning info', TBVERSION),
    array('Site Name', 'string', 'sitename', '$SITENAME', 'Name of your torrent tracker', 'TBDev.Net Tracker'),
    array('Site Url', 'url', 'siteurl', '$BASEURL', 'Your site url, used in page links (no ending slash)', 'http://domain.name'),
    array('Base Url', 'url', 'baseurl', '$DEFAULTBASEURL', 'Sites base path, used in emails (no ending slash)', 'http://domain.name'),
    array('Site Email', 'email', 'siteemail', '$SITEEMAIL', 'Email for sender/return path', 'noreply@tracker.tbdev.net'),
    array('Announce Urls', 'aurl', 'annurl', '$announce_urls[]', 'Announce urls', 'http://domain.name/announce.php'),
    array('System ID', 'int', 'system1', '$system1', 'System ID.', '2'),

    'Database',

    array('Host', 'string', 'dhost', '$mysql_host', 'Database host (domain or ip)', 'localhost'),
    array('User', 'string', 'duser', '$mysql_user', 'Database username', 'tb'),
    array('Password', 'password', 'dpass', '$mysql_pass', 'Database password', ''),
    array('Database', 'string', 'ddb', '$mysql_db', 'Database name', 'bittorrent'),

    'Switches',

    array('Site Online', 'tf', 'bonline', '$SITE_ONLINE', 'Site Open for business?', 'true'),
    array('Forum Online', 'tf', 'bfonline', '$FORUMS_ONLINE', 'Forum Open for business?', 'true'),
    array('error_reporting', 'tf', 'erreport', '$erreport', 'Enable error_reporting', 'false'),
    array('Members Only', 'tf', 'bmembers', '$MEMBERSONLY', 'Only registered users may use', 'true'),
    array('Email Confirmation', 'tf', 'bconfirm', 'ENA_EMAIL_CONFIRM', 'Use Email Confirmation', 'true'),
    array('Enable BBcodes on Details', 'tf', 'dtype', '$dtype', 'Enable BBcodes on torrent details page?', 'false'),
    array('NFO Required', 'tf', 'nforeq', '$nforeq', 'NFO Required for Upload', 'true'),
    array('Class Required To Upload', 'int', 'upclass', '$upclass', 'Minumum Class Required to Upload Torrents', '3'),
    array('Users', 'int', 'limitusers', '$maxusers', 'Max Users before signups close', '75000'),
    array('Invites', 'int', 'limitinvites', '$invites', 'Max invites allowed', '5000'),
    array('Peers', 'int', 'limitpeers', '$PEERLIMIT', 'Max Peers allowed, not implemented', '50000'),
    array('Torrent Size', 'bytes', 'limittsize', '$max_torrent_size', 'Max torrent filesize that can be uploaded', '10000000'),
    array('Max File Size', 'bytes', 'maxsize', '$maxsize', 'Max filesize that can be uploaded into bitbucket', '956 * 5024'),

    'Paths',

    array('Torrents', 'path', 'dirtorrents', '$torrent_dir', 'Server path to torrent folder (complete or relative, no ending slash)', 'torrents'),
    array('BitBucket', 'path', 'dirbucket', '$bitbucket', 'Server path to BitBucket folder (no beginning,no ending slash)', 'bitbucket'),
    array('Server Path to BitBucket', 'url', 'urltoimages', '$urltoimages', 'Relative Server/url path (complete or relative, no ending slash)', 'bitbucket'),
    array('Images', 'rurl', 'urlpics', '$pic_base_url', 'Relative Image url path (with beginning & ending slash)', '/pic/'),
    // array('Logs','path','blogs','$sql_error_log','Server path to dox folder (complete or relative, no ending slash)','/logs/sql_err_'.date("M_D_Y").'.log'),
    'Security',
    // array('Bans','path','banpath','$banpath', 'Server path to bans folder (no beginning,no ending slash)','include/bans'),
    array('Dictbreaker', 'path', 'dictbreaker', '$dictbreaker', 'Server path to dictbreaker folder (no beginning,no ending slash)', 'dictbreaker'),
    array('Max Login Attempts', 'int', 'maxloginattempts', '$maxloginattempts', 'Max failed logins before getting banned', '6'),
    array('Staff Forum ID', 'int', 'forumid', '$forumid', 'Your Staff Forum ID', '26'),

    'Timed',

    array('Announce Interval', 'sec', 'tannounce', '$announce_interval', 'Time between announces to give to user clients.', '60 * 30'),
    array('Autoclean Interval', 'sec', 'taclean', '$autoclean_interval', 'How long between autoclean runs.', '900'),
    array('SlowAutoclean Interval', 'sec', 'taslowclean', '$autoslowclean_interval', 'How long between slowautoclean runs.', '28800'),
    array('s2SlowAutoclean Interval', 'sec', 'ts2slowclean', '$s2autoslowclean_interval', 'How long between s2slowautoclean runs.', '28800'),
    array('Backup db Interval', 'sec', 'tabackup', '$backupdb_interval', 'How long between auto back up runs.', '172800'),
    array('Autohitrun Interval', 'sec', 'tahitrun', '$autohitrun_interval', 'How long between auto hit and run clean ups.', '21600'),
    array('Optimize db Interval', 'sec', 'taoptimize', '$optimizedb_interval', 'How long between auto optimizations.', '172800'),
    array('Signup Timeout', 'sec', 'tsignupto', '$signup_timeout', 'How long to wait before deleting unconfirmed accts.', '86400 * 3'),
    array('Invite Timeout', 'sec', 'tinviteto', '$invite_timeout', 'How long to wait before deleting invited unconfirmed accts.', '86400 * 3'),
    array('Read Post Expiry', 'sec', 'treadpostex', '$READPOST_EXPIRY', 'How long to wait before deleting old read posts.', '86400 * 14'),
    array('Delete torrents when Dead Torrent Time expires', 'tf', 'tdeadtime', '$tdeadtime', 'Delete torrents when $max_dead_torrent_time expires', 'false'),
    array('Dead Torrent Time', 'sec', 'tdeadtorrent', '$max_dead_torrent_time', 'How long to wait to make torrents invisible (no seeds/no peers)..', '6 * 3600'),
    array('Delete users when Dead User Time expires', 'tf', 'delaccounts', '$delaccounts', 'Delete users when $max_dead_user_time expires', 'false'),
    array('Dead User Time', 'sec', 'tdeaduser', '$max_dead_user_time', 'How long to wait before deleting inactive user accounts..', '42*86400'),
    array('Dead Topic Time', 'sec', 'tdeadtopic', '$max_dead_topic_time', 'How long to wait before setting inactive forum topics locked..', '7*86400'),

    'Cache',

    array('Cache', 'path', 'bcache', '$CACHE', 'Server path to cache folder (complete or relative, no ending slash)', 'cache'),
    // array('Cache Index Stats','string','indexstats','$indexstats','Time Till Cache Expires.(60 minutes)','30 * 30'),
    // array('Cache Top10 Stats','string','topten','$topten','Time Till Cache Expires.(100 minutes)','60 * 60'),
    /*
   'Seed Bonus',

       array('Big Ratio','string','toomuch','$toomuch','ratio > 999.999
','TooMuch!'),
   array('Seedbonus Class','string','millionaire','$millionaire','karma > 1,000,000.000
','Millionaire'),
   array('Seedbonus Class','string','upperclass','$upperclass','karma < 1,000,000.000
','Upper Class'),
   array('Seedbonus Class','string','middleclass','$middleclass','karma < 100,000.000
','Middle Class'),
   array('Seedbonus Class','string','poor','$poor','karma < 10,000.000
','Poor'),
   array('Seedbonus Class','string','bum','$bum','karma < 1000.000
','Bum'),
*/

    'User Class IDs',
    // array('UC_LEECH','string','userclass0','UC_LEECH','Define User Classes',''),
    array('UC_USER', 'string', 'userclass1', 'UC_USER', 'Define User Classes', '0'),
    array('UC_IRC_USER', 'string', 'userclass2', 'UC_IRC_USER', 'Define User Classes', ''),
    array('UC_POWER_USER', 'string', 'userclass3', 'UC_POWER_USER', 'Define User Classes', '1'),
    array('UC_VIP', 'string', 'userclass4', 'UC_VIP', 'Define User Classes', '2'),
    array('UC_SUPER_VIP', 'string', 'userclass5', 'UC_SUPER_VIP', 'Define User Classes', ''),
    array('UC_UPLOADER', 'string', 'userclass6', 'UC_UPLOADER', 'Define User Classes', '3'),
    array('UC_MODERATOR', 'string', 'userclass7', 'UC_MODERATOR', 'Define User Classes', '4'),
    array('UC_SUPER_MODERATOR', 'string', 'userclass8', 'UC_SUPER_MODERATOR', 'Define User Classes', ''),
    array('UC_ADMINISTRATOR', 'string', 'userclass9', 'UC_ADMINISTRATOR', 'Define User Classes', '5'),
    array('UC_SYSOP', 'string', 'userclass10', 'UC_SYSOP', 'Define User Classes', '6'),
    array('UC_STAFFLEADER', 'string', 'userclass11', 'UC_STAFFLEADER', 'Define User Classes', ''),
    array('UC_OWNER', 'string', 'userclass12', 'UC_OWNER', 'Define User Classes', ''),
    array('UC_CODER', 'string', 'userclass13', 'UC_CODER', 'Define User Classes', '7'),

    'User Class Names',
    // array('UC_LEECH','string','duserclass0','duserclass0','Define User Class Name','Leecher'),
    array('UC_USER', 'string', 'duserclass1', 'duserclass1', 'Define User Class Name', 'User'),
    array('UC_IRC_USER', 'string', 'duserclass2', 'duserclass2', 'Define User Class Name', 'IRC User'),
    array('UC_POWER_USER', 'string', 'duserclass3', 'duserclass3', 'Define User Class Name', 'Power User'),
    array('UC_VIP', 'string', 'duserclass4', 'duserclass4', 'Define User Class Name', 'VIP'),
    array('UC_SUPER_VIP', 'string', 'duserclass5', 'duserclass5', 'Define User Class Name', 'Super VIP'),
    array('UC_UPLOADER', 'string', 'duserclass6', 'duserclass6', 'Define User Class Name', 'Uploader'),
    array('UC_MODERATOR', 'string', 'duserclass7', 'duserclass7', 'Define User Class Name', 'Moderator'),
    array('UC_SUPER_MODERATOR', 'string', 'duserclass8', 'duserclass8', 'Define User Class Name', 'Super Moderator'),
    array('UC_ADMINISTRATOR', 'string', 'duserclass9', 'duserclass9', 'Define User Class Name', 'Administrator'),
    array('UC_SYSOP', 'string', 'duserclass10', 'duserclass10', 'Define User Class Name', 'Sysop'),
    array('UC_STAFFLEADER', 'string', 'duserclass11', 'duserclass11', 'Define User Class Name', 'Staff Leader'),
    array('UC_OWNER', 'string', 'duserclass12', 'duserclass12', 'Define User Class Name', 'Owner'),
    array('UC_CODER', 'string', 'duserclass13', 'duserclass13', 'Define User Class Name', 'Coder'),

    );

function is_valid_email($val)
{
    return preg_match('/^([a-zA-Z0-9_\-\.]+@(?:[a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,4}|\x22.+\x22\s\x3c[a-zA-Z0-9_\-\.]+@(?:[a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,4}\x3e)$/', $val);
}
function is_valid_url($val)
{
    $pp = parse_url($val);
    return (("$pp[scheme]://$pp[host]" . (isset($pp["port"]) ? ":$pp[port]":"") . (isset($pp["path"]) ? "$pp[path]":"") == $val) ? true:false);
}

function is_valid_path($val)
{
    return is_valid_rurl($val, 1);
}
function is_valid_rurl($val, $rta = 0)
{
    GLOBAL $submission;
    $pp = parse_url($val);
    if (($ok = ($pp["path"] == $val ? true:false)) && $submission) {
        $val = (($val[0] == '/' && $rta == 0) ? substr($val, 1):$val);
        if (!is_dir($val))
            mkdir($val, 0777);
        elseif (!$rta)
            // chmod($val,0777);  //== commented out due to error on chmod
            $ok = is_dir($val);
    }
    return $ok;
}

function is_valid_urls($val)
{
    if ($ok = is_array($val)) {
        foreach($val as $value) {
            if (($ok = is_valid_url($value)) === false)
                break;
        }
    }
    return ($ok);
}

function is_tf($val)
{
    return (in_array($val, array("true", "false", 1, 0)) ? true:false);
}
function is_numformula($val)
{
    return ((preg_match("/^[\s0-9-\x2b\x28\x29\x2a]+$/", $val) == 1) ? true:false);
}
function is_floatformula($val)
{
    return ((preg_match("/^[\s0-9-\x2b\x28\x29\x2a\x2e]+$/", $val) == 1) ? true:false);
}
function check_aurl($val)
{
    $arr = array();
    foreach($val as $value) {
        $value = fixup($value);
        if (!empty($value))
            $arr[] = $value;
    }
    return $arr;
}
function fixup($val)
{
    if ($val[0] == '"' || $val[0] == "'")
        $val = substr($val, 1, strlen($val)-2);
    return stripslashes(trim($val));
}

function calctime($val)
{
    $days = intval($val / 86400);
    $val -= $days * 86400;
    $hours = intval($val / 3600);
    $val -= $hours * 3600;
    $mins = intval($val / 60);
    $secs = $val - ($mins * 60);
    return "<br>&nbsp;&nbsp;&nbsp;$days days, $hours hrs, $mins minutes, $secs Seconds";
}
function calcbytes($val)
{
    $tb = intval($val / ($ml = 1073741824));
    $val -= $tb * $ml;
    $gb = intval($val / ($ml /= 1024));
    $val -= $gb * $ml;
    $mb = intval($val / ($ml /= 1024));
    $val -= ($mb * $ml);
    $kb = intval($val / ($ml /= 1024));
    $bytes = $val - ($kb * $ml);
    return "<br>&nbsp;&nbsp;&nbsp;$tb TB, $gb GB, $mb MB, $kb KB, $bytes Bytes";
}
// Setup array of Reference Values for quicker lookups
foreach($options as $key => $value) {
    if (is_array($value)) {
        $plkp[$value[2]] = $key;
        $ptyp[$key] = $value[1];
        $pnum[$key] = $templates[$value[1]][0];
        $prep[$key] = $value[2];
        $pvar[$key] = $value[3];
        $pdef[$key] = ($value[1] == 'aurl' ? explode('\n', $value[5]):$value[5]);
    }
}
// If this is a submitted form, fill in our form defaults
if ($_POST['action'] == 'submit') {
    $submission = true;
    foreach($ptyp as $pkey => $val)
    if ($val == 'tf')
        $pdef[$pkey] = 0;
    foreach($_POST as $pkey => $pvalue) {
        if (isset($plkp[$pkey])) {
            $key = $plkp[$pkey];
            $pdef[$key] = ($ptyp[$key] == 'aurl' ? explode("\n", $pvalue) : ($ptyp[$key] == 'tf' ? 1 :$pvalue));
        }
    }
} else
    // Read our config.php file and get valid contents
    // replace form defaults if option exists
    if ($fh = fopen(ConfigFN, 'r')) {
        $config = fread($fh, filesize(ConfigFN) + 1);
        fclose($fh);
        $haveconfig = true;
        preg_match_all("/^define\s*\(\s*[\x22\x27](.+)[\x22\x27]\s*,\s*(\d+|.+)\s*\)\s*;$/m", $config, $defines);
        preg_match_all("/^([$][a-zA-Z0-9\x5f]+[\x5b]?[\x5d]?)\s*=\s*(\d+|[\x22\x27].+[\x22\x27])\s*;$/m", $config, $vars);
        unset ($config);
        $config[0] = array_merge($defines[1], $vars[1]);
        $config[1] = array_merge($defines[2], $vars[2]);
        foreach($config[0] as $ck => $val) {
            if (!(($key = array_search($val, $pvar)) == false)) {
                if ($config[1][$ck][0] != '"')
                    $pdef[$key] = $config[1][$ck];
                else if ($ptyp[$key] != 'aurl')
                    $pdef[$key] = substr($config[1][$ck], 1, strlen($config[1][$ck])-2);
                else {
                    $pdef[$key][] = substr($config[1][$ck], 1, strlen($config[1][$ck])-2);
                    unset($pdef[$key][0]);
                }
            }
        }
    }
    // Validate the form entries
    foreach($pdef as $key => $val) {
        if (!empty($templates[$ptyp[$key]][2])) {
            if ($pnum[$key])
                eval("\$val = (" . ($ptyp[$key] == 'float'?'float':'int') . ")($val);");
            else
                $val = ($ptyp[$key] == 'aurl' ? check_aurl($val):fixup($val));
            // Use the defaults if validation fails
            $pdef[$key] = (!call_user_func($templates[$ptyp[$key]][2], $val) ? ($ptyp[$key] == 'aurl' ? explode("\n", $options[$key][5]):$options[$key][5]) : $val);
        }
    }
    // Simple login validation check
    if ($haveconfig) {
        $key = $plkp['duser'];
        $key2 = $plkp['dpass'];
        if (!(empty($pdef[$key])) && !(empty($pdef[$key2]))) {
            $validlogin = ($_POST['luser'] == $pdef[$key] && $_POST['lpass'] == $pdef[$key2]);
            if (!$validlogin) {
                begin_main_frame();
                begin_frame("Site Settings");
                begin_table(1);
                echo '<form action="" method="post" enctype="application/x-www-form-urlencoded" name="login">';
                tr($options[$key][4], '<input name="luser" type="text" size="83" maxlength="80">', 1);
                tr($options[$key2][4], '<input name="lpass" type="password" size="83" maxlength="80">', 1);
                end_table();
                echo '	<center><input type="submit" name="Submit" value="Submit">	</center>';
                end_frame();
                end_main_frame();
                stdfoot();
                die();
            }
        }
    }

    if ($submission) {
        if ($fh = fopen(ConfigFN, 'w')) {
            $config = "<?php\n//\n// Generated by core.php on " . gmdate("M d Y H:i:s") . "\n// Originally made by Laffin for XTBDevnet\n// Modified a little by pdq :p\n//\n\n";
            foreach($options as $okey => $oval)
            if (is_array($oval)) {
                $config .= "// " . $oval[4] . "\n";
                $q = ($pnum[$okey] ? '' : '"');
                $add = ($oval[3][0] != '$' ? true : false);
                if (!is_array($pdef[$okey]))
                    $config .= ($add ? "define ('":'') . $oval[3] . ($add ? "',":' = ') . $q . addcslashes(stripslashes($pdef[$okey]), "\0..\37\"$\\\177..\377") . $q . ($add ? ')':'') . ";\n";
                else
                    foreach($pdef[$okey] as $val)
                    $config .= ($add ? "define ('":'') . $oval[3] . ($add ? "',":' = ') . $q . addcslashes(stripslashes($val), "\0..\37\"$\\\177..\377") . $q . ($add ? ')':'') . ";\n";
            }
            $config .= "?>\n";
            fwrite($fh, $config);
            fclose($fh);
        }
    }
    // add some extra info to some options
    // final processing for form display
    foreach($pdef as $key => $val) {
        switch ($ptyp[$key]) {
            case 'sec':
                $options[$key][4] .= calctime($pdef[$key]);
                break;
            case 'bytes':
                $options[$key][4] .= calcbytes($pdef[$key]);
                break;
            case 'aurl':
                $options[$key][4] .= '<br >&nbsp;<strong>One per line.</strong>';
                $pdef[$key] = implode("\n", $pdef[$key]);
        }
    }
    // OMG, Finally the Output Portion of the script
    begin_main_frame();
    begin_frame('Site Settings');

    ?>
	<CENTER><H1><b><?= TBVERSION ?></b></H1></CENTER>
	<form action="" method="post" enctype="application/x-www-form-urlencoded" name="config">
<?php
    begin_table(1);
    foreach($options as $value) {
        if (is_string($value))
            echo "<tr><td colspan=2 class='colhead'><CENTER>$value</CENTER></td></tr>";
        else if (is_array($value)) {
            $key = $value[2];
            $val = htmlspecialchars(stripslashes($pdef[$plkp[$key]]));
            if ($value[1])
                $checked = $val ? ' checked':'';
            eval('$opt="' . addslashes($templates[$value[1]][1]) . ($value[1] == 'tf'?'':'<br />') . '";');
            if ($value[1] != 'hidden')
                tr($value[0], "&nbsp;$opt&nbsp;$value[4]", 1);
            else
                echo $opt;
        }
    }
    end_table();

    ?>
	<br />
	<center>
	<input name="action" type="hidden" value="submit" readonly>
	<input type="submit" name="Submit" value="Submit">
	&nbsp;&nbsp;&nbsp;
  <input type="reset" name="Reset" value="Reset">
	</center>
	</form>
<?php
    end_frame();

    end_main_frame();
    $pgt = utime() - $pgs;
    echo "<CENTER>Page Generated in $pgt Seconds</CENTER>";
    stdfoot();
    die();

    ?>
