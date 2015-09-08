<?php error_log(__FILE__);?>
<?php
$errormsg = '';
if (array_key_exists('add', $_POST) and $_POST['add'] == 'Create site') {
    if ($_POST['site_name'] == '') {
        $errormsg = 'the site name must have a value';
    } elseif ($_POST['default_domain'] == '') {
        $errormsg = 'the primary domain name must have a value';
    } elseif ($_POST['profile'] == '') {
        $errormsg = 'a profile must be selected';
    } else {
        $result = $site->add($auth->acc_id, $_POST['site_name'], $_POST['default_domain'], $_POST['profile']);
        if (!$result[0]) {
            $errormsg = $result[1];
        } else {
            header('Location: /sites');
        }
    }
}
?>
<div class="block midwidth">
  <h3>New site</h3>
  <form action="/sites/addsite" method="post">
  <table>
    <?php if (strlen($errormsg) > 0) {
    ?>
    <tfoot>
      <tr>
	<td colspan="2" class="error"><?php echo $errormsg;
    ?></td>
      </tr>
    </tfoot>
    <?php 
} ?>
    <tbody>
      <tr>
	<th>Site Name</th>
	<td><input class="text" size="30" maxlength="100" type="textbox" name="site_name"></td>
      </tr>
      <tr>
	<th>Primary Domain Name</th>
	<td><input class="text" size="30" maxlength="255" type="textbox" name="default_domain"></td>
      </tr>
      <tr>
	<th>Configuration Profile</th>
	<td>
	  <select name="profile">
	  <option value="default">Default</option>
	  <option value="wordpress">Wordpress</option>
	  <option value="mediawiki">Mediawiki</option>
	  </select>
	</td>
      </tr>
      <tr>
	<td>Example</td>
	<td>Site Name: myfirstwebsite<br/>
	    Primary Domain Name: blog.website.com<br/>
	    Configuration Profile: Wordpress</td>
      </tr>
      <tr>
	<th colspan="2" class="center"><input type="submit" name="add" value="Create site"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>