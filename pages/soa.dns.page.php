<?php
$domains = $dns->get_domains_for_acc_id($auth->acc_id);
if(!$domains[0]){
  $error = $domains[1];
  goto postprocessing;
}

$domain_administrator_email = "";
$refresh = "";
$retry = "";
$expire = "";
$minimum = "";

if(array_key_exists('update',$_POST) AND $_POST['update'] == "Update SOA"){
  if(!in_array($domain,$domains[1])){
    $errmsg = "Unable to alter domain '".$domain."' due to lack of permission";
    goto postprocessing;
  }else{
    if($_POST['section'] == "domain administrator email"){
      $domain_administrator_email = $_POST['value'];
    }
    if($_POST['section'] == "refresh"){
      $refresh = $_POST['value'];
    }
    if($_POST['section'] == "retry"){
      $retry = $_POST['value'];
    }
    if($_POST['section'] == "expire"){
      $expire = $_POST['value'];
    }
    if($_POST['section'] == "minimum"){
      $minimum = $_POST['value'];
    }
    $result = $dns->update_soa($domain_id,"",$domain_administrator_email,$refresh,$retry,$expire,$minimum);
    if(!$result[0]){
      $errormsg = $result[1];
      goto postprocessing;
    }
  }
}

postprocessing:
?>
<div class="block">
  <h3>SOA<?php if($domain_id>0){ echo " for ".$domain;}?></h3>
  <table>
    <tfoot>
      <tr>
	<td colspan="2">Minimum is the time-to-live (TTL) value.<br>All values are given in seconds. i.e. 300 = 5 minutes</td>
      </tr>
    </tfoot>
    <?php
      $soa = $dns->get_domain_soa($domain_id);
      echo "<tbody>\n";
      if($soa[0]){
	foreach($soa[1] as $key=>$value){
	  echo "\t<tr>\n";
	  echo "\t\t<th>".$key."</th>\n";
	  echo "\t\t<td>".$value."</td>\n";
	  echo "\t</tr>\n";
	}
	unset($key,$value);
      }else{
	echo "\t<tr>\n";
	echo "\t\t<th colspan=\"2\" class=\"error\">".$soa[1]."</th>\n";
	echo "\t</tr>\n";
      }
    ?>
    </tbody>
  </table>
</div>
<div class="block midwidth">
  <h3>Update SOA<?php if($domain_id>0){ echo " for ".$domain;}?></h3>
  <form action="/dns/soa/<?php echo $domain_id;?>" method="post">
  <table>
    <?php if(strlen($errormsg)>0){ ?>
    <tfoot>
      <tr>
	<td colspan="3" class="error"><?php echo $errormsg;?></td>
      </tr>
    </tfoot>
    <?php } ?>
    <tbody>
      <tr>
	<th>SOA Section</th>
	<th>Value</th>
      </tr>
      <tr>
	<td>
	  <select name="section">
	    <?php 
	    foreach($soa[1] as $key=>$value){
	      if($key != "primary dns" AND $key != "serial"){
		echo "\t\t<option value=\"".$key."\">".$key."</option>\n";
	      }
	    }
	    unset($key,$value); ?>
	  </select>
	</td>
	<td><input class="text" size="20" type="textbox" name="value"></td>
      </tr>
      <tr>
	<td>Example (1 Hour)</td>
	<td>3600</td>
      </tr>
      <tr>
	<td>Example (1 Day)</td>
	<td>86400</td>
      </tr>
      <tr>
	<th colspan="3" class="center"><input type="submit" name="update" value="Update SOA"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>