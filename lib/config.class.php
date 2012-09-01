<?php
/* 
* Albatross Manager
* 
* Configuration Sanitization class
* 
* Description:
*  Sanitizes all config variables and loads them into an object as variables
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
class config{
  // TODO: In configuration, set default account id

  function __construct() {
    $this->refresh();
  }

  function __destruct() {
    // Do nothing.
  }

  function refresh() { // TODO: Go through all the configuration variables, sanitize, then load as variables
    include("albatross.config.php");
    $this->base_home_dir = $conf['base_home_dir'];
    $this->cookie_domain = $conf['cookie_domain'];
    $this->webmail = $conf['webmail_link'];
    $this->rootpasswd = md5($conf['rootpassword']);
    $this->amm_log_file = $conf['amm_log_file'];
    $this->dns = $conf['dns'];
    $this->email = $conf['email'];
    $this->site = $conf['site'];
    $this->profile = $conf['profile'];
    $this->awstats = $conf['awstats'];
    $this->account = $conf['account'];
    if($conf['page_trace']===true OR $conf['page_trace']=="1"){
      $this->pt = true;
    }else{
      $this->pt = false;
    }
  }
}
// Create config object
$conf = new config();
?>
