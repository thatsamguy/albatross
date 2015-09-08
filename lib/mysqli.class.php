<?php
/*
* Albatross Manager
*
* MySQLi Connection Class
*
* Description:
*  Using the configuration from mysql.config.php, allows connection and disconnection from the MySQL Database.
*  It also initiates the escape string command.
*
* Copyright 2011 Samuel Bailey
*/

// Include Dependencies
include_once 'error.class.php';

class db
{
    public $sql;
    public $database = 'default';

    // Alias of dbconnect()
    public function __construct()
    {
        // start db connection
        $this->connect();
    }

    // Alias of dbclose()
    public function __destruct()
    {
        // remove db connection
        $this->close();
    }

    // creates database connection
    public function connect()
    {
        global $error;

        // Includes config details as array $sql_config
        include 'mysql.config.php';

        if (method_exists($this->sql, 'protocol_version')) {
            if (($this->sql->protocol_version) > 0) {
                return true;
            }
        }

        if (in_array($this->database, array_keys($sql_config))) {
            if (array_key_exists('port', $sql_config[$this->database])) {
                $this->sql = new mysqli($sql_config[$this->database]['host'], $sql_config[$this->database]['username'], $sql_config[$this->database]['password'], $sql_config[$this->database]['database'], $sql_config[$this->database]['port']);
            } else {
                $this->sql = new mysqli($sql_config[$this->database]['host'], $sql_config[$this->database]['username'], $sql_config[$this->database]['password'], $sql_config[$this->database]['database']);
            }

            // check connection
            if (mysqli_connect_errno()) {
                $error->add('mysqli->connect', mysqli_connect_error());
                return false;
            }
        } else {
            $errmsg = "database '".$this->database."' does not exist in mysqli config";
            $error->add('mysqli->connect', $errmsg);
            unset($errmsg);
            return false;
        }
        return true;
    }

    // Closes database connection
    public function close()
    {
        unset($this->sql);
    }

    //Escapes a string for use in mysql query
    public function esc($string)
    {
        global $error;

        if (is_string($string) and strlen($string) == 0) {
            $errmsg = 'unable to escape empty string';
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }

        // Fix case where $string is a numeric string of "0"
        if ($string == '0' and is_numeric($string)) {
            $return[0] = true;
            $return[1] = 0;
            goto end;
        }

        if (is_array($string)) {
            $errmsg = 'unable to escape array';
            $error->add('mysqli->esc', $errmsg.': '.json_encode($string));
            $return[0] = false;
            $return[1] = $errmsg;
            goto end;
        }

        if ($result = $this->sql->real_escape_string(trim($string))) {
            $return[0] = true;
            $return[1] = $result;
            goto end;
        } else {
            // query failed. attempt reconnect
            if (!$this->connect()) {
                $errmsg = 'unable to connect to database to escape string';
                $error->add('mysqli->esc', $errmsg);
                $return[0] = false;
                $return[1] = $errmsg;
                goto end;
            }
            // and try again
            if ($result = $this->sql->real_escape_string(trim($string))) {
                $return[0] = true;
                $return[1] = $result;
                goto end;
            } else {
                // other database error
                $error->add('mysqli->esc', "sql: '".$string."' ".$this->db->sql->error);
                $errmsg = 'unable to escape string';
                $error->add('mysqli->esc', $errmsg);
                $return[0] = false;
                $return[1] = $errmsg;
            }
        }

        end:

        unset($string, $result, $errmsg);
        return $return;
    }
}
