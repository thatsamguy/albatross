<?php
/* 
* Albatross Manager
* 
* DNS Database SQL Interface class
* 
* Description:
*  Contains all functions for the dns database
*
* Copyright 2011 Cyprix Enterprises
*/
?>
<?php
// Include Dependencies
include_once("config.class.php");
include_once("mysqli.class.php");
include_once("error.class.php");
include_once("email.class.php");
?>
<?php
class dns{ // TODO: migrate code to new system to avoid race condition on checking for db access
  private $email;

  function __construct() {
    // start db connection
    $this->db = new db();
    $this->db->database = "dns";
    $this->db->connect();
    $this->email = new email();
  }

  function __destruct() {
    // Do nothing.
    unset($this->db);
  }

  public function add_domain($domain,$acc_id){
    global $error; global $conf; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }
    // Check if domain already exists
    if(!is_array($return)){
      $domain_exists = $this->check_domain_name_exists(trim($domain));
      if(!$domain_exists[0]){
	$errmsg = $domain_exists[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$domain = $domain_exists[1];
      }
      unset($domain_exists);
    }

    // Check if this is a subdomain
    // TODO: major security bug - stop adding of subdomain if parent domain does not have the same owner
    if(!is_array($return)){
      $explode = explode(".",trim($domain));
      unset($explode[0]);
      $parent_domain = implode(".",$explode);
      unset($explode,$implode);
      $subdomain_check = $this->check_domain_name_exists($parent_domain);
      $is_subdomain = $subdomain_check[0];
      $parent_domain_id = $this->domain2did($parent_domain);
      if($parent_domain_id[0]){
	$parent_domain_id = $parent_domain_id[1];
      }else{
	$is_subdomain = true;
	$error->add("dns->add_domain","unable to work out parent domain of '".$domain."'");
	unset($parent_domain_id);
      }
      unset($subdomain_check,$parent_domain);
    }

    // TODO: Check for valid acc_id

    // Use default settings from conf
    $dns = $conf->dns;

    // Set dns nameservers
    //// Check if different ns avaliable for TLD
    if(!is_array($return)){
      $ns = $dns['ns']['default'];
      if(is_array($dns['ns']['tld'])){
	$array = explode(".",$domain);
	$array_count = count($array);
	$tld = $array[$array_count - 1];
	unset($array,$array_count);
	if(array_key_exists($tld,$dns['ns']['tld'])){
	  $ns = $dns['ns']['tld'][$tld];
	}
	unset($tld);
      }
    }

    // create domain in domains
    if(!is_array($return)){
      $query = "INSERT INTO domains VALUES(0,'".$domain."',null,null,'MASTER',null,'".$acc_id."')";
      if($this->db->sql->query($query)===TRUE){
	// Grab new domain_id
	$domain_id = $this->domain2did($domain);
	if(!$domain_id[0]){
	  $errmsg = $domain_id[1];
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg,$domain_id);
	}else{
	  $domain_id = $domain_id[1];
	}
      }else{
	$errmsg = "unable to update dns database with new domain information";
	$error->add("dns->add_domain",$this->db->sql->error);
	$error->add("dns->add_domain",$query);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($query);
    }

    // create default records in records
      // soa - create blank soa, then use update_soa with default
    if(!is_array($return)){
      $query = "INSERT INTO records VALUES(0,'".$domain_id."','".$domain."','SOA','".$ns[0]." ".$dns['soa']['domain email']." ".$dns['soa']['serial']." ".$dns['soa']['refresh']." ".$dns['soa']['retry']." ".$dns['soa']['expire']." ".$dns['soa']['minimum']."',".$dns['soa']['minimum'].",0,UTC_TIMESTAMP())";
      if($this->db->sql->query($query)===TRUE){
      }else{
	$errmsg = "unable to update dns database with new soa record information";
	$error->add("dns->add_domain",$this->db->sql->error);
	$error->add("dns->add_domain",$query);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($query);
    }
      // ns - add each nameserver
    if(!is_array($return)){
      foreach($ns as $nameserver){
	$add_nameserver = $this->add_record($domain_id,$domain,"NS",$nameserver);
	if(!$add_nameserver[0]){
	  $errmsg = $add_nameserver[1];
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}
	unset($add_nameserver);
      }
      unset($nameserver);
    }
      // ns - add nameserver to parent domain if subdomain
    if(!is_array($return)){
      if(!$is_subdomain){
	foreach($ns as $nameserver){
	  $add_nameserver = $this->add_record($parent_domain_id,$domain,"NS",$nameserver);
	  if(!$add_nameserver[0]){
	    $errmsg = $add_nameserver[1];
	    $return[0] = false;
	    $return[1] = $errmsg;
	    unset($errmsg);
	  }
	  unset($add_nameserver);
	}
	unset($nameserver);
      }
    }
      // rec - add each record from default conf
    if(!is_array($return)){
      foreach($dns['rec'] as $record){
	foreach($record as $key=>$item){
	  $record[$key] = str_replace("_domain_",$domain,$item);
	}
	unset($key,$item);
	if($record[3]==""){
	  $record[3] = 0;
	}
	$add_record = $this->add_record($domain_id,$record[0],$record[1],$record[2],$record[3]);
	if(!$add_record[0]){
	  $errmsg = $add_record[1];
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}
	unset($add_record);
      }
      unset($record);
    }

    if(!is_array($return)){
      $return[0] = true;
    }

    // Rollback if any fail and part of domain has been created.
    if(!$return[0]){
	$error->add("dns->add_domain","rollback");
	$this->remove_domain($domain_id,false);
    }else{
      // Add domain to email database
      $this->email->add_domain($domain);
    }

    unset($dns,$domain_id,$domain,$acc_id);

    return $return;
  }

  public function remove_domain($domain_id,$backup = true){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }
    // Check domain_id is valid
    if(!is_array($return)){
      $domain_exists = $this->check_domain_exists($domain_id);
      if(!$domain_exists[0]){
	$errmsg = $domain_exists[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$domain_id = $domain_exists[1];
      }
      unset($domain_exists);
    }

    // TODO: Backup domain and records on remove

    // Remove records
    if(!is_array($return)){
      $query = "DELETE FROM records WHERE domain_id='".$domain_id."'";
      if($this->db->sql->query($query)===TRUE){
      }else{
	$errmsg = "unable to remove records from the dns database";
	$error->add("dns->remove_domain",$this->db->sql->error);
	$error->add("dns->remove_domain",$query);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($query);
    }

    // Remove domain
    if(!is_array($return)){
      $query = "DELETE FROM domains WHERE id='".$domain_id."'";
      if($this->db->sql->query($query)===TRUE){
	$return[0] = true;
      }else{
	$errmsg = "unable to remove domain from the dns database";
	$error->add("dns->remove_domain",$this->db->sql->error);
	$error->add("dns->remove_domain",$query);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($query);
    }

    return $return;
  }

  // TODO: move acc_id functions to separate library
  public function set_domain_acc_id($domain_id,$acc_id){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // TODO: Check for valid acc_id

    // Check domain_id is valid
    if(!is_array($return)){
      $domain_exists = $this->check_domain_exists($domain_id);
      if(!$domain_exists[0]){
	$errmsg = $domain_exists[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$domain_id = $domain_exists[1];
      }
      unset($domain_exists);
    }

    // Update database domain entry
    if(!is_array($return)){
      $query = "UPDATE domains SET acc_id='".$acc_id."' WHERE id='".$domain_id."'";
      if($this->db->sql->query($query)===TRUE){
	$return[0] = true;
      }else{
	$errmsg = "unable to allocate domain to account in the dns database";
	$error->add("dns->set_domain_acc_id",$this->db->sql->error);
	$error->add("dns->set_domain_acc_id",$query);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($acc_id,$domain_id,$query);
    }

    return $return;
  }
  
  public function update_soa($domain_id,$primary_dns="",$domain_administrator_email="",$refresh="",$retry="",$expire="",$minimum=""){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    $array['primary dns']=$primary_dns;
    $array['domain administrator email']=$domain_administrator_email;
    $array['refresh']=$refresh;
    $array['retry']=$retry;
    $array['expire']=$expire;
    $array['minimum']=$minimum;
    unset($primary_dns,$domain_administrator_email,$refresh,$retry,$expire,$minimum);
    

    // Escape domain_id
    if(!is_array($return)){
      $esc = $this->db->esc($domain_id);
      unset($domain_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }else{
	$domain_id = $esc[1];
	unset($esc);
      }
    }

    // Retrieve current SOA
    if(!is_array($return)){
      $soa = $this->get_domain_soa($domain_id);
      if(!$soa[0]){
	$errmsg = $soa[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$soa);
      }
    }

    // Check each entry in $array for viability
    if(!is_array($return)){
      $keylist = array_keys($soa[1]);
      foreach($array as $key=>$value){
	if(in_array($key,$keylist) AND $value!=""){
	  // TODO: return error on validation failure
	  switch($key){
	    case "primary dns":
	      $array[$key] = trim($value);
	      break;
	    case "domain administrator email":
	      $array[$key] = str_replace('@','.',trim($value));
	      break;
	    default:
	      if($value>2147483647 OR $value<0){ // RFC 2181 - http://www.ietf.org/rfc/rfc2181.txt
		unset($array[$key]);
	      }
	      break;
	  }
	}else{
	  unset($array[$key]);
	}
      }
      unset($key,$value,$keylist);

      // Disable changing of serial
      unset($array['serial']);

      // Escape each entry in $array
      foreach($array as $key=>$value){
	$esc = $this->db->esc($value);
	if(!$esc[0]){
	  $errmsg = $esc[1];
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg,$esc);
	}else{
	  $array[$key] = $esc[1];
	  unset($esc);
	}
      }
      unset($key,$value);
    }

    //  Set ttl
    if(!is_array($return)){
      $ttl = "";
      foreach($array as $key=>$value){
	if($array[$key]!=""){
	  if($key=='minimum' AND $soa[1]['minimum']!=$value){
	    $ttl = $value;
	  }
	  $soa[1][$key] = $array[$key];
	}
      }
      $new_soa = implode(" ",$soa[1]);
      unset($soa,$array,$key,$value);
    }

    // Escape new_soa
    if(!is_array($return)){
      $esc = $this->db->esc($new_soa);
      unset($new_soa);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }else{
	$new_soa = $esc[1];
	unset($esc);
      }
    }

    // If TTL has changed, set all records of that domain to the same TTL
    // RFC 2181 - http://www.ietf.org/rfc/rfc2181.txt
    if(!is_array($return)){
      if($ttl!=""){
	$query = "UPDATE records SET ttl='".$ttl."' WHERE domain_id='".$domain_id."'";
	if($this->db->sql->query($query)===TRUE){
	// Do nothing
	}else{
	  $errmsg = "unable to update records in dns database with new ttl";
	  $error->add("dns->update_soa",$this->db->sql->error);
	  $error->add("dns->update_soa",$query);
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}
	unset($query);
      }
      unset($ttl);
    }

    // Update database entry with new soa
    if(!is_array($return)){
      $query = "UPDATE records SET content='".$new_soa."' WHERE domain_id='".$domain_id."' AND type='SOA'";
      if($this->db->sql->query($query)===TRUE){
	$return[0] = true;
	$this->increment_serial($domain_id);
      }else{
	$errmsg = "unable to update dns database with new soa";
	$error->add("dns->update_soa",$this->db->sql->error);
	$error->add("dns->update_soa",$query);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($new_soa,$domain_id,$query);
    }

    return $return;
  }


  public function add_record($domain_id,$name,$type,$content,$priority=0){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Check domain_id is valid
    if(!is_array($return)){
      $domain_exists = $this->check_domain_exists($domain_id);
      if(!$domain_exists[0]){
	$errmsg = $domain_exists[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$domain_id = $domain_exists[1];
      }
      unset($domain_exists);
    }

    // check validity of type, name and content
    if(!is_array($return)){
      $checktype = $this->check_type($type,$content,$name);
      unset($type,$content,$name);
      if(!$checktype[0]){
	$errmsg = $checktype[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$type = $checktype['type'];
	$content = $checktype['content'];
	$name = $checktype['name'];
      }
      unset($checktype);
    }

    // only apply priority for MX records
    if(!is_array($return)){
      if($type!="MX"){
	$priority = 0;
      }
    }

    // Check priority is valid
    if(!is_array($return)){
      if($priority>65535 OR $priority<0){
	$errmsg = "the priority must be zero or greater and less than or equal to 65535";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    // Grab ttl from domain
    if(!is_array($return)){
      $soa = $this->get_domain_soa($domain_id);
      if(!$soa[0]){
	$errmsg = $soa[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$ttl = $soa[1]['minimum'];
      }
      unset($soa);
    }

    // Update database with new information
    if(!is_array($return)){
      $query = "INSERT INTO records VALUES(0,".$domain_id.",'".$name."','".$type."','".$content."',".$ttl.",".$priority.",UTC_TIMESTAMP())";
      if($this->db->sql->query($query)===TRUE){
	$return[0] = true;
	$this->increment_serial($domain_id);
      }else{
	$errmsg = "unable to update dns database with new record information";
	$error->add("dns->add_record",$this->db->sql->error);
	$error->add("dns->add_record",$query);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($domain_id,$query);
    }

    return $return;
  }

  public function remove_record($record_id){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // return error if type=SOA
    if(!is_array($return)){
      $gettype = $this->get_record_type($record_id);
      if(!$gettype[0]){
	$errmsg = $gettype[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }elseif($gettype[1]=="SOA"){
	$errmsg = "removing soa records is not allowed";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($gettype);
    }

    // Escape record_id
    if(!is_array($return)){
      $esc = $this->db->esc($record_id);
      unset($record_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }else{
	$record_id = $esc[1];
	unset($esc);
      }
    }

    // Remove database entry
    if(!is_array($return)){
      $query = "DELETE FROM records WHERE id='".$record_id."' AND type!='SOA'";
      if($this->db->sql->query($query)===TRUE){
	$return[0] = true;
	$this->increment_serial($domain_id);
      }else{
	$errmsg = "unable to remove record from the dns database";
	$error->add("dns->remove_record",$this->db->sql->error);
	$error->add("dns->remove_record",$query);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($domain_id,$query);
    }

    return $return;
  }

  public function update_record($record_id,$content,$priority=0){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Grab record type
    if(!is_array($return)){
      $gettype = $this->get_record_type($record_id);
      if(!$gettype[0]){
	$errmsg = $gettype[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$type = $gettype[1];
      }
      unset($gettype);
    }

    // TODO: grab record type then check valid content for type 
    /*if(!is_array($return)){
      $checktype = $this->check_type($type,$content,"");
      unset($content);
      if(!$checktype[0]){
	$errmsg = $checktype[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$content = $checktype['content'];
      }
      unset($checktype);
    }*/

    // grab domain_id for this record
    if(!is_array($return)){
      $getdomain_id = $this->rid2did($record_id);
      if(!$getdomain_id[0]){
	$errmsg = $getdomain_id[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }else{
	$domain_id = $getdomain_id[1];
      }
      unset($getdomain_id);
    }

    // only apply priority for MX records
    if(!is_array($return)){
      if($type!="MX"){
	$priority = 0;
      }
    }

    // Check priority is valid
    if(!is_array($return)){
      if($priority>65535 OR $priority<0){
	$errmsg = "the priority must be zero or greater and less than or equal to 65535";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    // Escape record_id
    if(!is_array($return)){
      $esc = $this->db->esc($record_id);
      unset($record_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }else{
	$record_id = $esc[1];
	unset($esc);
      }
    }

    // Update database with new information
    if(!is_array($return)){
      $query = "UPDATE records SET content='".$content."',prio='".$priority."',last_modified=UTC_TIMESTAMP() WHERE id='".$record_id."'";
      if($this->db->sql->query($query)===TRUE){
	$return[0] = true;
	$this->increment_serial($domain_id);
      }else{
	$errmsg = "unable to update dns database with modified record information";
	$error->add("dns->update_record",$this->db->sql->error);
	$error->add("dns->update_record",$query);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($new_soa,$domain_id,$query);
    }

    return $return;
  }

  private function increment_serial($domain_id){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Escape domain_id
    if(!is_array($return)){
      $esc = $this->db->esc($domain_id);
      unset($domain_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }else{
	$domain_id = $esc[1];
	unset($esc);
      }
    }

    // Increment SOA serial
    if(!is_array($return)){
      $soa = $this->get_domain_soa($domain_id);
      if(!$soa[0]){
	$errmsg = $soa[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$soa);
      }else{
	$soa[1]['serial'] = $soa[1]['serial'] + 1;
	if($soa[1]['serial']>4294967295 OR $soa[1]['serial']<1){ // RFC1982 - http://tools.ietf.org/html/rfc1982
	  $soa[1]['serial'] = 1;
	}
	$new_soa = implode(" ",$soa[1]);
	unset($soa);
      }
    }

    // Escape new_soa
    if(!is_array($return)){
      $esc = $this->db->esc($new_soa);
      unset($new_soa);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }else{
	$new_soa = $esc[1];
	unset($esc);
      }
    }

    // Update database entry with new soa
    if(!is_array($return)){
      $query = "UPDATE records SET content='".$new_soa."',last_modified=UTC_TIMESTAMP() WHERE domain_id='".$domain_id."' AND type='SOA'";
      if($this->db->sql->query($query)===TRUE){
	$return[0] = true;
      }else{
	$errmsg = "unable to update dns database with new soa";
	$error->add("dns->increment_soa",$this->db->sql->error);
	$error->add("dns->increment_soa",$query);
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($new_soa,$domain_id,$query);
    }

    return $return;
  }

  // TODO: move acc_id functions to separate library
  public function get_domains_for_acc_id($acc_id){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Escape acc_id
    if(!is_array($return)){
      $esc = $this->db->esc($acc_id);
      unset($acc_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    if(!is_array($return)){
      $query = "SELECT id,name FROM domains WHERE acc_id='".$esc[1]."' ORDER BY id";
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no domains are connected to this account number";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return[0] = true;
	  while($row = $result->fetch_assoc()){
	    $return[1][$row['id']] = $row['name'];
	  }
	  $result->close();
	  unset($row,$result);
	}
	unset($num_rows);
      }else{
	$error->add("dns->get_domains_for_acc_id",$this->db->sql->error);
	$error->add("dns->get_domains_for_acc_id",$query);
	$errmsg = "unable to query database to check for domains";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($esc,$query);
    }
    
    return $return;
  }

  public function get_records_from_domain($domain_id){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Escape acc_id
    if(!is_array($return)){
      $esc = $this->db->esc($domain_id);
      unset($domain_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    if(!is_array($return)){
      $query = "SELECT id,name,type,content,ttl,prio,last_modified FROM records WHERE domain_id='".$esc[1]."' ORDER BY type desc,prio asc,name asc,id asc";
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no records are connected to this domain_id";
	  $error->add("dns->get_records_from_domain",$errmsg." domain_id='".$esc[1]."'");
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return[0] = true;
	  while($row = $result->fetch_assoc()){
	    $return[1][$row['id']]['name'] = $row['name'];
	    $return[1][$row['id']]['type'] = $row['type'];
	    $return[1][$row['id']]['content'] = $row['content'];
	    $return[1][$row['id']]['ttl'] = $row['ttl'];
	    $return[1][$row['id']]['priority'] = $row['prio'];
	    $return[1][$row['id']]['last modified'] = $row['last_modified'];
	  }
	  $result->close();
	  unset($row,$result);
	}
	unset($num_rows);
      }else{
	$error->add("dns->get_records_from_domain",$this->db->sql->error);
	$error->add("dns->get_records_from_domain",$query);
	$errmsg = "unable to query database to check for records";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($esc,$query);
    }
    
    return $return;
  }

  public function get_domain_soa($domain_id){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Escape acc_id
    if(!is_array($return)){
      $esc = $this->db->esc($domain_id);
      unset($domain_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    if(!is_array($return)){
      $query = "SELECT content FROM records WHERE domain_id='".$esc[1]."' and type='SOA'";
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no soa record exists for this domain_id";
	  $error->add("dns->get_domain_soa",$errmsg." domain_id='".$esc[1]."'");
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return[0] = true;
	  while($row = $result->fetch_assoc()){
	    $return[1] = $this->soa_to_array($row['content']);
	  }
	  $result->close();
	  unset($row,$result);
	}
	unset($num_rows);
      }else{
	$error->add("dns->get_domain_soa",$this->db->sql->error);
	$error->add("dns->get_domain_soa",$query);
	$errmsg = "unable to query database to check for soa record";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($esc,$query);
    }
    
    return $return;
  }

  private function soa_to_array($soa_content){
    $array = explode(" ",$soa_content);
    unset($soa_content);
    
    $return['primary dns'] = $array[0];
    $return['domain administrator email'] = $array[1];
    $return['serial'] = $array[2];
    $return['refresh'] = $array[3];
    $return['retry'] = $array[4];
    $return['expire'] = $array[5];
    $return['minimum'] = $array[6];
    unset($array);

    return $return;
  }

  private function get_record_type($record_id){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Escape record_id
    if(!is_array($return)){
      $esc = $this->db->esc($record_id);
      unset($record_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    if(!is_array($return)){
      $query = "SELECT type FROM records WHERE id='".$esc[1]."'";
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no record exists with id(".$esc[1].")";
	  $error->add("dns->get_record_id",$errmsg);
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return[0] = true;
	  while($row = $result->fetch_assoc()){
	    $return[1] = strtoupper($row['type']);
	  }
	  $result->close();
	  unset($row,$result);
	}
	unset($num_rows);
      }else{
	$error->add("dns->get_record_type",$this->db->sql->error);
	$error->add("dns->get_record_type",$query);
	$errmsg = "unable to query database to check for record type";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($esc,$query);
    }
    
    return $return;
  }

  private function check_domain_exists($domain_id){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Escape domain_id
    if(!is_array($return)){
      $esc = $this->db->esc($domain_id);
      unset($domain_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    if(!is_array($return)){
      $query = "SELECT id FROM domains WHERE id='".$esc[1]."'";
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no domain exists with id(".$esc[1].")";
	  $error->add("dns->check_domain_exists",$errmsg);
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return[0] = true;
	  $return[1] = $esc[1];
	  unset($row,$result);
	}
	unset($num_rows);
      }else{
	$error->add("dns->check_domain_exists",$this->db->sql->error);
	$error->add("dns->check_domain_exists",$query);
	$errmsg = "unable to query database to check if domain exists";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($esc,$query);
    }
    
    return $return;
  }

  private function check_domain_name_exists($domain){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Escape domain
    if(!is_array($return)){
      $esc = $this->db->esc($domain);
      unset($domain);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    if(!is_array($return)){
      $query = "SELECT name FROM domains WHERE name='".$esc[1]."'";
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows>0){
	  $errmsg = "domain '".$esc[1]."' already exists in the database";
	  $error->add("dns->check_domain_name_exists",$errmsg);
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return[0] = true;
	  $return[1] = $esc[1];
	  unset($row,$result);
	}
	unset($num_rows);
      }else{
	$error->add("dns->check_domain_name_exists",$this->db->sql->error);
	$error->add("dns->check_domain_name_exists",$query);
	$errmsg = "unable to query database to check if domain name exists";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($esc,$query);
    }
    
    return $return;
  }

  private function check_type($type,$content="",$name=""){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    $type = strtoupper($type);
    // Array of type with validation checking
    $array = array("A","CNAME","MX","TXT","NS","AAAA");
    // List of all other types supported by powerdns
    // array2("SOA","AAAA","AFSDB","CERT","DNSKEY","DS","HINFO","KEY","LOC","NAPTR","NSEC","PTR","RP","RRSIG","SPF","SSHFP","SRV");
    
    // If only type is sent, just check for a valid type
    if(!is_array($return)){
      if(in_array($type,$array) AND $content=="" AND $name==""){
	$return[0] = true;
      }elseif(!in_array($type,$array) AND $content=="" AND $name==""){
	$errmsg = "record type (".$type.") is either not valid or not supported";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
    }

    // If content or name is also sent, also check validity of variables
    if(!is_array($return)){
      if(in_array($type,$array) AND ($content!="" OR $name!="")){
	$content = trim($content);
	$name = trim($name);
	// TODO: Add validation checking
	switch($type){
	  case "A":
	    $content = $content;
	    $name = $name;
	    break;
	  default:
	    $content = $content;
	    $name = $name;
	    break;
	}
	// TODO: Add escaping for $content and $name
	// Escape content and name
	$esc = $this->db->esc($content);
	$esc2 = $this->db->esc($name);
	unset($content,$name);
	if(!$esc[0]){
	  $errmsg = $esc[1];
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}elseif(!$esc2[0]){
	  $errmsg = $esc2[1];
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return['type'] = $type;
	  $return['content'] = $esc[1];
	  $return['name'] = $esc2[1];
	  $return[0] = true;
	}
	unset($esc,$esc2);
      }
    }
    unset($type);

    return $return;
  }

  // Public alias of rid2did()
  public function get_record_domain_id($record_id){
    $return = $this->rid2did($record_id);
    return $return;
  }

  // Converts a record_id to domain_id
  private function rid2did($record_id){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Escape record_id
    if(!is_array($return)){
      $esc = $this->db->esc($record_id);
      unset($record_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    if(!is_array($return)){
      $query = "SELECT domain_id FROM records WHERE id='".$esc[1]."'";
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no record exists with id(".$esc[1].")";
	  $error->add("dns->rid2did",$errmsg);
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return[0] = true;
	  while($row = $result->fetch_assoc()){
	    $return[1] = strtoupper($row['domain_id']);
	  }
	  $result->close();
	  unset($row,$result);
	}
	unset($num_rows);
      }else{
	$error->add("dns->rid2did",$this->db->sql->error);
	$error->add("dns->rid2did",$query);
	$errmsg = "unable to query database to check for record type";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($esc,$query);
    }
    
    return $return;
  }

  // Converts a domain name to domain_id
  private function domain2did($domain){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Escape domain
    if(!is_array($return)){
      $esc = $this->db->esc($domain);
      unset($record_id);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    if(!is_array($return)){
      $query = "SELECT id FROM domains WHERE name='".$esc[1]."'";
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no domain exists with name(".$esc[1].")";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return[0] = true;
	  while($row = $result->fetch_assoc()){
	    $return[1] = strtoupper($row['id']);
	  }
	  $result->close();
	  unset($row,$result);
	}
	unset($num_rows);
      }else{
	$error->add("dns->domain2did",$this->db->sql->error);
	$error->add("dns->domain2did",$query);
	$errmsg = "unable to query database to check for domain_id";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($esc,$query);
    }
    
    return $return;
  }

  public function domain2acc_id($domain){
    global $error; $return = "";
    // Check for database access
    if(!$this->db->connect()){
      $errmsg = "unable to connect to dns database";
      $return[0] = false;
      $return[1] = $errmsg;
      unset($errmsg);
    }

    // Escape $domain
    if(!is_array($return)){
      $esc = $this->db->esc($domain);
      unset($domain);
      if(!$esc[0]){
	$errmsg = $esc[1];
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg,$esc);
      }
    }

    if(!is_array($return)){
      $query = "SELECT acc_id FROM domains WHERE name='".$esc[1]."' LIMIT 0,1";
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "no account for this domain";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  unset($errmsg);
	}else{
	  $return[0] = true;
	  while($row = $result->fetch_assoc()){
	    $return[1] = $row['acc_id'];
	  }
	  $result->close();
	  unset($row,$result);
	}
	unset($num_rows);
      }else{
	$error->add("dns->domain2acc_id",$this->db->sql->error);
	$error->add("dns->domain2acc_id",$query);
	$errmsg = "unable to query database to check for acc_id";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
      }
      unset($esc,$query);
    }
    
    return $return;
  }
}
?>
