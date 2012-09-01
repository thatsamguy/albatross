<?php
/* 
* Albatross Manager
* 
* Email-CalcSize Jobpack
* 
* Description:
*  Calculates the size of the email maildir
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
$error->add("jobpack-Email-CalcSize","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

// Check correct jobdata has been provided: acc_id email
if(!is_array($jobdata)){
  $errmsg = "jobdata not an array";
  $error->add("jobpack-Email-CalcSize",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Email-CalcSize","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!$jobdata[0] OR !array_key_exists("email",$jobdata[1]) OR !array_key_exists("acc_id",$jobdata[1]) OR $jobdata[1]['email']==""){
  $errmsg = "incorrect or invalid jobdata provided";
  $error->add("jobpack-Email-CalcSize",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Email-CalcSize","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$jobdata = $jobdata[1];

$result = $email->update_size($jobdata['acc_id'],$jobdata['email']);
if(!$result[0]){
  $errmsg = "unable to update email sizes";
  $error->add("jobpack-Email-CalcSize",$errmsg." '".$jobdata['acc_id']."->".$jobdata['email']."' ".$result[1]);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Email-CalcSize","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  unset($result);
  goto end;
}

unset($result);

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Email-CalcSize","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);

goto end;

// TODO - rollback on failure
end:
?>
