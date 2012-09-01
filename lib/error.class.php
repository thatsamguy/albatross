<?php
/* 
* Albatross Manager
* 
* Error Log class
* 
* Description:
*  Contains error logging functions
*
* Copyright 2011 Cyprix Enterprises
*/
?>
<?php
class error{
  public $log = "default";

  function __construct() {
    // Do nothing.
  }

  function __destruct() {
    // Do nothing.
  }

  public function add($reference,$message) { // add a new message to the error log
    global $conf;
    // Log message to file - TODO: Temporary solution until database created and tested. Also serves as backup to database
    if($this->log == "amm"){ $logfile = $conf->amm_log_file; }else{ $logfile = "error.log"; }
    file_put_contents($logfile, "[".gmdate("d/m/y H:i:s")."] ".$reference." \"".$message."\"\n", FILE_APPEND | LOCK_EX);

    unset($reference,$message,$logfile);
    return true;
  }
}
// Create error object
$error = new error();
?>
