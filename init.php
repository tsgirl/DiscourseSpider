<?php
//PHP7 support -- Connect to mysql database using mysqli
  $con = new mysqli($dbconfig['server'], $dbconfig['user'], $dbconfig['pass'], $dbconfig['name']);
  if(!$con) exit($con->connect_error);
  