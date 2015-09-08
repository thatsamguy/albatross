<?php
/*
* Albatross Manager
*
* Login Page
*
* Description:
*  Authentication system
*
* Copyright 2011 Cyprix Enterprises
*/

set_include_path(realpath('lib').':'.realpath('conf'));

// Check authentication
include_once 'auth.class.php';
$auth = new auth();
$error = '';

if (array_key_exists('username', $_POST) and array_key_exists('password', $_POST)) {
    $login = $auth->authenticate_user($_POST['username'], $_POST['password']);
    if ($login[0]) {
        header('Location: /');
    } else {
        $error = $login[1];
    }
} elseif (array_key_exists('SESSION', $_COOKIE)) {
    $login = $auth->authenticate_session();
    if ($login[0]) {
        header('Location: /');
    } else {
        $error = $login[1];
    }
}
unset($login);

?><!DOCTYPE html>
<html>
<head>
  <title>Albatross Manager :: Login</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <link type="text/css" href="style.css" rel="stylesheet" media="screen,projection" />
</head>
<body>
<div id="container">
  <div id="header">
    <img class="logo" src="images/albatross_logo.png" title="Albatross Manager" alt="Albatross Manager">
  </div>
  <div id="main">
    <div class="pagetitle fullwidth"><h1>Login to Albatross</h1></div>
    <div class="fullwidth">
      <form action="/login.php" method="post">
	<h3>Please login to use the Albatross Manager</h3>
	<table id="login">
	  <tfoot>
	    <tr><th colspan="2"><input class="submit" type="submit" value="Login" tabindex="4"></th></tr>
	  </tfoot>
	  <tbody>
	    <tr>
	      <td class="right"><label for="username">Username</label></td>
	      <td><input type="text" size="15" id="username" name="username" tabindex="1"></td>
	    </tr>
	    <tr>
	      <td class="right"><label for="password">Password</label></td>
	      <td><input type="password" size="15" id="password" name="password" tabindex="2"></td>
	    </tr>
	    <tr>
	      <td colspan="2" class="center"><input type="checkbox" id="remember" name="remember" tabindex="3">&nbsp;<label for="remember">Remember me</label></td>
	    </tr>
	  </tbody>
	</table>
      </form>
      <?php if (strlen($error) > 0) {
    ?><h4 class="error"><?php echo $error;
    ?></h4><?php
} ?>
    </div>
  </div>
  <div id="footer">
    <ul>
    <li>&copy; 2011 <a href="http://www.cyprix.com.au/">Cyprix Enterprises</a></li>
    <li>Icons by <a href="http://www.oxygen-icons.org/">Oxygen</a></li>
    <li><a href="/">About Albatross</a></li>
    <li><a href="/">Contact Us</a></li>
    </ul>
  </div>
</div>
</body>
</html>
