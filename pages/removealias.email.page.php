<?php
// work out domain and username from alias
$array = explode("@",$uri[2]);
$alias_domain = $array[1];
unset($array);
if(in_array($alias_domain,$domains[1])){
  if(array_key_exists('remove',$_POST) AND $_POST['remove'] == "Confirm remove alias"){
    $result = $email->remove_alias($uri[2]);
    if(!$result[0]){
      $errormsg = $result[1];
    }else{
      header("Location: /email/alias");
    }
    unset($alias_domain);
  }
?>
<div class="block">
  <h3>Remove alias</h3>
  <form action="/email/removealias/<?php echo $uri[2];?>" method="post">
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
	<td>Please confirm that you wish to completely remove the alias '<?php echo $uri[2];?>' from your email account.</td>
      </tr>
      <tr>
	<th class="center"><input type="submit" name="remove" value="Confirm remove alias"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>
<?php } ?>
<?php
include("alias.email.page.php");
?>