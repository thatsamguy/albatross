<?php
/* 
* Albatross Manager
* 
* Databases Page (menu)
* 
* Description:
*  Configures page title and menu
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$pt[]['database.menu.php :: start'] = microtime(true);
include_once("databases.class.php");
$database = new database();
$pt[]['database.menu.php :: include databases.class.php'] = microtime(true);
?>
<?php
$thispage['title'] = "Databases";
$thispage['data'] = "database.page.php";
$thispage['menu'][0]['title'] = "View databases";
$thispage['menu'][0]['image'] = "view-list-text.png";
$thispage['menu'][0]['link'] = "/database";
$thispage['menu'][1]['title'] = "New database";
$thispage['menu'][1]['image'] = "list-add.png";
$thispage['menu'][1]['link'] = "/database/createdb";
$thispage['menu'][9]['title'] = "phpMyAdmin";
$thispage['menu'][9]['image'] = "server-database.png";
$thispage['menu'][9]['link'] = "http://mysql.cyprix.com.au/";
$thispage['menu'][9]['new_window'] = true;
$subpages = array("view","createdb","remove");

if(in_array($subpage,$subpages)){
  $thispage['data'] = $subpage.".database.page.php";
  $db_info = $database->status($auth->acc_id);
  $pt[]['database.menu.php :: database->status'] = microtime(true);

  if($subpage == "view" OR $subpage == "remove"){
    $db_name = $uri[2];
    $thispage['menu'][3]['title'] = "Remove database";
    $thispage['menu'][3]['image'] = "list-remove.png";
    $thispage['menu'][3]['link'] = "/database/remove/".$db_name."";
    if($db_info[0]){
      if(array_key_exists($db_name,$db_info[1])){
	unset($uri[2]);
      }else{
	$thispage['data'] = "error.page.php";
      }
    }else{
      $thispage['data'] = "error.page.php";
    }
  }
}
$pt[]['database.menu.php :: end'] = microtime(true);
?>