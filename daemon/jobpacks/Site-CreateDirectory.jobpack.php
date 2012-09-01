<?php
/* 
* Albatross Manager
* 
* Site-CreateDirectory Jobpack
* 
* Description:
*  Creates the Directory for the new site and assigns the correct initial permissions
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$error->add("jobpack-Site-CreateDirectory","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

// Check correct jobdata has been provided: acc_id sitename
if(!is_array($jobdata)){
  $errmsg = "jobdata not an array";
  $error->add("jobpack-Site-CreateDirectory",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-CreateDirectory","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!$jobdata[0] OR !array_key_exists("sitename",$jobdata[1]) OR !array_key_exists("acc_id",$jobdata[1]) OR $jobdata[1]['sitename']==""){
  $errmsg = "incorrect or invalid jobdata provided";
  $error->add("jobpack-Site-CreateDirectory",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-CreateDirectory","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$jobdata = $jobdata[1];

// Check if directory already exists, if so modify permissions
if(is_dir($conf->base_home_dir."/".$jobdata['acc_id']."/".$jobdata['sitename'])){
  $errmsg = "directory already exists";
  $error->add("jobpack-Site-CreateDirectory",$errmsg." '".$conf->base_home_dir."/".$jobdata['acc_id']."/".$jobdata['sitename']."'");
  goto permissions;
}

// Create Directory
if(!mkdir($conf->base_home_dir."/".$jobdata['acc_id']."/".$jobdata['sitename'])){
  $errmsg = "unable to create directory";
  $error->add("jobpack-Site-CreateDirectory",$errmsg." '".$conf->base_home_dir."/".$jobdata['acc_id']."/".$jobdata['sitename']."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Site-CreateDirectory","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

permissions:
// Set Permissions
if(!chown($conf->base_home_dir."/".$jobdata['acc_id']."/".$jobdata['sitename'],"z".$jobdata['acc_id'])){
  $errmsg = "unable to set ownership";
  $error->add("jobpack-Site-CreateDirectory",$errmsg." '".$conf->base_home_dir."/".$jobdata['acc_id']."/".$jobdata['sitename']."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-CreateDirectory","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!chgrp($conf->base_home_dir."/".$jobdata['acc_id']."/".$jobdata['sitename'],"z".$jobdata['acc_id'])){
  $errmsg = "unable to set group permissions";
  $error->add("jobpack-Site-CreateDirectory",$errmsg." '".$conf->base_home_dir."/".$jobdata['acc_id']."/".$jobdata['sitename']."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-CreateDirectory","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!chmod($conf->base_home_dir."/".$jobdata['acc_id']."/".$jobdata['sitename'],0770)){
  $errmsg = "unable to set ownership permissions";
  $error->add("jobpack-Site-CreateDirectory",$errmsg." '".$conf->base_home_dir."/".$jobdata['acc_id']."/".$jobdata['sitename']."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-CreateDirectory","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Site-CreateDirectory","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
goto end;

// TODO - rollback on failure
?>
<?php
end:
?>
