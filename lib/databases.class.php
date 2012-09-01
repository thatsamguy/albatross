<?php
/* 
* Albatross Manager
* 
* User Databases SQL Interface class
* 
* Description:
*  Contains all functions for managing user databases
*
* Copyright 2010 Samuel Bailey
*/
?>
<?php
// Include Dependencies
include_once("config.class.php");
include_once("mysqli.class.php");
include_once("error.class.php");
?>
<?php
class database{
  private $db;

  function __construct(){
    // start db connection
    $this->db = new db();
    $this->db->database = "default";
    $this->db->connect();
  }

  function __destruct() {
    // Do nothing.
    unset($this->db);
  }

  public function add($name,$acc_id,$passwd){ // adds a new database for a user based upon the name provided
    global $error;
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

    // escape acc_id
    // TODO: Add check for a valid acc_id
    $esc = $this->db->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape passwd
    $esc = $this->db->esc($passwd);
    unset($passwd);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $passwd = $esc[1];
    unset($esc);
    
    // create database
    $query = "CREATE DATABASE ".$acc_id."_".$name;
    if($this->db->sql->query($query)===true){
      goto permissions;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	goto permissions;
      }else{
	// other database error
	$error->add("database->add",$this->db->sql->error);
	$error->add("database->add",$query);
	$errmsg = "unable to create new database, database may already exist";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
    }

    permissions:
    unset($query,$errmsg);
    // Add user and permissions
    $query = "GRANT ALL ON ".$acc_id."_".$name.".* to z".$acc_id."@localhost identified by '".$passwd."'";
    if($this->db->sql->query($query)===true){
      goto dblist;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	goto dblist;
      }else{
	// other database error
	$error->add("database->add",$this->db->sql->error);
	$error->add("database->add",$query);
	$errmsg = "unable to create new database permissions";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
    }

    dblist:
    unset($query,$errmsg);
    // Add user and permissions
    $query = "INSERT INTO userdbs VALUES(\"".$acc_id."\",\"".$acc_id."_".$name."\",NOW(),NOW())";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $acc_id."_".$name;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $acc_id."_".$name;
	goto end;
      }else{
	// other database error
	$error->add("database->add",$this->db->sql->error);
	$error->add("database->add",$query);
	$errmsg = "unable to create new database in database list";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
    }

