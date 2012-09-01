<?php
if(array_key_exists('remove',$_POST) AND $_POST['remove'] == "Confirm remove site" AND $sitename!="" AND strlen($sitename)<256){
  $result = $site->remove($auth->acc_id,$sitename);
  if(!$result[0]){
    $errormsg = $result[1];
  }else{
    header("Location: /sites");
  }
  unset($result);
}
?>
<div class="block">
  <h3>Remove site</h3>
  <form action="/sites/remove/<?php echo $sitename;?>" method="post">
  <table>
    <?php if(strlen($errormsg)>0){ ?>
    <tfoot>
      <tr>
	<td class="error"><?php echo $errormsg;?></td>
      </tr>
    </tfoot>
    <?php } ?>
    <tbody>
      <tr>
	<td>Please confirm that you wish to completely remove the site '<?php echo $sitename;?>' from your account.<br/>This will store a final backup in archives.<br/><p class="error">Note: all data from this site will be deleted!</p></td>
      </tr>
      <tr>
	<th class="center"><input type="submit" name="remove" value="Confirm remove site"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>