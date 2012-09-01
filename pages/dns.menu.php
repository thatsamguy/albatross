<?php
/* 
* Albatross Manager
* 
* DNS Page (menu)
* 
* Description:
*  Configures page title and menu
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$pt[]['dns.menu.php :: start'] = microtime(true);
include_once("dns.class.php");
$dns = new dns();
$pt[]['dns.menu.php :: include dns.class.php'] = microtime(true);
?>
<?php
$thispage['title'] = "Domain Names";
$thispage['data'] = "dns.page.php";
$thispage['menu'][1]['title'] = "View domains";
$thispage['menu'][1]['image'] = "view-list-text.png";
$thispage['menu'][1]['link'] = "/dns";
$thispage['menu'][2]['title'] = "Add domain";
$thispage['menu'][2]['image'] = "list-add.png";
$thispage['menu'][2]['link'] = "/dns/adddomain";
$subpages = array("view", "add", "server", "email", "soa", "domain", "record", "check", "adddomain", "removedomain");
$origpage = "";
if($subpage == "addalias" OR $subpage == "addaddress" OR $subpage == "addrecord" OR $subpage == "editrecord"){
  $origpage = $subpage;
  $subpage = "record";
}

if(in_array($subpage,$subpages)){
  $thispage['data'] = $subpage.".dns.page.php";

  $domain_id = $uri[2];
  $domains = $dns->get_domains_for_acc_id($auth->acc_id);
  if(array_key_exists($domain_id,$domains[1])){
    $domain = $domains[1][$domain_id];
  }elseif($domain_id==""){
    $domain = "";
  }else{
    $thispage['data'] = "error.page.php";
  }
  $pt[]['dns.menu.php :: dns->get_domains_for_acc_id'] = microtime(true);

  if(strlen($domain)>0){
    unset($thispage['menu2']);
    $thispage['menu2']['title'] = $domain;
    $thispage['menu2'][0]['title'] = "View records";
    $thispage['menu2'][0]['image'] = "view-list-text.png";
    $thispage['menu2'][0]['link'] = "/dns/domain/".$domain_id."";
    $thispage['menu2'][1]['title'] = "Add address";
    $thispage['menu2'][1]['image'] = "list-add.png";
    $thispage['menu2'][1]['link'] = "/dns/addaddress/".$domain_id."";
    $thispage['menu2'][2]['title'] = "Add alias";
    $thispage['menu2'][2]['image'] = "list-add.png";
    $thispage['menu2'][2]['link'] = "/dns/addalias/".$domain_id."";
    $thispage['menu2'][3]['title'] = "Add record";
    $thispage['menu2'][3]['image'] = "list-add.png";
    $thispage['menu2'][3]['link'] = "/dns/addrecord/".$domain_id."";
    $thispage['menu2'][4]['title'] = "DNS servers";
    $thispage['menu2'][4]['image'] = "network-server.png";
    $thispage['menu2'][4]['link'] = "/dns/server/".$domain_id;
    $thispage['menu2'][5]['title'] = "Email servers";
    $thispage['menu2'][5]['image'] = "internet-mail.png";
    $thispage['menu2'][5]['link'] = "/dns/email/".$domain_id;
    //$thispage['menu2'][6]['title'] = "Administrator email";
    //$thispage['menu2'][6]['image'] = "mail-mark-unread.png";
    //$thispage['menu2'][6]['link'] = "/dns/webmaster/".$domain_id;
    $thispage['menu2'][7]['title'] = "Change SOA and TTL";
    $thispage['menu2'][7]['image'] = "view-web-browser-dom-tree.png";
    $thispage['menu2'][7]['link'] = "/dns/soa/".$domain_id;
    $thispage['menu2'][8]['title'] = "Check worldwide propagation";
    $thispage['menu2'][8]['image'] = "applications-internet.png";
    $thispage['menu2'][8]['link'] = "/dns/check/".$domain_id;
    $thispage['menu2'][9]['title'] = "Remove domain";
    $thispage['menu2'][9]['image'] = "list-remove.png";
    $thispage['menu2'][9]['link'] = "/dns/removedomain/".$domain_id."";
  }
}
$pt[]['dns.menu.php :: end'] = microtime(true);
?>