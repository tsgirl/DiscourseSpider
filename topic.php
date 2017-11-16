<?php
  require_once('config.php');
  require_once('include.php');
  preg_match('/^http:\/\/|https:\/\/?(.+)/',$sysconfig['site'],$siteurl);
  if(isset($_REQUEST['pagesize'])&&is_numeric($_REQUEST['pagesize'])){
    $posts_per_page=$_REQUEST['pagesize'];
  }else{
    $posts_per_page=20;
  }
  if(isset($_REQUEST['page'])&&is_numeric($_REQUEST['page'])&&$_REQUEST['page']>0){
    $page=floor($_REQUEST['page']);
  }else{
    $page=1;
  }
  if(isset($_REQUEST['id'])&&is_numeric($_REQUEST['id'])){
    $id=$_REQUEST['id'];
  }else{
    exit('服务不可用。');
  }
  if(isset($_REQUEST['order'])&&is_numeric($_REQUEST['order'])){
    //0=updatime DESC, 1=creatime DESC, 2=updatime, 3=creatime
    switch($_REQUEST['order']){
      case 1 : $order='creatime DESC'; break;
      case 2 : $order='updatime'; break;
      case 3 : $order='updatime DESC'; break;
      default: $order='creatime';
    }
  }else{
    $order='creatime';
    $_REQUEST['order']=0;
  }
  $topic=fetcharray(dbquery("SELECT * FROM `topics` WHERE id={$id} LIMIT 1"));
  if(!$topic) exit('服务不可用。');
  $start=($page-1)*$posts_per_page;
  $dbs=fetcharray(dbquery('SELECT COUNT(*) FROM `posts` WHERE fr='.$id));
  $page_count=ceil($dbs[0]/$posts_per_page);
  if($page>$page_count) $start=0;
  $dbs=dbquery("SELECT * FROM `posts` WHERE fr={$id} ORDER BY {$order} LIMIT {$start}, {$posts_per_page}");
  $order
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $id.':'.$siteurl[1]; ?></title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;" name="viewport" />
    <meta name="description" content="DiscourseSpider Topic">
    <meta name="author" content="tsgirl">
    <!-- Le styles -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
    <link href="./css/bootstrap-responsive.min.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="./js/html5shiv.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
          </button>
          <ul class=nav>
            <li>
              <a href="latest.php"><?php echo $siteurl[1] ?></a>
            </li>
          </ul>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li>
              </li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    <div class="container">
    
      <div class="row">
        <div class="span12">
          <legend><?php echo base64_decode($topic['title']).' <a href="'; if($_REQUEST['order']==0){echo '?order=1&id='.$id;}else{echo '?order=0&id='.$id;} ?>" ><?php if($_REQUEST['order']==1) echo'▼'; if($_REQUEST['order']==0) echo'▲'; ?></a><a href="<?php echo '?id='.$id.'&page='.$page_count; ?>">►</a></legend>
          <label></label>
        </div>
      </div>
      <table class="table table-striped">
        <thead>
          <tr>
            <td class="span10"></td>
            <td class="span2"></td>
          </tr>
        </thead>
        <tbody>
          
<?php
  $userlist=array();
  while($post=fetcharray($dbs)){
    $user=array();
    echo '<tr><td>';
    for($x=0;$x<sizeof($userlist);$x++){
      if($userlist[$x]['id']==$post['userid']) $user=$userlist[$x];
    }
    if($user){
      echo '<p><b>'.base64_decode($user['username']).'</b> '.base64_decode($user['name']).'</p>';
    }else{
      $user=fetcharray(dbquery("SELECT * FROM `users` WHERE id={$post['userid']} LIMIT 1"));
      if($user){
        array_push($userlist, $user);
        echo '<p><b>'.base64_decode($user['username']).'</b> '.base64_decode($user['name']).'</p>';      
          }else{
        echo '<p>userid='.$post['userid'].'</p>';
      }
    }
    echo checkimg(base64_decode($post['content']));
    echo '</td><td>'.date("Y-m-d H:i", $post['creatime']).'</td></tr>';
  }
?>
          
        </tbody>
      </table>
      <div class="row">
<?php
  echo '<div class="span2"><a class="btn';
  if($page==1) echo ' disabled';
  echo '" href="?page='.($page-1).'&id='.$id.'&order='.$_REQUEST['order'];
  echo '">上一页</a></div>';
  echo '<div class="span2 offset6"><form method="get" class="form-inline" action=""><div class="input-append"><input class="input span1" id="appendedInputButton" name="page" type="text" placeholder="[1,';
  echo $page_count;
  echo ']" required><input type="hidden" name="id" value="'.$id.'"><input type="hidden" name="order" value="'.$_REQUEST['order'].'"><button class="btn" type="submit">跳页</button></div></form></div>';
  echo '<div class="span2"><a class="btn';
  if($page==$page_count) echo ' disabled';
  echo '" href="?page='.($page+1).'&id='.$id.'&order='.$_REQUEST['order'];
  echo '">下一页</a></div>';

?>
      </div>
    </div>
    
    <footer>
    </footer>
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="./js/jquery.min.js"></script>
    <script src="./js/bootstrap-transition.js"></script>
    <script src="./js/bootstrap-alert.js"></script>
    <script src="./js/bootstrap-modal.js"></script>
    <script src="./js/bootstrap-dropdown.js"></script>
    <script src="./js/bootstrap-scrollspy.js"></script>
    <script src="./js/bootstrap-tab.js"></script>
    <script src="./js/bootstrap-tooltip.js"></script>
    <script src="./js/bootstrap-popover.js"></script>
    <script src="./js/bootstrap-button.js"></script>
    <script src="./js/bootstrap-collapse.js"></script>
    <script src="./js/bootstrap-carousel.js"></script>
    <script src="./js/bootstrap-typeahead.js"></script>
  </body>
</html>