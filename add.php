<?php
  $ti=time();
  $maxe=ini_get('max_execution_time');
  require_once('config.php');
  require_once('include.php');
  if(!isset($_REQUEST['id'])||!is_numeric($_REQUEST['id'])) exit('服务不可用。');
  $topic_finish=0;
  $topic_part_finish=0;
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

  $session=getstream($_REQUEST['id'], $session['session']);
  if(!$session['content']) exit('<br />ERROR: can\'t decode return data ('.$_REQUEST['id'].'.json)');
  echo '<br />....Topic: ('.$session['original']['id'].')';
  $creatime=strtotime($session['original']['created_at']);
  $updatime=strtotime($session['original']['last_posted_at']);
  $session['original']['title']=base64_encode($session['original']['title']);
  $status['post']=mysql_fetch_array(dbquery('SELECT * FROM `topics` WHERE `id`='.$session['original']['id']));
  if($status['post']){
    if($status['post']['offset']==$session['original']['posts_count']) exit(' ['.$status['post']['offset'].'] post count unchanged, skipping...');
  }else{
    $username=$session['original']['details']['created_by']['username'];
    dbquery("INSERT INTO `topics` VALUES ('{$session['original']['id']}', '', 0, '{$session['original']['title']}', '{$creatime}', '{$updatime}', 0, '{$username}')");
  }
  $status['post']['count']=sizeof($session['content']);
  dbquery("UPDATE `topics` SET count='{$status['post']['count']}', updatime='{$updatime}' WHERE `id`='{$session['original']['id']}'");
  $status['post']=mysql_fetch_array(dbquery('SELECT * FROM `topics` WHERE `id`='.$session['original']['id']));
  $status['post']['stream']=$session['content'];
  echo ' ['.sizeof($session['content']).']';
  if($status['post']['offset']>=sizeof($status['post']['stream'])) exit(' ['.$status['post']['offset'].'] post count unchanged, skipping...');
  $page_finish=0;
  while($status['post']['offset']<sizeof($status['post']['stream'])){
    if($maxe&&(time()-$ti)>(0.8*$maxe)) break;
    $topic_finish=0;
    $session=getpost($_REQUEST['id'], $status['post']['stream'], $session['session'], $status['post']['offset'], 20);
    if(!$session['content']) exit('<br />ERROR: can\'t decode return data (t/'.$session['content']['id'].'/posts.json)');
    for($xx=0; $xx<sizeof($session['content']['post_stream']['posts']); $xx++){
      if($maxe&&(time()-$ti)>(0.9*$maxe)) break;
      $topic_part_finish=0;
      echo '<br />........Post: '.$status['post']['offset'].' ('.$session['content']['post_stream']['posts'][$xx]['id'].')';
      $creatime=strtotime($session['content']['post_stream']['posts'][$xx]['created_at']);
      $updatime=strtotime($session['content']['post_stream']['posts'][$xx]['updated_at']); 
      saveimg($session['content']['post_stream']['posts'][$xx]['cooked']);
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
  if($topic_finish) echo '<br />*** topic finish ***';
  

  exit('<br />*** TIME ELAPSED:'.(time()-$ti).' ***');
?>
  
 
  