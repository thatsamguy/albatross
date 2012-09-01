<?php
/* 
* Albatross Manager
* 
* Site-ConfPhpFpm Jobpack
* 
* Description:
*  Creates the site configuration file for php-fpm based on the appropriate template
*
* Copyright 2012 Samuel Bailey
*/
?>
<?php
$error->add("jobpack-Site-ConfPhpFpm","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

// Check correct jobdata has been provided: acc_id
if(!is_array($jobdata)){
  $errmsg = "jobdata not an array";
  $error->add("jobpack-Site-ConfPhpFpm",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-ConfPhpFpm","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!$jobdata[0] OR !array_key_exists("acc_id",$jobdata[1])){
  $errmsg = "incorrect or invalid jobdata provided";
  $error->add("jobpack-Site-ConfPhpFpm",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-ConfPhpFpm","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$jobdata = $jobdata[1];

// TODO - remove hardcoded path

// Read in template
$phpconf = file_get_contents("/var/wwwdata/albatross/100001/albatross/conf/php-fpm.template");
if(!$phpconf){
  $errmsg = "unable to load php-fpm template file";
  $error->add("jobpack-Site-ConfPhpFpm",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Site-ConfPhpFpm","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$phpconf = explode("\n",$phpconf);
$replacearray["_accid_"] = $jobdata['acc_id'];

// Convert template to account specific conf
foreach($phpconf as $key=>$value){
  // Remove comment lines
  if(substr($value,0,1)=="#"){ unset($phpconf[$key]); }else{
    // Replace all dynamic values
    $phpconf[$key] = str_replace(array_keys($replacearray),$replacearray,$value);
  }  
}
unset($key,$value);

$phpconf = implode("\n",$phpconf);

$result = file_put_contents("/etc/php-fpm.d/".$jobdata['acc_id'].".conf",$phpconf);
if(!$result){
  $errmsg = "unable to create php-fpm conf file";
  $error->add("jobpack-Site-ConfPhpFpm",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Site-ConfPhpFpm","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

// Add job to reload php-fpm
// TODO - check current workflow and add to new job
$result = $amm->add_job("PhpFpm-Reload",$jobdata['acc_id'],array("acc_id"=>$jobdata['acc_id']));
if(!$result[0]){
  $errmsg = "Unable to add PhpFpm-Reload job to queue";
  $error->add("jobpack-Site-ConfPhpFpm",$errmsg);
  $error->add("jobpack-Site-ConfPhpFpm",$result[1]);
}
unset($result);

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Site-ConfPhpFpm","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
goto end;

// TODO - rollback on failure
?>
<?php
end:
?>
