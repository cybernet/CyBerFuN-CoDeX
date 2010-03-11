<?php
  require_once($_SERVER["DOCUMENT_ROOT"] . "/include/bittorrent.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/cache/countries.php");
  dbconn();
  
  $_settings = $_SERVER["DOCUMENT_ROOT"]."/avatars/settings/";
  function getStats($user, $forced = false)
  {
      GLOBAL $_settings, $countries;
      
      if (!file_exists($_settings . $user . ".set") || !is_array($var = unserialize(file_get_contents($_settings . $user . ".set"))))
        return false;
      
      $q = mysql_query("SELECT u.id,u.last_login, u.reputation,u.uploaded,u.downloaded,u.country,u.agent,u.hits,u.uptime, count(p.id) as posts ,count(c.id) as comments FROM users as u LEFT JOIN posts as p ON u.id = p.userid LEFT JOIN comments as c ON c.user = u.id WHERE u.username = " . sqlesc($user) . " GROUP BY u.id") or die('Error Error Error!');
          if (mysql_num_rows($q) != 1)
              die('Error Error Error!');
          
          $a = mysql_fetch_assoc($q);
          $ops = array($var['line1']['value'], $var['line2']['value'], $var['line3']['value']);
          $i = 1;
          foreach ($ops as $op) {
              switch ($op) {
                  case 1:
                      $var['line'.$i]['value_p'] = $a['posts'] . " post" . ($a['posts'] > 1 ? "s" : "");
                      break;
                  case 2:
                      #$var['line'.$i]['value_p'] = mksize($a['downloaded']) . " - " . mksize($a['uploaded']);
                      $var['line'.$i]['value_p'] = prefixed($a['downloaded']) . " - " . prefixed($a['uploaded']);
                      break;
                  case 3:
                      #list($days,$hours,$mins) = explode(",",calctime($a['onirct']));
                      #$var['line'.$i]['value_p'] = "$days days - $hours hours";
                      $var['line'.$i]['value_p'] = "not yet";
                      break;
                  case 4:
                      $var['line'.$i]['value_p'] = $a['reputation'] . " point" . ($a['reputation'] > 1 ? "s" : "");
                      break;
                  case 5:
                      foreach ($countries as $c)
                          if ($c['id'] == $a['country'])
                            $var['line'.$i]['value_p'] = $c;
                      break;
                  case 6:
                      $var['line'.$i]['value_p'] = $a['comments'] . " comment" . ($a['comments'] > 1 ? "s" : "");
                      break;
                  case 7:
                      $var['line'.$i]['value_p'] = $a['agent'];
                      break;
                  case 8:
                      $var['line'.$i]['value_p'] = $a['hits'] . " hit" . ($a['hits'] > 1 ? "s" : "");
                      break;
                  case 9:
                      $lapsetime = ((($lapsetime = time() - sql_timestamp_to_unix_timestamp($a["last_login"])) / 3600) % 24) . ' h ' . (($lapsetime / 60) % 60) . ' min ' . ($lapsetime % 60) . ' s';
                      $var['line'.$i]['value_p'] = $lapsetime;
                      break;
              }
              $i++;
          }
		  if(is_writable($_settings.$user.".set"))
		  file_put_contents($_settings.$user.".set",serialize($var));
			else exit("Can't write user setting");
			
		  if(file_exists($_settings.$user.".png"))
			unlink($_settings.$user.".png");
		  return $var;
  }
?>