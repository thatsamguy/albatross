<?php error_log(__FILE__);?>
<?php
/* 
* Albatross Manager
* 
* User Sites SQL Interface class
* 
* Description:
*  Contains all functions for managing user sites
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
// Include Dependencies
include_once 'config.class.php';
include_once 'mysqli.class.php';
include_once 'error.class.php';
include_once 'amm.class.php';
?>
<?php
class site
{
    private $db;
    private $amm;

    public function __construct()
    {
        // start db connection
    $this->db = new db();
        $this->db->database = 'default';
        $this->db->connect();
        $this->amm = new amm();
    }

    public function __destruct()
    {
        // Do nothing.
    unset($this->db);
        unset($this->amm);
    }

    public function add($acc_id, $name, $domain, $profile = 'default')
    { // adds a new site
    global $error;
        global $conf;

    // escape name
    $esc = $this->db->esc(stripslashes(str_replace(' ', '', $name)));
        unset($name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $name = $esc[1];
        unset($esc);

    // escape acc_id
    // TODO: Add check for a valid acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape domain
    // TODO: Add check for valid and non-conflicking domain
    $esc = $this->db->esc($domain);
        unset($domain);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $domain = $esc[1];
        unset($esc);

    // escape profile
    // TODO: Add check for a valid profile
    $esc = $this->db->esc($profile);
        unset($profile);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $profile = $esc[1];
        unset($esc);

    // create site in database
    $query = "INSERT INTO sites VALUES('".$acc_id."','".$name."',NOW(),NOW())";
        if ($this->db->sql->query($query) === true) {
            goto datadirectory;
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      if ($this->db->sql->query($query) === true) {
          goto datadirectory;
      } else {
          // other database error
    $error->add('site->add', $this->db->sql->error);
          $error->add('site->add', $query);
          $errmsg = 'unable to create site, site may already exist';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
        }

        datadirectory:
    unset($query, $errmsg);
        $result = $this->amm->add_job('Site-CreateDirectory', $acc_id, $jobdata = array('acc_id' => $acc_id, 'sitename' => $name), '');
        if (!$result[0]) {
            $errmsg = 'Unable to add job to queue';
            $error->add('sites', $errmsg);
            $error->add('sites', $result[1]);
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }
        $jobid = $result[1];
        unset($result);

    // Check if CreateDirectory is successfull
    usleep(5000);
        $check = $this->amm->job_status($jobid);
        while ($check[0]) {
            if ($check[1] == 'failed') {
                $errmsg = 'Unable to create site directory';
                $error->add('sites', $errmsg);
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            } elseif ($check[1] = 'closed') {
                goto defaultconfig;
            }
            usleep(5000);
            $check = $this->amm->job_status($jobid);
        }

        defaultconfig:
    unset($query, $errmsg);
    // Add default configuration to database
    $find = array('_sitename_','_domain_','_accid_','_profile_');
        $andreplace = array($name,$domain,$acc_id,$profile);
        $conf_tmp = $this->update_attr($acc_id, $name, 'domains', 'default', $domain);
        if (!$conf_tmp[0]) {
            $errmsg = "unable to add default config for '".$acc_id."' '".$name."'";
            $error->add('site->add', $errmsg);
            $error->add('site->add', $conf_temp[1]);
            unset($errmsg);
        }
        foreach ($conf->site as $attr_group => $array) {
            foreach ($array as $attr => $value) {
                $value = str_replace($find, $andreplace, $value);
                $conf_tmp = $this->update_attr($acc_id, $name, $attr_group, $attr, $value);
                if (!$conf_tmp[0]) {
                    $errmsg = "unable to add default config for '".$acc_id."' '".$name."'";
                    $error->add('site->add', $errmsg);
                    $error->add('site->add', $conf_tmp[1]);
                    unset($errmsg);
                }
                unset($conf_tmp);
            }
        }
        foreach ($conf->profile as $attr_group => $array) {
            if ($profile == $attr_group) {
                foreach ($array as $attr => $value) {
                    $value = str_replace($find, $andreplace, $value);
                    $conf_tmp = $this->update_attr($acc_id, $name, $attr_group, $attr, $value);
                    if (!$conf_tmp[0]) {
                        $errmsg = "unable to add profile default config for '".$acc_id."' '".$name."'";
                        $error->add('site->add', $errmsg);
                        $error->add('site->add', $conf_tmp[1]);
                        unset($errmsg);
                    }
                    unset($conf_tmp);
                }
            }
        }

    // Add nginx conf based on profile
    $result = $this->amm->add_job('Site-ConfNginx', $acc_id, $jobdata = array('acc_id' => $acc_id, 'sitename' => $name, 'profile' => $profile), '');
        if (!$result[0]) {
            $errmsg = 'Unable to add job to queue';
            $error->add('sites', $errmsg);
            $error->add('sites', $result[1]);
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }
        $jobid = $result[1];
        unset($result);

    // Check if ConfNginx is successfull
    usleep(5000);
        $check = $this->amm->job_status($jobid);
        while ($check[0]) {
            if ($check[1] == 'failed') {
                $errmsg = 'Unable to create nginx configuration file';
                $error->add('sites', $errmsg);
                $error->add('sites', $check[1]);
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            } elseif ($check[1] = 'closed') {
                goto awstats;
            }
            usleep(5000);
            $check = $this->amm->job_status($jobid);
        }

    // Add awstats conf based on template
    awstats:
    $result = $this->amm->add_job('Site-ConfAwstats', $acc_id, $jobdata = array('acc_id' => $acc_id, 'sitename' => $name), '');
        if (!$result[0]) {
            $errmsg = 'Unable to add job to queue';
            $error->add('sites', $errmsg);
            $error->add('sites', $result[1]);
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }
        $jobid = $result[1];
        unset($result);

    // Check if ConfAwstats is successfull
    usleep(5000);
        $check = $this->amm->job_status($jobid);
        while ($check[0]) {
            if ($check[1] == 'failed') {
                $errmsg = 'Unable to create awstats configuration file';
                $error->add('sites', $errmsg);
                $error->add('sites', $check[1]);
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            } elseif ($check[1] = 'closed') {
                goto success;
            }
            usleep(5000);
            $check = $this->amm->job_status($jobid);
        }

        success:

    $return[0] = true;
        $return[1] = $name;

        end:
    unset($query, $name, $acc_id, $domain, $errmsg, $conf_tmp);

        return $return;
    }

    public function remove($acc_id, $site_name)
    {
        global $error;

        $result = $this->amm->add_job('Site-Remove', $acc_id, $jobdata = array('acc_id' => $acc_id, 'sitename' => $site_name), '');
        if (!$result[0]) {
            $errmsg = 'Unable to add job to queue';
            $error->add('sites', $errmsg);
            $error->add('sites', $result[1]);
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }
        $jobid = $result[1];
        unset($result);

    // Check if ConfAwstats is successfull
    usleep(5000);
        $check = $this->amm->job_status($jobid);
        while ($check[0]) {
            if ($check[1] == 'failed') {
                $errmsg = 'Unable to remove site files and configuration';
                $error->add('sites', $errmsg);
                $error->add('sites', $check[1]);
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            } elseif ($check[1] = 'closed') {
                goto databasepart1;
            }
            usleep(5000);
            $check = $this->amm->job_status($jobid);
        }

    // Remove site from database
    databasepart1:
    $query = "DELETE FROM sites WHERE acc_id='".$acc_id."' AND site_name='".$site_name."'";
        if ($this->db->sql->query($query) === true) {
            goto databasepart2;
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      if ($this->db->sql->query($query) === true) {
          goto databasepart2;
      } else {
          // other database error
    $error->add('site->remove', $this->db->sql->error);
          $error->add('site->remove', $query);
          $errmsg = 'unable to remove site from database';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
        }
        databasepart2:
    $query = "DELETE FROM sites_info WHERE acc_id='".$acc_id."' AND site_name='".$site_name."'";
        if ($this->db->sql->query($query) === true) {
            goto success;
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      if ($this->db->sql->query($query) === true) {
          goto success;
      } else {
          // other database error
    $error->add('site->remove', $this->db->sql->error);
          $error->add('site->remove', $query);
          $errmsg = 'unable to remove site from database';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
        }

        success:

    $return[0] = true;
        $errmsg = "site '".$site_name."' removed successfully";
        $return[1] = $errmsg;
        unset($errmsg);

        end:
    unset($query, $site_name, $acc_id, $errmsg);

        return $return;
    }

    public function archive($acc_id, $site_name)
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

    // Add Archive job to queue
    $result = $this->amm->add_job('Site-Archive', $acc_id, $jobdata = array('acc_id' => $acc_id, 'sitename' => $site_name), '');
        if (!$result[0]) {
            $errmsg = 'Unable to add job to queue';
            $error->add('sites->archive', $errmsg);
            $error->add('sites->archive', $result[1]);
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }
        $jobid = $result[1];
        unset($result);

    // Check if Job is successfull
    usleep(5000);
        $check = $this->amm->job_status($jobid);
        while ($check[0]) {
            if ($check[1] == 'failed') {
                $errmsg = 'Unable to archive site';
                $error->add('sites->archive', $errmsg);
                $error->add('sites->archive', $check[1]);
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            } elseif ($check[1] = 'closed') {
                $return[0] = true;
                goto end;
            }
            usleep(5000);
            $check = $this->amm->job_status($jobid);
        }

        end:
    unset($acc_id, $sitename, $check, $result, $errmsg, $jobid);

        return $return;
    }

    public function status($acc_id)
    { // grabs the current status of all sites for an acc_id
    global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // check database exists and grab info
    $query = "SELECT * FROM sites WHERE acc_id='".$acc_id."'";
        if ($result = $this->db->sql->query($query)) {
            // see if databases exist....
      $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $errmsg = 'this account currently has no sites';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            } else {
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $return[1][$row['site_name']]['created'] = $row['created'];
                    $return[1][$row['site_name']]['modified'] = $row['modified'];
                }
                $result->close();
                unset($row, $result);
                goto moresize;
            }
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                // see if databases exist....
    $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $errmsg = 'this account currently has no sites';
                    $return[0] = false;
                    $return[1] = $errmsg;
                    goto end;
                } else {
                    $return[0] = true;
                    while ($row = $result->fetch_assoc()) {
                        $return[1][$row['site_name']]['created'] = $row['created'];
                        $return[1][$row['site_name']]['modified'] = $row['modified'];
                    }
                    $result->close();
                    unset($row, $result);
                    goto moresize;
                }
            } else {
                // other database error
    $error->add('site->status', $this->db->sql->error);
                $error->add('site->status', $query);
                $errmsg = 'unable to query database to check if sites exist';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        moresize:
    $totalsize = 0;
        $totalbandwidth = 0;
        foreach (array_keys($return[1]) as $site_name) {
            $size = $this->site_size($acc_id, $site_name, false);
            if ($size[0]) {
                $return[1][$site_name]['size'] = $size[1];
            } else {
                $return[1][$site_name]['size'] = '';
            }
            $size = $this->site_size($acc_id, $site_name, true);
            $totalsize = $totalsize + $size[1];
            unset($size);
            $httpbandwidth = $this->site_httpbandwidth($acc_id, $site_name);
            if ($httpbandwidth[0]) {
                $totalbandwidth = $totalbandwidth + $httpbandwidth[1]['viewed'] + $httpbandwidth[1]['nonviewed'];
                $return[1][$site_name]['bandwidth-http-viewed'] = $httpbandwidth[1]['viewed'];
                $return[1][$site_name]['bandwidth-http-nonviewed'] = $httpbandwidth[1]['nonviewed'];
            } else {
                $return[1][$site_name]['bandwidth-http-viewed'] = '';
                $return[1][$site_name]['bandwidth-http-nonviewed'] = '';
            }
            unset($httpbandwidth);
            $defaultdomain = $this->get_attr($acc_id, $site_name, 'domains', 'default');
            if ($defaultdomain[0]) {
                $return[1][$site_name]['defaultdomain'] = $defaultdomain[1];
            } else {
                $return[1][$site_name]['defaultdomain'] = '';
            }
            unset($defaultdomain);
            $phpenabled = $this->get_attr($acc_id, $site_name, 'php', 'enabled');
            if ($phpenabled[0]) {
                $return[1][$site_name]['phpenabled'] = $phpenabled[1];
            } else {
                $return[1][$site_name]['phpenabled'] = '';
            }
            unset($phpenabled);
            $phpmicrocacheenabled = $this->get_attr($acc_id, $site_name, 'php', 'microcache');
            if ($phpmicrocacheenabled[0]) {
                $return[1][$site_name]['phpmicrocache'] = $phpmicrocacheenabled[1];
            } else {
                $return[1][$site_name]['phpmicrocache'] = '';
            }
            unset($phpmicrocacheenabled);
            $customcode = $this->get_attr_group($acc_id, $site_name, 'custom');
            if ($customcode[0]) {
                $return[1][$site_name]['customcode'] = 'enabled';
            } else {
                $return[1][$site_name]['customcode'] = '';
            }
            unset($customcode);
            $profile = $this->get_attr($acc_id, $site_name, 'default', 'profile');
            if ($profile[0]) {
                $return[1][$site_name]['profile'] = $profile[1];
            } else {
                $return[1][$site_name]['profile'] = '';
            }
            unset($profile);
        }
        unset($site_name);
        $return[1]['__totalsize__'] = $totalsize;
        unset($totalsize);
        $return[1]['__totalbandwidth__'] = $totalbandwidth;
        unset($totalbandwidth);

        end:
    unset($query, $acc_id, $errmsg);

        return $return;
    }

    private function site_size($acc_id, $site_name, $raw = false)
    { // calculates the size of the site in human readable sizes
    global $error;
        global $conf;

        if ($raw) {
            $type = 'raw';
        } else {
            $type = 'human';
        }
        unset($raw);

        $size = $this->get_attr($acc_id, $site_name, 'size', $type);
        if ($size[0]) {
            $size = $size[1];
        } else {
            $this->amm->add_job('Site-CalcSize', $acc_id, $jobdata = array('acc_id' => $acc_id, 'sitename' => $site_name), '');
      // then try again
      $size = $this->get_attr($acc_id, $site_name, 'size', $type);
            if ($size[0]) {
                $size = $size[1];
            } else {
                if ($type == 'raw') {
                    $size = 0;
                }
                if ($type == 'human') {
                    $size = 'unknown';
                }
            }
        }

        $return[0] = true;
        $return[1] = $size;
        unset($size);

        end:
    unset($acc_id, $site_name, $errmsg, $type);

        return $return;
    }

    public function update_size($acc_id, $site_name)
    {
        global $error;
        global $conf;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

    // Check site directory exists
    if (!is_dir($conf->base_home_dir.'/'.$acc_id.'/'.$site_name)) {
        $errmsg = 'site directory does not exist';
        $return[0] = false;
        $return[1] = $errmsg." '".$acc_id.'->'.$site_name."'";
        goto end;
    }

    // Calculate sizes
    $rawsize = exec('sudo du -s '.$conf->base_home_dir.'/'.$acc_id.'/'.$site_name.'/');
        $rawsize = explode('/', $rawsize);
        $rawsize = trim($rawsize[0]);

        $humansize = exec('sudo du -sh '.$conf->base_home_dir.'/'.$acc_id.'/'.$site_name.'/');
        $humansize = explode('/', $humansize);
        $humansize = trim($humansize[0]);
        if ($humansize == '0') {
            $humansize = 'zero';
        }

        $result = $this->update_attr($acc_id, $site_name, 'size', 'raw', $rawsize);
        if (!$result[0]) {
            $return[0] = false;
            $return[1] = $result[1];
            unset($result);
            goto end;
        }
        unset($result);

        $result = $this->update_attr($acc_id, $site_name, 'size', 'human', $humansize);
        if (!$result[0]) {
            $return[0] = false;
            $return[1] = $result[1];
            unset($result);
            goto end;
        }
        unset($result);

        $result = $this->update_attr($acc_id, $site_name, 'size', 'lastupdate', date('Y-m-d H:i:s'));
        if (!$result[0]) {
            $return[0] = false;
            $return[1] = $result[1];
            unset($result);
            goto end;
        }
        unset($result);

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $type, $value, $errmsg);

        return $return;
    }

    private function site_httpbandwidth($acc_id, $site_name)
    { // returns the viewed and nonviewed httpbandwidth of the site in raw
    global $error;
        global $conf;

        $return = array();

        $result = $this->get_attr($acc_id, $site_name, 'bandwidth-http-viewed', date('Y-m'));
        if (!$result[0]) {
            $return[1]['viewed'] = 0;
        } else {
            $return[1]['viewed'] = $result[1];
        }
        unset($result);

        $result = $this->get_attr($acc_id, $site_name, 'bandwidth-http-nonviewed', date('Y-m'));
        if (!$result[0]) {
            $return[1]['nonviewed'] = 0;
        } else {
            $return[1]['nonviewed'] = $result[1];
        }
        unset($result);

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $errmsg);

        return $return;
    }

    public function awstats_httpbandwidth($acc_id, $site_name)
    {
        global $error;
        global $conf;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

        $result = $this->get_attr($acc_id, $site_name, 'awstats', 'name');
        if (!$result[0]) {
            $errmsg = "unable to find awstats file in database for '".$acc_id.'->'.$site_name."'";
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }
        $site_awstats = $result[1];
        unset($result);

    // grab & calc awstats http bandwidth for site
    $bandwidth = shell_exec('grep -i -A 25 begin_time /var/lib/awstats/awstats'.date('m').date('Y').'.'.$acc_id.'.'.$site_awstats.'.txt | grep -vi time');
        $viewed = 0;
        $nonviewed = 0;
        $bandwidth = explode("\n", $bandwidth);
        foreach ($bandwidth as $line) {
            $temp = explode(' ', $line);
            $viewed = $viewed + $temp[3];
            $nonviewed = $nonviewed + $temp[6];
            unset($temp);
        }
        unset($line, $linenum);

        $result = $this->update_attr($acc_id, $site_name, 'bandwidth-http-viewed', date('Y-m'), $viewed);
        if (!$result[0]) {
            $return[0] = false;
            $return[1] = $result[1];
            unset($result);
            goto end;
        }
        unset($result);

        $result = $this->update_attr($acc_id, $site_name, 'bandwidth-http-nonviewed', date('Y-m'), $nonviewed);
        if (!$result[0]) {
            $return[0] = false;
            $return[1] = $result[1];
            unset($result);
            goto end;
        }
        unset($result);

        $result = $this->update_attr($acc_id, $site_name, 'bandwidth-http', 'lastupdate', date('Y-m-d H:i:s'));
        if (!$result[0]) {
            $return[0] = false;
            $return[1] = $result[1];
            unset($result);
            goto end;
        }
        unset($result);

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $type, $value, $viewed, $nonviewed, $errmsg);

        return $return;
    }

    public function stats($acc_id)
    { // returns each site name and a url for the awstats stats page for that site
    global $error;
        global $conf;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // check database exists and grab info
    $query = "SELECT * FROM sites_info WHERE acc_id='".$acc_id."' AND attr_group='awstats' AND attr='name'";
        if ($result = $this->db->sql->query($query)) {
            // see if databases exist....
      $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $errmsg = 'this account currently has no sites';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            } else {
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $return[1][$row['site_name']] = $conf->awstats['url'].$acc_id.'.'.$row['value'];
                }
                $result->close();
                unset($row, $result);
                goto end;
            }
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                // see if databases exist....
    $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $errmsg = 'this account currently has no sites';
                    $return[0] = false;
                    $return[1] = $errmsg;
                    goto end;
                } else {
                    $return[0] = true;
                    while ($row = $result->fetch_assoc()) {
                        $return[1][$row['site_name']] = $conf->awstats_url.$acc_id.'.'.$row['value'];
                    }
                    $result->close();
                    unset($row, $result);
                    goto end;
                }
            } else {
                // other database error
    $error->add('site->stats', $this->db->sql->error);
                $error->add('site->stats', $query);
                $errmsg = 'unable to query database to check if sites exist';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $acc_id, $errmsg);

        return $return;
    }

    public function get_attr($acc_id, $site_name, $attr_group, $attr)
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

    // escape $attr_group
    $esc = $this->db->esc($attr_group);
        unset($attr_group);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $attr_group = $esc[1];
        unset($esc);

    // escape $attr
    $esc = $this->db->esc($attr);
        unset($attr);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $attr = $esc[1];
        unset($esc);

    // check database exists and grab info
    $query = "SELECT value FROM sites_info WHERE acc_id='".$acc_id."' AND site_name='".$site_name."' AND attr_group='".$attr_group."' AND attr='".$attr."'";
        if ($result = $this->db->sql->query($query)) {
            // see if attribute exists....
      $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $errmsg = 'no attribute exists';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            } else {
                $return[0] = true;
                $row = $result->fetch_assoc();
                $return[1] = $row['value'];
                $result->close();
                unset($row, $result);
                goto end;
            }
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                // see if databases exist....
    $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $errmsg = 'no attribute exists';
                    $return[0] = false;
                    $return[1] = $errmsg;
                    goto end;
                } else {
                    $return[0] = true;
                    $row = $result->fetch_assoc();
                    $return[1] = $row['value'];
                    $result->close();
                    unset($row, $result);
                    goto end;
                }
            } else {
                // other database error
    $error->add('site->get_attr', $this->db->sql->error);
                $error->add('site->get_attr', $query);
                $errmsg = 'unable to query database to check if attribute exists';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $acc_id, $attr_group, $attr, $errmsg);

        return $return;
    }

    private function update_attr($acc_id, $site_name, $attr_group, $attr, $value)
    {
        global $error;

    // Check if attr already exists.
    $attr_exists = $this->get_attr($acc_id, $site_name, $attr_group, $attr);

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

    // escape $attr_group
    $esc = $this->db->esc($attr_group);
        unset($attr_group);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $attr_group = $esc[1];
        unset($esc);

    // escape $attr
    $esc = $this->db->esc($attr);
        unset($attr);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $attr = $esc[1];
        unset($esc);

    // escape $value
    $esc = $this->db->esc($value);
        unset($value);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $value = $esc[1];
        unset($esc);

        if ($attr_exists[0]) {
            // Attr already exists, update
      $query = "UPDATE sites_info SET value='".$value."' WHERE acc_id='".$acc_id."' AND site_name='".$site_name."' AND attr_group='".$attr_group."' AND attr='".$attr."'";
            if ($this->db->sql->query($query) === true) {
                $return[0] = true;
                goto end;
            } else {
                // query failed. attempt reconnect
    if (!$this->db->connect()) {
        $errmsg = 'database is not avaliable';
        $return[0] = false;
        $return[1] = $errmsg;
        goto end;
    }
    // and try again
    if ($this->db->sql->query($query) === true) {
        goto end;
    } else {
        // other database error
      $error->add('site->update_attr', $this->db->sql->error);
        $error->add('site->update_attr', $query);
        $errmsg = 'unable to update site attribute';
        $return[0] = false;
        $return[1] = $errmsg;
        goto end;
    }
            }
        } else {
            // Attr does not exist, insert
      $query = "INSERT INTO sites_info VALUES('".$acc_id."','".$site_name."','".$attr_group."','".$attr."','".$value."')";
            if ($this->db->sql->query($query) === true) {
                $return[0] = true;
                goto end;
            } else {
                // query failed. attempt reconnect
    if (!$this->db->connect()) {
        $errmsg = 'database is not avaliable';
        $return[0] = false;
        $return[1] = $errmsg;
        goto end;
    }
    // and try again
    if ($this->db->sql->query($query) === true) {
        goto end;
    } else {
        // other database error
      $error->add('site->update_attr', $this->db->sql->error);
        $error->add('site->update_attr', $query);
        $errmsg = 'unable to add site attribute, attribute may already exist';
        $return[0] = false;
        $return[1] = $errmsg;
        goto end;
    }
            }
        }

        end:
    unset($query, $acc_id, $attr_group, $attr, $value, $attr_exists, $errmsg);

        return $return;
    }

    private function get_attr_group($acc_id, $site_name, $attr_group)
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

    // escape $attr_group
    $esc = $this->db->esc($attr_group);
        unset($attr_group);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $attr_group = $esc[1];
        unset($esc);

    // grab info
    $query = "SELECT attr,value FROM sites_info WHERE acc_id='".$acc_id."' AND site_name='".$site_name."' AND attr_group='".$attr_group."' ORDER BY attr asc,value asc";
        if ($result = $this->db->sql->query($query)) {
            // see if attributes exists....
      $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $errmsg = 'no attributes exists';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            } else {
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $return[1][$row['attr']] = $row['value'];
                }
                $result->close();
                unset($row, $result);
                goto end;
            }
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                // see if attribute exists....
    $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $errmsg = 'no attributes exists';
                    $return[0] = false;
                    $return[1] = $errmsg;
                    goto end;
                } else {
                    $return[0] = true;
                    while ($row = $result->fetch_assoc()) {
                        $return[1][$row['attr']] = $row['value'];
                    }
                    $result->close();
                    unset($row, $result);
                    goto end;
                }
            } else {
                // other database error
    $error->add('site->get_attr_group', $this->db->sql->error);
                $error->add('site->get_attr_group', $query);
                $errmsg = 'unable to query database to check if attributes exist';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $acc_id, $attr_group, $attr, $errmsg);

        return $return;
    }

    public function get_domainslist($acc_id, $site_name)
    { // returns the domainslist string, does not include the default domain
    $result = $this->get_attr_group($acc_id, $site_name, 'domains');
        if (!$result[0]) {
            $return = $result;
            goto end;
        }

        $return[1] = '';
        foreach ($result[1] as $key => $value) {
            if ($key != 'default') {
                $return[1] = $return[1].' '.$value;
            }
        }
        $return[0] = true;
        if (trim($return[1]) == '') {
            $return[1] = '';
        } else {
            $return[1] = ' '.trim($return[1]);
        }

        end:
    unset($acc_id, $site_name, $result);

        return $return;
    }

    public function get_redirectlist($acc_id, $site_name)
    { // returns the redirectlist as a nginx conf string
    $result = $this->get_attr_group($acc_id, $site_name, 'redirects');
        if (!$result[0]) {
            $return = $result;
            goto end;
        }

        $return[1] = '';
        foreach ($result[1] as $key => $value) {
            // structure: "  redirect ^/_value_$ _value2_ _value3_;\n"
      $value = json_decode($value, true);
            if (strtolower($value['type']) != 'redirect' and strtolower($value['type']) != 'permanent') {
                $value['type'] = 'redirect';
            }
            $return[1] = $return[1].'    rewrite ^/'.$value['regex'].'$ '.$value['destination'].' '.strtolower($value['type']).";\n";
        }
        $return[0] = true;
        if (trim($return[1]) == '') {
            $return[1] = '';
        } else {
            $return[1] = "\n".$return[1]."\n";
        }

        end:
    unset($acc_id, $site_name, $result);

        return $return;
    }

    public function get_customcode($acc_id, $site_name)
    { // returns the custom code as a nginx conf string
    $result = $this->get_attr_group($acc_id, $site_name, 'custom');
        if (!$result[0]) {
            $return = $result;
            goto end;
        }

        $return[1] = '';
        foreach ($result[1] as $key => $value) {
            $return[1] = $return[1].$value."\n";
        }
        $return[0] = true;
        if (trim($return[1]) == '') {
            $return[1] = '';
        } else {
            $return[1] = "\n".$return[1]."\n";
        }

        end:
    unset($acc_id, $site_name, $result, $key, $value);

        return $return;
    }

    public function get_php_microcache($acc_id, $site_name)
    { // returns true or false for enabling fcgi microcaching
    $result = $this->get_attr_group($acc_id, $site_name, 'php');
        if (!$result[0]) {
            $return = $result;
            goto end;
        }

        $return[1] = '';
        $return[0] = false;
        foreach ($result[1] as $key => $value) {
            if ($key == 'microcache' and $value == 'enabled') {
                $return[0] = true;
                goto end;
            }
        }

        end:
    unset($acc_id, $site_name, $result, $key, $value);

        return $return;
    }

    public function php_microcache_add($acc_id, $site_name)
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

        $result = $this->update_attr($acc_id, $site_name, 'php', 'microcache', 'enabled');
        unset($array);
        if (!$result[0]) {
            $errmsg = "unable to enable php microcache for '".$acc_id."' '".$site_name."'";
            $return[0] = false;
            $return[1] = $errmsg;
            $error->add('site->php_microcache_add', $errmsg);
            $error->add('site->php_microcache_add', $result[1]);
            unset($errmsg);
            goto end;
        }

        $this->flag_set($acc_id, $site_name, 'nginx');

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $errmsg);

        return $return;
    }

    public function php_microcache_remove($acc_id, $site_name)
    {
        global $error;

        $return = $this->attr_remove($acc_id, $site_name, 'php', 'microcache');

        if ($return[0]) {
            $this->flag_set($acc_id, $site_name, 'nginx');
        }

        unset($acc_id, $site_name);

        return $return;
    }

    public function get_redirect_array($acc_id, $site_name)
    { // returns the redirectlist as an array
    $result = $this->get_attr_group($acc_id, $site_name, 'redirects');
        if (!$result[0]) {
            $return = $result;
            goto end;
        }

        $return[1] = array();
        foreach ($result[1] as $key => $value) {
            $value = json_decode($value, true);
            $value['redirect'] = $key;
            if (strtolower($value['type']) != 'redirect' and strtolower($value['type']) != 'permanent') {
                $value['type'] = 'redirect';
            }
            $return[1][] = $value;
        }
        $return[0] = true;

        end:
    unset($acc_id, $site_name, $result);

        return $return;
    }

    public function redirect_add($acc_id, $site_name, $regex, $destination, $type = 'redirect')
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        if (strlen($regex) == 0 or strlen($destination) == 0) {
            $errmsg = 'Redirect and Destiation cannot be blank.';
            $return[0] = false;
            $return[1] = $errmsg;
            unset($errmsg);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

        $type = strtolower($type);
        if ($type != 'permanent' and $type != 'redirect') {
            $type = 'redirect';
        }

        $datenow = new DateTime();
        $datenow = $datenow->getTimestamp();

        $array = array('regex' => $regex,'destination' => $destination,'type' => $type);

        $result = $this->update_attr($acc_id, $site_name, 'redirects', $datenow.'_'.mt_rand(0, 9), json_encode($array));
        unset($array);
        if (!$result[0]) {
            $errmsg = "unable to add '".$regex.'->'.$destination."' to redirects for '".$acc_id."' '".$site_name."'";
            $return[0] = false;
            $return[1] = $errmsg;
            $error->add('site->redirect_add', $errmsg);
            $error->add('site->redirect_add', $result[1]);
            unset($errmsg);
            goto end;
        }

        $this->flag_set($acc_id, $site_name, 'nginx');

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $regex, $destination, $type, $errmsg);

        return $return;
    }

    public function redirect_remove($acc_id, $site_name, $redirect)
    {
        global $error;

        $return = $this->attr_remove($acc_id, $site_name, 'redirects', $redirect);

        if ($return[0]) {
            $this->flag_set($acc_id, $site_name, 'nginx');
        }

        unset($acc_id, $site_name, $redirect, $errmsg, $query);

        return $return;
    }

    private function attr_remove($acc_id, $site_name, $attr_group, $attr)
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

    // escape $attr_group
    $esc = $this->db->esc($attr_group);
        unset($attr_group);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $attr_group = $esc[1];
        unset($esc);

    // escape $attr
    $esc = $this->db->esc($attr);
        unset($attr);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $attr = $esc[1];
        unset($esc);

        $query = "DELETE FROM sites_info WHERE acc_id='".$acc_id."' AND site_name='".$site_name."' AND attr_group='".$attr_group."' AND attr='".$attr."'";
        if ($this->db->sql->query($query) === true) {
            $return[0] = true;
            goto end;
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      if ($this->db->sql->query($query) === true) {
          $return[0] = true;
          goto end;
      } else {
          // other database error
    $error->add('site->attr_remove', $this->db->sql->error);
          $error->add('site->attr_remove', $query);
          $errmsg = "unable to remove attr '".$attr_group.'->'.$attr."' from database for '".$acc_id."' '".$site_name."'";
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
        }

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $attr_group, $attr, $errmsg, $query);

        return $return;
    }

    public function update_profile($acc_id, $site_name, $profile)
    { // Changes the site nginx profile
    global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

    // escape $profile
    // TODO: check for valid profile
    $esc = $this->db->esc($profile);
        unset($profile);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $profile = $esc[1];
        unset($esc);

    // Grab original value
    $result = $this->get_attr($acc_id, $site_name, 'default', 'profile');
        if ($result[0]) {
            $original = $result[1];
        } else {
            $original = 'default';
        }
        unset($result);

    // Update Database entry
    $result = $this->update_attr($acc_id, $site_name, 'default', 'profile', $profile);
        if (!$result[0]) {
            $errmsg = "unable to update profile for '".$acc_id."' '".$name."'";
            $error->add('site->update_profile', $errmsg);
            $error->add('site->update_profile', $result[1]);
            unset($errmsg);
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }

    // Add job to queue
    $result = $this->amm->add_job('Site-ConfNginx', $acc_id, $jobdata = array('acc_id' => $acc_id, 'sitename' => $site_name, 'profile' => $profile), '');
        if (!$result[0]) {
            $errmsg = 'Unable to add job to queue';
            $error->add('sites->update_profile', $errmsg);
            $error->add('sites->update_profile', $result[1]);
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }
        $jobid = $result[1];
        unset($result);

        $result = $this->amm->add_job('Site-ConfAwstats', $acc_id,
    $jobdata = array('acc_id' => $acc_id, 'sitename' => $site_name), '');
        if (!$result[0]) {
            $errmsg = 'Unable to add job to queue';
            $error->add('sites->update_profile', $errmsg);
            $error->add('sites->update_profile', $result[1]);
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }
        unset($result);

    // Check if Job is successfull
    usleep(5000);
        $check = $this->amm->job_status($jobid);
        while ($check[0]) {
            if ($check[1] == 'failed') {
                $errmsg = 'Unable to update profile';
                $error->add('sites->update_profile', $errmsg);
                $error->add('sites->update_profile', $check[1]);
                $return[0] = false;
                $return[1] = $errmsg;
                goto fail;
            } elseif ($check[1] = 'closed') {
                $return[0] = true;
                goto end;
            }
            usleep(5000);
            $check = $this->amm->job_status($jobid);
        }

        fail:
    // Update Database entry
    $result = $this->update_attr($acc_id, $site_name, 'default', 'profile', $original);
        if (!$result[0]) {
            $errmsg = "unable to update profile for '".$acc_id."' '".$name."'";
            $error->add('site->update_profile', $errmsg);
            $error->add('site->update_profile', $result[1]);
            unset($errmsg);
            goto end;
        }

        $result = $this->update_attr($acc_id, $site_name, 'awstats', 'name', $site_name);
        if (!$result[0]) {
            $errmsg = "unable to update awstats name for '".$acc_id."' '".$name."'";
            $error->add('site->update_profile', $errmsg);
            $error->add('site->update_profile', $result[1]);
            unset($errmsg);
            goto end;
        }

        end:

    if ($return[0]) {
        $this->flag_unset($acc_id, $site_name, 'nginx');
    }

        unset($acc_id, $sitename, $profile, $check, $result, $original, $errmsg, $jobid);

        return $return;
    }

    public function change_profile($acc_id, $site_name, $profile)
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

    // escape $profile
    $esc = $this->db->esc($profile);
        unset($profile);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $profile = $esc[1];
        unset($esc);

        $result = $this->update_attr($acc_id, $site_name, 'default', 'profile', $profile);
        if (!$result[0]) {
            $errmsg = "unable to change profile to '".$profile."' for '".$acc_id."' '".$site_name."'";
            $return[0] = false;
            $return[1] = $errmsg;
            $error->add('site->change_profile', $errmsg);
            $error->add('site->change_profile', $result[1]);
            unset($errmsg);
            goto end;
        }

        $this->flag_set($acc_id, $site_name, 'nginx');

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $profile, $errmsg);

        return $return;
    }

    public function domainlist_add($acc_id, $site_name, $domain)
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

    // escape $domain
    $esc = $this->db->esc($domain);
        unset($domain);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $domain = $esc[1];
        unset($esc);

        $datenow = new DateTime();
        $datenow = $datenow->getTimestamp();

    // Major TODO - check for valid domain structure
    // Major TODO - check if domain already allocated elseswhere in server

    $result = $this->update_attr($acc_id, $site_name, 'domains', $datenow.'_'.mt_rand(0, 9), $domain);
        if (!$result[0]) {
            $errmsg = "unable to add '".$domain."' to domainlist for '".$acc_id."' '".$site_name."'";
            $return[0] = false;
            $return[1] = $errmsg;
            $error->add('site->domainlist_add', $errmsg);
            $error->add('site->domainlist_add', $result[1]);
            unset($errmsg);
            goto end;
        }

        $this->flag_set($acc_id, $site_name, 'nginx');

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $domain, $errmsg);

        return $return;
    }

    public function domainlist_remove($acc_id, $site_name, $domain)
    {
        global $error;

        $result = $this->get_attr_group($acc_id, $site_name, 'domains');
        if (!$result[0]) {
            $return = $result;
            goto end;
        }

        $attr = '';
        foreach ($result[1] as $key => $value) {
            // set domain to remove, but not primary domain
      if ($value == $domain and $key != 'default') {
          $attr = $key;
      }
        }
        unset($result);

        if ($attr != '') {
            $query = "DELETE FROM sites_info WHERE acc_id='".$acc_id."' AND site_name='".$site_name."' AND attr_group='domains' AND attr='".$attr."'";
            if ($this->db->sql->query($query) === true) {
                $return[0] = true;
                goto end;
            } else {
                // query failed. attempt reconnect
    if (!$this->db->connect()) {
        $errmsg = 'database is not avaliable';
        $return[0] = false;
        $return[1] = $errmsg;
        goto end;
    }
    // and try again
    if ($this->db->sql->query($query) === true) {
        $return[0] = true;
        goto end;
    } else {
        // other database error
      $error->add('site->domainlist_remove', $this->db->sql->error);
        $error->add('site->domainlist_remove', $query);
        $errmsg = "unable to remove '".$domain."' from domainlist for '".$acc_id."' '".$site_name."'";
        $return[0] = false;
        $return[1] = $errmsg;
        goto end;
    }
            }
        }

        $return[0] = true;

        end:
    if ($return[0]) {
        $this->flag_set($acc_id, $site_name, 'nginx');
    }
        unset($acc_id, $site_name, $domain, $errmsg, $query);

        return $return;
    }

    public function domain_makeprimary($acc_id, $site_name, $domain)
    { // set domain as primary, set primary as additional domain
    global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

    // escape $domain
    $esc = $this->db->esc($domain);
        unset($domain);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $domain = $esc[1];
        unset($esc);

        $result = $this->get_attr_group($acc_id, $site_name, 'domains');
        if (!$result[0]) {
            $return = $result;
            goto end;
        }

        foreach ($result[1] as $key => $value) {
            if ($value == $domain) { // Remove normal domainlist of primary domain
    $remove = $this->attr_remove($acc_id, $site_name, 'domains', $key);
                if (!$remove[0]) {
                    $return = $remove;
                    unset($remove);
                    goto end;
                }
                unset($remove);
            }
            if ($key == 'default') { // Make old primary domain a normal domainlist
    $olddefault = $this->domainlist_add($acc_id, $site_name, $value);
                if (!$olddefault[0]) {
                    $return = $olddefault;
                    unset($olddefault);
                    goto end;
                }
                unset($olddefault);
            }
        }
        unset($result, $key, $value);

    // set domain as primary domain
    $result = $this->update_attr($acc_id, $site_name, 'domains', 'default', $domain);
        if (!$result[0]) {
            $errmsg = "unable to set '".$domain."' to as primary for '".$acc_id."' '".$site_name."'";
            $return[0] = false;
            $return[1] = $errmsg;
            $error->add('site->domain_makeprimary', $errmsg);
            $error->add('site->domain_makeprimary', $result[1]);
            unset($errmsg);
            goto end;
        }

        $this->flag_set($acc_id, $site_name, 'nginx');

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $domain, $key, $value, $result, $errmsg);

        return $return;
    }

    public function flag_set($acc_id, $site_name, $type)
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            $error->add('site->flag_set', 'acc_id:'.$return[1]);
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            $error->add('site->flag_set', 'site_name:'.$return[1]);
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

        $type = strtolower(trim($type));
        $type_array = array('nginx');

        if (!in_array($type, $type_array)) {
            $errmsg = 'invalid flag type provided';
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }

        $datenow = new DateTime();
        $datenow = $datenow->getTimestamp();

        $result = $this->update_attr($acc_id, $site_name, 'flag', $type, $datenow);
        if (!$result[0]) {
            $errmsg = "unable set flag of type '".$type."' for '".$acc_id."'->'".$site_name."'";
            $return[0] = false;
            $return[1] = $errmsg;
            $error->add('site->flag_set', $errmsg);
            $error->add('site->flag_set', $result[1]);
            unset($errmsg);
            goto end;
        }

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $type, $errmsg);

        return $return;
    }

    public function flag_unset($acc_id, $site_name, $type)
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            $error->add('site->flag_unset', 'acc_id:'.$return[1]);
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            $error->add('site->flag_unset', 'site_name:'.$return[1]);
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

        $type = strtolower(trim($type));
        $type_array = array('nginx');

        if (!in_array($type, $type_array)) {
            $errmsg = 'invalid flag type provided';
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }

        $datenow = new DateTime();
        $datenow = $datenow->getTimestamp();

        $result = $this->update_attr($acc_id, $site_name, 'flag', $type, 'unset');
        if (!$result[0]) {
            $errmsg = "unable unset flag of type '".$type."' for '".$acc_id."'->'".$site_name."'";
            $return[0] = false;
            $return[1] = $errmsg;
            $error->add('site->flag_set', $errmsg);
            $error->add('site->flag_set', $result[1]);
            unset($errmsg);
            goto end;
        }

        $return[0] = true;

        end:
    unset($acc_id, $site_name, $type, $errmsg);

        return $return;
    }

    public function flag_check($acc_id, $site_name, $type)
    {
        global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
        unset($acc_id);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            $error->add('site->flag_check', 'acc_id:'.$return[1]);
            unset($esc);
            goto end;
        }

        $acc_id = $esc[1];
        unset($esc);

    // escape $site_name
    $esc = $this->db->esc($site_name);
        unset($site_name);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            $error->add('site->flag_check', 'site_name:'.$return[1]);
            unset($esc);
            goto end;
        }

        $site_name = $esc[1];
        unset($esc);

        $type = strtolower(trim($type));
        $type_array = array('nginx');

        if (!in_array($type, $type_array)) {
            $errmsg = 'invalid flag type provided';
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }

        $result = $this->get_attr($acc_id, $site_name, 'flag', $type);
        if (!$result[0]) {
            $errmsg = "unable check flag of type '".$type."' for '".$acc_id."'->'".$site_name."'";
            $return[0] = false;
            $return[1] = $errmsg;
            $error->add('site->flag_set', $errmsg);
            $error->add('site->flag_set', $result[1]);
            unset($errmsg);
            goto end;
        }

        if ($result[1] == 'unset') {
            $return[0] = false;
            $return[1] = 'flag is unset';
        } else {
            $return[0] = true;
        }

        end:
    unset($acc_id, $site_name, $type, $errmsg);

        return $return;
    }

    public function sched_site_size()
    { // check which sites need a size check and adds to queue
    global $error;

    // grab from database which sites need updating
    $query = "SELECT acc_id,site_name FROM sites_info WHERE attr_group='size' AND attr='lastupdate' AND UNIX_TIMESTAMP(value)<(UNIX_TIMESTAMP(NOW())-60*60*3)";
        if ($result = $this->db->sql->query($query)) {
            $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $return[0] = true;
                goto end;
            } else {
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $this->amm->add_job('Site-CalcSize', '100000', $jobdata = array('acc_id' => $row['acc_id'], 'sitename' => $row['site_name']), '');
                }
                $result->close();
                unset($row, $result);
                goto end;
            }
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $return[0] = true;
                    goto end;
                } else {
                    $return[0] = true;
                    while ($row = $result->fetch_assoc()) {
                        $this->amm->add_job('Site-CalcSize', '100000', $jobdata = array('acc_id' => $row['acc_id'], 'sitename' => $row['site_name']), '');
                    }
                    $result->close();
                    unset($row, $result);
                    goto end;
                }
            } else {
                // other database error
    $error->add('site->sched_site_size', $this->db->sql->error);
                $error->add('site->sched_site_size', $query);
                $errmsg = 'unable to query database to check site sizes';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $errmsg);

        return $return;
    }

    public function sched_site_httpbandwidth()
    { // check which sites need a size check and adds to queue
    global $error;

    // grab from database which sites need updating
    $query = "SELECT acc_id,value as site_name FROM sites_info WHERE attr_group='awstats' AND attr='name'";
        if ($result = $this->db->sql->query($query)) {
            $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $return[0] = true;
                goto end;
            } else {
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $this->amm->add_job('Site-CalcHttpBandwidth', '100000', $jobdata = array('acc_id' => $row['acc_id'], 'sitename' => $row['site_name']), '');
                }
                $result->close();
                unset($row, $result);
                goto end;
            }
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $return[0] = true;
                    goto end;
                } else {
                    $return[0] = true;
                    while ($row = $result->fetch_assoc()) {
                        $this->amm->add_job('Site-CalcHttpBandwidth', '100000', $jobdata = array('acc_id' => $row['acc_id'], 'sitename' => $row['site_name']), '');
                    }
                    $result->close();
                    unset($row, $result);
                    goto end;
                }
            } else {
                // other database error
    $error->add('site->sched_site_httpbandwidth', $this->db->sql->error);
                $error->add('site->sched_site_httpbandwidth', $query);
                $errmsg = 'unable to query database to check http bandwidth for sites';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $errmsg);

        return $return;
    }
}
?>
