<?php
function getsession($session=''){
  global $sysconfig;
  $cookie='_t='.$sysconfig['_t'].'; ';
  if($session) $cookie.='_forum_session='.$session.'; ';
  $_ti=time().rand(111,999);
  preg_match('/^http:\/\/|https:\/\/?(.+)/',$sysconfig['site'],$siteurl);
  $header=Array(':authority'=>$siteurl[1],
    ':method'=>'GET',
    ':path'=>'/',
    ':scheme'=>'https',
    'accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'accept-language'=>'en-US,en;q=0.8',
    'x-csrf-token'=>'undefined',
    'x-requested-with'=>'XMLHttpRequest');
  $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $sysconfig['site']);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_REFERER, $sysconfig['site']);
	curl_setopt($ch, CURLOPT_USERAGENT, $sysconfig['ua']);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
  curl_setopt($ch, CURLOPT_NOBODY, true);
  $gd = curl_exec($ch);
	curl_close($ch);
	if(!$gd) exit('<br />ERROR: server returns nothing!!!');
	preg_match('/X-Discourse-Username:?(.+?)\s/',$gd,$user);
	preg_match('/_forum_session=?(.+?);/',$gd,$session2);
	preg_match('/_t=?(.+?);/',$gd,$_t);
	preg_match('/\{?(.+)\}$/',$gd,$json);
	if(!isset($json[0])||!$json[0]) $json=array(null, null);
  if(!isset($session2[0])||!$session2[0]) $session2=array(null, $session['session']);
  if(!isset($_t[0])||!$_t[0]) $_t=array(null, $sysconfig['_t']);
  if(stristr($_t[1],'"')) $_t=array(null, $sysconfig['_t']);
  if($_t[1]!=$sysconfig['_t']){
    dbquery('UPDATE `config` SET `value`=\''.base64_encode(json_encode(array('session'=>$session[1], '_t'=>$_t[1]))).'\' WHERE `id`=1');
    $sysconfig['_t']=$_t[1];
	}
	return array('user'=>$user[1], 'session'=>$session2[1], '_t'=>$_t[1]);
}


function dbquery($sql){
  $dbq=mysql_query($sql);
  if(!$dbq){
    $dberr=mysql_error();
    if(substr($dberr,0,9)!='Duplicate'){
      exit('MySQL ERROR:'.$dberr.'<br />'.$sql);
    }else{
      return null;
    }
  }else{
    return $dbq;
  }
}


function getlatest($page=0, $session='', $desc=0){
  $asc = $desc==1?'':'order=activity&ascending=true&';
  $re = getjson('/latest.json?'.$asc.'page='.$page.'&_='.time().rand(001,999), $session);
	$gd = json_decode($re['content'], true);
  if(!$gd) return Array('content'=>null, 'session'=>$re['session'], '_t'=>$re['_t']);
	return Array('content'=>$gd, 'session'=>$re['session'], '_t'=>$re['_t']);
}


function getstream($id, $session=''){
  if( !$id || !is_numeric($id) ) return null;
  $re = getjson('/t/'.$id.'.json?_='.time().rand(001,999), $session);
	$gd = json_decode($re['content'], true);
  if(!$gd) return Array('content'=>null, 'session'=>$re['session'], '_t'=>$re['_t']);
	return Array('content'=>$gd['post_stream']['stream'], 'session'=>$re['session'], '_t'=>$re['_t']);
}


function getpost($id, $stream, $session='', $start=0, $count=20){
  if( !$id || !is_numeric($id) || !$stream ) return null;
  if($start>sizeof($stream)) return null;
  $args='';
  $count=$start+$count;
  if($count>sizeof($stream)) $count=sizeof($stream);
  for($i=$start; $i<$count; $i++){
    $args.='&post_ids[]='.$stream[$i];
  }
  $re = getjson('/t/'.$id.'/posts.json?_='.time().rand(001,999).$args, $session);
	$gd = json_decode($re['content'], true);
  if(!$gd) return Array('content'=>null, 'session'=>$re['session'], '_t'=>$re['_t']);
	return Array('content'=>$gd, 'session'=>$re['session'], '_t'=>$re['_t']);
}


function getjson($url, $session=''){
  global $sysconfig;
  preg_match('/^http:\/\/|https:\/\/?(.+)/',$sysconfig['site'],$siteurl);
  $header=Array(':authority'=>$siteurl[1],
    ':method'=>'GET',
    ':path'=>$url,
    ':scheme'=>'https',
    'accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'accept-language'=>'en-US,en;q=0.8',
    'x-csrf-token'=>'undefined',
    'x-requested-with'=>'XMLHttpRequest');
  $cookie='_t='.$sysconfig['_t'].';';
  if($session) $cookie.='_forum_session='.$session.';';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $sysconfig['site'].$url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_USERAGENT, $sysconfig['ua']);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
  $gd = curl_exec($ch);
	curl_close($ch);
	if(!$gd) return array('content'=>null, 'session'=>null, '_t'=>null);
	preg_match('/_forum_session=?(.+?);/',$gd,$session2);
	preg_match('/_t=?(.+?);/',$gd,$_t);
	preg_match('/\{?(.+)\}$/',$gd,$json);
	if(!isset($json[0])||!$json[0]) $json=array(null, null);
  if(!isset($session2[0])||!$session2[0]) $session2=array(null, $session);
  if(!isset($_t[0])||!$_t[0]) $_t=array(null, $sysconfig['_t']);
  if(stristr($_t[1],'"')) $_t=array(null, $sysconfig['_t']);
  if($_t[1]!=$sysconfig['_t']){
    dbquery('UPDATE `config` SET `value`=\''.base64_encode(json_encode(array('session'=>$session[1], '_t'=>$_t[1]))).'\' WHERE `id`=1');
    $sysconfig['_t']=$_t[1];
	}
	return array('original'=>$gd, 'content'=>$json[0], 'session'=>$session2[1], '_t'=>$_t[1]);
}
?>