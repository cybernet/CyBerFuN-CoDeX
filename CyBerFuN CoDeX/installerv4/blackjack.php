<?php
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
parked();
dbconn(false);
maxcoder();
getpage();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_POWER_USER) {
    stdhead();
    stdmsg("Sorry...", "You must be a Power User or above to play Blackjack.");
    stdfoot();
    exit;
}
if ($CURUSER["blackjackban"] == 'yes') {
    stdhead();
    stdmsg("Sorry...", "You are Banned from playing Blackjack.  (See site staff for the reason why !");
    stdfoot();
    exit;
}

// Bet size - 100 MB
$mb = 100 * 1024 * 1024;

$now = sqlesc(get_date_time());
// Pull the user's statisctics
$r = sql_query("SELECT bjwins, bjlosses FROM users WHERE id=$CURUSER[id]");
$a = mysql_fetch_array($r);
$tot_wins = $a[bjwins];
$tot_losses = $a[bjlosses];
$tot_games = $tot_wins + $tot_losses;
// If this gets through there is an error somewhere!
$win_perc = "<a href=\"/sendmessage.php?receiver=1\">Error!</a>";
// Calculate user's win percentage
if ($tot_losses == 0) {
    if ($tot_wins > 0) // 0 losses, > 0 wins = 100%
        $win_perc = "100%";
    if ($tot_wins == 0) // 0 losses, 0 wins = "---"
        $win_perc = "---";
} else if ($tot_losses > 0) {
    if ($tot_wins == 0) // > 0 losses, 0 wins = 0%
        $win_perc = "0";
    if ($tot_wins > 0) // > 0 losses, > 0 wins = return win % rounded to nearest tenth
        $win_perc = number_format(($tot_wins / $tot_games) * 100, 1);
    $win_perc .= "%";
}
// Add a user's +/- statistic
$plus_minus = $tot_wins - $tot_losses;
if ($plus_minus >= 0) {
    $plus_minus = prefixed(($tot_wins - $tot_losses) * $mb);
} else {
    $plus_minus = "-";
    $plus_minus .= prefixed(($tot_losses - $tot_wins) * $mb);
}
// Game Mechanics
if ($_POST["game"]) {
    $cardcountres = sql_query("select count(id) from cards") or sqlerr(__FILE__, __LINE__);
    $cardcountarr = mysql_fetch_array($cardcountres);
    $cardcount = $cardcountarr[0];
    if ($_POST["game"] == 'start') {
        if ($CURUSER["uploaded"] < $mb)
            stderr("Sorry " . $CURUSER["username"], "You haven't uploaded " . prefixed($mb) . " yet.");
        $required_ratio = 0.3;
        if ($CURUSER["downloaded"] > 0)
            $ratio = number_format($CURUSER["uploaded"] / $CURUSER["downloaded"], 2);
        else
        if ($CURUSER["uploaded"] > 0)
            $ratio = 999;
        else
            $ratio = 0;
        if ($ratio < $required_ratio)
            stderr("Sorry " . $CURUSER["username"], "Your ratio is lower than the requirement of " . $required_ratio . "%.");
        $res = sql_query("select count(*) from blackjack where userid=$CURUSER[id] and status='waiting'");
        $arr = mysql_fetch_array($res);
        if ($arr[0] > 0) {
            stderr("Sorry", "You'll have to wait until your last game completes before you play a new one.");
        } else {
            $res = sql_query("select count(*) from blackjack where userid=$CURUSER[id] and status='playing'");
            $arr = mysql_fetch_array($res);
            if ($arr[0] > 0)
                stderr("Sorry", "You must finish your old game first. <form method=post name=form action=$phpself><input type=hidden name=game value=cont><input type=submit value=' Continue old game '></form>", false);
        }
        $cardid = rand(1, $cardcount);
        $cardres = sql_query("select * from cards where id=$cardid") or sqlerr(__FILE__, __LINE__);
        $cardarr = mysql_fetch_array($cardres);
        if ($cardarr[points] == 1)
            $cardarr[points] = 11;
        sql_query("insert into blackjack (userid, points, cards, date) values($CURUSER[id], $cardarr[points], $cardid, $now)") or sqlerr(__FILE__, __LINE__);
        stdhead("Blackjack");

        echo("<h1>Welcome, <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>!</h1>\n");
        echo("<table cellspacing=0 cellpadding=3 width=600>\n");
        echo("<tr><td colspan=2 cellspacing=0 cellpadding=5 >");
        echo("<form name=blackjack method=post action=$phpself>");
        echo("<table class=message width=100% cellspacing=0 cellpadding=5 bgcolor=black>\n");
        echo("<tr><td align=center><img src=pic/cards/" . $cardarr["pic"] . " width=71 height=96 border=0></td></tr>");
        echo("<tr><td align=center><b>Points = $cardarr[points]</b></td></tr>");
        echo("<tr><td align=center><input type=hidden name=game value=cont><input type=submit value=' Hit me! '></td></tr>");
        echo("</table>");
        echo("</form>");
        echo("</td></tr></table><br>");
        stdfoot();
    } elseif ($_POST["game"] == 'cont') {
        $playeres = sql_query("select * from blackjack where userid=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
        $playerarr = mysql_fetch_array($playeres);
        $showcards = "";
        $aces = 0;
        $points = 0;
        $cards = $playerarr["cards"];
        $usedcards = explode(" ", $cards);
        $arr = array();
        foreach($usedcards as $array_list)
        $arr[] = $array_list;
        foreach($arr as $card_id) {
            $used_card = sql_query("SELECT * FROM cards WHERE id='$card_id'") or sqlerr(__FILE__, __LINE__);
            $used_cards = mysql_fetch_array($used_card);
            $showcards .= "<img src=pic/cards/" . $used_cards["pic"] . " width=71 height=96 border=0> ";
            if ($used_cards["points"] > 1)
                $points = $points + $used_cards[points];
            else
                $aces = $aces + 1;
        }
        $cardid = rand(1, $cardcount);
        while (in_array($cardid, $arr)) {
            $cardid = rand(1, $cardcount);
        }
        $cardres = sql_query("select * from cards where id=$cardid") or sqlerr(__FILE__, __LINE__);
        $cardarr = mysql_fetch_array($cardres);
        $showcards .= "<img src=pic/cards/" . $cardarr["pic"] . " width=71 height=96 border=0> ";
        if ($cardarr["points"] > 1)
            $points = $points + $cardarr["points"];
        else
            $aces = $aces + 1;
        for($i = 0; $i < $aces; $i++) {
            if ($points < 11 && $aces - $i == 1)
                $points = $points + 11;
            else
                $points = $points + 1;
        }

        $mysqlcards = "$playerarr[cards] $cardid";
        sql_query("update blackjack set points=$points, cards='$mysqlcards' where userid=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
        if ($points == 21) {
            $waitres = sql_query("select count(*) from blackjack where status='waiting' and userid!=$CURUSER[id]");
            $waitarr = mysql_fetch_array($waitres);
            stdhead("Blackjack");
            echo("<h1>Game over</h1>\n");
            echo("<table cellspacing=0 cellpadding=3 width=600>\n");
            echo("<tr><td colspan=2 cellspacing=0 cellpadding=5 >");
            echo("<table class=message width=100% cellspacing=0 cellpadding=5 bgcolor=black>\n");
            echo("<tr><td align=center>$showcards</td></tr>");
            echo("<tr><td align=center><b>Points = $points</b></td></tr>");
            if ($waitarr[0] > 0) {
                $r = sql_query("select * from blackjack where status='waiting' and userid!=$CURUSER[id] order by date asc LIMIT 1");
                $a = mysql_fetch_assoc($r);
                if ($a["points"] != 21) {
                    $winorlose = "you won " . prefixed($mb);
                    sql_query("update users set uploaded = uploaded + $mb, bjwins = bjwins + 1 where id=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
                    sql_query("update users set uploaded = uploaded - $mb, bjlosses = bjlosses + 1 where id=$a[userid]") or sqlerr(__FILE__, __LINE__);
                    sql_query("delete from blackjack where userid=$CURUSER[id]");
                    sql_query("delete from blackjack where userid=$a[userid]");
                    $dt = sqlesc(get_date_time());
                    $msg = sqlesc("You lost to $CURUSER[username] (You had $a[points] points, $CURUSER[username] had 21 points).\n\n - [b][url=blackjack.php]Play again[/url][/b]");
                    $subject = sqlesc("BlackJack Results.");
                    sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES(0, $a[userid], $dt, $msg, 0, $subject)") or sqlerr(__FILE__, __LINE__);
                } else {
                    $winorlose = "nobody won";
                    sql_query("delete from blackjack where userid=$CURUSER[id]");
                    sql_query("delete from blackjack where userid=$a[userid]");
                    $dt = sqlesc(get_date_time());
                    $msg = sqlesc("You tied with $CURUSER[username] (You both had $a[points] points).\n\n - [b][url=blackjack.php]Play again[/url][/b]");
                    $subject = sqlesc("BlackJack Results.");
                    sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES(0, $a[userid], $dt, $msg, 0, $subject)") or sqlerr(__FILE__, __LINE__);
                }
                echo("<tr><td align=center>Your opponent was " . get_user_name($a["userid"]) . ", they had $a[points] points, $winorlose.<br /><br /><center><b><a href=blackjack.php>Play again</a></b></center></td></tr>");
            } else {
                sql_query("update blackjack set status = 'waiting', date='" . get_date_time() . "' where userid = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
                echo("<tr><td align=center>There are no other players, so you'll have to wait until someone plays against you.<br />You will receive a PM with the game results.<br /><br /><center><b><a href=blackjack.php>Back</a></b><br /></center></td></tr>");
            }
            echo("</table>");
            echo("</td></tr></table><br>");
            stdfoot();
        } elseif ($points > 21) {
            $waitres = sql_query("select count(*) from blackjack where status='waiting' and userid!=$CURUSER[id]");
            $waitarr = mysql_fetch_array($waitres);
            stdhead("Blackjack");
            echo("<h1>Game over</h1>\n");
            echo("<table cellspacing=0 cellpadding=3 width=600>\n");
            echo("<tr><td colspan=2 cellspacing=0 cellpadding=5 >");
            echo("<table class=message width=100% cellspacing=0 cellpadding=5 bgcolor=black>\n");
            echo("<tr><td align=center>$showcards</td></tr>");
            echo("<tr><td align=center><b>Points = $points</b></td></tr>");
            if ($waitarr[0] > 0) {
                $r = sql_query("select * from blackjack where status='waiting' and userid!=$CURUSER[id] order by date asc LIMIT 1");
                $a = mysql_fetch_assoc($r);
                if ($a["points"] > 21) {
                    $winorlose = "nobody won";
                    sql_query("delete from blackjack where userid=$CURUSER[id]");
                    sql_query("delete from blackjack where userid=$a[userid]");
                    $dt = sqlesc(get_date_time());
                    $msg = sqlesc("Your opponent was $CURUSER[username], nobody won.\n\n - [b][url=blackjack.php]Play again[/url][/b]");
                    $subject = sqlesc("BlackJack Results.");
                    sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES(0, $a[userid], $dt, $msg, 0, $subject)") or sqlerr(__FILE__, __LINE__);
                } else {
                    $winorlose = "you lost " . prefixed($mb);
                    sql_query("update users set uploaded = uploaded - $mb, bjlosses = bjlosses + 1 where id=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
                    sql_query("update users set uploaded = uploaded + $mb, bjwins = bjwins + 1 where id=$a[userid]") or sqlerr(__FILE__, __LINE__);
                    sql_query("delete from blackjack where userid=$CURUSER[id]");
                    sql_query("delete from blackjack where userid=$a[userid]");
                    $dt = sqlesc(get_date_time());
                    $msg = sqlesc("You beat $CURUSER[username] (You had $a[points] points, $CURUSER[username] had $points points).\n\n - [b][url=blackjack.php]Play again[/url][/b]");
                    $subject = sqlesc("BlackJack Results.");
                    sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, Subject) VALUES(0, $a[userid], $dt, $msg, 0, $subject)") or sqlerr(__FILE__, __LINE__);
                }
                echo("<tr><td align=center>Your opponent was " . get_user_name($a["userid"]) . ", they had $a[points] points, $winorlose.<br /><br /><center><b><a href=blackjack.php>Play again</a></b></center></td></tr>");
            } else {
                sql_query("update blackjack set status = 'waiting', date='" . get_date_time() . "' where userid = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
                echo("<tr><td align=center>There are no other players, so you'll have to wait until someone plays against you.<br />You will receive a PM with the game results.<br /><br /><center><b><a href=blackjack.php>Back</a></b><br /></center></td></tr>");
            }
            echo("</table>");
            echo("</td></tr></table><br>");
            stdfoot();
        } else {
            stdhead("Blackjack");
            echo("<h1>Welcome, <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>!</h1>\n");
            echo("<table cellspacing=0 cellpadding=3 width=600>\n");
            echo("<tr><td colspan=2 cellspacing=0 cellpadding=5 >");
            echo("<table class=message width=100% cellspacing=0 cellpadding=5 bgcolor=black>\n");
            echo("<tr><td align=center>$showcards</td></tr>");
            echo("<tr><td align=center><b>Points = $points</b></td></tr>");
            echo("<form name=blackjack method=post action=$phpself>");
            echo("<tr><td align=center><input type=hidden name=game value=cont><input type=submit value=' Hit Me '></td></tr>");
            echo("</form>");
            echo("<form name=blackjack method=post action=$phpself>");
            echo("<tr><td align=center><input type=hidden name=game value=stop><input type=submit value=' Stay '></td></tr>");
            echo("</form>");
            echo("</table>");
            echo("</td></tr></table><br>");
            stdfoot();
        }
    } elseif ($_POST["game"] == 'stop') {
        $playeres = sql_query("select * from blackjack where userid=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
        $playerarr = mysql_fetch_array($playeres);
        $showcards = "";
        $cards = $playerarr["cards"];
        $usedcards = explode(" ", $cards);
        $arr = array();
        foreach($usedcards as $array_list)
        $arr[] = $array_list;
        foreach($arr as $card_id) {
            $used_card = sql_query("SELECT * FROM cards WHERE id='$card_id'") or sqlerr(__FILE__, __LINE__);
            $used_cards = mysql_fetch_array($used_card);
            $showcards .= "<img src=pic/cards/" . $used_cards["pic"] . " width=71 height=96 border=0> ";
        }
        $waitres = sql_query("select count(*) from blackjack where status='waiting' and userid!=$CURUSER[id]");
        $waitarr = mysql_fetch_array($waitres);
        stdhead("Blackjack");
        echo("<h1>Game over</h1>\n");
        echo("<table cellspacing=0 cellpadding=3 width=600>\n");
        echo("<tr><td colspan=2 cellspacing=0 cellpadding=5 >");
        echo("<table class=message width=100% cellspacing=0 cellpadding=5 bgcolor=black>\n");
        echo("<tr><td align=center>$showcards</td></tr>");
        echo("<tr><td align=center><b>Points = $playerarr[points]</b></td></tr>");
        if ($waitarr[0] > 0) {
            $r = sql_query("select * from blackjack where status='waiting' and userid!=$CURUSER[id] order by date asc LIMIT 1");
            $a = mysql_fetch_assoc($r);
            if ($a["points"] == $playerarr[points]) {
                $winorlose = "nobody won";
                sql_query("delete from blackjack where userid=$CURUSER[id]");
                sql_query("delete from blackjack where userid=$a[userid]");
                $dt = sqlesc(get_date_time());
                $msg = sqlesc("Your opponent was $CURUSER[username], you both had $a[points] points - it was a tie.\n\n - [b][url=blackjack.php]Play again[/url][/b]");
                $subject = sqlesc("BlackJack Results.");
                sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES(0, $a[userid], $dt, $msg, 0, $subject)") or sqlerr(__FILE__, __LINE__);
            } elseif ($a["points"] < $playerarr[points] && $a[points] < 21) {
                $winorlose = "you won " . prefixed($mb);
                sql_query("update users set uploaded = uploaded + $mb, bjwins = bjwins + 1 where id=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
                sql_query("update users set uploaded = uploaded - $mb, bjlosses = bjlosses + 1 where id=$a[userid]") or sqlerr(__FILE__, __LINE__);
                sql_query("delete from blackjack where userid=$CURUSER[id]");
                sql_query("delete from blackjack where userid=$a[userid]");
                $dt = sqlesc(get_date_time());
                $msg = sqlesc("You lost to $CURUSER[username] (You had $a[points] points, $CURUSER[username] had $playerarr[points] points).\n\n - [b][url=blackjack.php]Play again[/url][/b]");
                $subject = sqlesc("BlackJack Results.");
                sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES(0, $a[userid], $dt, $msg, 0, $subject)") or sqlerr(__FILE__, __LINE__);
            } elseif ($a["points"] > $playerarr[points] && $a[points] < 21) {
                $winorlose = "you lost " . prefixed($mb);
                sql_query("update users set uploaded = uploaded - $mb, bjlosses = bjlosses + 1 where id=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
                sql_query("update users set uploaded = uploaded + $mb, bjwins = bjwins + 1 where id=$a[userid]") or sqlerr(__FILE__, __LINE__);
                sql_query("delete from blackjack where userid=$CURUSER[id]");
                sql_query("delete from blackjack where userid=$a[userid]");
                $dt = sqlesc(get_date_time());
                $msg = sqlesc("You beat $CURUSER[username] (You had $a[points] points, $CURUSER[username] had $playerarr[points] points).\n\n - [b][url=blackjack.php]Play again[/url][/b]");
                $subject = sqlesc("BlackJack Results.");
                sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES(0, $a[userid], $dt, $msg, 0, $subject)") or sqlerr(__FILE__, __LINE__);
            } elseif ($a["points"] == 21) {
                $winorlose = "you lost " . prefixed($mb);
                sql_query("update users set uploaded = uploaded - $mb, bjlosses = bjlosses + 1 where id=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
                sql_query("update users set uploaded = uploaded + $mb, bjwins = bjwins + 1 where id=$a[userid]") or sqlerr(__FILE__, __LINE__);
                sql_query("delete from blackjack where userid=$CURUSER[id]");
                sql_query("delete from blackjack where userid=$a[userid]");
                $dt = sqlesc(get_date_time());
                $msg = sqlesc("You beat $CURUSER[username] (You had $a[points] points, $CURUSER[username] had $playerarr[points] points).\n\n - [b][url=blackjack.php]Play again[/url][/b]");
                $subject = sqlesc("BlackJack Results.");
                sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES(0, $a[userid], $dt, $msg, 0, $subject)") or sqlerr(__FILE__, __LINE__);
            } elseif ($a["points"] < $playerarr[points] && $a[points] > 21) {
                $winorlose = "you lost " . prefixed($mb);
                sql_query("update users set uploaded = uploaded - $mb, bjlosses = bjlosses + 1 where id=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
                sql_query("update users set uploaded = uploaded + $mb, bjwins = bjwins + 1 where id=$a[userid]") or sqlerr(__FILE__, __LINE__);
                sql_query("delete from blackjack where userid=$CURUSER[id]");
                sql_query("delete from blackjack where userid=$a[userid]");
                $dt = sqlesc(get_date_time());
                $msg = sqlesc("You beat $CURUSER[username] (You had $a[points] points, $CURUSER[username] had $playerarr[points] points).\n\n - [b][url=blackjack.php]Play again[/url][/b]");
                $subject = sqlesc("BlackJack Results.");
                sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES(0, $a[userid], $dt, $msg, 0, $subject)") or sqlerr(__FILE__, __LINE__);
            } elseif ($a["points"] > $playerarr[points] && $a[points] > 21) {
                $winorlose = "you won " . prefixed($mb);
                sql_query("update users set uploaded = uploaded + $mb, bjwins = bjwins + 1 where id=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
                sql_query("update users set uploaded = uploaded - $mb, bjlosses = bjlosses + 1 where id=$a[userid]") or sqlerr(__FILE__, __LINE__);
                sql_query("delete from blackjack where userid=$CURUSER[id]");
                sql_query("delete from blackjack where userid=$a[userid]");
                $dt = sqlesc(get_date_time());
                $msg = sqlesc("You lost to $CURUSER[username] (You had $a[points] points, $CURUSER[username] had $playerarr[points] points).\n\n - [b][url=blackjack.php]Play again[/url][/b]");
                $subject = sqlesc("BlackJack Results.");
                sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES(0, $a[userid], $dt, $msg, 0, $subject)") or sqlerr(__FILE__, __LINE__);
            }
            echo("<tr><td align=center>Your opponent was " . get_user_name($a["userid"]) . ", they had $a[points] points, $winorlose.<br /><br /><center><b><a href=blackjack.php>Play again</a></b></center></td></tr>");
        } else {
            sql_query("update blackjack set status = 'waiting', date='" . get_date_time() . "' where userid = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
            echo("<tr><td align=center>There are no other players, so you'll have to wait until someone plays against you.<br />You will receive a PM with the game results.<br /><br /><center><b><a href=blackjack.php>Back</a></b><br /></center></td></tr>");
        }
        echo("</table>");
        echo("</td></tr></table><br>");
        stdfoot();
    }
} else {
    // Start screen - Not currently playing a game
    stdhead("Blackjack");
    echo("<h1><center>Blackjack</center></h1>\n");
    echo("<table cellspacing=0 cellpadding=3 width=400>\n");
    echo("<tr><td colspan=2 cellspacing=0 cellpadding=5 align=center>\n");
    echo("<table class=message width=100% cellspacing=0 cellpadding=10 bgcolor=black>\n");
    echo("<tr><td align=center><img src=pic/cards/tp.bmp width=71 height=96 border=0> <img src=pic/cards/vp.bmp width=71 height=96 border=0> </td></tr>\n");
    echo("<tr><td align=left>You must collect 21 points without going over.<br><br>\n");
    echo("<b>NOTE:</b> By playing blackjack, you are betting 100 MB of upload credit!</td></tr>\n");
    echo("<tr><td align=center>\n");
    echo("<form name=form method=post action=$phpself><input type=hidden name=game value=start><input type=submit class=btn value='Start!'>\n");
    echo("</td></tr></table>\n");
    echo("</td></tr></table>\n");

    echo("<br /><br /><br />\n");

    echo("<table cellspacing=0 cellpadding=3 width=400>\n");
    echo("<tr><td colspan=2 cellspacing=0 cellpadding=5 align=center>\n");
    echo("<h1><a href=bjstats.php?type=3><center>Personal Statistics</center></a></h1>\n");
    echo("<tr><td align=left><b>Wins</b></td><td align=center><b>$tot_wins</b></td></tr>\n");
    echo("<tr><td align=left><b>Losses</b></td><td align=center><b>$tot_losses</b></td></tr>\n");
    echo("<tr><td align=left><b>Games Played</b></td><td align=center><b>$tot_games</b></td></tr>\n");
    echo("<tr><td align=left><b>Win Percentage</b></td><td align=center><b>$win_perc</b></td></tr>\n");
    echo("<tr><td align=left><b>+/-</b></td><td align=center><b>$plus_minus</b></td></tr>\n");
    echo("</td></tr></table>\n");

    stdfoot();
}

?>