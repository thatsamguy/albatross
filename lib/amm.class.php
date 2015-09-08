<?php
/* 
* Albatross Manager
* 
* Albatross Manager Monitor (AMM) Daemon Management and SQL Interface class
* 
* Description:
*  Contains all functions for managing the AMM including inserting new jobs
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
// Include Dependencies
include_once 'config.class.php';
include_once 'mysqli.class.php';
include_once 'error.class.php';
?>
<?php
class amm
{
    private $db;

    public function __construct()
    {
        // start db connection
    $this->db = new db();
        $this->db->database = 'default';
        $this->db->connect();
    }

    public function __destruct()
    {
        // Do nothing.
    unset($this->db);
    }

    public function add_job($jobtype, $acc_id, $jobdata = array(), $workflow = '')
    { // adds a new job to the amm queue. $jobdata is a key=>value array of data.
    global $error;

    // check for a vaild jobtype
    $check = $this->job_type_check($jobtype);
        unset($jobtype);
        if (!$check[0]) {
            $return[0] = false;
            $return[1] = $check[1];
            unset($check);
            goto end;
        }

        $jobtype = $check[1];
        unset($check);

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

    // escape $jobarray
    $jobdata = json_encode($jobdata);
        $esc = $this->db->esc($jobdata);
        unset($jobdata);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $jobdata = $esc[1];
        unset($esc);

    // escape $workflow
    if ($workflow != '') {
        $esc = $this->db->esc($workflow);
        unset($workflow);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $workflow = $esc[1];
        unset($esc);
    }

        if ($workflow == 'new') {
            $workflowdata = base_convert(mt_rand(1679616, 60466175), 10, 36);
        } elseif (strlen($workflow) != 5) {
            $workflowdata = '';
        }

        $uid = time().'_'.mt_rand(100, 999);

    // Add job to database queue
    $query = 'INSERT INTO amm VALUES("'.$uid.'","'.$jobtype.'","new","'.$acc_id.'","'.$jobdata.'",NOW(),NOW(),0,"'.$workflowdata.'")';
        if ($this->db->sql->query($query) === true) {
            $return[0] = true;
            $return[1] = $uid;
            goto end;
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again - with a different uid
      $uid = time().'_'.mt_rand(100, 999);
            if ($workflow == 'new') {
                $workflowdata = base_convert(mt_rand(1679616, 60466175), 10, 36);
            }
            $query = 'INSERT INTO amm VALUES("'.$uid.'","'.$jobtype.'","new","'.$acc_id.'","'.$jobdata.'",NOW(),NOW(),0,"'.$workflowdata.'")';
            if ($this->db->sql->query($query) === true) {
                $return[0] = true;
                $return[1] = $uid;
                goto end;
            } else {
                // other database error
    $error->add('amm->add_job', $this->db->sql->error);
                $error->add('amm->add_job', $query);
                $errmsg = 'unable to add new job to queue';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            }
        }

        end:
    unset($jobtype, $acc_id, $jobdata, $query, $uid, $workflow, $workflowdata, $errmsg);

        return $return;
    }

    public function add_schedule($jobtype, $schedule)
    { // adds a new schedule to the amm queue
    global $error;

    // check for a vaild jobtype
    $check = $this->job_type_check($jobtype);
        unset($jobtype);
        if (!$check[0]) {
            $return[0] = false;
            $return[1] = $check[1];
            unset($check);
            goto end;
        }

        $jobtype = $check[1];
        unset($check);

        if ($schedule <= 0 or  $schedule > 99999) {
            $errmsg = 'schedule must be greater than zero and less than 99999';
            $return[0] = false;
            $return[1] = $errmsg;
            unset($errmsg);
            goto end;
        }

    // Add schedule to database
    $query = 'INSERT INTO amm_sched VALUES(0,"'.$jobtype.'","'.$schedule.'",NOW(),NOW())';
        if ($this->db->sql->query($query) === true) {
            $return[0] = true;
            $return[1] = $this->db->sql->insert_id;
            goto end;
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
            if ($this->db->sql->query($query) === true) {
                $return[0] = true;
                $return[1] = $uid;
                goto end;
            } else {
                // other database error
    $error->add('amm->add_schedule', $this->db->sql->error);
                $error->add('amm->add_schedule', $query);
                $errmsg = 'unable to add new schedule';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            }
        }

        end:
    unset($jobtype, $acc_id, $schedule, $query, $errmsg);

        return $return;
    }

    public function schedule_update($uid)
    { // updates lastrun of schedule
    global $error;

    // escape uid
    $esc = $this->db->esc($uid);
        unset($uid);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $uid = $esc[1];
        unset($esc);

    // Add schedule to database
    $query = "UPDATE amm_sched SET lastrun=NOW() WHERE uid='".$uid."'";
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
            if ($this->db->sql->query($query) === true) {
                $return[0] = true;
                $return[1] = $uid;
                goto end;
            } else {
                // other database error
    $error->add('amm->schedule_update', $this->db->sql->error);
                $error->add('amm->schedule_update', $query);
                $errmsg = 'unable to update schedule';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            }
        }

        end:
    unset($uid, $query, $errmsg);

        return $return;
    }

    public function get_new_jobs()
    { // grabs array of jobtype[]->uid if new jobs are avaliable
    global $error;

    // grab info on database
    $query = "SELECT uid,jobtype,created FROM amm WHERE jobstatus='new' OR jobstatus='tryagain' ORDER BY created asc";
        if ($result = $this->db->sql->query($query)) {
            $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $return[0] = false;
                goto end;
            }
            $return[0] = true;
            while ($row = $result->fetch_assoc()) {
                $return[1]['count'] = $num_rows;
                $return[1][$row['jobtype']][] = $row['uid'];
            }
            $result->close();
            unset($row, $result);
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
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $return[0] = false;
                    goto end;
                }
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $return[1]['count'] = $num_rows;
                    $return[1][$row['jobtype']][] = $row['uid'];
                }
                $result->close();
                unset($row, $result);
                goto end;
            } else {
                // other database error
    $error->add('amm->get_new_jobs', $this->db->sql->error);
                $error->add('amm->get_new_jobs', $query);
                $errmsg = 'unable to query database to get new jobs';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $num_rows);

        return $return;
    }

    public function check_schedules()
    { // checks which schedules need to run and adds them to the job queue
    global $error;

    // grab schedules awaiting to run from database
    $query = 'SELECT * FROM amm_sched WHERE UNIX_TIMESTAMP(lastrun)<(UNIX_TIMESTAMP(NOW())-(schedule*60)) ORDER BY lastrun asc';
        if ($result = $this->db->sql->query($query)) {
            $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $return[0] = false;
                goto end;
            }
            $return[0] = true;
            while ($row = $result->fetch_assoc()) {
                $job = $this->add_job($row['jobtype'], '100000', array(), '');
                if ($job[0]) {
                    $this->schedule_update($row['uid']);
                }
                unset($job);
            }
            $result->close();
            unset($row, $result);
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
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $return[0] = false;
                    goto end;
                }
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $job = $this->add_job($row['jobtype'], '100000', array(), '');
                    if ($job[0]) {
                        $this->schedule_update($row['uid']);
                    }
                    unset($job);
                }
                $result->close();
                unset($row, $result);
                goto end;
            } else {
                // other database error
    $error->add('amm->check_schedules', $this->db->sql->error);
                $error->add('amm->check_schedules', $query);
                $errmsg = 'unable to query database to check schedules';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $num_rows);

        return $return;
    }

    public function get_active_jobs()
    { // grabs array of currently active jobs
    global $error;

    // grab info from database
    $query = "SELECT uid,created FROM amm WHERE jobstatus='active' ORDER BY created asc";
        if ($result = $this->db->sql->query($query)) {
            $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $return[0] = false;
                goto end;
            }
            $return[0] = true;
            while ($row = $result->fetch_assoc()) {
                $return[1]['count'] = $num_rows;
                $return[1]['jobs'][] = $row['uid'];
            }
            $result->close();
            unset($row, $result);
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
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $return[0] = false;
                    goto end;
                }
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $return[1]['count'] = $num_rows;
                    $return[1]['jobs'][] = $row['uid'];
                }
                $result->close();
                unset($row, $result);
                goto end;
            } else {
                // other database error
    $error->add('amm->get_active_jobs', $this->db->sql->error);
                $error->add('amm->get_active_jobs', $query);
                $errmsg = 'unable to query database to get database size';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $num_rows);

        return $return;
    }

    public function get_job_data($uid)
    { // Grabs jobdata from database, and returns as a json_decoded array
    global $error;

    // escape uid
    $esc = $this->db->esc($uid);
        unset($uid);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $uid = $esc[1];
        unset($esc);

    // grab jobdata from database
    $query = "SELECT jobdata FROM amm WHERE uid='".$uid."'";
        if ($result = $this->db->sql->query($query)) {
            $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $return[0] = false;
                goto end;
            }
            $return[0] = true;
            while ($row = $result->fetch_assoc()) {
                $return[1] = json_decode($row['jobdata'], true);
            }
            $result->close();
            unset($row, $result);
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
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $return[0] = false;
                    goto end;
                }
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $return[1] = json_decode($row['jobdata']);
                }
                $result->close();
                unset($row, $result);
                goto end;
            } else {
                // other database error
    $error->add('amm->get_job_data', $this->db->sql->error);
                $error->add('amm->get_job_data', $query);
                $errmsg = 'unable to query database to get jobdata';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $num_rows);

        return $return;
    }

    public function get_job_attempt($uid)
    { // Grabs job attempt number from database
    global $error;

    // escape uid
    $esc = $this->db->esc($uid);
        unset($uid);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $uid = $esc[1];
        unset($esc);

    // grab jobdata from database
    $query = "SELECT attempt FROM amm WHERE uid='".$uid."'";
        if ($result = $this->db->sql->query($query)) {
            $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $errmsg = 'no results';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
            }
            $return[0] = true;
            while ($row = $result->fetch_assoc()) {
                $return[1] = $row['attempt'];
            }
            $result->close();
            unset($row, $result);
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
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $errmsg = 'no results';
                    $return[0] = false;
                    $return[1] = $errmsg;
                    unset($errmsg);
                    goto end;
                }
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $return[1] = $row['attempt'];
                }
                $result->close();
                unset($row, $result);
                goto end;
            } else {
                // other database error
    $error->add('amm->get_job_attempt', $this->db->sql->error);
                $error->add('amm->get_job_attempt', $query);
                $errmsg = 'unable to query database to get job attempt number';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $num_rows);

        return $return;
    }

    public function job_status($uid)
    { // returns jobs current status
    global $error;

    // escape uid
    $esc = $this->db->esc($uid);
        unset($uid);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $uid = $esc[1];
        unset($esc);

    // grab jobdata from database
    $query = "SELECT jobstatus FROM amm WHERE uid='".$uid."'";
        if ($result = $this->db->sql->query($query)) {
            $num_rows = $result->num_rows;
            if ($num_rows == 0) {
                $return[0] = false;
                goto end;
            }
            $return[0] = true;
            while ($row = $result->fetch_assoc()) {
                $return[1] = $row['jobstatus'];
            }
            $result->close();
            unset($row, $result);
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
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows == 0) {
                    $return[0] = false;
                    goto end;
                }
                $return[0] = true;
                while ($row = $result->fetch_assoc()) {
                    $return[1] = $row['jobstatus'];
                }
                $result->close();
                unset($row, $result);
                goto end;
            } else {
                // other database error
    $error->add('amm->job_status', $this->db->sql->error);
                $error->add('amm->job_status', $query);
                $errmsg = 'unable to query database to get jobstatus';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }

        end:
    unset($query, $num_rows);

        return $return;
    }

    public function update_jobstatus($uid, $jobstatus)
    {
        global $error;

    // escape uid
    $esc = $this->db->esc($uid);
        unset($uid);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $uid = $esc[1];
        unset($esc);

    // escape jobstatus
    $esc = $this->db->esc($jobstatus);
        unset($jobstatus);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $jobstatus = $esc[1];
        unset($esc);

    // check for valid jobstatus
    $jobstatus_array = array('new','active','closed','failed','tryagain');
        if (!in_array($jobstatus, $jobstatus_array)) {
            $return[0] = false;
            $errmsg = "invalid job status '".$jobstatus."'";
            $return[1] = $errmsg;
            unset($errmsg);
            goto end;
        }

        $attempt = '';
        if ($jobstatus == 'tryagain') {
            // check current attempt
      $attemptnumber = $this->get_job_attempt($uid);
            if (!$attemptnumber[0]) {
                $return[0] = false;
                $return[1] = $attemptnumber[1];
                unset($attemptnumber);
                goto end;
            }
            $attemptnumber = $attemptnumber[1] + 1;

            $attempt = ",attempt='".$attemptnumber."'";

      // TODO - remove hardcoded maximum attempts
      // mark as failed when attemptnumber hits max
      if ($attemptnumber >= 4) {
          $jobstatus = 'failed';
      }

            unset($attemptnumber);
        }

    // Update job status in database
    $query = 'UPDATE amm SET jobstatus="'.$jobstatus.'",lastupdate=NOW()'.$attempt.' WHERE uid="'.$uid.'"';
        if ($this->db->sql->query($query) === true) {
            $return[0] = true;
            $return[1] = $uid;
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
          $return[1] = $uid;
          goto end;
      } else {
          // other database error
    $error->add('amm->update_jobstatus', $this->db->sql->error);
          $error->add('amm->update_jobstatus', $query);
          $errmsg = 'unable to update job status';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
        }

        end:
    unset($uid, $jobstatus, $query, $jobstatus_array);

        return $return;
    }

    public function status()
    {
        return false;
    }

    private function job_type_check($jobtype)
    { // checks for a valid jobtype and escapes the return value
    global $error;

    // escape jobtype
    $esc = $this->db->esc(stripslashes($jobtype));
        unset($jobtype);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }

        $jobtype = $esc[1];
        unset($esc);

    // TODO - remove hardcoded path
    if (is_readable('/var/wwwdata/albatross/100001/albatross/daemon/jobpacks/'.$jobtype.'.jobpack.php')) {
        $return[0] = true;
        $return[1] = $jobtype;
    } else {
        $return[0] = false;
        $errmsg = 'Invalid job type';
        $error->add('amm->job_type_check', 'unable to access jobpack: '.$jobtype.'.jobpack.php');
        $return[1] = $errmsg;
    }

        end:
    unset($jobtype, $jobarray);

        return $return;
    }
}
?>
