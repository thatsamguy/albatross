<div class="block midwidth">
  <h3>Domains</h3>
  <?php
    $domains = $dns->get_domains_for_acc_id($auth->acc_id);
  ?>
  <table>
    <?php
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Domain name</th>\n";
      echo "\t\t<th>Records</th>\n";
      echo "\t</tr>\n";
      echo "</thead>\n";
      if(!$domains[0]){
	echo "<tfoot>\n";
	echo "\t<tr>\n";
	echo "\t\t<td colspan=\"2\" class=\"error\">".$domains[1]."</th>\n";
	echo "\t</tr>\n";
	echo "</tfoot>\n";
      }
      asort($domains[1]);
      foreach($domains[1] as $domain_id=>$domain){
	$records = $dns->get_records_from_domain($domain_id);
	echo "<tbody>\n";
	echo "\t<tr>\n";
	echo "\t\t<th><a href=\"/dns/domain/".$domain_id."\">".$domain."</a></th>\n";
	echo "\t\t<td class=\"center\">".count($records[1])."</td>\n";
	echo "\t\t<td class=\"center\"><a href=\"/dns/domain/".$domain_id."\">View Records</a></td>\n";
	echo "\t</tr>\n";
	if(!$records[0]){
	  echo "\t<tr>\n";
	  echo "\t\t<th colspan=\"4\" class=\"error=\">".$records[1]."</th>\n";
	  echo "\t</tr>\n";
	}
	unset($records,$soa);
      }
    ?>
    </tbody>
  </table>
</div>
<?php unset($domains,$domain_id,$domain);?>
