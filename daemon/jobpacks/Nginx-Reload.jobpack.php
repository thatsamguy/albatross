<?php
/* 
* Albatross Manager
* 
* Nginx-Reload Jobpack
* 
* Description:
*  Tells nginx to reload its configuration files, does a configtest first
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$error->add("jobpack-Nginx-Reload","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

include_once("sites.class.php");
$site = new site();

// Check correct jobdata has been provided: acc_id sitename
if(!is_array($jobdata)){
  $errmsg = "jobdata not an array";
  $error->add("jobpack-Nginx-Reload",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Nginx-Reload","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!$jobdata[0] OR !array_key_exists("sitename",$jobdata[1]) OR !array_key_exists("acc_id",$jobdata[1]) OR $jobdata[1]['sitename']==""){
  $errmsg = "incorrect or invalid jobdata provided";
  $error->add("jobpack-Nginx-Reload",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Nginx-Reload","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$jobdata = $jobdata[1];

// Do a configtest before reload
unset($result,$retval);
exec("/sbin/service nginx configtest",$result,$retval);
if($retval != 0){
  $errmsg = "unable to run configtest";
  $error->add("jobpack-Nginx-Reload",$errmsg);
  $error->add("jobpack-Nginx-Reload","retval: ".$retval." result: ".json_encode($result));
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Nginx-Reload","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}else{
  // TODO - fix checking of valid configtest
}

reload:
// Reload config
unset($result,$retval);
exec("/sbin/service nginx reload",$result,$retval);
if($retval != 0){
  $errmsg = "unable to reload nginx configuration";
  $error->add("jobpack-Nginx-Reload",$errmsg);
  $error->add("jobpack-Nginx-Reload","retval: ".$retval." result: ".json_encode($result));
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Nginx-Reload","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}else{
  // TODO - fix checking of successfull reload
}

success:
// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Nginx-Reload","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
goto end;
?>
<?php
end:
?>
