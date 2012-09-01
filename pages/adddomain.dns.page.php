<?php
if(array_key_exists('add',$_POST) AND $_POST['add'] == "Add domain"){
  $result = $dns->add_domain($_POST['domain_name'],$auth->acc_id);
  if(!$result[0]){
    $errormsg = $result[1];
  }
}
?>
<div class="block">
  <h3>Add new domain</h3>
  <form action="/dns/adddomain" method="post">
  <table>
    <?php if(strlen($errormsg)>0){ ?>
    <tfoot>
      <tr>
	<td colspan="2" class="error"><?php echo $errormsg;?></td>
      </tr>
    </tfoot>
    <?php } ?>
    <tbody>
      <tr>
	<th>Domain Name</th>
	<td><input class="text" size="20" type="textbox" name="domain_name"></td>
      </tr>
      <tr>
	<td>Example</td>
	<td>cyprix.com.au</td>
      </tr>
      <tr>
	<th colspan="2" class="center"><input type="submit" name="add" value="Add domain"></th>
      </tr>
      <tr>
	<td colspan="2"><p>Once you add your domain, you will need to delegate your domain to the DNS servers.</p>The severs are listed on the "DNS Servers" page for each domain.</p></td>
      </tr>
    </tbody>
  </table>
  </form>
</div>
<?php include("dns.page.php");?>