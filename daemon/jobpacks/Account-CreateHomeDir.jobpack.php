<?php
/* 
* Albatross Manager
* 
* Account-CreateHomeDir Jobpack
* 
* Description:
*  Creates the Directory for the new account and assigns the correct initial permissions
*  On success requests initial subfolders and files to be created
*  
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$error->add("jobpack-Account-CreateHomeDir","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

// Check correct jobdata has been provided: acc_id
if(!is_array($jobdata)){
  $errmsg = "jobdata not an array";
  $error->add("jobpack-Account-CreateHomeDir",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Account-CreateHomeDir","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!$jobdata[0] OR !array_key_exists("acc_id",$jobdata[1])){
  $errmsg = "incorrect or invalid jobdata provided";
  $error->add("jobpack-Account-CreateHomeDir",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Account-CreateHomeDir","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$jobdata = $jobdata[1];

// Check if home directory already exists, if so modify permissions
if(is_dir($conf->base_home_dir."/".$jobdata['acc_id'])){
  $errmsg = "home directory already exists";
  $error->add("jobpack-Account-CreateHomeDir",$errmsg." '".$conf->base_home_dir."/".$jobdata['acc_id']."'");
  goto permissions;
}

// Create Home Directory
if(!mkdir($conf->base_home_dir."/".$jobdata['acc_id'])){
  $errmsg = "unable to create home directory";
  $error->add("jobpack-Account-CreateHomeDir",$errmsg." '".$conf->base_home_dir."/".$jobdata['acc_id']."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Account-CreateHomeDir","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

permissions:
// Set Permissions
if(!chown($conf->base_home_dir."/".$jobdata['acc_id'],"nginx")){
  $errmsg = "unable to set ownership";
  $error->add("jobpack-Account-CreateHomeDir",$errmsg." '".$conf->base_home_dir."/".$jobdata['acc_id']."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Account-CreateHomeDir","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!chgrp($conf->base_home_dir."/".$jobdata['acc_id'],"z".$jobdata['acc_id'])){
  $errmsg = "unable to set group permissions";
  $error->add("jobpack-Account-CreateHomeDir",$errmsg." '".$conf->base_home_dir."/".$jobdata['acc_id']."' to '"."z".$jobdata['acc_id']."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Account-CreateHomeDir","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!chmod($conf->base_home_dir."/".$jobdata['acc_id'],0751)){
  $errmsg = "unable to set ownership permissions";
  $error->add("jobpack-Account-CreateHomeDir",$errmsg." '".$conf->base_home_dir."/".$jobdata['acc_id']."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Account-CreateHomeDir","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

// Add job to create initial folders
// TODO - check current workflow and add to new job
$result = $amm->add_job("Account-CreateFolders",$jobdata['acc_id'],array("acc_id"=>$jobdata['acc_id']));
if(!$result[0]){
  $errmsg = "Unable to add Account-CreateFolders job to queue";
  $error->add("jobpack-Account-CreateHomeDir",$errmsg);
  $error->add("jobpack-Account-CreateHomeDir",$result[1]);
}
unset($result);

// Add job to create initial files
// TODO - check current workflow and add to new job
$result = $amm->add_job("Account-CreateFiles",$jobdata['acc_id'],array("acc_id"=>$jobdata['acc_id']));
if(!$result[0]){
  $errmsg = "Unable to add Account-CreateFiles job to queue";
  $error->add("jobpack-Account-CreateHomeDir",$errmsg);
  $error->add("jobpack-Account-CreateHomeDir",$result[1]);
}
unset($result);

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Account-CreateHomeDir","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
goto end;

// TODO - rollback on failure
?>
<?php
end:
?>
