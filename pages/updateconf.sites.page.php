<?php
if (array_key_exists('apply', $_POST) and $_POST['apply'] == 'Confirm apply configuration' and $sitename != '' and strlen($sitename) < 256) {
    $thissite = $site_data[1][$sitename];
    if ($thissite['profile'] != 'custom') {
        $result = $site->update_profile($auth->acc_id, $sitename, $thissite['profile']);
    } else {
        $result[0] = false;
        $errmsg = 'custom profiles are not able to be automatically applied';
        $result[1] = $errmsg;
        unset($errmsg);
    }
    if (!$result[0]) {
        $errormsg = $result[1];
    } else {
        header('Location: /sites/view/'.$sitename);
    }
    unset($result);
}
?>
<div class="block">
  <h3>Apply Configuration</h3>
  <form action="/sites/updateconf/<?php echo $sitename;?>" method="post">
  <table>
    <?php if (strlen($errormsg) > 0) {
    ?>
    <tfoot>
      <tr>
	<td class="error"><?php echo $errormsg;
    ?></td>
      </tr>
    </tfoot>
    <?php 
} ?>
    <tbody>
      <tr>
	<td>Please confirm that you wish to apply the current configuration to the site '<?php echo $sitename;?>'.<br><br>This will change all settings to match the ones you have set here.<br/><p class="error">Note: all previous configuration will be lost!</p></td>
      </tr>
      <tr>
	<th class="center"><input type="submit" name="apply" value="Confirm apply configuration"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>