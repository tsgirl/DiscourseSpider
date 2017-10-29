<?php
  $ti=time();
  $maxe=ini_get('max_execution_time');
  require_once('config.php');
  require_once('include.php');
  $page_finish=0;
  $topic_finish=0;
  $topic_part_finish=0;
  $page_break=0;
  $con = mysql_connect($dbconfig['server'],$dbconfig['user'],$dbconfig['pass']);
  if(!$con) exit(mysql_error());
  mysql_select_db($dbconfig['name'], $con);
  $dbs=mysql_fetch_array(dbquery('SELECT * FROM `config` WHERE `id`=1'));
  if(!$dbs||!$dbs['value']||!$session=json_decode(base64_decode($dbs['value']), true)){
    if(!$dbs) dbquery('INSERT INTO `config` (id, name) VALUES (1, \'userinfo\')');
    $session=getsession();
    dbquery('UPDATE `config` SET `value`=\''.base64_encode(json_encode($session)).'\' WHERE `id`=1');
  }else{
    $sysconfig['_t']=$session['_t'];
    $session=getsession();
  }
  if(!$session['user']) echo '<br />WARNING: username not found in response (_t is invaild?)';
  $status['page']=mysql_fetch_array(dbquery('SELECT * FROM `status` WHERE `id`=1'));
  $status['topic']=mysql_fetch_array(dbquery('SELECT * FROM `status` WHERE `id`=2'));
  $status['finish']=mysql_fetch_array(dbquery('SELECT * FROM `status` WHERE `id`=3'));
  echo '<br />Page: '.$status['page']['value'];
  $re=getlatest($status['page']['value'], $session['session'], $status['finish']['value']);
  $session['session']=$re['session'];
  if(!$re['content']) exit('<br />ERROR: can\'t decode return data (latest.json)');
  if(sizeof($re['content']['topic_list']['topics'])==0) exit('<br />ERROR: return topic list empty (latest.json)');
  for($x=$status['topic']['value'];$x<sizeof($re['content']['topic_list']['topics']);$x++){
    if($maxe&&(time()-$ti)>(0.7*$maxe)) break;
    echo '<br />....Topic: '.$status['topic']['value'].' ('.$re['content']['topic_list']['topics'][$x]['id'].')';
    $creatime=strtotime($re['content']['topic_list']['topics'][$x]['created_at']);
    $updatime=strtotime($re['content']['topic_list']['topics'][$x]['last_posted_at']);
    $re['content']['topic_list']['topics'][$x]['title']=base64_encode($re['content']['topic_list']['topics'][$x]['title']);
    $status['post']=mysql_fetch_array(dbquery('SELECT * FROM `topics` WHERE `id`='.$re['content']['topic_list']['topics'][$x]['id']));
    if($status['post']){
      if($status['post']['offset']==$re['content']['topic_list']['topics'][$x]['posts_count']){
        echo ' ['.$status['post']['offset'].'] post count unchanged, skipping...';
        $topic_finish=1;
        $status['topic']['value']++;
        if($status['finish']['value']){
          if($re['content']['topic_list']['topics'][$x]['pinned_globally']){
            continue;
          }else{
            $page_finish=1;
            $page_break=1;
            break;
          }
        }else{
          continue;
        }
      }
    }else{
      $username='';
      for($xx=0; $xx<sizeof($re['content']['users']); $xx++){
        if($re['content']['users'][$xx]['id']==$re['content']['topic_list']['topics'][$x]['posters'][0]['user_id']){
          $username=base64_encode($re['content']['users'][$xx]['username']);
          break;
        }
      }
      dbquery("INSERT INTO `topics` VALUES ('{$re['content']['topic_list']['topics'][$x]['id']}', '', 0, '{$re['content']['topic_list']['topics'][$x]['title']}', '{$creatime}', '{$updatime}', 0, '{$username}')");
    }
    $session=getstream($re['content']['topic_list']['topics'][$x]['id'], $session['session']);
    if(!$session['content']) exit('<br />ERROR: can\'t decode return data ('.$re['content']['topic_list']['topics'][$x]['id'].'.json)');
    $status['post']['count']=sizeof($session['content']);
    dbquery("UPDATE `topics` SET count='{$status['post']['count']}', updatime='{$updatime}' WHERE `id`='{$re['content']['topic_list']['topics'][$x]['id']}'");
    $status['post']=mysql_fetch_array(dbquery('SELECT * FROM `topics` WHERE `id`='.$re['content']['topic_list']['topics'][$x]['id']));
    $status['post']['stream']=$session['content'];
    echo ' ['.sizeof($session['content']).']';
    if($status['post']['offset']>=sizeof($status['post']['stream'])){
      $topic_finish=1;
      $page_finish=1;
      $status['topic']['value']++;
      continue;
    }
    $page_finish=0;
    while($status['post']['offset']<sizeof($status['post']['stream'])){
      if($maxe&&(time()-$ti)>(0.8*$maxe)) break;
      $topic_finish=0;
      $session=getpost($re['content']['topic_list']['topics'][$x]['id'], $status['post']['stream'], $session['session'], $status['post']['offset'], 20);
      if(!$session['content']) exit('<br />ERROR: can\'t decode return data (t/'.$re['content']['topic_list']['topics'][$x]['id'].'/posts.json)');
      for($xx=0; $xx<sizeof($session['content']['post_stream']['posts']); $xx++){
        if($maxe&&(time()-$ti)>(0.9*$maxe)) break;
        $topic_part_finish=0;
        echo '<br />........Post: '.$status['post']['offset'].' ('.$session['content']['post_stream']['posts'][$xx]['id'].')';
        $creatime=strtotime($session['content']['post_stream']['posts'][$xx]['created_at']);
        $updatime=strtotime($session['content']['post_stream']['posts'][$xx]['updated_at']); 
        $session['content']['post_stream']['posts'][$xx]['cooked']=base64_encode($session['content']['post_stream']['posts'][$xx]['cooked']);
        if(isset($session['content']['post_stream']['posts'][$xx]['reply_to_user'])){
          $session['content']['post_stream']['posts'][$xx]['reply_to_user']['username']=base64_encode($session['content']['post_stream']['posts'][$xx]['reply_to_user']['username']);
        }else{
          $session['content']['post_stream']['posts'][$xx]['reply_to_user']=array('username'=>'');
        }
        $session['content']['post_stream']['posts'][$xx]['name']=base64_encode($session['content']['post_stream']['posts'][$xx]['name']);
        $session['content']['post_stream']['posts'][$xx]['username']=base64_encode($session['content']['post_stream']['posts'][$xx]['username']);
        $session['content']['post_stream']['posts'][$xx]['avatar_template']=base64_encode($session['content']['post_stream']['posts'][$xx]['avatar_template']);
        dbquery("INSERT INTO `posts` VALUES ('{$session['content']['post_stream']['posts'][$xx]['id']}', '{$session['content']['post_stream']['posts'][$xx]['topic_id']}', '{$session['content']['post_stream']['posts'][$xx]['cooked']}', '{$creatime}', '{$updatime}', '{$session['content']['post_stream']['posts'][$xx]['user_id']}', '{$session['content']['post_stream']['posts'][$xx]['reply_to_user']['username']}')");
        dbquery("INSERT INTO `users` VALUES ('{$session['content']['post_stream']['posts'][$xx]['user_id']}', '{$session['content']['post_stream']['posts'][$xx]['name']}', '{$session['content']['post_stream']['posts'][$xx]['username']}', '{$session['content']['post_stream']['posts'][$xx]['avatar_template']}')");
        $status['post']['offset']++;
        dbquery('UPDATE `topics` SET offset='.$status['post']['offset'].' WHERE id='.$session['content']['post_stream']['posts'][$xx]['topic_id']);
        if(($xx+1)==sizeof($session['content']['post_stream']['posts'])) $topic_part_finish=1;
      } 
      if($topic_part_finish==1&&$status['post']['offset']>=sizeof($status['post']['stream'])) $topic_finish=1;
    }
    if($topic_finish){
      if(($x+1)<sizeof($re['content']['topic_list']['topics'])){
        dbquery('UPDATE `status` SET value='.($x+1).' WHERE id=2');
        $status['topic']['value']=$x+1;
        $page_finish=0;
      }else{
        dbquery('UPDATE `status` SET value=0 WHERE id=2');
        $status['topic']['value']=0;
        $page_finish=1;
        echo '<br />*** no more topics ***';
      }
    }
  }
  if($page_finish){
    if($page_break){
      dbquery('UPDATE `status` SET value=0 WHERE id=1');
      dbquery('UPDATE `status` SET value=0 WHERE id=2');
      echo '<br />*** refresh finish ***';
    }else{
      if(isset($re['content']['topic_list']['more_topics_url'])){
        dbquery('UPDATE `status` SET value='.($status['page']['value']+1).' WHERE id=1');
        $status['page']['value']++;
        echo '<br />*** to next page ***';
      }else{
        dbquery('UPDATE `status` SET value=0 WHERE id=1');
        dbquery('UPDATE `status` SET value=0 WHERE id=2');
        $status['topic']['value']=0;
        dbquery('UPDATE `status` SET value=1 WHERE id=3');
        echo '<br />*** no more pages ***';
      }  
      
    }

  }
  exit('<br />*** TIME ELAPSED:'.(time()-$ti).' ***');
?>
  
 
  