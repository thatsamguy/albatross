<?php
/* 
* Albatross Manager
* 
* Site-Archive Jobpack
* 
* Description:
*  Creates an archive on request of the site directory
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$error->add('jobpack-Site-Archive', 'Gathered job data for '.$jobid.'. Data: '.json_encode($jobdata));

// Check correct jobdata has been provided: acc_id sitename
if (!is_array($jobdata)) {
    $errmsg = 'jobdata not an array';
    $error->add('jobpack-Site-Archive', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'failed');
    if (!$update[0]) {
        $error->add('jobpack-Site-Archive', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
}

if (!$jobdata[0] or !array_key_exists('sitename', $jobdata[1]) or !array_key_exists('acc_id', $jobdata[1]) or $jobdata[1]['sitename'] == '') {
    $errmsg = 'incorrect or invalid jobdata provided';
    $error->add('jobpack-Site-Archive', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'failed');
    if (!$update[0]) {
        $error->add('jobpack-Site-Archive', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
}

$jobdata = $jobdata[1];

// Check backup directory exists
if (!is_dir($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive')) {
    if (!mkdir($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive')) {
        $errmsg = 'unable to create directory';
        $error->add('jobpack-Site-Archive', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id']."/archive'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'tryagain');
        if (!$update[0]) {
            $error->add('jobpack-Site-Archive', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }

  // Set Permissions
  if (!chown($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive', 'nginx')) {
      $errmsg = 'unable to set ownership permissions';
      $error->add('jobpack-Site-Archive', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id']."/archive'");
      unset($errmsg);
      $update = $amm->update_jobstatus($jobid, 'failed');
      if (!$update[0]) {
          $error->add('jobpack-Site-Archive', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
      }
      unset($update);
      goto end;
  }

    if (!chgrp($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive', 'z'.$jobdata['acc_id'])) {
        $errmsg = 'unable to set ownership permissions';
        $error->add('jobpack-Site-Archive', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id']."/archive'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Site-Archive', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }

    if (!chmod($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive', 0771)) {
        $errmsg = 'unable to set ownership permissions';
        $error->add('jobpack-Site-Archive', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id']."/archive'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Site-Archive', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }
}

// Backup site directory
if (is_dir($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$jobdata['sitename'])) {
    $timestamp = time();
    exec('zip -r '.$conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_'.$timestamp.'.zip '.$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$jobdata['sitename']);

  // Set Permissions
  if (!chown($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_'.$timestamp.'.zip', 'z'.$jobdata['acc_id'])) {
      $errmsg = 'unable to set ownership permissions';
      $error->add('jobpack-Site-Archive', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_'.$timestamp.".zip'");
      unset($errmsg);
      $update = $amm->update_jobstatus($jobid, 'failed');
      if (!$update[0]) {
          $error->add('jobpack-Site-Archive', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
      }
      unset($update);
      goto end;
  }

    if (!chgrp($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_'.$timestamp.'.zip', 'z'.$jobdata['acc_id'])) {
        $errmsg = 'unable to set ownership permissions';
        $error->add('jobpack-Site-Archive', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_'.$timestamp.".zip'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Site-Archive', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }

    if (!chmod($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_'.$timestamp.'.zip', 0640)) {
        $errmsg = 'unable to set ownership permissions';
        $error->add('jobpack-Site-Archive', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_'.$timestamp.".zip'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Site-Archive', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }
}

// Mark job as closed
$update = $amm->update_jobstatus($jobid, 'closed');
if (!$update[0]) {
    $error->add('jobpack-Site-Archive', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
} unset($update);

goto end;

end:
unset($timestamp);
?>
