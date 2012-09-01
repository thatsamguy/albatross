<?php
/* 
* Albatross Manager
* 
* Site-ConfAwstats Jobpack
* 
* Description:
*  Creates the site configuration file for awstats based on the appropriate template
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$error->add("jobpack-Site-ConfAwstats","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

include_once("sites.class.php");
$site = new site();

// Check correct jobdata has been provided: acc_id sitename
if(!is_array($jobdata)){
  $errmsg = "jobdata not an array";
  $error->add("jobpack-Site-ConfAwstats",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-ConfAwstats","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!$jobdata[0] OR !array_key_exists("sitename",$jobdata[1]) OR !array_key_exists("acc_id",$jobdata[1]) OR $jobdata[1]['sitename']==""){
  $errmsg = "incorrect or invalid jobdata provided";
  $error->add("jobpack-Site-ConfAwstats",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-ConfAwstats","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$jobdata = $jobdata[1];

// TODO - remove hardcoded path

// Read in template
$awstatsconf = file_get_contents("/var/wwwdata/albatross/100001/albatross/conf/awstats.template");
if(!$awstatsconf){
  $errmsg = "unable to load awstats template file";
  $error->add("jobpack-Site-ConfAwstats",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Site-ConfAwstats","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$awstatsconf = explode("\n",$awstatsconf);
$replacearray["_basedir_"] = $conf->base_home_dir;
$replacearray["_accid_"] = $jobdata['acc_id'];
$replacearray["_sitename_"] = $jobdata['sitename'];
$domain = $site->get_attr($jobdata['acc_id'],$jobdata['sitename'],"domains","default");
if(!$domain[0]){
  $errmsg = "unable to locate primary domain";
  $error->add("jobpack-Site-ConfAwstats",$errmsg." '".$domain[1]."'");
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Site-ConfAwstats","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
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

// Convert profile template to site specific conf
foreach($awstatsconf as $key=>$value){
  // Remove comment lines
  if(substr($value,0,1)=="#"){ unset($awstatsconf[$key]); }else{
    // Replace all dynamic values
    $awstatsconf[$key] = str_replace(array_keys($replacearray),$replacearray,$value);
  }  
}
unset($key,$value);

$awstatsconf = implode("\n",$awstatsconf);

$result = file_put_contents("/etc/awstats/awstats.".$jobdata['acc_id'].".".$jobdata['sitename'].".conf",$awstatsconf);
if(!$result){
  $errmsg = "unable to create awstats conf file";
  $error->add("jobpack-Site-ConfAwstats",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Site-ConfAwstats","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Site-ConfAwstats","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
goto end;

// TODO - rollback on failure
?>
<?php
end:
?>
