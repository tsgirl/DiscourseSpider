<?php
//////////////////////////////////////
//设置是否显示错误
//某些奇葩的运行环境如果显示发生的错误
//会使echo等输出失效
//Set whether errors are displayed
//Displaying an error disables output
//in some environment.
error_reporting(E_ALL);
//////////////////////////////////////
//指定目标站点的时区，如果时间显示不对
//，请修改此处
//Specify the timezone of target site,
//modify if time displayed is incorrect.
date_default_timezone_set('UTC');
//////////////////////////////////////
//以下是数据库设置
//Database connection details
$dbconfig=Array(
'server'=>'localhost',
//MySQL服务器地址
//格式：'服务器:端口'
//MYSQL SERVER: 'host:port'
'user'=>'test',
//登录数据库的用户名
//user name of database
'pass'=>'test',
//登录数据库的密码
//password
'name'=>'discoursespider'
//数据库名，请手动建立该数据库并
//导入install.sql
//Database name, create it manually if
//not exists, then run install.sql.
//////////////////////////////////////
);
//////////////////////////////////////
//以下是程序设置
//System settings
$sysconfig=Array(
'site'=>'https://try.discourse.org',
//目标论坛网址，前面加http(s)://，后面不要/
//url of target forum, including "http(s)://"
//but not the "/" at the end
'_t'=>'',
//从已登录cookie中复制_t的值到此处
//论坛无需登录也可访问的可以留空
//此设置仅第一次有效
//如需更改，编辑此文件并清空表`config`
//Copy value of _t from cookies after
//login into the forum.
//Can be left empty if forum can be 
//accessed without logining in.
//Vaild only on first run.
//To change cookies, truncate table
// `config` after editing this file.
'ua'=>'Mozilla/5.0 (Windows NT 6.3; Win32; x86; rv:57.0) Gecko/20100101 Firefox/57.0',
//因discourse程序会检测useragent，需要设置
//Discourse checks useragent, so input a
//vaild UA string here.
'saveimg'=>true
//保存图片到本地
//save pictures.
//////////////////////////////////////
);
?>