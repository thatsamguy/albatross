<?php
/* 
* Albatross Manager
* 
* Account-CreateAccount Jobpack
* 
* Description:
*  Creates a new account and assigns the correct permissions
*  
* Copyright 2011 Cyprix Enterprises
*/
?>
<?php
// Include Dependencies
include_once("account.class.php");
$account = new account();
?>
<?php
$error->add("jobpack-Account-CreateAccount","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

// Check correct jobdata has been provided: acc_id
if(!is_array($jobdata)){
  $errmsg = "jobdata not an array";
  $error->add("jobpack-Account-CreateAccount",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Account-CreateAccount","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!$jobdata[0] OR !array_key_exists("username",$jobdata[1]) OR !array_key_exists("password",$jobdata[1])){
  $errmsg = "incorrect or invalid jobdata provided";
  $error->add("jobpack-Account-CreateAccount",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Account-CreateAccount","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$jobdata = $jobdata[1];


$result = $account->add($jobdata['username'],$jobdata['password']);
if(!$result){
  $errmsg = "unable to create account";
  $error->add("jobpack-Account-CreateAccount",$errmsg." '".$jobdata['username']."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Account-CreateAccount","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}else{
  // Account created successfully, create homedir, files, folders and permissions
  $acc_id = $result[1];
  unset($result);
  $result = $amm->add_job("Account-CreateHomeDir",$acc_id,array("acc_id"=>$acc_id));
  if(!$result[0]){
    $errmsg = "Unable to add Account-CreateHomeDir job to queue";
    $error->add("jobpack-Account-CreateAccount",$errmsg);
    $error->add("jobpack-Account-CreateAccount",$result[1]);
  }
  unset($result);
  // Create php-fpm file
  $result = $amm->add_job("Site-ConfPhpFpm",$acc_id,array("acc_id"=>$acc_id));
  if(!$result[0]){
    $errmsg = "Unable to add Account-Site-ConfPhpFpm job to queue";
    $error->add("jobpack-Account-CreateAccount",$errmsg);
    $error->add("jobpack-Account-CreateAccount",$result[1]);
  }
  unset($result);
  unset($acc_id);
}

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Account-CreateAccount","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
goto end;

// TODO - rollback on failure
?>
<?php
end:
?>
