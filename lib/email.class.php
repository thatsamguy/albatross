 <?php
/* 
* Albatross Manager
* 
* Email Database SQL Interface class
* 
* Description:
*  Contains all functions for the email database
*
* Copyright 2011 Cyprix Enterprises
*/
?>
<?php
// Include Dependencies
include_once("config.class.php");
include_once("mysqli.class.php");
include_once("error.class.php");
include_once("amm.class.php");
include_once("dns.class.php");
?>
<?php
class email{
  private $db;

  function __construct(){
    // start db connection
    $this->db = new db();
    $this->db->database = "email";
    $this->db->connect();
    $this->db2 = new db();
    $this->db2->database = "default";
    $this->db2->connect();
    $this->amm = new amm();
  }

  function __destruct() {
    // Do nothing.
    unset($this->db);
  }

  public function add_domain($domain){ // adds domains to database as inactive
    global $error;
    // escape domain
    $esc = $this->db->esc($domain);
    unset($domain);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $domain = $esc[1];
    unset($esc);
    
    // check if domain exists
    $query = "SELECT domain from domain WHERE domain=\"".$domain."\"";
    if($result = $this->db->sql->query($query)){
      // see if domain exists....
      $num_rows = $result->num_rows;
      if($num_rows!=0){
	// domain exists already
	$errmsg = "domain already exists in email database";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows!=0){
	  // domain exists already
	  $errmsg = "domain already exists in email database";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
      }else{
	// other database error
	$error->add("email->add_domain",$this->db->sql->error);
	$error->add("email->add_domain",$query);
	$errmsg = "unable to query email database to check for domains";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }
    unset($query,$result,$num_rows);
    
    // add domain to database, leave inactive
    $query = "INSERT INTO domain VALUES(\"".$domain."\",0)";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $domain;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $domain;
	goto end;
      }else{
	// other database error
	$error->add("email->add_domain",$this->db->sql->error);
	$error->add("email->add_domain",$query);
	$errmsg = "unable to add domain to email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$result,$num_rows,$domain,$errmsg);
    return $return;
  }

  public function remove_domain($domain){
    global $error;
    // escape domain
    $esc = $this->db->esc($domain);
    unset($domain);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $domain = $esc[1];
    unset($esc);
    
    // remove domain from database
    $query = "DELETE FROM domain WHERE domain=\"".$domain."\"";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $domain;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $domain;
	goto end;
      }else{
	// other database error
	$error->add("email->remove_domain",$this->db->sql->error);
	$error->add("email->remove_domain",$query);
	$errmsg = "unable to remove domain from email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$domain,$errmsg);
    return $return;
  }

  public function activate_domain_master($domain){
    $return = $this->update_domain($domain,"1");
    unset($domain);
    return $return;
  }

  public function activate_domain_backup($domain){
    $return = $this->update_domain($domain,"2");
    unset($domain);
    return $return;
  }

  public function deactivate_domain($domain){
    $return = $this->update_domain($domain,"0");
    unset($domain);
    return $return;
  }

  private function update_domain($domain,$active){
    global $error;
    // escape domain
    $esc = $this->db->esc($domain);
    unset($domain);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $domain = $esc[1];
    unset($esc);
    
    // update domain record in database
    $query = "UPDATE domain SET active='".$active."' WHERE domain=\"".$domain."\"";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $domain;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $domain;
	goto end;
      }else{
	// other database error
	$error->add("email->update_domain",$this->db->sql->error);
	$error->add("email->update_domain",$query);
	$errmsg = "unable to update email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$domain,$errmsg);
    return $return;
  }

  public function get_domain_status($domain){ // Returns domain status
    global $error;
    // escape domain
    $esc = $this->db->esc($domain);
    unset($domain);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $domain = $esc[1];
    unset($esc);
    
    $query = "SELECT active from domain WHERE domain=\"".$domain."\"";
    if($result = $this->db->sql->query($query)){
      $num_rows = $result->num_rows;
      if($num_rows==0){
	// No status exists (or domain) - Add domain with status 0 (inactive), then return inactive
	$this->add_domain($domain); // don't worry about errors - it'll fix itself later, hopefully.
	$return[0] = true;
	$return[1] = "inactive";
	goto end;
      }else{
	$row = $result->fetch_assoc();
	if($row['active'] == "1"){
	  $return[1] = "active";
	}elseif($row['active'] == "2"){
	  $return[1] = "backup";
	}elseif($row['active'] == "0"){
	  $return[1] = "inactive";
	}else{
	  $return[1] = "unknown";
	}
	$return[0] = true;
	unset($row);
	goto end;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  // No status exists (or domain) - Add domain with status 0 (inactive), then return inactive
	  $this->add_domain($domain); // don't worry about errors - it'll fix itself later, hopefully.
	  $return[0] = true;
	  $return[1] = "inactive";
	  goto end;
	}else{
	  $row = $result->fetch_assoc();
	  if($row['active'] == "1"){
	    $return[1] = "active";
	  }elseif($row['active'] == "2"){
	    $return[1] = "backup";
	  }elseif($row['active'] == "0"){
	    $return[1] = "inactive";
	  }else{
	    $return[1] = "unknown";
	  }
	  $return[0] = true;
	  unset($row);
	  goto end;
	}
      }else{
	// other database error
	$error->add("email->get_domain_status",$this->db->sql->error);
	$error->add("email->get_domain_status",$query);
	$errmsg = "unable to query email database to check domain status";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }

    end:
    unset($query,$domain,$errmsg,$num_rows,$result);
    return $return;
  }

  public function add_email($email,$password,$name=""){
    global $error; global $conf;
    
    // TODO: Check for valid email address format
    
    // escape email
    $esc = $this->db->esc($email);
    unset($email);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $email = $esc[1];
    unset($esc);
    
    // escape password
    $esc = $this->db->esc($password);
    unset($password);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $password = $esc[1];
    unset($esc);

    if($name!=""){
      // escape name
      $esc = $this->db->esc($name);
      unset($name);
      if(!$esc[0]){
	$return[0] = false;
	$return[1] = $esc[1];
	unset($esc);
	goto end;
      }
      
      $name = $esc[1];
      unset($esc);
    }

    // work out domain and username from email address
    $array = explode("@",$email);
    $username = $array[0];
    $domain = $array[1];

    // grab default conf data
    $uid = $conf->email['uid'];
    $gid = $conf->email['gid'];
    $home = $conf->email['home'];
    $maildir = $conf->email['maildir'];
    $maildir = str_replace("_domain_",$domain,$maildir);
    $maildir = str_replace("_username_",$username,$maildir);
    if(substr($maildir, -1)!="/"){ $maildir .= "/";; }

    // check if email exists
    $query = "SELECT email from users WHERE email=\"".$email."\"";
    if($result = $this->db->sql->query($query)){
      // see if email exists....
      $num_rows = $result->num_rows;
      if($num_rows!=0){
	// domain exists already
	$errmsg = "email already exists in email database";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows!=0){
	  // email exists already
	  $errmsg = "email already exists in email database";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
      }else{
	// other database error
	$error->add("email->add_email",$this->db->sql->error);
	$error->add("email->add_email",$query);
	$errmsg = "unable to query email database to check for email";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }
    unset($query,$result,$num_rows);
    
    // add email to database, make active
    $query = "INSERT INTO users VALUES(\"".$email."\",\"\",ENCRYPT(\"".$password."\"),\"".$name."\",\"".$uid."\",\"".$gid."\",\"".$home."\",\"".$maildir."\",1,\"".$domain."\")";
    if($this->db->sql->query($query)===true){
      goto mkdir;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	goto mkdir;
      }else{
	// other database error
	$error->add("email->add_email",$this->db->sql->error);
	$error->add("email->add_email",$query);
	$errmsg = "unable to add email to email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($domain,$username,$email,$password,$uid,$gid,$home,$maildir,$name);
    unset($query,$result,$num_rows,$errmsg);
    return $return;

    mkdir:
    // TODO: fix for alternate maildir config
    system('sudo /bin/mkdir /var/vhosts/'.$domain.'/ -p',$retval);
    if($retval != 0){
      // fail, rollback changes
      $errmsg = "Unable to create domain maildir";
      $error->add("email->add_email",$errmsg." ".$maildir);
      $return[0] = false;
      $return[1] = $errmsg;
      $this->remove_email($email);
      goto end;
    }
    system('sudo /bin/chmod 700 /var/vhosts/'.$domain.'/ -fR',$retval);
    if($retval != 0){
      // fail, rollback changes
      $errmsg = "Unable to set domain maildir permissions";
      $error->add("email->add_email",$errmsg." ".$maildir);
      $return[0] = false;
      $return[1] = $errmsg;
      $this->remove_email($email);
      rmdir($maildir);
      goto end;
    }
    system('sudo /bin/chown vhosts:vhosts /var/vhosts/'.$domain.'/ -fR',$retval);
    if($retval != 0){
      // fail, rollback changes
      $errmsg = "Unable to set domain maildir owner";
      $error->add("email->add_email",$errmsg." ".$maildir);
      $return[0] = false;
      $return[1] = $errmsg;
      $this->remove_email($email);
      rmdir($maildir);
      goto end;
    }
    // TOOD: Send welcome email
    $eol = "\n"; 
    $headers = 'From: Cyprix Webmaster<webmaster@cyprix.com.au>'.$eol; 
    $headers .= 'Return-Path: Cyprix Webmaster<webmaster@cyprix.com.au>'.$eol;
    $headers .= 'Message-ID: <'.date('Ymdhis').'.'.substr(strtoupper(md5(mt_rand())),0,10).'@cyprix.com.au>'.$eol;
    $headers .= 'X-Mailer: PHP v'.phpversion().$eol; 
    $headers .= 'MIME-Version: 1.0'.$eol; 
    $headers .= 'Content-Type: text/plain; charset=ISO-8859-1; format=flowed'.$eol; 
    $headers .= 'Content-Transfer-Encoding: 7bit'.$eol; 
    $subject = "[Cyprix] Welcome to CyprixMail";
    $body = "Welcome to your Cyprix email account.

For your reference:

Username: $email
Password: $password

Cyprix Webmaster
  ";
    $msg = $body.$eol.$eol;
    $errmsg = "unable to send mail to ".$email;
    mail($email, $subject, $msg, $headers) or $error->add("email->add_email",$errmsg);
    unset($errmsg,$eol,$headers,$subject,$msg,$body);
    $return[0] = true;
    $return[1] = $email;
    goto end;
  }

  public function remove_email($email){
    global $error;
    // escape email
    $esc = $this->db->esc($email);
    unset($email);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $email = $esc[1];
    unset($esc);
    
    // remove email from database
    $query = "DELETE FROM users WHERE email=\"".$email."\"";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $email;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $email;
	goto end;
      }else{
	// other database error
	$error->add("email->remove_email",$this->db->sql->error);
	$error->add("email->remove_email",$query);
	$errmsg = "unable to remove email from email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }
    
    // TODO: move email maildir to archive, which will be deleted after a set period of time
    // TODO: remove settings from RoundCube

    end:
    unset($query,$email,$errmsg);
    return $return;
  }

  private function update_email($email,$field,$update){
    global $error;
    // escape email
    $esc = $this->db->esc($email);
    unset($email);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $email = $esc[1];
    unset($esc);

    // escape field
    $esc = $this->db->esc($field);
    unset($field);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $field = $esc[1];
    unset($esc);

    // escape update
    $esc = $this->db->esc($update);
    unset($update);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $update = $esc[1];
    unset($esc);

    
    // update email record in database
    $query = "UPDATE users SET ".$field."='".$update."' WHERE email=\"".$email."\"";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $email;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $email;
	goto end;
      }else{
	// other database error
	$error->add("email->update_email",$this->db->sql->error);
	$error->add("email->update_email",$query);
	$errmsg = "unable to update email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$email,$field,$update,$errmsg);
    return $return;
  }

  public function activate_email($email){
    $return = $this->update_email($email,"active","1");
    unset($email);
    return $return;
  }

  public function deactivate_email($email){
    $return = $this->update_email($email,"active","0");
    unset($email);
    return $return;
  }

  public function update_password($email,$password){
    global $error;
    // TODO: Check password meets requirements
    // escape email
    $esc = $this->db->esc($email);
    unset($email);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $email = $esc[1];
    unset($esc);

    // escape password
    $esc = $this->db->esc($password);
    unset($password);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $password = $esc[1];
    unset($esc);
    
    // update email record in database
    $query = "UPDATE users SET passwdCrypt=ENCRYPT('".$password."') WHERE email=\"".$email."\"";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $email;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $email;
	goto end;
      }else{
	// other database error
	$error->add("email->update_password",$this->db->sql->error);
	$error->add("email->update_password",$query);
	$errmsg = "unable to update email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$email,$password,$errmsg);
    return $return;
  }

  public function update_email_name($email,$name){ // updates account name for the email address
    return $this->update_email($email,"name",$name);
  }

  public function add_alias($alias,$destination){ // adds aliases to database as active
    global $error;
    // escape alias
    $esc = $this->db->esc($alias);
    unset($alias);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    $alias = $esc[1];
    unset($esc);

    // escape destination
    $esc = $this->db->esc($destination);
    unset($destination);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    $destination = $esc[1];
    unset($esc);

    // work out domain and username from alias
    $array = explode("@",$alias);
    $domain = $array[1];
    unset($array);
    
    // check if alias already exists
    $query = "SELECT email from alias WHERE email=\"".$alias."\"";
    if($result = $this->db->sql->query($query)){
      // see if alias exists....
      $num_rows = $result->num_rows;
      if($num_rows!=0){
	// alias exists already
	$errmsg = "alias already exists in the email database";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows!=0){
	  // alias exists already
	  $errmsg = "alias already exists in the email database";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
      }else{
	// other database error
	$error->add("email->add_alias",$this->db->sql->error);
	$error->add("email->add_alias",$query);
	$errmsg = "unable to query email database to check for aliases";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }
    unset($query,$result,$num_rows);
    
    // add alias to database, add as active
    $query = "INSERT INTO alias VALUES(\"".$alias."\",\"".$destination."\",1,\"".$domain."\")";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $alias;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $alias;
	goto end;
      }else{
	// other database error
	$error->add("email->add_alias",$this->db->sql->error);
	$error->add("email->add_alias",$query);
	$errmsg = "unable to add alias to email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$result,$num_rows,$alias,$destination,$errmsg,$domain);
    return $return;
  }

  public function remove_alias($alias){
    global $error;
    // escape alias
    $esc = $this->db->esc($alias);
    unset($alias);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $alias = $esc[1];
    unset($esc);
    
    // remove email from database
    $query = "DELETE FROM alias WHERE email=\"".$alias."\"";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $alias;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $alias;
	goto end;
      }else{
	// other database error
	$error->add("email->remove_alias",$this->db->sql->error);
	$error->add("email->remove_alias",$query);
	$errmsg = "unable to remove alias from email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$alias,$errmsg);
    return $return;
  }

  public function activate_alias($alias){
    $return = $this->update_alias($alias,"1");
    unset($alias);
    return $return;
  }

  public function deactivate_alias($alias){
    $return = $this->update_alias($alias,"0");
    unset($alias);
    return $return;
  }

  private function update_alias($alias,$active){
    global $error;
    // escape alias
    $esc = $this->db->esc($alias);
    unset($alias);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $alias = $esc[1];
    unset($esc);
    
    // update domain record in database
    $query = "UPDATE alias SET active='".$active."' WHERE email=\"".$alias."\"";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $alias;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $alias;
	goto end;
      }else{
	// other database error
	$error->add("email->update_alias",$this->db->sql->error);
	$error->add("email->update_alias",$query);
	$errmsg = "unable to update email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$alias,$errmsg);
    return $return;
  }

  public function get_domains($active="-1"){ // returns array of domains in database (all or matching active)
    global $error;
    if($active==0 OR $active==1 or $active==2){
      $active = " WHERE active='".$active."'";
    }else{
      $active = "";
    }

    // get domains from database
    $query = "SELECT * FROM domain".$active;
    if($result = $this->db->sql->query($query)){
      $return[0] = true;
      $i=0;
      while($row = $result->fetch_assoc()){
	$return[1][$i]['domain'] = $row['domain'];
	if($row['active'] == 1){
	  $return[1][$i]['active'] = "master";
	}elseif($row['active'] == 2){
	  $return[1][$i]['active'] = "backup";
	}else{
	  $return[1][$i]['active'] = "inactive";
	}
	$i++;
      }
      $result->close();
      unset($row,$result,$i);
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($result = $this->db->sql->query($query)){
	$return[0] = true;
	$i=0;
	while($row = $result->fetch_assoc()){
	  $return[1][$i]['domain'] = $row['domain'];
	  if($row['active'] == 1){
	    $return[1][$i]['active'] = "master";
	  }elseif($row['active'] == 2){
	    $return[1][$i]['active'] = "backup";
	  }else{
	    $return[1][$i]['active'] = "inactive";
	  }
	  $i++;
	}
	$result->close();
	unset($row,$result,$i);
	goto end;
      }else{
	// other database error
	$error->add("email->get_domains",$this->db->sql->error);
	$error->add("email->get_domains",$query);
	$errmsg = "unable to update email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$domain,$errmsg);
    return $return;
  }

  public function get_email($email){ // returns array of data for a single email account
    global $error;

    // escape email
    $esc = $this->db->esc($email);
    unset($email);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }

    $email = $esc[1];
    unset($esc);
    // get email from database
    $query = "SELECT email,name,maildir,active,domain FROM users WHERE email=\"".$email."\"";
    if($result = $this->db->sql->query($query)){
      if($result->num_rows == 0){
	$errmsg = "email does not exist in the database";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      $return[0] = true;
      while($row = $result->fetch_assoc()){
	$return[1]['email'] = $row['email'];
	$return[1]['name'] = $row['name'];
	$return[1]['maildir'] = $row['maildir'];
	if($row['active'] == 1){
	  $return[1]['active'] = "active";
	}else{
	  $return[1]['active'] = "inactive";
	}
	$return[1]['domain'] = $row['domain'];
      }
      $result->close();
      unset($row,$result);
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($result = $this->db->sql->query($query)){
	if($result->num_rows == 0){
	  $errmsg = "email does not exist in the database";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	$return[0] = true;
	while($row = $result->fetch_assoc()){
	  $return[1]['email'] = $row['email'];
	  $return[1]['name'] = $row['name'];
	  $return[1]['maildir'] = $row['maildir'];
	  if($row['active'] == 1){
	    $return[1]['active'] = "active";
	  }else{
	    $return[1]['active'] = "inactive";
	  }
	  $return[1]['domain'] = $row['domain'];
	}
	$result->close();
	unset($row,$result);
	goto end;
      }else{
	// other database error
	$error->add("email->get_email",$this->db->sql->error);
	$error->add("email->get_email",$query);
	$errmsg = "unable to query email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$domain,$errmsg);
    return $return;
  }

  public function get_alias($alias){ // returns data on a single alias
    global $error;

    // escape alias
    $esc = $this->db->esc($alias);
    unset($alias);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }

    $alias = $esc[1];
    unset($esc);
    // get alias from database
    $query = "SELECT email,destination,active FROM alias WHERE email=\"".$alias."\"";
    if($result = $this->db->sql->query($query)){
      if($result->num_rows == 0){
	$errmsg = "email does not exist in the database";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      $return[0] = true;
      while($row = $result->fetch_assoc()){
	$return[1]['alias'] = $row['email'];
	$return[1]['destination'] = $row['destination'];
	if($row['active'] == 1){
	  $return[1]['active'] = "active";
	}else{
	  $return[1]['active'] = "inactive";
	}
      }
      $result->close();
      unset($row,$result);
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($result = $this->db->sql->query($query)){
	if($result->num_rows == 0){
	  $errmsg = "email does not exist in the database";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	$return[0] = true;
	while($row = $result->fetch_assoc()){
	  $return[1]['alias'] = $row['email'];
	  $return[1]['destination'] = $row['destination'];
	  if($row['active'] == 1){
	    $return[1]['active'] = "active";
	  }else{
	    $return[1]['active'] = "inactive";
	  }
	}
	$result->close();
	unset($row,$result);
	goto end;
      }else{
	// other database error
	$error->add("email->get_alias",$this->db->sql->error);
	$error->add("email->get_alias",$query);
	$errmsg = "unable to update email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$domain,$errmsg);
    return $return;
  }

  public function get_alias_by_destination($destination){ // returns an array of aliases for a given email address
    global $error;

    $email = $destination;
    unset($destination);

    // escape email
    $esc = $this->db->esc($email);
    unset($email);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }

    $email = $esc[1];
    unset($esc);
    // get alias from database
    $query = "SELECT email,active FROM alias WHERE destination=\"".$email."\"";
    if($result = $this->db->sql->query($query)){
      if($result->num_rows == 0){
	$errmsg = "there are no aliases for this email address";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      $return[0] = true;
      $i = 0;
      while($row = $result->fetch_assoc()){
	$return[1][$i]['alias'] = $row['email'];
	if($row['active'] == 1){
	  $return[1][$i]['active'] = "active";
	}else{
	  $return[1][$i]['active'] = "inactive";
	}
	$i++;
      }
      $result->close();
      unset($row,$result,$i);
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($result = $this->db->sql->query($query)){
	if($result->num_rows == 0){
	  $errmsg = "email does not exist in the database";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	$return[0] = true;
	$i = 0;
	while($row = $result->fetch_assoc()){
	  $return[1][$i]['alias'] = $row['email'];
	  if($row['active'] == 1){
	    $return[1][$i]['active'] = "active";
	  }else{
	    $return[1][$i]['active'] = "inactive";
	  }
	  $i++;
	}
	$result->close();
	unset($row,$result,$i);
	goto end;
      }else{
	// other database error
	$error->add("email->get_alias_by_destination",$this->db->sql->error);
	$error->add("email->get_alias_by_destination",$query);
	$errmsg = "unable to update email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$domain,$errmsg);
    return $return;
  }

  public function get_emails_by_domain($domain){
    global $error;

    // escape domain
    $esc = $this->db->esc($domain);
    unset($domain);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }

    $domain = $esc[1];
    unset($esc);
    // get emails from database
    $query = "SELECT email,name,maildir,active FROM users WHERE domain=\"".$domain."\" ORDER BY email";
    if($result = $this->db->sql->query($query)){
      if($result->num_rows == 0){
	$errmsg = "there are no emails in the database for this domain";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      $return[0] = true;
      $i = 0;
      while($row = $result->fetch_assoc()){
	$return[1][$i]['email'] = $row['email'];
	$return[1][$i]['name'] = $row['name'];
	$return[1][$i]['maildir'] = $row['maildir'];
	if($row['active'] == 1){
	  $return[1][$i]['active'] = "active";
	}else{
	  $return[1][$i]['active'] = "inactive";
	}
	$i++;
      }
      unset($i);
      $result->close();
      unset($row,$result);
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($result = $this->db->sql->query($query)){
	if($result->num_rows == 0){
	  $errmsg = "there are no emails in the database for this domain";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	$return[0] = true;
	$i = 0;
	while($row = $result->fetch_assoc()){
	  $return[1][$i]['email'] = $row['email'];
	  $return[1][$i]['name'] = $row['name'];
	  $return[1][$i]['maildir'] = $row['maildir'];
	  if($row['active'] == 1){
	    $return[1][$i]['active'] = "active";
	  }else{
	    $return[1][$i]['active'] = "inactive";
	  }
	  $i++;
	}
	$result->close();
	unset($row,$result);
	goto end;
      }else{
	// other database error
	$error->add("email->get_emails_by_domain",$this->db->sql->error);
	$error->add("email->get_emails_by_domain",$query);
	$errmsg = "unable to query email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$domain,$errmsg);
    return $return;
  }

  public function get_aliases_by_domain($domain){
    global $error;

    // escape domain
    $esc = $this->db->esc($domain);
    unset($domain);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }

    $domain = $esc[1];
    unset($esc);
    // get emails from database
    $query = "SELECT email,destination,active FROM alias WHERE domain=\"".$domain."\" ORDER BY email";
    if($result = $this->db->sql->query($query)){
      if($result->num_rows == 0){
	$errmsg = "there are no aliases in the database for this domain";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      $return[0] = true;
      $i = 0;
      while($row = $result->fetch_assoc()){
	$return[1][$i]['alias'] = $row['email'];
	$return[1][$i]['destination'] = $row['destination'];
	if($row['active'] == 1){
	  $return[1][$i]['active'] = "active";
	}else{
	  $return[1][$i]['active'] = "inactive";
	}
	$i++;
      }
      unset($i);
      $result->close();
      unset($row,$result);
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "email database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($result = $this->db->sql->query($query)){
	if($result->num_rows == 0){
	  $errmsg = "there are no aliases in the database for this domain";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	$return[0] = true;
	$i = 0;
	while($row = $result->fetch_assoc()){
	  $return[1][$i]['alias'] = $row['email'];
	  $return[1][$i]['destination'] = $row['destination'];
	  if($row['active'] == 1){
	    $return[1][$i]['active'] = "active";
	  }else{
	    $return[1][$i]['active'] = "inactive";
	  }
	  $i++;
	}
	$result->close();
	unset($row,$result);
	goto end;
      }else{
	// other database error
	$error->add("email->get_aliases_by_domain",$this->db->sql->error);
	$error->add("email->get_aliases_by_domain",$query);
	$errmsg = "unable to query email database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    end:
    unset($query,$domain,$errmsg);
    return $return;
  }

  private function attr_remove($acc_id,$attr_group,$attr){
    global $error;

    // escape acc_id
    $esc = $this->db2->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape $attr_group
    $esc = $this->db2->esc($attr_group);
    unset($attr_group);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr_group = $esc[1];
    unset($esc);

    // escape $attr
    $esc = $this->db2->esc($attr);
    unset($attr);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr = $esc[1];
    unset($esc);

    $query = "DELETE FROM account_info WHERE type='email' AND acc_id='".$acc_id."' AND attr_group='".$attr_group."' AND attr='".$attr."'";
    if($this->db2->sql->query($query)===true){
      $return[0] = true;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db2->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db2->sql->query($query)===true){
	$return[0] = true;
	goto end;
      }else{
	// other database error
	$error->add("email->attr_remove",$this->db2->sql->error);
	$error->add("email->attr_remove",$query);
	$errmsg = "unable to remove attr '".$attr_group."->".$attr."' from database for '".$acc_id."'";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
    }

    $return[0] = true;

    end:
    unset($acc_id,$attr_group,$attr,$errmsg,$query);
    return $return;
  }

  public function get_attr($acc_id,$attr_group,$attr){
    global $error;

    // escape acc_id
    $esc = $this->db2->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape $attr_group
    $esc = $this->db2->esc($attr_group);
    unset($attr_group);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr_group = $esc[1];
    unset($esc);

    // escape $attr
    $esc = $this->db2->esc($attr);
    unset($attr);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr = $esc[1];
    unset($esc);
    
    // check database exists and grab info
    $query = "SELECT value FROM account_info WHERE acc_id='".$acc_id."' AND type='email' AND attr_group='".$attr_group."' AND attr='".$attr."'";
    if($result = $this->db2->sql->query($query)){
      // see if attribute exists....
      $num_rows = $result->num_rows;
      if($num_rows==0){
	$errmsg = "no attribute exists";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }else{
	$return[0] = true;
	$row = $result->fetch_assoc();
	$return[1] = $row['value'];
	$result->close();
	unset($row,$result);
	goto end;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db2->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db2->sql->query($query)){
	// see if databases exist....
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no attribute exists";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}else{
	  $return[0] = true;
	  $row = $result->fetch_assoc();
	  $return[1] = $row['value'];
	  $result->close();
	  unset($row,$result);
	  goto end;
	}
      }else{
	// other database error
	$error->add("email->get_attr",$this->db2->sql->error);
	$error->add("email->get_attr",$query);
	$errmsg = "unable to query database to check if attribute exists";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }

    end:
    unset($query,$acc_id,$attr_group,$attr,$errmsg);
    return $return;
  }

  private function update_attr($acc_id,$attr_group,$attr,$value){
    global $error;

    // Check if attr already exists.
    $attr_exists = $this->get_attr($acc_id,$attr_group,$attr);

    // escape acc_id
    $esc = $this->db2->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape $attr_group
    $esc = $this->db2->esc($attr_group);
    unset($attr_group);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr_group = $esc[1];
    unset($esc);

    // escape $attr
    $esc = $this->db2->esc($attr);
    unset($attr);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr = $esc[1];
    unset($esc);

    // escape $value
    $esc = $this->db2->esc($value);
    unset($value);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $value = $esc[1];
    unset($esc);
    
    if($attr_exists[0]){
      // Attr already exists, update
      $query = "UPDATE account_info SET value='".$value."' WHERE acc_id='".$acc_id."' AND type='email' AND attr_group='".$attr_group."' AND attr='".$attr."'";
      if($this->db2->sql->query($query)===true){
	$return[0] = true;
	goto end;
      }else{
	// query failed. attempt reconnect
	if(!$this->db2->connect()){
	  $errmsg = "database is not avaliable";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	// and try again
	if($this->db2->sql->query($query)===true){
	  goto end;
	}else{
	  // other database error
	  $error->add("email->update_attr",$this->db2->sql->error);
	  $error->add("email->update_attr",$query);
	  $errmsg = "unable to update site attribute";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
      }
    }else{
      // Attr does not exist, insert
      $query = "INSERT INTO account_info VALUES('".$acc_id."','email','".$attr_group."','".$attr."','".$value."')";
      if($this->db2->sql->query($query)===true){
	$return[0] = true;
	goto end;
      }else{
	// query failed. attempt reconnect
	if(!$this->db2->connect()){
	  $errmsg = "database is not avaliable";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	// and try again
	if($this->db2->sql->query($query)===true){
	  goto end;
	}else{
	  // other database error
	  $error->add("email->update_attr",$this->db2->sql->error);
	  $error->add("email->update_attr",$query);
	  $errmsg = "unable to add email attribute, attribute may already exist";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
      }
    }

    end:
    unset($query,$acc_id,$attr_group,$attr,$value,$attr_exists,$errmsg);
    return $return;
  }

  private function get_attr_group($acc_id,$attr_group){
    global $error;

    // escape acc_id
    $esc = $this->db2->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape $attr_group
    $esc = $this->db2->esc($attr_group);
    unset($attr_group);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr_group = $esc[1];
    unset($esc);
    
    // grab info
    $query = "SELECT attr,value FROM account_info WHERE acc_id='".$acc_id."' AND type='email' AND attr_group='".$attr_group."' ORDER BY attr asc,value asc";
    if($result = $this->db2->sql->query($query)){
      // see if attributes exists....
      $num_rows = $result->num_rows;
      if($num_rows==0){
	$errmsg = "no attributes exists";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }else{
	$return[0] = true;
	while($row = $result->fetch_assoc()){
	  $return[1][$row['attr']] = $row['value'];
	}
	$result->close();
	unset($row,$result);
	goto end;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db2->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db2->sql->query($query)){
	// see if attribute exists....
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no attributes exists";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}else{
	  $return[0] = true;
	  while($row = $result->fetch_assoc()){
	    $return[1][$row['attr']] = $row['value'];
	  }
	  $result->close();
	  unset($row,$result);
	  goto end;
	}
      }else{
	// other database error
	$error->add("email->get_attr_group",$this->db2->sql->error);
	$error->add("email->get_attr_group",$query);
	$errmsg = "unable to query database to check if attributes exist";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }

    end:
    unset($query,$acc_id,$attr_group,$attr,$errmsg);
    return $return;

  }

  public function email_size($acc_id,$email,$raw=false){ // calculates the size of the email address in human or raw sizes
    global $error; global $conf;

    if($raw){
      $type = "raw";
    }else{
      $type = "human";
    }
    unset($raw);

    // escape $email
    $esc = $this->db2->esc($email);
    unset($email);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $email = $esc[1];
    unset($esc);

    $size = $this->get_attr($acc_id,$email,"size-".$type);
    if($size[0]){
      $size = $size[1];
    }else{
      $this->amm->add_job("Email-CalcSize",$acc_id,$jobdata=array("acc_id"=>$acc_id,"email"=>$email),"");
      // then try again
      $size = $this->get_attr($acc_id,$email,"size-".$type);
      if($size[0]){
	$size = $size[1];
      }else{
	if($type == "raw"){
	  $size = 0;
	}
	if($type == "human"){
	  $size = "unknown";
	}
      }   
    }

    $return[0] = true;
    $return[1] = $size;
    unset($size);

    end:
    unset($acc_id,$email,$errmsg,$type);
    return $return;
  }

  public function update_size($acc_id,$email){
    global $error; global $conf;

    // escape acc_id
    $esc = $this->db2->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      $error->add("email->update_size",$esc[1]);
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape $email
    $esc = $this->db2->esc($email);
    unset($email);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      $error->add("email->update_size",$esc[1]);
      unset($esc);
      goto end;
    }
    
    $email = $esc[1];
    unset($esc);

    // grab email data
    $email = $this->get_email($email);
    if(!$email[0]){
      $return[0] = false;
      $return[1] = $email[1];
      goto end;
    }else{
      $email = $email[1];
    }

    // Calculate sizes
    $rawsize = exec("du -s ".$email['maildir']);
    $rawsize = explode("/",$rawsize);
    $rawsize = trim($rawsize[0]);
    if($rawsize == "0" OR $rawsize == ""){
      $rawsize = 0;
    }

    $humansize = exec("du -sh ".$email['maildir']);
    $humansize = explode("/",$humansize);
    $humansize = trim($humansize[0]);
    if($humansize == "0" OR $humansize == ""){
      $humansize = "zero";
    }

    $result = $this->update_attr($acc_id,$email['email'],"size-raw",$rawsize);
    if(!$result[0]){
      $return[0] = false;
      $return[1] = $result[1];
      $error->add("email->update_size","unable to update attr size-raw: ".$result[1]);
      unset($result);
      goto end;
    }
    unset($result);

    $result = $this->update_attr($acc_id,$email['email'],"size-human",$humansize);
    if(!$result[0]){
      $return[0] = false;
      $return[1] = $result[1];
      $error->add("email->update_size","unable to update attr size-human: ".$result[1]);
      unset($result);
      goto end;
    }
    unset($result);

    $result = $this->update_attr($acc_id,$email['email'],"size-lastupdate",date("Y-m-d H:i:s"));
    if(!$result[0]){
      $return[0] = false;
      $return[1] = $result[1];
      $error->add("email->update_size","unable to update attr size-lastupdate: ".$result[1]);
      unset($result);
      goto end;
    }
    unset($result);

    $return[0] = true;

    end:
    unset($acc_id,$email,$value,$errmsg,$rawsize,$humansize);
    return $return;
  }

  public function sched_email_size(){ // check which emails need a size check and adds to queue
    global $error;

    // grab from database which sites need updating
    $query = "SELECT acc_id,attr_group as email FROM account_info WHERE type='email' AND attr='size-lastupdate' AND UNIX_TIMESTAMP(value)<(UNIX_TIMESTAMP(NOW())-60*60*3)";
    if($result = $this->db2->sql->query($query)){
      $num_rows = $result->num_rows;
      if($num_rows==0){
	$return[0] = true;
	goto end;
      }else{
	$return[0] = true;
	while($row = $result->fetch_assoc()){
	  $this->amm->add_job("Email-CalcSize","100000",$jobdata=array("acc_id"=>$row['acc_id'],"email"=>$row['email']),"");
	}
	$result->close();
	unset($row,$result);
	goto end;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db2->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db2->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $return[0] = true;
	  goto end;
	}else{
	  $return[0] = true;
	  while($row = $result->fetch_assoc()){
	    $this->amm->add_job("Email-CalcSize","100000",$jobdata=array("acc_id"=>$row['acc_id'],"email"=>$row['email']),"");
	  }
	  $result->close();
	  unset($row,$result);
	  goto end;
	}
      }else{
	// other database error
	$error->add("email->sched_email_size",$this->db->sql->error);
	$error->add("email->sched_email_size",$query);
	$errmsg = "unable to query database to check email sizes";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }

    end:
    unset($query,$errmsg);
    return $return;
  }

  public function vsftpd_bandwidth($log=""){
    global $error; global $conf;
    if(strlen($log)==0){ $log = "/var/log/vsftpd.log.1"; }

    $logfile = file_get_contents($log);
    if(!$logfile){
      $errmsg = "unable to load log file: ".$log;
      $return[0] = false;
      $return[1] = $errmsg;
      goto end;
    }else{
      $logfile = explode("\n",$logfile);
      $result = array();
      foreach($logfile as $num=>$line){
	$line = trim($line);
	$temp = explode(" ",$line);
	foreach($temp as $num=>$value){
	  if($value=="bytes,"){
	    $user = explode("[",$line);
	    $user = explode("]",$user[2]);
	    $user = $user[0];
	    if(strtolower(substr($user,0,1))=="z"){
	      $user = substr($user,1);
	      $result[$user]['lastupdate'] = substr($line,0,24);
	      $result[$user]['total'][substr($line,20,4)][substr($line,4,3)] = $result[$user]['total'][substr($line,20,4)][substr($line,4,3)] + $temp[($num - 1)];
	     }
	    unset($user);
	  }
	}
	unset($temp,$line);
      }
      if(count($result)>0){
	// Update database with results
      }else{
	$return[0] = true;
	goto end;
      }
    }

    foreach($result as $acc_id=>$data){
      $result = $this->vsftpd_get_attr($acc_id,"vsftpd","lastupdate");
      if(!$result[0]){
	$result[1] = 0;
      }
      $prev = strtotime($result[1]);
      unset($result);
      if(strtotime($data['lastupdate'])>$prev){
	$result = $this->vsftpd_update_attr($acc_id,"vsftpd","lastupdate",date("Y-m-d H:i:s",strtotime($data['lastupdate'])));
	if(!$result[0]){
	  $return[0] = false;
	  $return[1] = $result[1];
	  $error->add("email->vsftpd_bandwidth","unable to update attr lastupdate: ".$result[1]);
	  unset($result);
	  goto end;
	}
	unset($result);

	foreach($data['total'] as $year=>$data2){
	  foreach($data2 as $month=>$value){
	    $month = str_replace(
	      array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"),
	      array("01","02","03","04","05","06","07","08","09","10","11","12"),
	      $month);
	    // get current value
	    $result = $this->vsftpd_get_attr($acc_id,"vsftpd",$year."-".$month);
	    if(!$result[0]){
	      $result[1] = 0;
	    }
	    $value = $value + $result[1];
	    unset($result);
	    // update value in database
	    $result = $this->vsftpd_update_attr($acc_id,"vsftpd",$year."-".$month,$value);
	    if(!$result[0]){
	      $return[0] = false;
	      $return[1] = $result[1];
	      $error->add("email->vsftpd_bandwidth","unable to update attr: ".$result[1]);
	      unset($result);
	      goto end;
	    }
	    unset($result);
	  }
	}
      }
    }

    $return[0] = true;

    end:
    unset($log,$logfile,$acc_id,$prev,$errmsg);
    return $return;
  }

  public function vsftpd_get_attr($acc_id,$attr_group,$attr){
    global $error;

    // escape acc_id
    $esc = $this->db2->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape $attr_group
    $esc = $this->db2->esc($attr_group);
    unset($attr_group);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr_group = $esc[1];
    unset($esc);

    // escape $attr
    $esc = $this->db2->esc($attr);
    unset($attr);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr = $esc[1];
    unset($esc);
    
    // check database exists and grab info
    $query = "SELECT value FROM account_info WHERE acc_id='".$acc_id."' AND type='ftp' AND attr_group='".$attr_group."' AND attr='".$attr."'";
    if($result = $this->db2->sql->query($query)){
      // see if attribute exists....
      $num_rows = $result->num_rows;
      if($num_rows==0){
	$errmsg = "no attribute exists";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }else{
	$return[0] = true;
	$row = $result->fetch_assoc();
	$return[1] = $row['value'];
	$result->close();
	unset($row,$result);
	goto end;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db2->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db2->sql->query($query)){
	// see if databases exist....
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no attribute exists";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}else{
	  $return[0] = true;
	  $row = $result->fetch_assoc();
	  $return[1] = $row['value'];
	  $result->close();
	  unset($row,$result);
	  goto end;
	}
      }else{
	// other database error
	$error->add("email->vsftpd_get_attr",$this->db2->sql->error);
	$error->add("email->vsftpd_get_attr",$query);
	$errmsg = "unable to query database to check if attribute exists";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }

    end:
    unset($query,$acc_id,$attr_group,$attr,$errmsg);
    return $return;
  }

  private function vsftpd_update_attr($acc_id,$attr_group,$attr,$value){
    global $error;

    // Check if attr already exists.
    $attr_exists = $this->vsftpd_get_attr($acc_id,$attr_group,$attr);

    // escape acc_id
    $esc = $this->db2->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape $attr_group
    $esc = $this->db2->esc($attr_group);
    unset($attr_group);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr_group = $esc[1];
    unset($esc);

    // escape $attr
    $esc = $this->db2->esc($attr);
    unset($attr);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr = $esc[1];
    unset($esc);

    // escape $value
    $esc = $this->db2->esc($value);
    unset($value);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $value = $esc[1];
    unset($esc);
    
    if($attr_exists[0]){
      // Attr already exists, update
      $query = "UPDATE account_info SET value='".$value."' WHERE acc_id='".$acc_id."' AND type='ftp' AND attr_group='".$attr_group."' AND attr='".$attr."'";
      if($this->db2->sql->query($query)===true){
	$return[0] = true;
	goto end;
      }else{
	// query failed. attempt reconnect
	if(!$this->db2->connect()){
	  $errmsg = "database is not avaliable";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	// and try again
	if($this->db2->sql->query($query)===true){
	  goto end;
	}else{
	  // other database error
	  $error->add("email->vsftpd_update_attr",$this->db2->sql->error);
	  $error->add("email->vsftpd_update_attr",$query);
	  $errmsg = "unable to update ftp attribute";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
      }
    }else{
      // Attr does not exist, insert
      $query = "INSERT INTO account_info VALUES('".$acc_id."','ftp','".$attr_group."','".$attr."','".$value."')";
      if($this->db2->sql->query($query)===true){
	$return[0] = true;
	goto end;
      }else{
	// query failed. attempt reconnect
	if(!$this->db2->connect()){
	  $errmsg = "database is not avaliable";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	// and try again
	if($this->db2->sql->query($query)===true){
	  goto end;
	}else{
	  // other database error
	  $error->add("email->vsftpd_update_attr",$this->db2->sql->error);
	  $error->add("email->vsftpd_update_attr",$query);
	  $errmsg = "unable to add ftp attribute, attribute may already exist";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
      }
    }

    end:
    unset($query,$acc_id,$attr_group,$attr,$value,$attr_exists,$errmsg);
    return $return;
  }

  public function dovecot_bandwidth($log=""){
    global $error; global $conf;
    if(strlen($log)==0){ $log = "/var/log/mail/info.log.1"; }

    $logfile = file_get_contents($log);
    if(!$logfile){
      $errmsg = "unable to load log file: ".$log;
      $return[0] = false;
      $return[1] = $errmsg;
      goto end;
    }else{
      $logfile = explode("\n",$logfile);
      $result = array();
      $dns = new dns();
      $domain_array = array();
      foreach($logfile as $num=>$line){
	$line = trim($line);
	$temp = explode(" ",substr($line,16));
	foreach($temp as $num=>$value){
	  if($value=="dovecot:" AND stripos($line,"bytes")!==false){
	    $domain = substr($line,stripos($line,"@")+1,strripos($line,")")-strlen($line));
	    if(array_key_exists($domain,$domain_array)){
	      $acc_check[0] = true;
	      $acc_check[1] = $domain_array[$domain];
	    }else{
	      $acc_check = $dns->domain2acc_id($domain);
	    }
	    if($acc_check[0]){
	      $domain_array[$domain] = $acc_check[1];
	      $bytes = substr($line,stripos($line,"bytes")+6);
	      $bytes = explode("/",$bytes);
	      $bytes = array_sum($bytes);
	      if(strtolower(substr($line,0,3))!=strtolower(date("M")) AND date("M")=="Jan"){ $year = date("Y") - 1; }else{ $year = date("Y"); }
	      $result[$acc_check[1]]['lastupdate'] = trim(substr($line,0,7).$year.substr($line,6,9));
	      $result[$acc_check[1]]['total'][$year][substr($line,0,3)] = $result[$acc_check[1]]['total'][$year][substr($line,0,3)] + $bytes;
	    }
	    unset($domain,$acc_check,$bytes);
	  }
	}
	unset($temp,$line);
      }
      unset($domain_array);

      if(count($result)>0){
	// Update database with results
      }else{
	$return[0] = true;
	goto end;
      }
    }

    foreach($result as $acc_id=>$data){
      $result = $this->dovecot_get_attr($acc_id,"dovecot","lastupdate");
      if(!$result[0]){
	$result[1] = 0;
      }
      $prev = strtotime($result[1]);
      unset($result);
      if(strtotime($data['lastupdate'])>$prev){
	$result = $this->dovecot_update_attr($acc_id,"dovecot","lastupdate",date("Y-m-d H:i:s",strtotime($data['lastupdate'])));
	if(!$result[0]){
	  $return[0] = false;
	  $return[1] = $result[1];
	  $error->add("email->dovecot_bandwidth","unable to update attr lastupdate: ".$result[1]);
	  unset($result);
	  goto end;
	}
	unset($result);

	foreach($data['total'] as $year=>$data2){
	  foreach($data2 as $month=>$value){
	    $month = str_replace(
	      array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"),
	      array("01","02","03","04","05","06","07","08","09","10","11","12"),
	      $month);
	    // get current value
	    $result = $this->dovecot_get_attr($acc_id,"dovecot",$year."-".$month);
	    if(!$result[0]){
	      $result[1] = 0;
	    }
	    $value = $value + $result[1];
	    unset($result);
	    // update value in database
	    $result = $this->dovecot_update_attr($acc_id,"dovecot",$year."-".$month,$value);
	    if(!$result[0]){
	      $return[0] = false;
	      $return[1] = $result[1];
	      $error->add("email->dovecot_bandwidth","unable to update attr: ".$result[1]);
	      unset($result);
	      goto end;
	    }
	    unset($result);
	  }
	}
      }
    }

    $return[0] = true;

    end:
    unset($log,$logfile,$acc_id,$prev,$errmsg);
    return $return;
  }

  public function dovecot_get_attr($acc_id,$attr_group,$attr){
    global $error;

    // escape acc_id
    $esc = $this->db2->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape $attr_group
    $esc = $this->db2->esc($attr_group);
    unset($attr_group);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr_group = $esc[1];
    unset($esc);

    // escape $attr
    $esc = $this->db2->esc($attr);
    unset($attr);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr = $esc[1];
    unset($esc);
    
    // check database exists and grab info
    $query = "SELECT value FROM account_info WHERE acc_id='".$acc_id."' AND type='email' AND attr_group='".$attr_group."' AND attr='".$attr."'";
    if($result = $this->db2->sql->query($query)){
      // see if attribute exists....
      $num_rows = $result->num_rows;
      if($num_rows==0){
	$errmsg = "no attribute exists";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }else{
	$return[0] = true;
	$row = $result->fetch_assoc();
	$return[1] = $row['value'];
	$result->close();
	unset($row,$result);
	goto end;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db2->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db2->sql->query($query)){
	// see if databases exist....
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no attribute exists";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}else{
	  $return[0] = true;
	  $row = $result->fetch_assoc();
	  $return[1] = $row['value'];
	  $result->close();
	  unset($row,$result);
	  goto end;
	}
      }else{
	// other database error
	$error->add("email->dovecot_get_attr",$this->db2->sql->error);
	$error->add("email->dovecot_get_attr",$query);
	$errmsg = "unable to query database to check if attribute exists";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }

    end:
    unset($query,$acc_id,$attr_group,$attr,$errmsg);
    return $return;
  }

  private function dovecot_update_attr($acc_id,$attr_group,$attr,$value){
    global $error;

    // Check if attr already exists.
    $attr_exists = $this->dovecot_get_attr($acc_id,$attr_group,$attr);

    // escape acc_id
    $esc = $this->db2->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape $attr_group
    $esc = $this->db2->esc($attr_group);
    unset($attr_group);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr_group = $esc[1];
    unset($esc);

    // escape $attr
    $esc = $this->db2->esc($attr);
    unset($attr);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $attr = $esc[1];
    unset($esc);

    // escape $value
    $esc = $this->db2->esc($value);
    unset($value);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $value = $esc[1];
    unset($esc);
    
    if($attr_exists[0]){
      // Attr already exists, update
      $query = "UPDATE account_info SET value='".$value."' WHERE acc_id='".$acc_id."' AND type='email' AND attr_group='".$attr_group."' AND attr='".$attr."'";
      if($this->db2->sql->query($query)===true){
	$return[0] = true;
	goto end;
      }else{
	// query failed. attempt reconnect
	if(!$this->db2->connect()){
	  $errmsg = "database is not avaliable";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	// and try again
	if($this->db2->sql->query($query)===true){
	  goto end;
	}else{
	  // other database error
	  $error->add("email->dovecot_update_attr",$this->db2->sql->error);
	  $error->add("email->dovecot_update_attr",$query);
	  $errmsg = "unable to update email attribute";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
      }
    }else{
      // Attr does not exist, insert
      $query = "INSERT INTO account_info VALUES('".$acc_id."','email','".$attr_group."','".$attr."','".$value."')";
      if($this->db2->sql->query($query)===true){
	$return[0] = true;
	goto end;
      }else{
	// query failed. attempt reconnect
	if(!$this->db2->connect()){
	  $errmsg = "database is not avaliable";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	// and try again
	if($this->db2->sql->query($query)===true){
	  goto end;
	}else{
	  // other database error
	  $error->add("email->dovecot_update_attr",$this->db2->sql->error);
	  $error->add("email->dovecot_update_attr",$query);
	  $errmsg = "unable to add email attribute, attribute may already exist";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
      }
    }

    end:
    unset($query,$acc_id,$attr_group,$attr,$value,$attr_exists,$errmsg);
    return $return;
  }
}
?>