    end:
    unset($query,$name,$acc_id,$passwd,$errmsg);
    return $return;
  }

  public function remove($database){
    global $error;
    // escape database
    $esc = $this->db->esc($database);
    unset($database);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $database = $esc[1];
    unset($esc);

    // TODO: Add archiving of database before removal

    // remove database
    $query = "DROP DATABASE ".$database;
    if($this->db->sql->query($query)===true){
      goto dblist;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	goto dblist;
      }else{
	// other database error
	$error->add("database->remove",$this->db->sql->error);
	$error->add("database->remove",$query);
	$errmsg = "unable to remove database";
	$return[0] = false;
	$return[1] = $errmsg;
      }
    }

    dblist:
    unset($query,$errmsg);
    // remove database from db list
    $query = "DELETE FROM userdbs WHERE db='".$database."'";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $database;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $database;
	goto end;
      }else{
	// other database error
	$error->add("database->remove",$this->db->sql->error);
	$error->add("database->remove",$query);
	$errmsg = "unable to remove database from database list";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
    }

    end:
    unset($query,$database,$errmsg);
    return $return;
  }

  public function archive($database){ return false; }

  public function update_passwd($name,$acc_id,$passwd){ // updates the access password for the user account for all databases
    global $error;
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

    // escape acc_id
    $esc = $this->db->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);

    // escape passwd
    $esc = $this->db->esc($passwd);
    unset($passwd);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $passwd = $esc[1];
    unset($esc);
    
    // check database exists in userdbs list
    $query = "SELECT db FROM userdbs WHERE acc_id='".$acc_id."' AND db='".$acc_id."_".$name."'";
    if($result = $this->db->sql->query($query)){
      // see if database exists....
      $num_rows = $result->num_rows;
      if($num_rows==0){
	$errmsg = "database '".$acc_id."_".$name."' does not exist in database list";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }else{
	goto permissions;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db->sql->query($query)){
	// see if database exists....
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "database '".$acc_id."_".$name."' does not exist in database list";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}else{
	  goto permissions;
	}
      }else{
	// other database error
	$error->add("database->update_passwd",$this->db->sql->error);
	$error->add("database->update_passwd",$query);
	$errmsg = "unable to query database to check if database exists";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }

    permissions:
    unset($query,$errmsg);
    // Add user and permissions
    $query = "GRANT ALL ON ".$acc_id."_".$name.".* to z".$acc_id."@localhost identified by '".$passwd."'";
    if($this->db->sql->query($query)===true){
      goto dblist;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	goto dblist;
      }else{
	// other database error
	$error->add("database->update_passwd",$this->db->sql->error);
	$error->add("database->update_passwd",$query);
	$errmsg = "unable to update database permissions";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
    }

    dblist:
    unset($query,$errmsg);
    // Add user and permissions
    $query = "UPDATE userdbs SET modified=NOW() WHERE acc_id='".$acc_id."' AND db='".$acc_id."_".$name."'";
    if($this->db->sql->query($query)===true){
      $return[0] = true;
      $return[1] = $acc_id."_".$name;
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      if($this->db->sql->query($query)===true){
	$return[0] = true;
	$return[1] = $acc_id."_".$name;
	goto end;
      }else{
	// other database error
	$error->add("database->update_passwd",$this->db->sql->error);
	$error->add("database->update_passwd",$query);
	$return[0] = true;
	$return[1] = $acc_id."_".$name;
	goto end;
      }
    }

    end:
    unset($query,$name,$acc_id,$passwd,$errmsg);
    return $return;
  }

  public function status($acc_id){ // grabs the current status of all databases for an acc_id
    global $error;

    // escape acc_id
    $esc = $this->db->esc($acc_id);
    unset($acc_id);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $acc_id = $esc[1];
    unset($esc);
    
    // check database exists and grab info
    $query = "SELECT * FROM userdbs WHERE acc_id='".$acc_id."'";
    if($result = $this->db->sql->query($query)){
      // see if databases exist....
      $num_rows = $result->num_rows;
      if($num_rows==0){
	$errmsg = "this account currently has no databases";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }else{
	$return[0] = true;
	while($row = $result->fetch_assoc()){
	  $return[1][$row['db']]['created'] = $row['created'];
	  $return[1][$row['db']]['modified'] = $row['modified'];
	}
	$result->close();
	unset($row,$result);
	goto moresize;
      }
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db->sql->query($query)){
	// see if databases exist....
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "this account currently has no databases";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}else{
	  $return[0] = true;
	  while($row = $result->fetch_assoc()){
	    $return[1][$row['db']]['created'] = $row['created'];
	    $return[1][$row['db']]['modified'] = $row['modified'];
	  }
	  $result->close();
	  unset($row,$result);
	  goto moresize;
	}
      }else{
	// other database error
	$error->add("database->status",$this->db->sql->error);
	$error->add("database->status",$query);
	$errmsg = "unable to query database to check if databases exist";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }

    moresize:
    foreach(array_keys($return[1]) as $database){
      $tmp_array = $this->database_size($database);
      if($tmp_array[0]){
	$return[1][$database] = array_merge($return[1][$database],$tmp_array[1]);
      }
      unset($tmp_array);
    }
    unset($database,$array);

    end:
    unset($query,$acc_id,$errmsg);
    return $return;
  }

  private function database_size($database){ // returns number of tables, and total size of table rows and indicies
    global $error;

    // Connect to information_schema database
    unset($this->db);
    $this->db = new db();
    $this->db->database = "information_schema";
    $this->db->connect();

    // escape acc_id
    $esc = $this->db->esc($database);
    unset($database);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $database = $esc[1];
    unset($esc);
    
    // grab info on database
    $query = "SELECT count(table_schema) as numtables, sum(data_length) as data_size, sum(index_length) as index_size FROM tables WHERE TABLE_SCHEMA='".$database."'";
    if($result = $this->db->sql->query($query)){
      $return[0] = true;
      while($row = $result->fetch_assoc()){
	$return[1]['numtables'] = $row['numtables'];
	$return[1]['data_size'] = $row['data_size'];
	$return[1]['index_size'] = $row['index_size'];
	$return[1]['total_size'] = $row['data_size'] + $row['index_size'];
      }
      $result->close();
      unset($row,$result);
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db->sql->query($query)){
	$return[0] = true;
	while($row = $result->fetch_assoc()){
	  $return[1]['numtables'] = $row['numtables'];
	  $return[1]['data_size'] = $row['data_size'];
	  $return[1]['index_size'] = $row['index_size'];
	  $return[1]['total_size'] = $row['data_size'] + $row['index_size'];
	}
	$result->close();
	unset($row,$result);
	goto end;
      }else{
	// other database error
	$error->add("database->database_size",$this->db->sql->error);
	$error->add("database->database_size",$query);
	$errmsg = "unable to query database to get database size";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }

    end:
    // Reconnect to information_schema database
    unset($this->db);
    $this->db = new db();
    $this->db->database = "default";
    $this->db->connect();
    unset($query,$database,$errmsg);
    return $return;
  }

  public function db_status($database){ // grabs the current status of each table for a database
    global $error;

    // Connect to information_schema database
    unset($this->db);
    $this->db = new db();
    $this->db->database = "information_schema";
    $this->db->connect();

    // escape acc_id
    $esc = $this->db->esc($database);
    unset($database);
    if(!$esc[0]){
      $return[0] = false;
      $return[1] = $esc[1];
      unset($esc);
      goto end;
    }
    
    $database = $esc[1];
    unset($esc);
    
    // check database exists and grab info
    $query = "select table_name,engine,row_format,table_rows,data_length,index_length,table_collation,table_comment from tables WHERE TABLE_SCHEMA='".$database."'";
    if($result = $this->db->sql->query($query)){
      $num_rows = $result->num_rows;
      if($num_rows==0){
	$errmsg = "this database currently has no tables";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      $return[0] = true;
      $i = 0;
      while($row = $result->fetch_assoc()){
	$return[1][$i]['table_name'] = $row['table_name'];
	$return[1][$i]['engine'] = $row['engine'];
	$return[1][$i]['row_format'] = $row['row_format'];
	$return[1][$i]['table_rows'] = $row['table_rows'];
	$return[1][$i]['data_length'] = $row['data_length'];
	$return[1][$i]['index_length'] = $row['index_length'];
	$return[1][$i]['table_collation'] = $row['table_collation'];
	$return[1][$i]['table_comment'] = $row['table_comment'];
	$i++;
      }
      $result->close();
      unset($row,$result,$i);
      goto end;
    }else{
      // query failed. attempt reconnect
      if(!$this->db->connect()){
	$errmsg = "database is not avaliable";
	$return[0] = false;
	$return[1] = $errmsg;
	goto end;
      }
      // and try again
      unset($result);
      if($result = $this->db->sql->query($query)){
	$num_rows = $result->num_rows;
	if($num_rows==0){
	  $errmsg = "this database currently has no tables";
	  $return[0] = false;
	  $return[1] = $errmsg;
	  goto end;
	}
	$return[0] = true;
	$i = 0;
	while($row = $result->fetch_assoc()){
	  $return[1][$i]['table_name'] = $row['table_name'];
	  $return[1][$i]['engine'] = $row['engine'];
	  $return[1][$i]['row_format'] = $row['row_format'];
	  $return[1][$i]['table_rows'] = $row['table_rows'];
	  $return[1][$i]['data_length'] = $row['data_length'];
	  $return[1][$i]['index_length'] = $row['index_length'];
	  $return[1][$i]['table_collation'] = $row['table_collation'];
	  $return[1][$i]['table_comment'] = $row['table_comment'];
	  $i++;
	}
	$result->close();
	unset($row,$result,$i);
	goto end;
      }else{
	// other database error
	$error->add("database->db_status",$this->db->sql->error);
	$error->add("database->db_status",$query);
	$errmsg = "unable to query database to get database size";
	$return[0] = false;
	$return[1] = $errmsg;
	unset($errmsg);
	goto end;
      }
    }

    end:
    // Reconnect to information_schema database
    unset($this->db);
    $this->db = new db();
    $this->db->database = "default";
    $this->db->connect();
    unset($query,$database,$errmsg);
    return $return;
  }
}
?>
