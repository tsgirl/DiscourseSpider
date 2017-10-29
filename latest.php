<?php
  require_once('config.php');
  require_once('include.php');
  $con = mysql_connect($dbconfig['server'],$dbconfig['user'],$dbconfig['pass']);
  if(!$con) exit(mysql_error());
  mysql_select_db($dbconfig['name'], $con);
  preg_match('/^http:\/\/|https:\/\/?(.+)/',$sysconfig['site'],$siteurl);
  if(isset($_REQUEST['pagesize'])&&is_numeric($_REQUEST['pagesize'])){
    $topics_per_page=$_REQUEST['pagesize'];
  }else{
    $topics_per_page=20;
  }
  if(isset($_REQUEST['page'])&&is_numeric($_REQUEST['page'])&&$_REQUEST['page']>0){
    $page=floor($_REQUEST['page']);
  }else{
    $page=1;
  }
  if(isset($_REQUEST['order'])&&is_numeric($_REQUEST['order'])){
    //0=updatime DESC, 1=creatime DESC, 2=updatime, 3=creatime
    switch($_REQUEST['order']){
      case 1 : $order='creatime DESC'; break;
      case 2 : $order='updatime'; break;
      case 3 : $order='creatime'; break;
      default: $order='updatime DESC';
    }
  }else{
    $order='updatime DESC';
    $_REQUEST['order']=0;
  }
  $start=($page-1)*$topics_per_page;
  $dbs=mysql_fetch_array(dbquery('SELECT COUNT(*) FROM `topics`'));
  $page_count=ceil($dbs[0]/$topics_per_page);
  if($page>$page_count) $start=0;
  $dbs=dbquery("SELECT * FROM `topics` ORDER BY {$order} LIMIT {$start}, {$topics_per_page}");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>latest:<?php echo $siteurl[1]; ?></title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;" name="viewport" />
    <meta name="description" content="DiscourseSpider Latest">
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
    <div class="row"><div class="span12"></div></div>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <td class="span7">Topic</td>
            <td class="span2">Author</td>
            <td class="span1">Replies</td>
            <td class="span2"><a href="<?php if($_REQUEST['order']==0){echo '?order=2';}else{echo '?order=0';} ?>" >Activity<?php if($_REQUEST['order']==2) echo'▲'; if($_REQUEST['order']==0) echo'▼'; ?></a></td>
          </tr>
        </thead>
        <tbody>
          
<?php
  while($topic=mysql_fetch_array($dbs)){
    if(!$topic['count']){
      $topic['count']=sizeof(json_decode($topic['stream'],true));
      dbquery("UPDATE `topics` SET count='{$topic['count']}', stream='' WHERE id={$topic['id']}");
    }
    echo '<tr><td><a target="_blank" href="topic.php?id='.$topic['id'].'">'.base64_decode($topic['title']).'</a></td>';
    echo '<td>'.base64_decode($topic['username']).'</td>';
    echo '<td>'.($topic['count']-1).'</td>';
    echo '<td>'.date("Y-m-d H:i", $topic['updatime']).'</td></tr>';
  }
?>
          
        </tbody>
      </table>
      <div class="row">
<?php
  echo '<div class="span2"><a class="btn';
  if($page==1) echo ' disabled';
  echo '" href="?page='.($page-1);
  echo '">Prev</a></div>';
  echo '<div class="span2 offset6"><form method="get" class="form-inline" action=""><div class="input-append"><input class="input span1" id="appendedInputButton" name="page" type="text" placeholder="[1,';
  echo $page_count;
  echo ']" required><input type="hidden" name="order" value="'.$_REQUEST['order'].'"><button class="btn" type="submit">toPage</button></div></form></div>';
  echo '<div class="span2"><a class="btn';
  if($page==$page_count) echo ' disabled';
  echo '" href="?page='.($page+1);
  echo '">Next</a></div>';

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