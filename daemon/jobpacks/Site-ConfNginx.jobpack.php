<?php
/* 
* Albatross Manager
* 
* Site-ConfNginx Jobpack
* 
* Description:
*  Creates the site configuration file for nginx based on the appropriate profile
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$error->add("jobpack-Site-ConfNginx","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

include_once("sites.class.php");
$site = new site();

// Check correct jobdata has been provided: acc_id sitename
if(!is_array($jobdata)){
  $errmsg = "jobdata not an array";
  $error->add("jobpack-Site-ConfNginx",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-ConfNginx","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!$jobdata[0] OR !array_key_exists("sitename",$jobdata[1]) OR !array_key_exists("acc_id",$jobdata[1]) OR !array_key_exists("profile",$jobdata[1]) OR $jobdata[1]['sitename']==""){
  $errmsg = "incorrect or invalid jobdata provided";
  $error->add("jobpack-Site-ConfNginx",$errmsg." ".json_encode($jobdata));
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-ConfNginx","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$jobdata = $jobdata[1];

// TODO - remove hardcoded path

// Check profile is correct
if(!is_readable("/var/wwwdata/albatross/100001/albatross/conf/".$jobdata['profile'].".profile")){
  $errmsg = "falling back to default profile, incorrect or invalid profile provided";
  $error->add("jobpack-Site-ConfNginx",$errmsg." '".$jobdata['profile']."'");
  unset($errmsg);
  $jobdata[1]['profile'] = "default";
}

// TODO - remove hardcoded path

// Read in profile
$nginxconf = file_get_contents("/var/wwwdata/albatross/100001/albatross/conf/".$jobdata['profile'].".profile");
if(!$nginxconf){
  $errmsg = "unable to load profile";
  $error->add("jobpack-Site-ConfNginx",$errmsg." '".$jobdata['profile']."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Site-ConfNginx","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$nginxconf = explode("\n",$nginxconf);
$replacearray["_basedir_"] = $conf->base_home_dir;
$replacearray["_accid_"] = $jobdata['acc_id'];
$replacearray["_sitename_"] = $jobdata['sitename'];
$domain = $site->get_attr($jobdata['acc_id'],$jobdata['sitename'],"domains","default");
if(!$domain[0]){
  $errmsg = "unable to locate primary domain";
  $error->add("jobpack-Site-ConfNginx",$errmsg." '".$domain[1]."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Site-ConfNginx","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}else{
  $replacearray["_domain_"] = $domain[1];
}
unset($domain);

// generate domainlist from database
$domainslist = $site->get_domainslist($jobdata['acc_id'],$jobdata['sitename']);
if($domainslist[0]){
  $replacearray["_domainlist_"] = $domainslist[1];
}else{
  $replacearray["_domainlist_"] = "";
}
unset($domainslist);


$directory = $site->get_attr($jobdata['acc_id'],$jobdata['sitename'],$jobdata['profile'],"directory");
if($directory[0]){
  $replacearray["_directory_"] = $directory[1];
}else{
  $replacearray["_directory_"] = "/";
}
unset($directory);

$subdirectory = $site->get_attr($jobdata['acc_id'],$jobdata['sitename'],$jobdata['profile'],"subdirectory");
if($subdirectory[0]){
  $replacearray["_subdirectory_"] = $subdirectory[1];
}else{
  $replacearray["_subdirectory_"] = "";
}
unset($subdirectory);

// generate list of redirects from the database
$redirectlist = $site->get_redirectlist($jobdata['acc_id'],$jobdata['sitename']);
if($redirectlist[0]){
  $replacearray["_redirect_"] = $redirectlist[1];
}else{# _custom_ = custom code
  $replacearray["_redirect_"] = "";
}
unset($redirectlist);

// insert custom code direct from the database
$custom = $site->get_customcode($jobdata['acc_id'],$jobdata['sitename']);
if($custom[0]){
  $replacearray["_custom_"] = $custom[1];
}else{
  $replacearray["_custom_"] = "";
}
unset($custom);

// insert custom code direct from the database
$fcgimicrocache = $site->get_php_microcache($jobdata['acc_id'],$jobdata['sitename']);
if($fcgimicrocache[0]){
  $replacearray["_fastcgi_"] = "\n        fastcgi_cache  microcache;";
}else{
  $replacearray["_fastcgi_"] = "";
}
unset($custom);

// Convert profile template to site specific conf
foreach($nginxconf as $key=>$value){
  // Remove comment lines
  if(substr($value,0,1)=="#"){ unset($nginxconf[$key]); }else{
    // Replace all dynamic values
    $nginxconf[$key] = str_replace(array_keys($replacearray),$replacearray,$value);
  }  
}
unset($key,$value);

$nginxconf = implode("\n",$nginxconf);

$result = file_put_contents("/etc/nginx/conf.d/albatross/".$jobdata['acc_id'].".".$jobdata['sitename'].".conf",$nginxconf);
if(!$result){
  $errmsg = "unable to create nginx conf file";
  $error->add("jobpack-Site-ConfNginx",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Site-ConfNginx","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Site-ConfNginx","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);

// Add job to reload nginx
// TODO - check current workflow and add to new job
$result = $amm->add_job("Nginx-Reload",$jobdata['acc_id'],array("acc_id"=>$jobdata['acc_id'],"sitename"=>$jobdata['sitename']),"");
if(!$result[0]){
  $errmsg = "Unable to add Nginx-Reload job to queue";
  $error->add("jobpack-Site-ConfNginx",$errmsg);
  $error->add("jobpack-Site-ConfNginx",$result[1]);
}
unset($result);

goto end;

// TODO - rollback on failure
end:
?>
