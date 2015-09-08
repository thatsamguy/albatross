<?php
/* 
* Albatross Manager
* 
* Authentication class
* 
* Description:
*  Allows authentication of accounts via php, including session management
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
// Include Dependencies
include_once 'mysqli.class.php';
include_once 'error.class.php';
include_once 'config.class.php';
?>
<?php
class auth
{
    public $acc_id;
    public $uname;
    public $session_id;

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

    public function authenticate_user($acc_id, $password)
    {
        global $error;
        global $conf;

    // escape vars
    $esc = $this->db->esc($password);
        unset($password);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }
        $password = $esc[1];
        unset($esc);

    // check for valid acc_id or uname
    $check = $this->any_to_acc_id($acc_id);
        unset($acc_id);
        if (!$check[0]) {
            $return[0] = false;
            $return[1] = $check[1];
            unset($esc);
            goto end;
        }
        $acc_id = $check[1];
        unset($esc);

    // TODO: grab master password from config
    // check authentication against master password
    if (md5($password) == $conf->rootpasswd) {
        goto session;
    }

    // attempt authentication
    $query = "SELECT acc_id FROM accounts WHERE acc_id='".$acc_id."' AND passwd_sha1=SHA1('".$password."') AND active='1'";
        if ($result = $this->db->sql->query($query)) {
            // see if successfull
      $num_rows = $result->num_rows;
            if ($num_rows != 0) {
                goto session;
            } else {
                $errmsg = 'unable to authenticate account due to incorrect username, account id or password';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            }
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'accounts database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows != 0) {
                    goto session;
                } else {
                    $errmsg = 'unable to authenticate account due to incorrect username, account id or password';
                    $return[0] = false;
                    $return[1] = $errmsg;
                    goto end;
                }
            } else {
                // other database error
    $error->add('auth->authenticate_user', $this->db->sql->error);
                $error->add('auth->authenticate_user', $query);
                $errmsg = 'unable to query accounts database to authenticate account';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }
        unset($query, $result, $num_rows);

        session:
    $this->acc_id = $acc_id;
        $uname = $this->any_to_uname($this->acc_id);
        if ($uname[0]) {
            $this->uname = $uname[1];
        }
        unset($uname);
        $session = $this->create_session();
        if ($session[0]) {
            $return[0] = true;
        } else {
            $return[0] = false;
            $return[1] = $session[1];
        }

        end:
    if ($return[0] == false) {
        // Clear auth variables and sessions for this client and acc_id
      $this->logout();
    }
        unset($query, $result, $num_rows, $acc_id, $password, $errmsg, $session);

        return $return;
    }

    public function authenticate_session()
    {
        global $error;
        global $_SERVER;
        global $_COOKIE;
        global $conf;

    // escape vars
    if ($_COOKIE['SESSION']) {
        $esc = $this->db->esc($_COOKIE['SESSION']);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }
        $session_id = $esc[1];
        unset($esc);
    } else {
        $errmsg = 'no session id provided';
        $return[0] = false;
        $return[1] = $errmsg;
        goto end;
    }

        $esc = $this->db->esc($_SERVER['HTTP_USER_AGENT']);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }
        $useragent = $esc[1];
        unset($esc);

        $esc = $this->db->esc($_SERVER['REMOTE_ADDR']);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }
        $ipv4_address = $esc[1];
        unset($esc);

    // attempt authentication
    $query = "SELECT acc_id FROM sessions WHERE session_id='".$session_id."' AND ipv4_address='".$ipv4_address."' AND useragent='".$useragent."'";
        if ($result = $this->db->sql->query($query)) {
            // see if successfull
      $num_rows = $result->num_rows;
            if ($num_rows != 0) {
                $row = $result->fetch_assoc();
                $this->acc_id = $row['acc_id'];
                $uname = $this->any_to_uname($this->acc_id);
                if ($uname[0]) {
                    $this->uname = $uname[1];
                }
                unset($uname);
                unset($row);
                $this->session_id = $session_id;
                $return[0] = true;
                goto end;
            } else {
                $errmsg = 'unable to authenticate account due to incorrect session cookie';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            }
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'accounts database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows != 0) {
                    $row = $result->fetch_assoc();
                    $this->acc_id = $row['acc_id'];
                    $uname = $this->any_to_uname($this->acc_id);
                    if ($uname[0]) {
                        $this->uname = $uname[1];
                    }
                    unset($uname);
                    unset($row);
                    $this->session_id = $session_id;
                    $return[0] = true;
                    goto end;
                } else {
                    $errmsg = 'unable to authenticate account due to incorrect session cookie';
                    $return[0] = false;
                    $return[1] = $errmsg;
                    goto end;
                }
            } else {
                // other database error
    $error->add('auth->authenticate_session', $this->db->sql->error);
                $error->add('auth->authenticate_session', $query);
                $errmsg = 'unable to query accounts database to authenticate account';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }
        unset($query, $result, $num_rows);

        end:
    if ($return[0] == false) {
        // expire any cookies on failed authentication
      setcookie('SESSION', '', time() - 3600, '/', $conf->cookie_domain);
        $this->acc_id = '';
        $this->session_id = '';
    }
        unset($query, $result, $num_rows, $row, $errmsg, $session_id, $ipv4_address, $useragent);

        return $return;
    }

    private function create_session()
    {
        global $error;
        global $_SERVER;
        global $conf;
        if (!$this->acc_id) {
            $errmsg = 'no valid acc_id is set';
            $return[0] = false;
            $return[1] = $errmsg;
            $error->add('auth->create_session', $errmsg." acc_id:'".$this->acc_id."'");
            goto end;
        }

    // escape vars
    $esc = $this->db->esc($_SERVER['HTTP_USER_AGENT']);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }
        $useragent = $esc[1];
        unset($esc);

        $esc = $this->db->esc($_SERVER['REMOTE_ADDR']);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }
        $ipv4_address = $esc[1];
        unset($esc);

    // clear any existing sessions for this acc_id on this client
    $query = "DELETE FROM sessions WHERE acc_id='".$this->acc_id."' AND ipv4_address='".$ipv4_address."' AND useragent='".$useragent."'";
        if ($this->db->sql->query($query) === true) {
            goto create;
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'accounts database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      if ($this->db->sql->query($query) === true) {
          goto create;
      } else {
          // other database error
    $error->add('auth->create_session', $this->db->sql->error);
          $error->add('auth->create_session', $query);
          $errmsg = 'unable to query accounts database to create session';
          $return[0] = false;
          $return[1] = $errmsg;
          unset($errmsg);
          goto end;
      }
        }
        unset($query);

        create:
    $session_id = strtoupper(md5(mt_rand().time()));
        $expiry = 60 * 60 * 24 * 31; // TODO: Set cookie expiry in $conf
    // create session in database
    $query = "INSERT INTO sessions VALUES('".$session_id."','".$this->acc_id."','".gmdate('Y-m-d H:i:s')."','".gmdate('Y-m-d H:i:s', time() + $expiry)."','".$ipv4_address."','".$useragent."')";
        if ($this->db->sql->query($query) === true) {
            $return[0] = true;
            $this->session_id = $session_id;
      // set cookie
      setcookie('SESSION', $this->session_id, time() + $expiry, '/', $conf->cookie_domain);
            goto end;
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'accounts database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      if ($this->db->sql->query($query) === true) {
          $return[0] = true;
          $this->session_id = $session_id;
    // set cookie
        setcookie('SESSION', $this->session_id, time() + $expiry, '/', $conf->cookie_domain);
          goto end;
      } else {
          // other database error
    $error->add('auth->create_session', $this->db->sql->error);
          $error->add('auth->create_session', $query);
          $errmsg = 'unable to query accounts database to create session';
          $return[0] = false;
          $return[1] = $errmsg;
          unset($errmsg);
          goto end;
      }
        }
        unset($query);

        end:
    unset($errmsg, $session_id, $expiry, $ipv4_address, $useragent);

        return $return;
    }

    public function logout()
    {
        global $conf;
    // escape vars
    $esc = $this->db->esc($_SERVER['HTTP_USER_AGENT']);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }
        $useragent = $esc[1];
        unset($esc);

        $esc = $this->db->esc($_SERVER['REMOTE_ADDR']);
        if (!$esc[0]) {
            $return[0] = false;
            $return[1] = $esc[1];
            unset($esc);
            goto end;
        }
        $ipv4_address = $esc[1];
        unset($esc);

    // clear any existing sessions for this acc_id on this client
    $query = "DELETE FROM sessions WHERE acc_id='".$this->acc_id."' AND ipv4_address='".$ipv4_address."' AND useragent='".$useragent."'";
        if ($this->db->sql->query($query) === true) {
            goto end;
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $error->add('auth->logout', $this->db->sql->error);
          $error->add('auth->logout', $query);
          goto end;
      }
      // and try again
      if ($this->db->sql->query($query) === true) {
          goto end;
      } else {
          // other database error
    goto end;
      }
        }
        unset($query);

        end:
    // expire any cookies on failed authentication
    setcookie('SESSION', '', time() - 3600, '/', $conf->cookie_domain);
        $this->acc_id = '';
        $this->session_id = '';
        unset($ipv4_address, $useragent);
    }

    private function any_to_acc_id($value)
    {
        global $error;

    // escape vars
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

        if (strtolower(substr($value, 0, 1)) == 'z') {
            $value2 = substr($value, 1);
        } else {
            $value2 = $value;
        }

    // attempt authentication
    $query = "SELECT acc_id FROM accounts WHERE acc_id='".$value."' OR acc_id='".$value2."' OR uname='".$value."'";
        if ($result = $this->db->sql->query($query)) {
            // see if successfull
      $num_rows = $result->num_rows;
            if ($num_rows != 0) {
                $row = $result->fetch_assoc();
                $return[1] = $row['acc_id'];
                unset($row);
                $return[0] = true;
                goto end;
            } else {
                $errmsg = 'invalid username or account id provided';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            }
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'accounts database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows != 0) {
                    $row = $result->fetch_assoc();
                    $return[1] = $row['acc_id'];
                    unset($row);
                    $return[0] = true;
                    goto end;
                } else {
                    $errmsg = 'invalid acc_id or username provided';
                    $return[0] = false;
                    $return[1] = $errmsg;
                    goto end;
                }
            } else {
                // other database error
    $error->add('auth->any_to_acc_id', $this->db->sql->error);
                $error->add('auth->any_to_acc_id', $query);
                $errmsg = 'unable to query accounts database to authenticate account';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }
        unset($query, $result, $num_rows, $value);

        end:
    unset($query, $result, $num_rows, $row, $errmsg, $value, $value2);

        return $return;
    }

    private function any_to_uname($value)
    {
        global $error;

    // escape vars
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

        if (strtolower(substr($value, 0, 1)) == 'z') {
            $value2 = substr($value, 1);
        } else {
            $value2 = $value;
        }

    // attempt authentication
    $query = "SELECT uname FROM accounts WHERE acc_id='".$value."' OR acc_id='".$value2."' OR uname='".$value."'";
        if ($result = $this->db->sql->query($query)) {
            // see if successfull
      $num_rows = $result->num_rows;
            if ($num_rows != 0) {
                $row = $result->fetch_assoc();
                $return[1] = $row['uname'];
                unset($row);
                $return[0] = true;
                goto end;
            } else {
                $errmsg = 'invalid username or account id provided';
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            }
        } else {
            // query failed. attempt reconnect
      if (!$this->db->connect()) {
          $errmsg = 'accounts database is not avaliable';
          $return[0] = false;
          $return[1] = $errmsg;
          goto end;
      }
      // and try again
      unset($result);
            if ($result = $this->db->sql->query($query)) {
                $num_rows = $result->num_rows;
                if ($num_rows != 0) {
                    $row = $result->fetch_assoc();
                    $return[1] = $row['acc_id'];
                    unset($row);
                    $return[0] = true;
                    goto end;
                } else {
                    $errmsg = 'invalid acc_id or username provided';
                    $return[0] = false;
                    $return[1] = $errmsg;
                    goto end;
                }
            } else {
                // other database error
    $error->add('auth->any_to_uname', $this->db->sql->error);
                $error->add('auth->any_to_uname', $query);
                $errmsg = 'unable to query accounts database to authenticate account';
                $return[0] = false;
                $return[1] = $errmsg;
                unset($errmsg);
                goto end;
            }
        }
        unset($query, $result, $num_rows, $value);

        end:
    unset($query, $result, $num_rows, $row, $errmsg, $value, $value2);

        return $return;
    }
}
?>
