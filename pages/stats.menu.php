<?php
/* 
* Albatross Manager
* 
* Stats Page (menu)
* 
* Description:
*  Configures page title and menu
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$pt[]['stats.menu.php :: start'] = microtime(true);
include_once 'sites.class.php';
$site = new site();
include_once 'email.class.php';
$email = new email();
include_once 'dns.class.php';
$dns = new dns();
include_once 'databases.class.php';
$database = new database();
$pt[]['stats.menu.php :: include sites,email,dns,databases.class.php'] = microtime(true);
?>
<?php
$thispage['title'] = 'Statistics';
$thispage['data'] = 'stats.page.php';
#$thispage['menu'][0]['title'] = "View addresses";
#$thispage['menu'][0]['image'] = "view-list-text.png";
#$thispage['menu'][0]['link'] = "/email";
/*$subpages = array("account","alias","addalias","removealias","addemail","removeaccount");
if(in_array($subpage,$subpages)){
  $thispage['data'] = $subpage.".email.page.php";
  if($subpage == "alias" OR $subpage == "addalias" OR $subpage == "removealias"){
    $alias_address = $uri[2];
    $domains = $dns->get_domains_for_acc_id($auth->acc_id);
  }
  if($subpage == "addemail"){
    $domains = $dns->get_domains_for_acc_id($auth->acc_id);
  }
  if($subpage == "account" OR $subpage == "removeaccount"){
    $email_address = $uri[2];
    $email_acc = $email->get_email($email_address);
    if($email_acc[0]){
      $email_acc = $email_acc[1];
      $domains = $dns->get_domains_for_acc_id($auth->acc_id);
      if($domains[0]){
    if(in_array($email_acc['domain'],$domains[1])){
      unset($uri[2]);
    }else{
      $thispage['data'] = "error.page.php";
    }
      }else{
    $thispage['data'] = "error.page.php";
      }
    }else{
      $thispage['data'] = "error.page.php";
    }
  }
}*/
$pt[]['stats.menu.php :: end'] = microtime(true);
?>
