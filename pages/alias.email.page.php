<div class="block doublewidth">
  <h3>Aliases</h3>
  <?php
    $domains = $dns->get_domains_for_acc_id($auth->acc_id);
    if(!$domains[0]){
      $error = $domains[1];
    }else{
      asort($domains[1]);
      $email_list = array();
      $alias_list = array();
      foreach($domains[1] as $domain){
	/*$array = $email->get_emails_by_domain($domain);
	if($array[0] AND is_array($array[1])){
	  $email_list[$domain] = $array[1];
	}
	unset($array);*/
	$array = $email->get_aliases_by_domain($domain);
	if($array[0] AND is_array($array[1])){
	  $alias_list[$domain] = $array[1];
	}
	unset($array);
      }
      unset($domain);
      if(!count($alias_list)>0){
	$errmsg = "there are no aliases for your account";
	$errormsg = $errmsg;
      }else{
	if($uri[2]=="activate" OR $uri[2]=="deactivate" AND strlen($uri[3])>0){
	  // work out domain and username from alias
	  $array = explode("@",$uri[3]);
	  $alias_domain = $array[1];
	  unset($array);
	  if(in_array($alias_domain,$domains[1])){
	    if($uri[2]=="activate"){
	      $email->activate_alias($uri[3]);
	    }elseif($uri[2]=="deactivate"){
	      $email->deactivate_alias($uri[3]);
	    }
	    unset($alias_domain,$alias_list);
	    $alias_list = array();
	    foreach($domains[1] as $domain){
	      $array = $email->get_aliases_by_domain($domain);
	      if($array[0] AND is_array($array[1])){
		$alias_list[$domain] = $array[1];
	      }
	      unset($array);
	    }
	    unset($domain);
	  }
	}
      }
    }
  ?>
  <table>
    <?php
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Alias</th>";
      echo "\t\t<th>Destination</th>";
      echo "\t\t<th colspan=\"2\"></th>";
      echo "\t</tr>\n";
      echo "</thead>\n";
      if(strlen($errormsg)>0){
	echo "<tfoot>\n";
	echo "\t<tr>\n";
	echo "\t\t<td colspan=\"4\" class=\"error\">".$errormsg."</td>\n";
	echo "\t</tr>\n";
	echo "</tfoot>\n";
      }else{
	echo "<tbody>\n";
	foreach($alias_list as $key=>$domain){
	  echo "\t<tr>\n";
	  echo "\t\t<th colspan=\"4\">".$key."</th>\n";
	  echo "\t</tr>\n";
	  foreach($domain as $account){
	    echo "\t<tr>\n";
	    echo "\t\t<td>".$account['alias']."</td>\n";
	    echo "\t\t<td>".str_replace(",","<br>",$account['destination'])."</td>\n";
	    if($account['active'] == "active"){
	      echo "\t\t<td class=\"center\"><a href=\"/email/alias/deactivate/".$account['alias']."\">Deactivate</a></td>\n";
	    }elseif($account['active'] == "inactive"){
	      echo "\t\t<td class=\"center\"><a href=\"/email/alias/activate/".$account['alias']."\">Activate</a></td>\n";
	    }
	    echo "\t\t<td class=\"center\"><a href=\"/email/removealias/".$account['alias']."\">Remove</a></td>\n";
	    echo "\t</tr>\n";
	  }
	  unset($account,$key);
	}
	unset($domain);
	echo "</tbody>\n";
      }
    ?>
  </table>
</div>
