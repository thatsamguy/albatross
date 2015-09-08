<?php error_log(__FILE__);?>
<?php
/* 
* Albatross Manager
* 
* Account-CreateFiles Jobpack
* 
* Description:
*  Creates the initial files for the new account and assigns the correct permissions
*  
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$error->add('jobpack-Account-CreateFiles', 'Gathered job data for '.$jobid.'. Data: '.json_encode($jobdata));

// Check correct jobdata has been provided: acc_id
if (!is_array($jobdata)) {
    $errmsg = 'jobdata not an array';
    $error->add('jobpack-Account-CreateFiles', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'failed');
    if (!$update[0]) {
        $error->add('jobpack-Account-CreateFiles', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
}

if (!$jobdata[0] or !array_key_exists('acc_id', $jobdata[1])) {
    $errmsg = 'incorrect or invalid jobdata provided';
    $error->add('jobpack-Account-CreateFiles', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'failed');
    if (!$update[0]) {
        $error->add('jobpack-Account-CreateFiles', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
}

$jobdata = $jobdata[1];

// Grab default folder configuration
$conf->refresh();
$accountdata = $conf->account;
$files = $accountdata['file'];
unset($accountdata);

$replacearray['_accid_'] = $jobdata['acc_id'];

foreach ($files as $key => $array) {
    foreach ($array as $key2 => $value) {
        $files[$key][$key2] = str_replace(array_keys($replacearray), $replacearray, $value);
    }
    unset($key2, $value);
}
unset($key, $array);

foreach ($files as $array) {
    // Read in template
  $fileconf = ' ';
    if ($array[1] != '') {
        $fileconf = file_get_contents('/var/wwwdata/albatross/100001/albatross/conf/'.$array[1]);
        if (!$fileconf) {
            $errmsg = 'unable to load template file';
            $error->add('jobpack-Account-CreateFiles', $errmsg." '".$array[1]."'");
            unset($errmsg);
            $update = $amm->update_jobstatus($jobid, 'tryagain');
            if (!$update[0]) {
                $error->add('jobpack-Account-CreateFiles', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
            }
            unset($update);
            break;
        }

        $fileconf = explode("\n", $fileconf);

    // Convert template to account specific conf
    foreach ($fileconf as $key => $value) {
        // Remove comment lines
      if (substr($value, 0, 1) == '#') {
          unset($fileconf[$key]);
      } else {
          // Replace all dynamic values
    $fileconf[$key] = str_replace(array_keys($replacearray), $replacearray, $value);
      }
    }
        unset($key, $value);

        $fileconf = implode("\n", $fileconf);
    }

    $result = file_put_contents($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0], $fileconf);
    if (!$result) {
        $errmsg = 'unable to create conf file';
        $error->add('jobpack-Account-CreateFiles', $errmsg." '".$jobdata['acc_id'].'/'.$array[0]."'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'tryagain');
        if (!$update[0]) {
            $error->add('jobpack-Account-CreateFiles', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }

  // Set Permissions
  if (!chown($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0], $array[2])) {
      $errmsg = 'unable to set ownership';
      $error->add('jobpack-Account-CreateFiles', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0]."' '".$array[2]."'");
      unset($errmsg);
      $update = $amm->update_jobstatus($jobid, 'failed');
      if (!$update[0]) {
          $error->add('jobpack-Account-CreateFiles', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
      }
      unset($update);
      goto end;
  }

    if (!chgrp($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0], $array[3])) {
        $errmsg = 'unable to set group permissions';
        $error->add('jobpack-Account-CreateFiles', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0]."' '".$array[3]."'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Account-CreateFiles', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }

    if (!chmod($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0], $array[4])) {
        $errmsg = 'unable to set ownership permissions';
        $error->add('jobpack-Account-CreateFiles', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0]."' '".$array[4]."'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Account-CreateFiles', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }
}

// Mark job as closed
$update = $amm->update_jobstatus($jobid, 'closed');
if (!$update[0]) {
    $error->add('jobpack-Account-CreateFiles', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
} unset($update);
goto end;

// TODO - rollback on failure
?>
<?php
end:
?>
