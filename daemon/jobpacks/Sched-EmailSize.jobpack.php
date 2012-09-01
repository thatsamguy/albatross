<?php
/* 
* Albatross Manager
* 
* Sched-EmailSize Jobpack
* 
* Description:
*  Checks which emails have not had their size updated recently and adds check to job queue
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
// Include Dependencies
include_once("email.class.php");
$email = new email();
?>
<?php
$error->add("jobpack-Sched-EmailSize","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

$result = $email->sched_email_size();
if(!$result[0]){
  $errmsg = "unable to check email sizes";
  $error->add("jobpack-Sched-EmailSize",$errmsg." ".$result[1]);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Sched-EmailSize","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  unset($result);
  goto end;
}

unset($result);

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Sched-EmailSize","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);

goto end;

end:
?>
