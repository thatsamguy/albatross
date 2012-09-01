<?php
if(array_key_exists('add',$_POST) AND $_POST['add'] == "Add alias"){
  if($_POST['alias']=="" OR $_POST['destination']==""){
    //$error = "Both the alias and destination must have a value";
  }elseif(in_array(strtolower($_POST['domain']),$domains[1])){
    if($_POST['alias'] == "*" AND $_POST['destination']!=""){
      $alias = "@".strtolower($_POST['domain']);
    }else{
      $alias = $_POST['alias']."@".strtolower($_POST['domain']);
    }
    // TODO: Add check for a real destination (in correct format)
    $result = $email->add_alias($alias,$_POST['destination']);
    if(!$result[0]){
      $errormsg = $result[1];
    }
  }else{
    $errormsg = "Aliases can only be added for domains that you control";
  }
}
if($domains[0]){
?>
<div class="block midwidth">
  <h3>Add alias</h3>
  <form action="/email/addalias" method="post">
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
	<th>Alias</th>
	<td><input class="text" size="15" type="textbox" name="alias">&nbsp;@
	  <select name="domain">
	    <?php foreach($domains[1] as $item){
	      echo "\t\t<option value=\"".$item."\">".$item."</option>\n";
	    } ?>
	  </select>
	</td>
      </tr>
      <tr>
	<th>Destination</th>
	<td><input class="text" size="40" type="textbox" name="destination"></td>
      </tr>
      <tr>
	<td>Example</td>
	<td>test@cyprix.com.au -> realaccount@cyprix.com.au</td>
      </tr>
      <tr>
	<th colspan="2">If you wish to have a wildcard alias to capture all email for that domain please enter * in the alias box.</th>
      </tr>
      <tr>
	<th colspan="2" class="center"><input type="submit" name="add" value="Add alias"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>
<?php }
unset($domains);
?>
<?php include("alias.email.page.php");?>
