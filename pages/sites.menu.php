<?php
/* 
* Albatross Manager
* 
* Sites Page (menu)
* 
* Description:
*  Configures page title and menu
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$pt[]['sites.menu.php :: start'] = microtime(true);
include_once("sites.class.php");
$site = new site();
$pt[]['sites.menu.php :: include sites.class.php'] = microtime(true);
?>
<?php
$thispage['title'] = "Manage Sites";
$thispage['data'] = "sites.page.php";
$thispage['menu'][0]['title'] = "View sites";
$thispage['menu'][0]['image'] = "view-list-text.png";
$thispage['menu'][0]['link'] = "/sites";
$thispage['menu'][1]['title'] = "Add site";
$thispage['menu'][1]['image'] = "list-add.png";
$thispage['menu'][1]['link'] = "/sites/addsite";
$subpages = array("addsite","view","remove","updateconf");
$pt[]['sites.menu.php :: create menu arrays'] = microtime(true);

if(in_array($subpage,$subpages)){
  $thispage['data'] = $subpage.".sites.page.php";
  $site_data = $site->status($auth->acc_id);
  $pt[]['sites.menu.php :: site->status'] = microtime(true);

  if($subpage == "view" OR $subpage == "remove" OR $subpage == "updateconf"){
    $sitename = $uri_fullcase[2];
    $thispage['menu'][2]['title'] = "Apply configuration";
    $thispage['menu'][2]['image'] = "system-software-update.png";
    $thispage['menu'][2]['link'] = "/sites/updateconf/".$sitename."";
    $thispage['menu'][3]['title'] = "Backup now";
    $thispage['menu'][3]['image'] = "utilities-file-archiver.png";
    $thispage['menu'][3]['link'] = "/archive/createbackup/".$sitename."";
    $thispage['menu'][8]['title'] = "Remove site";
    $thispage['menu'][8]['image'] = "list-remove.png";
    $thispage['menu'][8]['link'] = "/sites/remove/".$sitename."";

    if($site_data[0]){
      if(array_key_exists($sitename,$site_data[1])){
	unset($uri[2]);
      }else{
	$thispage['data'] = "error.page.php";
      }
    }else{
      $thispage['data'] = "error.page.php";
    }
  }
}
$pt[]['sites.menu.php :: end'] = microtime(true);
?>
