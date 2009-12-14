<?php
sleep(2);
require_once $_SERVER["DOCUMENT_ROOT"] . "/include/bittorrent.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/include/bbcode_functions.php";
dbconn();

$do = (isset($_POST["action"]) ? $_POST["action"] : "");
$choice = (isset($_POST["choice"]) ? 0 + $_POST["choice"] : 0);
$pollId = (isset($_POST["pollId"]) ? 0 + $_POST["pollId"] : 0);
$userId = $CURUSER["id"];

if ($do == "load") {
    // check to see if user voted :)
    $r_check = mysql_query("SELECT p.id,p.added,p.question,pa.selection,pa.userid FROM polls AS p LEFT JOIN pollanswers AS pa ON p.id=pa.pollid  AND pa.userid=" . $userId . " ORDER BY p.id DESC LIMIT 1") or sqlerr();
    $ar_check = mysql_fetch_assoc($r_check);
    if (mysql_num_rows($r_check) == 1) {
        $r_op = mysql_query("select * from polls WHERE id=" . $ar_check["id"]) or sqlerr();
        $a_op = mysql_fetch_assoc($r_op);
        for($i = 0;$i < 20;$i++) {
            if (!empty($a_op["option$i"]))
                $options[$i] = format_comment($a_op["option$i"]);
        }

        if ($ar_check["userid"] == null) {
            print("<div id=\"poll_title\">" . format_comment($ar_check["question"]) . "</div>\n");

            foreach($options as $op_id => $op_val) {
                print("<div><input type=\"radio\" onclick=\"addvote(" . $op_id . ")\" name=\"choices\" value=\"" . $op_id . "\" id=\"opt_" . $op_id . "\" /><label for=\"opt_" . $op_id . "\">&nbsp;" . $op_val . "</label></div>\n");
            }
            print("<input type=\"hidden\" value=\"\" name=\"choice\" id=\"choice\"/>");
            print("<input type=\"hidden\" value=\"" . $ar_check["id"] . "\" name=\"pollId\" id=\"pollId\"/>");
            print("<div align=\"center\"><input type=\"button\" value=\"Vote ->\" style=\"display:none;\" id=\"vote_b\" onclick=\"vote();\"/></div>");
        } else {
            $r = mysql_query("SELECT count(id) as count , selection  FROM pollanswers WHERE pollid=" . $ar_check["id"] . " AND selection < 20 GROUP BY selection") or sqlerr();

            while ($a = mysql_fetch_assoc($r)) {
                $total += $a["count"];
                $votes[$a["selection"]] = 0 + $a["count"];
            }

            foreach($options as $k => $op) {
                $results[] = array(0 + $votes[$k], $op);
            }

            function srt($a, $b)
            {
                if ($a[0] > $b[0]) return -1;
                if ($a[0] < $b[0]) return 1;
                return 0;
            }
            usort($results, srt);

            print("<div id=\"poll_title\">" . format_comment($ar_check["question"]) . "</div>\n");
            print("<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" style=\"border:none\" id=\"results\" class=\"results\">");
            $i = 0;
            foreach($results as $result) {
                print("<tr><td align=\"left\" width=\"40%\">" . $result[1] . "</td><td align=\"left\" width=\"60%\" valing=\"middle\"><div class=\"bar" . ($i == 0 ? "max" : "") . "\"  name=\"" . ($result[0] / $total * 100) . "\" id=\"poll_result\">&nbsp;</div></td><td>&nbsp;<b>" . number_format(($result[0] / $total * 100), 2) . "%</b></td></tr>\n");
                $i++;
            }
            print("</table>");
            print("<div align=\"center\"><b>Vote counted : " . $total . "</b></div>");
        }
    } else
        print("No current poll");
} elseif ($do == "vote") {
    if ($pollId == 0)
        print(json_encode(array("status" => 0 , "msg" => "Something was not good!")));

    else {
        $check = mysql_result(mysql_query("SELECT count(id) FROM pollanswers WHERE pollid=" . $pollId . " AND userid=" . $userId . ""), 0);
        // die($check);
        if ($check == 0) {
            mysql_query("INSERT INTO pollanswers VALUES(0,$pollId, $userId, $choice)") or die(mysql_error());
            if (mysql_affected_rows() != 1)
                print(json_encode(array("status" => 0 , "msg" => "There was an error will storing your vote!Please try again")));
            else
                print(json_encode(array("status" => 1)));
        } else
            print(json_encode(array("status" => 0 , "msg" => "Dupe vote")));
    }
}

?>