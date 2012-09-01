<?php
/* 
* Albatross Manager
* 
* Account Database SQL Interface class
* 
* Description:
*  Contains all functions for the accounts database and interface with the system users
*
* Copyright 2011 Cyprix Enterprises
*/
?>
<?php
// Include Dependencies
include_once("config.class.php");
include_once("mysqli.class.php");
include_once("error.class.php");
?>
<?php
class account{ // TODO: migrate code to new system to avoid race condition on checking for db access

  function __construct() {
    // start db connection
    $this->db = new db();
    $this->db->database = "default";
    $this->db->connect();
  }

  function __destruct() {
    // Do nothing.
    unset($this->db);
  }

  public function add($username,$password){ // Creates a new account - NOTE: Only run as daemon jobpack
    global $error; global $conf;
    $return = "";
    // This function requires access to usermod and useradd at the shell level via sudo
    // Check for root access to useradd and usermod
    system("/usr/sbin/useradd",$retval[0]);
    system("/usr/sbin/usermod",$retval[1]);
    // TODO: Check root access to chmod and chown
    // TODO: Fix missing return check for statements below
    if($retval[0] == 2){
      if($retval[1] != 2){
	$errmsg = "no permission to modify system account";
	$error->add("account->add",$errmsg);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
    }else{
      $errmsg = "no permission to add system account";
      $error->add("account->add",$errmsg);
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }
    unset($retval);
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }
    // Sanitize and check values
      // MySQL columns for table accounts
      /*+---------------+------------------+------+-----+---------+-------+
      | Field         | Type             | Null | Key | Default | Extra |
      +---------------+------------------+------+-----+---------+-------+
      | acc_id        | int(10) unsigned | NO   | PRI | NULL    |       |
      | uname         | varchar(30)      | NO   | UNI | NULL    |       |
      | home_dir      | mediumtext       | NO   |     | NULL    |       |
      | passwd_crypt  | varchar(20)      | NO   |     | NULL    |       |
      | passwd_sha1   | varchar(40)      | NO   |     | NULL    |       |
      | passwd_md5    | varchar(20)      | NO   |     | NULL    |       |
      | passwd_shadow | varchar(50)      | NO   |     | NULL    |       |
      | system_uid    | int(10) unsigned | NO   |     | NULL    |       |
      | system_gid    | int(10) unsigned | NO   |     | NULL    |       |
      | system_uname  | varchar(30)      | NO   | UNI | NULL    |       |
      | date_created  | datetime         | NO   |     | NULL    |       |
      | last_modified | datetime         | NO   |     | NULL    |       |
      +---------------+------------------+------+-----+---------+-------+*/
    // Check for a valid username
    if(!is_array($return)){
      $uname = $this->check_username($username);
      if(!$uname[0]){
	$errmsg = $uname[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$uname);
      }else{
	unset($username);
      }
    }
    // Check for a valid password
    if(!is_array($return)){
      $passwd = $this->check_password($password);
      if(!$passwd[0]){
	$errmsg = $passwd[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$passwd);
      }else{
	unset($password);
      }
    }
    // Select an acc_id - manual increment
    if(!is_array($return)){
      if($result = $this->db->sql->query("SELECT max(acc_id) as acc_id FROM accounts")){
	$num_rows = $result->num_rows;
	if($num_rows==1){
	  while($row = $result->fetch_assoc()){
	    $acc_id = $row['acc_id'] + 1;
	  }
	  $result->close();
	  unset($row,$result);
	}else{
	  // TODO: In configuration, set default account id
	  $errmsg = "cannot find maximum account id";
	  $error->add("account->add",$errmsg);
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}
	unset($num_rows);
      }else{
	$error->add("account->add",$mysqli->error);
	$errmsg = "unable to find current the maximum account id due to database error";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($result);
    }
    // encrypt password for useradd
    if(!is_array($return)){
      $passwd_crypt = $this->encrypt_password_for_shadow($passwd[1]);
      if($passwd_crypt[0]){
	$passwd[2] = $passwd_crypt[1];
      }else{
	$errmsg = $passwd_crypt[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($passwd_crypt);
    }
    // TODO: Add rollback of each part of account creation if one part fails.
    // Create account in database
    $base_home_dir = $conf->base_home_dir;
    // If mysql insert error, check for same acc_id, then try again with increment + 1
    $query = "INSERT INTO accounts VALUES('".$acc_id."','".$uname[1]."','".$base_home_dir."/".$acc_id."',ENCRYPT('".$passwd[1]."'),SHA1('".$passwd[1]."'),MD5('".$passwd[1]."'),'".$passwd[2]."','".$acc_id."','".$acc_id."','z".$acc_id."','".gmdate("Y-m-d H:i:s")."','".gmdate("Y-m-d H:i:s")."',1)";
    if(!is_array($return)){
      if($this->db->sql->query($query)===TRUE){
	// Successfully added account to database
      }else{
	$errmsg = "unable to add account to sql database";
	$error->add("account->add",$mysqli->error);
	$error->add("account->add","error with sql query '".$query."'");
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
    }
    unset($query);
    // Create home directory with permission 0751
    if(!is_array($return)){
      if(!mkdir($base_home_dir."/".$acc_id, 0751)){
	$errmsg = "unable to create home directory '".$base_home_dir."/".$acc_id."'";
	$error->add("account->add",$errmsg);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
    }
    // Create System user and group
    if(!is_array($return)){
      //sudo /usr/sbin/useradd -s /bin/bash -d /var/wwwdata/albatross/100001 -M -u 100001 -p password z100001
      system("/usr/sbin/useradd -s /bin/bash  -d ".$base_home_dir."/".$acc_id." -M -u ".$acc_id." -p ".$passwd[2]." z".$acc_id,$retval);
      if($retval != 0){
	$errmsg = "unable to create system user '".$acc_id."'";
	$error->add("account->add",$errmsg);
	$return[0] = false;
	$return[1] = $errmsg.$retval."/usr/sbin/useradd -s /bin/bash -d ".$base_home_dir."/".$acc_id." -M -u ".$acc_id." -p ".$passwd[1]." ".$acc_id;
	unset($errmsg);
      }
      unset($retval);
    }
    // Add user group to nginx permissions
    if(!is_array($return)){
      system("/usr/sbin/usermod -a -G ".$acc_id." nginx",$retval);
      if($retval != 0){
	$errmsg = "unable to add system user group '".$acc_id."' to nginx";
	$error->add("account->add",$errmsg);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($retval);
    }
    // Weirdness Check
    if(!is_array($return)){
      $return[0] = true;
      $return[1] = $acc_id;
    }
    return $return;
  }

  public function change_password($acc_id,$password){ // Changes the password - NOTE: must be run as daemon jobpack
    global $error; $return = "";
    // This function requires access to usermod at the shell level via root
    // Check for root access to usermod
    system("/usr/sbin/usermod",$retval[0]);
    // TODO: Check root access to chmod and chown
    if($retval[0] != 2){
      $errmsg = "no permission to modify system account";
      $error->add("account->change_password",$errmsg);
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }
    unset($retval);
    // Check for database access
    if(!is_array($return)){
      if(!$this->db->connect()){
	$errmsg = "unable to connect to database";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
    }
    // Check acc_id exists
    if(!is_array($return)){
      $id = $this->check_acc_id($acc_id);
      if($id[0]){
	$acc_id = $id[1];
      }else{
	$errmsg = $id[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$id);
      }
    }
    // Check and Sanitize password
    if(!is_array($return)){
      $passwd = $this->check_password($password);
      unset($password);
      if(!$passwd[0]){
	$errmsg = $passwd[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$passwd);
      }
      $passwd = $passwd[1];
    }
    // Encrypt password for usermod
    if(!is_array($return)){
      $passwd_crypt = $this->encrypt_password_for_shadow($passwd);
      if($passwd_crypt[0]){
	$passwd_shadow = $passwd_crypt[1];
      }else{
	$errmsg = $passwd_crypt[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($passwd_crypt);
    }
    // At this stage we should have three validated and sanitized variables: acc_id, passwd and passwd_shadow
    // Update passwords in database
    if(!is_array($return)){
      $query = "UPDATE accounts SET passwd_crypt=ENCRYPT('".$passwd."'),passwd_sha1=SHA1('".$passwd."'),passwd_md5=MD5('".$passwd."'),passwd_shadow='".$passwd_shadow."' WHERE acc_id='".$acc_id."'";
      if($this->db->sql->query($query)===TRUE){
	// Successfully updated password in accounts database
      }else{
	$errmsg = "unable to update password in sql accounts database";
	$error->add("account->change_password",$mysqli->error);
	$error->add("account->change_password","error with sql query '".$query."'");
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($query);
    }
    // Update system password
    if(!is_array($return)){
      system("/usr/sbin/usermod -p ".$passwd_shadow." z".$acc_id,$retval);
      if($retval != 0){
	$errmsg = "unable to change system password for user 'z".$acc_id."'";
	$error->add("account->change_password",$errmsg);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$msg = "All passwords updated successfully";
	$return[0] = true;
	$return[1] = $msg;
	unset($msg);
      }
      unset($retval);
    }
    unset($acc_id,$passwd,$passwd_shadow);
    return $return;
  }

  private function check_password($password){
    global $error; $return = "";
    $value = trim($password);
    unset($password);
    if($value==""){
      $errmsg = "no password provided";
      $error->add("account->check_password",$errmsg);
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }elseif(is_string($value)){
      $valid_chars = array('~','!','@','#','$','%','^','&','*','(',')','_','-','+','|','[',']','{','}','`','?','<','>');
      if(!ctype_alnum(str_replace($valid_chars, '', $value))) {
	$errmsg = "the password conatins invalid characters";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$return[0] = true;
	$return[1] = $value;
      }
      unset($valid_chars,$value);
    }else{
      $errmsg = "the password is not a string";
      $error->add("account->check_password",$errmsg);
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }
    return $return;
  }

  private function check_username($username){
    if(!$this->db->connect()){
      $errmsg = "unable to connect to database to check for duplicate username";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }else{
      $value = strtolower(trim($username));
      $esc = $this->db->esc($value);
      unset($username);
      $valid_chars = array('_','-');
      if(strlen($value)<3){
	$errmsg = "usernames must be at least 3 characters in length";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }elseif(!ctype_alnum(str_replace($valid_chars, '', $value))) {
	$errmsg = "usernames may only contain alpha-numeric characters, underscore and dash";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }elseif(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }elseif($result = $this->db->sql->query("SELECT uname FROM accounts WHERE uname='".$esc[1]."'")){
	$num_rows = $result->num_rows;
	if($num_rows>0){
	  $errmsg = "username already exists";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return[0] = true;
	  $return[1] = $esc[1];
	}
	unset($num_rows);
      }else{
	$error->add("account->check_username",$mysqli->error);
	$errmsg = "unable to query database to check for duplicate username";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($valid_chars,$value,$esc);
    }
    return $return;
  }

  private function check_acc_id($account_id){ // Checks for a valid acc_id in the database
    global $error;
    $return = "";
    if(!$this->db->connect()){
      $errmsg = "unable to connect to database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }else{
      $id = $this->db->esc($account_id);
      unset($account_id);
      if(!$id[0]){
	$errmsg = $id[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$acc_id = $id[1];
      }
      unset($id);
      if(!is_array($return)){
	$query = "SELECT acc_id FROM accounts WHERE acc_id='".$acc_id."'";
	if($result = $this->db->sql->query($query)){
	  $num_rows = $result->num_rows;
	  if($num_rows==0){
	    $errmsg = "acc_id '".$acc_id."' does not exist in the database";
	    $return[0] = false;
	    $return[1] = $errmsg;
	    unset($errmsg);
	  }else{
	    $return[0] = true;
	    $return[1] = $acc_id;
	  }
	  unset($num_rows);
	}else{
	  $error->add("account->check_acc_id",$mysqli->error);
	  $errmsg = "unable to query database to check for valid acc_id";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}
      }
    }
    return $return;
  }

  private function encrypt_password_for_shadow($password){
    global $error; $return = "";
    if($password==""){
      $errmsg = "no password provided";
      $error->add("account->encrypt_password_for_shadow",$errmsg);
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }elseif(is_string($password)){
      if(CRYPT_MD5 == 1){
	$passwd = str_replace("$","\\$",crypt($password));
	$return[0] = true;
	$return[1] = $passwd;
      }else{
	$errmsg = "unable to encrypt password for system account as crypt md5 is not working";
	$error->add("account->encrypt_password_for_shadow",$errmsg);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
    }else{
      $errmsg = "the password is not a string";
      $error->add("account->encrypt_password_for_shadow",$errmsg);
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }
    unset($password,$passwd);
    return $return;
  }
}
?>
