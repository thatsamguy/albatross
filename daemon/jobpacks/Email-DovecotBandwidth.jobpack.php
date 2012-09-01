<?php
/* 
* Albatross Manager
* 
* Email-DovecotBandwidth Jobpack
* 
* Description:
*  Calculates the email dovecot bandwidth for each account from the rotated mail log files
*
* Copyright 2011 Cyprix Enterprises
*/
?>
<?php
// Include Dependencies
include_once("email.class.php");
$email = new email();
?>
<?php
$error->add("jobpack-Email-DovecotBandwidth","Gathered job data for ".$jobid.".");

$result = $email->dovecot_bandwidth("/var/log/mail/info.log.1");
if(!$result[0]){
  $errmsg = "unable to update ftp bandwidth";
  $error->add("jobpack-Email-DovecotBandwidth",$errmsg." ".$result[1]);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Email-DovecotBandwidth","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  unset($result);
  goto end;
}

unset($result);

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Email-DovecotBandwidth","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);

goto end;

// TODO - rollback on failure
end:
?>
