<?php
if (!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
// == Better cleanup function with autocleanup , autoslowcleanup, auto backup , auto hit and run cleanup and finally mysql-optimize by x0r
// == All tagged default code Modified and updated by Bigjoos credits to pdq for the idea & jaits for the method :)
require_once ROOT_PATH."include/bittorrent.php";
require_once ROOT_PATH."include/user_functions.php";

function docleanup()
{
    global $torrent_dir, $usergroups, $signup_timeout, $max_dead_torrent_time, $autoclean_interval, $queries, $query_stat, $tdeadtime, $delaccounts, $oldtorrents, $slotduration, $max_dead_topic_time, $max_dead_user_time, $ad_ratio, $ap_time, $ap_limit, $ap_ratio, $torrent_ttl;
    set_time_limit(1200);
    $result = sql_query("show processlist") or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_array($result)) {
        if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
            $sql = "kill " . $row["Id"] . "";
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
        }
    }
    ignore_user_abort(1);
    do {
        $res = sql_query("SELECT id FROM torrents");
        $ar = array();
        while ($row = mysql_fetch_array($res)) {
            $id = $row[0];
            $ar[$id] = 1;
        }
        if (!count($ar))
            break;

        $dp = @opendir($torrent_dir);
        if (!$dp)
            break;

        $ar2 = array();
        while (($file = readdir($dp)) !== false) {
            if (!preg_match('/^(\d+)\.torrent$/', $file, $m))
                continue;
            $id = $m[1];
            $ar2[$id] = 1;
            if (isset($ar[$id]) && $ar[$id])
                continue;
            $ff = $torrent_dir . "/$file";
            unlink($ff);
        }
        closedir($dp);

        if (!count($ar2))
            break;

        $delids = array();
        foreach (array_keys($ar) as $k) {
            if (isset($ar2[$k]) && $ar2[$k])
                continue;
            $delids[] = $k;
            unset($ar[$k]);
        }
        if (count($delids))
            sql_query("DELETE FROM torrents WHERE id IN (" . join(",", $delids) . ")");

        $res = sql_query("SELECT torrent FROM peers GROUP BY torrent");
        $delids = array();
        while ($row = mysql_fetch_array($res)) {
            $id = $row[0];
            if (isset($ar[$id]) && $ar[$id])
                continue;
            $delids[] = $id;
        }
        if (count($delids))
            sql_query("DELETE FROM peers WHERE torrent IN (" . join(",", $delids) . ")");

        $res = sql_query("SELECT torrent FROM files GROUP BY torrent");
        $delids = array();
        while ($row = mysql_fetch_array($res)) {
            $id = $row[0];
            if ($ar[$id])
                continue;
            $delids[] = $id;
        }
        if (count($delids))
            sql_query("DELETE FROM files WHERE torrent IN (" . join(",", $delids) . ")");
    } while (0);

    $deadtime = deadtime();
    sql_query("DELETE FROM peers WHERE last_action < FROM_UNIXTIME($deadtime)");

    if ($tdeadtime) {
        $deadtime -= $max_dead_torrent_time;
        sql_query("UPDATE torrents SET visible='no' WHERE visible='yes' AND last_action < FROM_UNIXTIME($deadtime)");
    }

    $deadtime = time() - $signup_timeout;
    sql_query("DELETE FROM users WHERE status = 'pending' AND added < FROM_UNIXTIME($deadtime) AND last_login < FROM_UNIXTIME($deadtime) AND last_access < FROM_UNIXTIME($deadtime)");

  $torrents = array();
	$res = mysql_query("SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder");
	while ($row = mysql_fetch_assoc($res)) {
		if ($row["seeder"] == "yes")
			$key = "seeders";
		else
			$key = "leechers";
		$torrents[$row["torrent"]][$key] = $row["c"];
	}

	$res = mysql_query("SELECT torrent, COUNT(*) AS c FROM comments GROUP BY torrent");
	while ($row = mysql_fetch_assoc($res)) {
		$torrents[$row["torrent"]]["comments"] = $row["c"];
	}

	$fields = explode(":", "comments:leechers:seeders");
	$res = mysql_query("SELECT id, seeders, leechers, comments FROM torrents");
	while ($row = mysql_fetch_assoc($res)) {
		$id = $row["id"];
		$torr = $torrents[$id];
		foreach ($fields as $field) {
			if (!isset($torr[$field]))
				$torr[$field] = 0;
		}
		$update = array();
		foreach ($fields as $field) {
			if ($torr[$field] != $row[$field])
				$update[] = "$field = " . $torr[$field];
		}
		if (count($update))
			mysql_query("UPDATE torrents SET " . implode(",", $update) . " WHERE id = $id");
	}
    // === Update karma seeding bonus
    /**
    * Use ONLY one of the two options below...
    * the first is per torrents seeded, the second will only give the bonus for ONE torrent     no matter how many are seeded.
    * also you will have to play with how much bonus you want to give...
    * ie: seedbonus+0.0225 = 0.25 bonus points per hour
    * seedbonus+0.125 = 0.5 bonus points per hour
    * seedbonus+0.225 = 1 bonus point per hour
    */
    // === Update karma seeding bonus... made nicer by devinkray :D
    // ==   Updated and optimized by pdq :)
    // === using this will work for multiple torrents UP TO 5!... change the 5 to whatever... 1 to give the karma for only 1 torrent at a time, or 100 to make it unlimited (almost) your choice :P
    // /======seeding bonus per torrent
    $res = sql_query('SELECT COUNT(torrent) As tcount, userid FROM peers WHERE seeder =\'yes\' GROUP BY userid') or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) > 0) {
        while ($arr = mysql_fetch_assoc($res)) {
            if ($arr['tcount'] >= 1000)
                $arr['tcount'] = 5;
            $users_buffer[] = '(' . $arr['userid'] . ',0.225 * ' . $arr['tcount'] . ')';
        }
        if (sizeof($users_buffer) > 0) {
            sql_query("INSERT INTO users (id,seedbonus) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE seedbonus=seedbonus+values(seedbonus)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("autoclean", "Auto Cleanup - " . $count / 2 . " users received seedbonus");
        }
        unset ($users_buffer);
    }
    // delete old login attempts
    $secs = 1 * 86400; // Delete failed login attempts per one day.
    $dt = sqlesc(get_date_time(gmtime() - $secs)); // calculate date.
    sql_query("DELETE FROM loginattempts WHERE banned='no' AND added < $dt"); // do job.
    // Update stats
    $seeders = get_row_count("peers", "WHERE seeder='yes'");
    $leechers = get_row_count("peers", "WHERE seeder='no'");
    sql_query("UPDATE avps SET value_u=$seeders WHERE arg='seeders'") or sqlerr(__FILE__, __LINE__);
    sql_query("UPDATE avps SET value_u=$leechers WHERE arg='leechers'") or sqlerr(__FILE__, __LINE__);
    // Cf's update forum post/topic count
    $forums = @sql_query("SELECT t.forumid, count( DISTINCT p.topicid ) AS topics, count( * ) AS posts FROM posts p LEFT JOIN topics t ON t.id = p.topicid LEFT JOIN forums f ON f.id = t.forumid GROUP BY t.forumid");
    while ($forum = mysql_fetch_assoc($forums)) {
        /*
        $postcount = 0;
        $topiccount = 0;
        $topics = sql_query("select id from topics where forumid=$forum[id]");
        while ($topic = mysql_fetch_assoc($topics))
        {
            $res = sql_query("select count(*) from posts where topicid=$topic[id]");
            $arr = mysql_fetch_row($res);
            $postcount += $arr[0];
            ++$topiccount;
        } */
        @sql_query("update forums set postcount={$forum['posts']}, topiccount={$forum['topics']} where id={$forum['forumid']}");
    }
    // Reduce Counters for all user rows
    sql_query("UPDATE users SET
pm_count = if( pm_count > 1, pm_count -2, pm_count ) ,
post_count = if( post_count > 1, post_count -2, post_count ),
comment_count = if( comment_count > 1, comment_count -2, comment_count )") or sqlerr(__FILE__, __LINE__);
    $registered = get_row_count('users');
    $unverified = get_row_count('users', "WHERE status='pending'");
    $male = get_row_count("users", "WHERE gender='Male'");
    $female = get_row_count("users", "WHERE gender='Female'");
    $torrents = get_row_count('torrents');
    $seeders = get_row_count('peers', "WHERE seeder='yes'");
    $leechers = get_row_count('peers', "WHERE seeder='no'");
    $torrentstoday = get_row_count('torrents', 'WHERE added > DATE_SUB(NOW(), INTERVAL 1 DAY)');
    $donors = get_row_count('users', "WHERE donor='yes'");
    $unconnectables = get_row_count("peers", " WHERE connectable='no'");
    $forumposts = get_row_count("posts");
    $forumtopics = get_row_count("topics");
    $dt = sqlesc(get_date_time(gmtime() - 300)); // Active users last 5 minutes
    $numactive = get_row_count("users", "WHERE last_access >= $dt");
    sql_query("UPDATE stats SET regusers = '$registered', unconusers = '$unverified', male = '$male', female= '$female', torrents = '$torrents', seeders = '$seeders', leechers = '$leechers', unconnectables = '$unconnectables', torrentstoday = '$torrentstoday', donors = '$donors', forumposts = '$forumposts', forumtopics = '$forumtopics', numactive = '$numactive' WHERE id = '1' LIMIT 1");
    //begin staff secureip//
    $tempip = 43200;
    $tempipdel = sqlesc(get_date_time(gmtime() - $tempip));
    sql_query("DELETE FROM ipsecureip WHERE temp='yes' AND added < $tempipdel");
    //end of staff secureip//
    write_log("autoclean", " -------------------- Auto cleanup Complete using $queries queries --------------------");
   }

function doslowcleanup()
{
    global $SITENAME, $ai, $autoinvitetime, $DEFAULTBASEURL, $usergroups, $torrent_dir, $autoslowclean_interval, $READPOST_EXPIRY, $CACHE, $queries, $query_stat, $delaccounts, $max_dead_user_time, $ad_ratio, $ap_time, $ap_limit, $ap_ratio;
    set_time_limit(1200);
    $result = sql_query("show processlist") or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_array($result)) {
        if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
            $sql = "kill " . $row["Id"] . "";
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
        }
    }
    ignore_user_abort(1);
    if ($delaccounts) {
        // delete inactive user accounts
        $dt = sqlesc(get_date_time(gmtime() - $max_dead_user_time));
        $maximumclass = UC_POWER_USER;
        sql_query("DELETE FROM users WHERE status='confirmed' AND parked='no' AND class <= $maximumclass AND last_access < $dt");
    }
    
    // == Updated promote power users
    $limit = 25 * 1024 * 1024 * 1024;
    $res = sql_query("SELECT id, uploaded, downloaded FROM users WHERE class = 0 AND uploaded >= $limit AND uploaded / downloaded >= $ap_ratio AND enabled='yes' and added < DATE_SUB(NOW(), INTERVAL 28 DAY)") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysql_num_rows($res) > 0) {
        $msg = "Congratulations, you have been Auto-Promoted to [b]Power User[/b]. :)\n You can enter the casino your account wont get deleted automatically and you get one extra invite :).\n";
        while ($arr = mysql_fetch_assoc($res)) {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $modcomment = sqlesc(gmdate("Y-m-d H:i") . " - Promoted to Power User by System (UL=" . prefixed($arr['uploaded']) . ", DL=" . prefixed($arr['downloaded']) . ", R=" . $ratio . ") \n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ',NOW(), ' . sqlesc($msg) . ', \'Promotion\')';
            $users_buffer[] = '(' . $arr['id'] . ',1,1,' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, class, invites, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE class=values(class), invites = invites+values(invites), modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("promotion", "Delayed Cleanup: Promoted " . $count / 2 . " member(s) from User to Power User");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
        status_change($arr['id']);
    }
    // == Updated demote power users
    $res = sql_query("SELECT id, uploaded, downloaded FROM users WHERE class = 1 AND uploaded / downloaded < $ad_ratio") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysql_num_rows($res) > 0) {
        $msg = "You have been auto-demoted from [b]Power User[/b] to [b]User[/b] because your share ratio has dropped below < $ad_ratio.\n";

        while ($arr = mysql_fetch_assoc($res)) {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $modcomment = sqlesc(gmdate("Y-m-d H:i") . " - Demoted To User by System (UL=" . prefixed($arr['uploaded']) . ", DL=" . prefixed($arr['downloaded']) . ", R=" . $ratio . ") \n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ',NOW(), ' . sqlesc($msg) . ', \'Demotion\')';
            $users_buffer[] = '(' . $arr['id'] . ',0,' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, class, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE class=values(class),
modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("demotion", "Delayed Cleanup: Demoted " . $count / 2 . " member(s) from Power User to User");
            status_change($arr['id']);
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    // ////////////////////end//
    // ///////////////////////happyhour////
    $f = "$CACHE/happyhour.txt"; //==linux
    //$f = "C://AppServ/www/cache/happyhour.txt"; //==windows - *change* appserv if you use xammp
    $happy = unserialize(file_get_contents($f));
    $happyHour = strtotime($happy["time"]);
    $curDate = time();
    $happyEnd = $happyHour + 3600;
    if ($happy["status"] == 0) {
        write_log("happyhour", "Happy hour was @ " . date("Y-m-d H:i" , $happyHour) . " and Catid " . $happy["catid"] . " ");
        happyFile("set");
    } elseif (($curDate > $happyEnd) && $happy["status"] == 1)
        happyFile("reset");
    // ////////////end///////
    // Remove userprofile views
    $days = 7;
    $dt = sqlesc(get_date_time(gmtime() - ($days * 68400)));
    sql_query("DELETE FROM userhits WHERE added < $dt");
    // //////////////////reset ips/////
    $secs = 24 * 60 * 60; //  24Hours * 60 minutes * 60 seconds...
    $dt = sqlesc(get_date_time(gmtime() - $secs));
    sql_query("UPDATE users SET ip = '' WHERE last_access < $dt");
    // Remove expired readposts...
    $dt = sqlesc(get_date_time(gmtime() - $READPOST_EXPIRY));
    sql_query("DELETE readposts FROM readposts ".
    "LEFT JOIN posts ON readposts.lastpostread = posts.id ".
    "WHERE posts.added < $dt");
    // Automatic Invite
	  if ($ai == 1) {
		$days = $autoinvitetime * 86400;
		$dt = sqlesc(get_date_time(gmtime() - $days));
		$res = sql_query('SELECT id,modcomment,lastinvite,class FROM users WHERE class >= \''.UC_USER.'\' and enabled=\'yes\'and status=\'confirmed\' and lastinvite < '.$dt);
		if (mysql_num_rows($res) > 0)
		{		 	
			while ($arr = mysql_fetch_array($res))
			{
				$subject = sqlesc('Automatic Invite!');
				$lastinvite=sqlesc(get_date_time());
				$gid = $arr['class'] + 1;
				$getamount = sql_query('SELECT autoinvite FROM usergroups WHERE gid = '.$gid);
				$amount = mysql_fetch_array($getamount);
				if ($amount['autoinvite'] != 0) {
					$msg = sqlesc('Congratulations, you have received '.$amount['autoinvite'].' invites.
					If you would like to invite your friends, please click [url='.$DEFAULTBASEURL.'/invite.php]here[/url].');
					$modcomment = htmlspecialchars($arr['modcomment']);
					$modcomment = gmdate("Y-m-d") . " - Earned ".$amount['autoinvite']." invites by system.\n". $modcomment;
					$modcom = sqlesc($modcomment);
					sql_query("UPDATE users SET lastinvite = $lastinvite, invites = invites + ".$amount['autoinvite'].", modcomment = $modcom WHERE id = ".sqlesc($arr['id']));
					sql_query("INSERT INTO messages (sender, receiver, added, subject, msg, poster) VALUES(0, $arr[id], $lastinvite, $subject, $msg, 0)");
				}
			}
		 }
	  }
    write_log("slowautoclean", " -------------------- Delayed cleanup Complete using $queries queries --------------------");
}

function dos2slowcleanup()
{
    global $SITENAME, $DEFAULTBASEURL, $usergroups, $torrent_dir, $s2autoslowclean_interval, $queries, $query_stat, $torrent_dir, $tdeadtime, $oldtorrents, $slotduration, $max_dead_torrent_time, $max_dead_topic_time, $torrent_ttl;
    set_time_limit(1200);
    $result = sql_query("show processlist") or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_array($result)) {
        if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
            $sql = "kill " . $row["Id"] . "";
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
        }
    }
    ignore_user_abort(1);
    /*
/////////////////updated/modified clear bonus points
    $expiry_warn = 60; // 60 days
    $warn_dt = sqlesc(get_date_time(gmtime() - ($expiry_warn * 86400)));
    $maxwarn = UC_CODER;
    $res = sql_query("SELECT id FROM users WHERE seedbonus > '80000' AND lastchange < $warn_dt AND class < $maxwarn") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysql_num_rows($res) > 0) {
        $msg = "Use your Bonus Points please or it will be reset by System in 10 days.\n";
        while ($arr = mysql_fetch_assoc($res)) {
            $modcomment = sqlesc(gmdate("Y-m-d H:i") . " - Bonus points expire pm sent\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ',NOW(), ' . sqlesc($msg) . ', \'Karma Points Reset\')';
            $users_buffer[] = '(' . $arr['id'] . ',' . $modcomment . ')';
            }
            if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("bonusreset", "User bonus points reset, Delayed Cleanup: reset karma points pm sent to " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    /////////////////updated/modified clear bonus points
    $expiry_timeout = 90; // 90 days
    $rem_dt = sqlesc(get_date_time(gmtime() - ($expiry_timeout * 86400)));
    $maxrem = UC_CODER;
    $ras = sql_query("SELECT id FROM users WHERE seedbonus > '80000' AND lastchange < $rem_dt AND class < $maxrem") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysql_num_rows($ras) > 0) {
        $msg = "Bonus points deleted, You cant sit on them for 3 month's without using them you freak :-P Auto exchanged for 1 Tb.\n";
        while ($arr = mysql_fetch_assoc($ras)) {
            $modcomment = sqlesc(gmdate("Y-m-d H:i") . " - Bonus points expired - reset to 0\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ',NOW(), ' . sqlesc($msg) . ', \'Karma Points Reset\')';
            $users_buffer[] = '(' . $arr['id'] . ',0,\'0000-00-00 00:00:00\',1099511627776,' . $modcomment . ')';
            }
            if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, seedbonus, lastchange, uploaded, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE seedbonus=values(seedbonus), uploaded=uploaded+values(uploaded), lastchange=values(lastchange), modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("bonusreset", "User bonus points reset, Delayed Cleanup: reset karma points on " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
///////////////////end//////////////
*/
// == updated remove expired warnings
    $res = sql_query("SELECT id FROM users WHERE warned='yes' AND warneduntil < NOW() AND warneduntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysql_num_rows($res) > 0) {
        $msg = "Your warning has been removed. Please keep in your best behaviour from now on.\n";
        while ($arr = mysql_fetch_assoc($res)) {
            $modcomment = sqlesc(gmdate("Y-m-d H:i") . " - Warning Automatically Removed By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ',NOW(), ' . sqlesc($msg) . ', \'Warning Removed\')';
            $users_buffer[] = '(' . $arr['id'] . ',\'no\',\'0000-00-00 00:00:00\',' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, warned, warneduntil, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE warned=values(warned),
warneduntil=values(warneduntil),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("autodewarn", "Delayed Cleanup: System Removed Warning(s) from " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    // == snuggs donation progress //== updated
    // === remove karma vip - change class to whatever is under your vip class number
    $res = sql_query("SELECT id, modcomment FROM users WHERE vip_added='yes' AND vip_until < NOW()") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysql_num_rows($res) > 0) {
        $subject = "VIP status removed by system.";
        $msg = "Your VIP status has timed out and has been auto-removed by the system. Become a VIP again by donating to " . $SITENAME . ", or exchanging some Karma Bonus Points. Cheers !\n";
        while ($arr = mysql_fetch_assoc($res)) {
            $modcomment = sqlesc(gmdate("Y-m-d H:i") . " - Vip status Automatically Removed By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ',NOW(), ' . sqlesc($msg) . ', \'Vip status expired.\')';
            $users_buffer[] = '(' . $arr['id'] . ',1,\'no\',\'0000-00-00 00:00:00\', ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, class, vip_added, vip_until, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE class=values(class),
vip_added=values(vip_added),vip_until=values(vip_until),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("autoremvip", "Delayed Cleanup: Karma Vip status expired - " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    // ===end===//
    // ===clear funds after one month
    $secs = 28 * 86400;
    $dt = sqlesc(get_date_time(gmtime() - $secs));
    sql_query("DELETE FROM funds WHERE added < $dt");
    // ===end
    // === remove donor status if time up AND set class back to power user... remember to set the class number for your system//==updated===//
    $res = sql_query("SELECT id, modcomment FROM users WHERE donor='yes' AND donoruntil < NOW() AND donoruntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysql_num_rows($res) > 0) {
        $subject = "Donor status removed by system.";
        $msg = "Your Donor status has timed out and has been auto-removed by the system, and your VIP status has been removed. We would like to thank you once again for your support to " . $SITENAME . " . If you wish to re-new your donation,Visit the site paypal link. Cheers!\n";
        while ($arr = mysql_fetch_assoc($res)) {
            $modcomment = sqlesc(gmdate("Y-m-d H:i") . " - Donation status Automatically Removed By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ',NOW(), ' . sqlesc($msg) . ', \'Donation status expired thanks for the support.\')';
            $users_buffer[] = '(' . $arr['id'] . ',1,\'no\',\'0000-00-00 00:00:00\', ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, class, donor, donoruntil, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE class=values(class),
donor=values(donor),donoruntil=values(donoruntil),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("autoremdon", "Delayed Cleanup: Donation status expired - " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    // ===end===//
    // === Updated remove custom smilies :)
    $res = sql_query("SELECT id FROM users WHERE smile_until < NOW() AND smile_until <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysql_num_rows($res) > 0) {
        $msg = "Your Custom smilies have timed out and has been auto-removed by the system. If you would like to have them again, exchange some Karma Bonus Points again. Cheers!\n";
        while ($arr = mysql_fetch_assoc($res)) {
            $modcomment = sqlesc(gmdate("Y-m-d H:i") . " - Custom smilies Automatically Removed By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ',NOW(), ' . sqlesc($msg) . ', \'Custom Smilies\')';
            $users_buffer[] = '(' . $arr['id'] . ',\'0000-00-00 00:00:00\',' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, smile_until, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE smile_until=values(smile_until),
modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("customsmiles", "Delayed Cleanup: Removed Custom smilies from " . $count / 2 . " members");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
        // /==updated/modified autoleech warning script////
    $minratio = 0.4; // ratio < 0.4
    $downloaded = 10 * 1024 * 1024 * 1024; // + 10 GB
    $length = 3 * 7; // Give 3 weeks to let them sort there shit
    $res = sql_query("SELECT id FROM users WHERE class >= 0 AND leechwarn = 'no' AND uploaded / downloaded < $minratio AND downloaded >= $downloaded") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysql_num_rows($res) > 0) {
        $dt = sqlesc(get_date_time());
        $msg = "You have been warned and your download rights have been removed due to your low ratio. You need to get a ratio of 0.7 within the next 3 weeks or your downloads will remain disabled.";
        $leechwarnuntil = sqlesc(get_date_time(gmtime() + ($length * 86400)));
        while ($arr = mysql_fetch_assoc($res)) {
            $modcomment = sqlesc(gmdate("Y-m-d H:i") . " - Automatically Leech warned and downloads disabled By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ',NOW(), ' . sqlesc($msg) . ', \'Auto Leech Warn\')';
            $users_buffer[] = '(' . $arr['id'] . ',\'yes\',' . $leechwarnuntil . ',\'no\', ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, leechwarn, leechwarnuntil, downloadpos, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE leechwarn=values(leechwarn),
leechwarnuntil=values(leechwarnuntil),downloadpos=values(downloadpos),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("autowarn", "Delayed Cleanup: System applied auto leech Warning(s) to  " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    // //////////////////////////////////////////////////
    // ==Modified autoleech warn system - Remove warning and enable downloads
    $minratio = 0.7; // ratio > 0.7
    $res = sql_query("SELECT id FROM users WHERE downloadpos = 'no' AND leechwarn = 'yes' AND uploaded / downloaded >= $minratio") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = array();
    if (mysql_num_rows($res) > 0) {
        $msg = "Your warning for a low ratio has been removed and your downloads enabled. We highly recommend you to keep your ratio positive to avoid being automatically warned again.\n";
        while ($arr = mysql_fetch_assoc($res)) {
            $modcomment = sqlesc(gmdate("Y-m-d H:i") . " - Leech warn removed and download enabled By System\n");
            $msgs_buffer[] = '(0,' . $arr['id'] . ',NOW(), ' . sqlesc($msg) . ', \'Auto leech warn removal and download\')';
            $users_buffer[] = '(' . $arr['id'] . ',\'no\',\'0000-00-00 00:00:00\',\'yes\', ' . $modcomment . ')';
        }
        if (sizeof($msgs_buffer) > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO users (id, leechwarn, leechwarnuntil, downloadpos, modcomment) VALUES " . implode(', ', $users_buffer) . " ON DUPLICATE key UPDATE leechwarn=values(leechwarn),
leechwarnuntil=values(leechwarnuntil),downloadpos=values(downloadpos),modcomment=concat(values(modcomment),modcomment)") or sqlerr(__FILE__, __LINE__);
            $count = mysql_affected_rows();
            write_log("autodewarn", "Delayed Cleanup: System removed auto leech Warning(s) and renabled download(s) - " . $count / 2 . " Member(s)");
        }
        unset ($users_buffer);
        unset ($msgs_buffer);
    }
    // ///////////Delete old pms////////////
    $secs = 15 * 86400; //change this to fit your needs
    $dt = sqlesc(get_date_time(gmtime() - $secs));
    sql_query("DELETE FROM messages WHERE added < $dt");
    // //////////////////////////
    $secs = 28 * 86400; //change this to fit your needs
    $dt = sqlesc(get_date_time(gmtime() - $secs));
    sql_query("DELETE FROM iplog WHERE access < $dt");
    // delete from shoutbox after 2 days
    $secs = 2 * 86400;
    $dt = sqlesc(get_date_time(gmtime() - $secs));
    sql_query("DELETE FROM shoutbox WHERE " . time() . " - date > $secs") or sqlerr(__FILE__, __LINE__);
    // Delete Orphaned announcement_processors
    sql_query("DELETE announcement_process FROM announcement_process LEFT JOIN users ON announcement_process.user_id = users.id WHERE users.id IS NULL");
    // Delete expired announcements and processors
    sql_query("DELETE FROM announcement_main WHERE expires < " . sqlesc(get_date_time()));
    sql_query("DELETE announcement_process FROM announcement_process LEFT JOIN announcement_main ON announcement_process.main_id = announcement_main.main_id WHERE announcement_main.main_id IS NULL");
    // //////auto-delete old torrents////////         
    if ($oldtorrents) {
    $dt = sqlesc(get_date_time(gmtime() - $torrent_ttl));
    $days = 2;
    $days_la = 7;
    $dt_la = sqlesc(get_date_time(gmtime() - ($days_la * 86400)));
    $res = sql_query("SELECT id, name FROM torrents WHERE added < $dt AND seeders=0 AND leechers=0 AND last_action < $dt_la ");
    if (mysql_num_rows($res) > 0)
    {
    $deadcount = mysql_num_rows($res);
        while ($arr = mysql_fetch_assoc($res))
        {
             $ids[] = $arr['id'];
             $names[] = $arr['name'];
        }
    @unlink("$torrent_dir/".join(',', $ids).".torrent");
    sql_query("DELETE FROM torrents WHERE id IN (".join(',', $ids).")");
    sql_query("DELETE FROM peers WHERE torrent IN (".join(',', $ids).")");
    sql_query("DELETE FROM snatched WHERE torrent IN (".join(',', $ids).")");
    sql_query("DELETE FROM comments WHERE torrent IN (".join(',', $ids).")");
    sql_query("DELETE FROM files WHERE torrent IN (".join(',', $ids).")");
    write_log("torrentdelete", $deadcount." Torrents (".join(',', $ids).") (".join(', ', $names).") were deleted by system (older than $days days and no seeders or leechers in 7 day's)");
    //==autoshout - comment out if not required
    $message = $deadcount." Torrents (".join(',', $ids).") (".join(', ', $names).")) were deleted by system (older than $days days and no seeders or leechers in 7 day's)";
    autoshout($message);
    }
    }
    // lock topics where last post was made more than x days ago
    $res = sql_query("SELECT topics.id FROM topics LEFT JOIN posts ON topics.lastpost = posts.id AND topics.sticky = 'no' WHERE " . gmtime() . " - UNIX_TIMESTAMP(posts.added) > $max_dead_topic_time") or sqlerr(__FILE__, __LINE__);
    while ($arr = mysql_fetch_assoc($res))
    sql_query("UPDATE topics SET locked='yes' WHERE id=$arr[id]") or sqlerr(__FILE__, __LINE__);
    // / freeslots
    $dt = sqlesc(get_date_time(gmtime() - ($slotduration * 86400)));
    sql_query("UPDATE freeslots SET doubleup = 'no' WHERE addedup<$dt") or sqlerr(__FILE__, __LINE__);
    sql_query("UPDATE freeslots SET free = 'no' WHERE addedfree<$dt") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM freeslots WHERE doubleup = 'no' AND free = 'no'") or sqlerr(__FILE__, __LINE__);
    write_log("slowautoclean", " -------------------- Delayed cleanup stage 2 Complete using $queries queries --------------------");
    }

function dolotterycleanup()
{
    global $SITENAME, $DEFAULTBASEURL, $usergroups, $autolottery_interval, $queries, $query_stat;
    set_time_limit(1200);
    $result = sql_query("show processlist") or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_array($result)) {
        if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
            $sql = "kill " . $row["Id"] . "";
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
        }
    }
    ignore_user_abort(1);
//==Seedbonus lottery
$res = sql_query("SELECT * FROM lottery_config") or sqlerr(__FILE__, __LINE__);
while ($arr = mysql_fetch_assoc($res))
$arr_config[$arr['name']] = $arr['value'];

if ($arr_config['enable'] == 1)
{
if (get_date_time() > $arr_config['end_date'])
{
if ($arr_config["ticket_amount_type"] == seedbonus)
$arr_config['ticket_amount'] = $arr_config['ticket_amount'];
$size = $arr_config['ticket_amount'];

if ($arr_config["ticket_amount_type"] == seedbonus)
$arr_config['prize_fund'] = $arr_config['prize_fund'];
$prize_fund = $arr_config['prize_fund'];

$total = mysql_num_rows(sql_query("SELECT * FROM tickets"));
if ($arr_config["use_prize_fund"])
{
$pot = $prize_fund / $arr_config['total_winners'];
$res = sql_query("SELECT user FROM tickets ORDER BY RAND() LIMIT $arr_config[total_winners]") or sqlerr();
$who_won = array();
$msg = sqlesc("Congratulations, You have won 1: ".($pot)." . This has been added to your seedbonus total amount. Thanks for playing Lottery.");
while ($arr = mysql_fetch_assoc($res))
{
$res2 = sql_query("SELECT seedbonus, modcomment FROM users WHERE id = $arr[user]") or sqlerr(__FILE__, __LINE__);
$arr2 = mysql_fetch_assoc($res2);
$modcomment = $arr2['modcomment'];
$modcom = sqlesc("User won the lottery: " . ($pot) . " at " . get_date_time() . "\n" . $modcomment);
sql_query("UPDATE users SET seedbonus = seedbonus  + $pot, modcomment = $modcom WHERE id = $arr[user]") or sqlerr();
sql_query("INSERT INTO messages (sender, receiver, added, subject, msg, poster) VALUES(0, $arr[user], NOW(), 'You have won the Lottery', $msg, 0)") or sqlerr(__FILE__, __LINE__);
$who_won[] = $arr['user'];
}
}
else
{
$pot = $total * $size / $arr_config['total_winners'];
$res = sql_query("SELECT user FROM tickets ORDER BY RAND() LIMIT $arr_config[total_winners]") or sqlerr();
$who_won = array();
$msg = sqlesc("Congratulations, You have won : ".($pot).". This has been added to your seedbonus total amount. Thanks for playing Lottery.");
while ($arr = mysql_fetch_assoc($res))
{
$res2 = sql_query("SELECT seedbonus, modcomment FROM users WHERE id = $arr[user]") or sqlerr(__FILE__, __LINE__);
$arr2 = mysql_fetch_assoc($res2);
$modcomment = $arr2['modcomment'];
$modcom = sqlesc("User won the lottery: " . ($pot) . " at " . get_date_time() . "\n" . $modcomment);
sql_query("UPDATE users SET seedbonus = seedbonus + $pot , modcomment = $modcom WHERE id = $arr[user]") or sqlerr();
sql_query("INSERT INTO messages (sender, receiver, added, subject, msg, poster) VALUES(0, $arr[user], NOW(), 'You have won the Lottery', $msg, 0)") or sqlerr(__FILE__, __LINE__);
$who_won[] = $arr['user'];
}
}
$who_won = implode("|", $who_won);
$who_won_date = get_date_time();
$who_won_prize = $pot;
sql_query("TRUNCATE TABLE tickets") or sqlerr(__FILE__, __LINE__);
if ($who_won != '')
{
sql_query("UPDATE lottery_config SET value = '$who_won' WHERE name = 'lottery_winners'") or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE lottery_config SET value = '$who_won_prize' WHERE name = 'lottery_winners_amount'") or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE lottery_config SET value = '$who_won_date' WHERE name = 'lottery_winners_time'") or sqlerr(__FILE__, __LINE__);
}
sql_query("UPDATE lottery_config SET value = '0' WHERE name = 'enable'") or sqlerr(__FILE__, __LINE__);
}
}
//==end seedbonus lottery
write_log("autoclean", " -------------------- lottery Complete using $queries queries --------------------");
}

function dobackupdb()
{
    global $SITENAME, $CURUSER, $DEFAULTBASEURL, $backupdb_interval, $queries, $query_stat;
    set_time_limit(1200);
    $result = mysql_query("show processlist") or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_array($result)) {
        if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
            $sql = "kill " . $row["Id"] . "";
            mysql_query($sql) or sqlerr(__FILE__, __LINE__);
        }
    }
    ignore_user_abort(1);
    /* Your db-globals */
    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db, $DEFAULTBASEURL, $backup_dir, $backupdb_interval;
    $host = 'localhost';
    $db = 'xxxxx';
    $user = 'xxxxx';
    $pass = 'xxxxx';

    /* Change to the name of your backup directory */
    $backupdir = '/backup';
    /* Compute day, month, year, hour and min. */
    $today = getdate();
    $day = $today['mday'];
    if ($day < 10) {
        $day = "0$day";
    }
    $month = $today['mon'];
    if ($month < 10) {
        $month = "0$month";
    }
    $year = $today['year'];
    $hour = $today['hours'];
    $min = $today['minutes'];
    $sec = "00";
    /* Add path to your backup dir here, eg; /var/www/ */
    $dir = "/home/domain/public_html";
    /*
Execute mysqldump command.
It will produce a file named $db-$year$month$day-$hour$min.gz
under $DOCUMENT_ROOT/$backupdir
getenv('DOCUMENT_ROOT'),
*/
/*
    // ///////windows mysqldump
    system(sprintf('c:\AppServ\mysql\bin\mysqldump --opt -h %s -u %s -p%s %s  > %s/%s/%s-%s-%s-%s.sql', $host, $user, $pass, $db, getenv('DOCUMENT_ROOT'), $backupdir, $db, $day, $month, $year));
    $name = $db . "-" . $day . "-" . $month . "-" . $year . ".gz";
    $date = date("Y-m-d");
    $day = date("d");
    */
    //////////Liux mysqldump
system(sprintf( '/usr/bin/mysqldump --opt -h %s -u %s -p%s %s  > %s/%s/%s-%s-%s-%s.sql', $host,  $user,  $pass,  $db,  getenv('DOCUMENT_ROOT'),  $backupdir,  $db,  $day,  $month,  $year ));
    $name = $db."-".$day."-".$month."-".$year.".gz";$date = date("Y-m-d");
    $day = date("d");
    write_log("autobackupdb","----------------------Auto Back Up Complete using $queries queries---------------------");
}

function doautohitrun()
{
    global $SITENAME, $CURUSER, $DEFAULTBASEURL, $usergroups, $autohitrun_interval, $queries, $query_stat;
    set_time_limit(1200);
    $result = sql_query("show processlist") or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_array($result)) {
        if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
            $sql = "kill " . $row["Id"] . "";
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
        }
    }
    ignore_user_abort(1);
    //=== hit and run... after 12 hours grace :-P, add the mark of Cain...
    $res = sql_query('SELECT id FROM snatched WHERE hit_and_run <> \'0000-00-00 00:00:00\' AND hit_and_run < '.sqlesc(get_date_time(gmtime() - (43200)))) or sqlerr(__FILE__, __LINE__);    
    while ($arr = mysql_fetch_assoc($res))
    {
    sql_query('UPDATE snatched SET mark_of_cain = \'yes\' WHERE id='.sqlesc($arr['id'])) or sqlerr(__FILE__, __LINE__);
    }
    //=== hit and run... disable Downloading rights if they have 3 mark'S of cain
    $res_fuckers = sql_query('SELECT COUNT(*) AS poop, snatched.userid, users.username, users.modcomment, users.hit_and_run_total, users.downloadpos FROM snatched LEFT JOIN users ON snatched.userid = users.id WHERE snatched.mark_of_cain = \'yes\' AND users.leechwarn = \'no\' GROUP BY snatched.userid') or sqlerr(__FILE__, __LINE__);    
    while ($arr_fuckers = mysql_fetch_assoc($res_fuckers))
    {
        if ($arr_fuckers['poop'] > 2 && $arr_fuckers['downloadpos'] == 'yes')
        {
        //=== set them to no DLs
        $subject = sqlesc('Download rights disabled by System');
        $msg = sqlesc("Sorry ".$arr_fuckers['username'].",\n because you have 3 or more torrents that have not been seeded to either a 1:1 ratio, or for the expected seeding time, your downloading rights have been disabled by the auto system.\nTo get your Downloading rights back is simple,\n just start seeding the torrents in your profile [ click your username, then click your [url=".$DEFAULTBASEURL."/userdetails.php?id=".$arr_fuckers['userid']."&completed=1]Completed Torrents[/url] link to see what needs seeding ] and your downloading rights will be turned back on by System after the next clean-time [ updates 3 times a day ].\n\nDownloads are disabled after a member has three or more torrents that have not been seeded to either a 1 to 1 ratio, OR for the required seed time [ please see the [url=".$DEFAULTBASEURL."/faq.php]FAQ[/url] or [url=".$DEFAULTBASEURL."/rules.php]Site Rules[/url] for more info ]\n\nIf this message has been in error, or you feel there is a good reason for it, please feel free to PM a staff member with your concerns.\n\n we will do our best to fix this situation.\n\nBest of luck!\n ".$SITENAME." staff.\n");
        $modcomment = safeChar($arr_fuckers['modcomment']);
        $modcomment =  gmdate("Y-m-d") . " - Download rights removed for H and R - AutoSystem.\n". $modcomment;
        $modcom =  sqlesc($modcomment);
        sql_query("INSERT INTO messages (sender, receiver, added, msg, subject, poster) VALUES(0, $arr_fuckers[userid], ".sqlesc(get_date_time()).", $msg, $subject, 0)") or sqlerr(__FILE__, __LINE__);    
        sql_query('UPDATE users SET hit_and_run_total = hit_and_run_total + '.$arr_fuckers['poop'].', downloadpos = \'no\', hnrwarn = \'yes\', modcomment = '.$modcom.'  WHERE downloadpos = \'yes\' AND id='.sqlesc($arr_fuckers['userid'])) or sqlerr(__FILE__, __LINE__);
        }
    }
//=== hit and run... turn their DLs back on if they start seeding again
$res_good_boy = sql_query('SELECT id, username, modcomment FROM users WHERE hnrwarn = \'yes\' AND downloadpos = \'no\'') or sqlerr(__FILE__, __LINE__);

while ($arr_good_boy = mysql_fetch_assoc($res_good_boy))
    {
    $res_count = sql_query('SELECT COUNT(*) FROM snatched WHERE userid = '.sqlesc($arr_good_boy['id']).' AND mark_of_cain = \'yes\'') or sqlerr(__FILE__, __LINE__);
    $arr_count = mysql_fetch_row($res_count);
        if ($arr_count[0] < 3)
        {
        //=== enable DLs and remove warning
        $subject = sqlesc('Download rights restored by System');
        $msg = sqlesc("Hi ".$arr_good_boy['username'].",\n congratulations! because you have seeded the torrents that needed seeding, your downloading rights have been restored by System!\n\nhave fun!\n ".$SITENAME." staff.\n");
        $modcomment = safeChar($arr_good_boy['modcomment']);
        $modcomment =  gmdate("Y-m-d") . " - Download rights restored from H and R - AutoSystem.\n". $modcomment;
        $modcom =  sqlesc($modcomment);
        sql_query("INSERT INTO messages (sender, receiver, added, msg, subject, poster) VALUES(0, ".sqlesc($arr_good_boy['id']).", ".sqlesc(get_date_time()).", $msg, $subject, 0)") or sqlerr(__FILE__, __LINE__);
        sql_query('UPDATE users SET downloadpos = \'yes\', hnrwarn = \'no\', modcomment = '.$modcom.'  WHERE id = '.sqlesc($arr_good_boy['id'])) or sqlerr(__FILE__, __LINE__);
        }
    }
    write_log("autohitrun", " -------------------- Auto hit and run clean Complete using $queries queries --------------------");
    // /////end hitrun script////////
}

function dooptimizedb()
{
    global $SITENAME, $CURUSER, $DEFAULTBASEURL, $usergroups, $optimizedb_interval, $queries, $query_stat;
    set_time_limit(1200);
    $result = sql_query("show processlist") or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_array($result)) {
        if (($row["Time"] > 100) || ($row["Command"] == "Sleep")) {
            $sql = "kill " . $row["Id"] . "";
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
        }
    }
    ignore_user_abort(1);
    $alltables = sql_query("SHOW TABLES") or sqlerr(__FILE__, __LINE__);
    while ($table = mysql_fetch_assoc($alltables)) {
        foreach ($table as $db => $tablename) {
            $sql = "OPTIMIZE TABLE $tablename";
            /* Preg match the sql incase it was hijacked somewhere!(will use CHECK|ANALYZE|REPAIR|later) */
            if (preg_match('@^(CHECK|ANALYZE|REPAIR|OPTIMIZE)[[:space:]]TABLE[[:space:]]' . $tablename . '$@i', $sql))
                @sql_query($sql) or die("<b>Something was not right!</b>.\n<br />Query: " . $sql . "<br />\nError: (" . mysql_errno() . ") " . safeChar(mysql_error()));
        }
    }
    @mysql_free_result($alltables);
    write_log("autooptimizedb", " --------------------Auto Optimization Complete using $queries queries --------------------");
}
////////////////////////////////////////////////////////
?>