<?php
/* 
* Albatross Manager
* 
* Email Page (menu)
* 
* Description:
*  Configures page title and menu
*
* Copyright 2011 Cyprix Enterprises
*/
?>
<?php
$pt[]['email.menu.php :: start'] = microtime(true);
include_once("email.class.php");
include_once("dns.class.php");
$email = new email();
$dns = new dns();
$pt[]['email.menu.php :: include email.class.php'] = microtime(true);
?>
<?php
$thispage['title'] = "Email Addresses";
$thispage['data'] = "email.page.php";
$thispage['menu'][0]['title'] = "View addresses";
$thispage['menu'][0]['image'] = "view-list-text.png";
$thispage['menu'][0]['link'] = "/email";
$thispage['menu'][1]['title'] = "View aliases";
$thispage['menu'][1]['image'] = "view-list-text.png";
$thispage['menu'][1]['link'] = "/email/alias";
$thispage['menu'][2]['title'] = "Add email";
$thispage['menu'][2]['image'] = "list-add.png";
$thispage['menu'][2]['link'] = "/email/addemail";
$thispage['menu'][3]['title'] = "Add alias";
$thispage['menu'][3]['image'] = "list-add.png";
$thispage['menu'][3]['link'] = "/email/addalias";
$subpages = array("account","alias","addalias","removealias","addemail","removeaccount");
$pt[]['email.menu.php :: menu array'] = microtime(true);
if(in_array($subpage,$subpages)){
  $thispage['data'] = $subpage.".email.page.php";
  if($subpage == "alias" OR $subpage == "addalias" OR $subpage == "removealias"){
    $alias_address = $uri[2];
    $domains = $dns->get_domains_for_acc_id($auth->acc_id);
    $pt[]['email.menu.php :: dns->get_domains_for_acc_id'] = microtime(true);
  }
  if($subpage == "addemail"){
    $domains = $dns->get_domains_for_acc_id($auth->acc_id);
    $pt[]['email.menu.php :: dns->get_domains_for_acc_id'] = microtime(true);
  }
  if($subpage == "account" OR $subpage == "removeaccount"){
    $email_address = $uri[2];
    $email_acc = $email->get_email($email_address);
    $pt[]['email.menu.php :: email->get_email'] = microtime(true);
    if($email_acc[0]){
      $email_acc = $email_acc[1];
      $domains = $dns->get_domains_for_acc_id($auth->acc_id);
      $pt[]['email.menu.php :: dns->get_domains_for_acc_id'] = microtime(true);
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
}
$pt[]['email.menu.php :: end'] = microtime(true);
?>