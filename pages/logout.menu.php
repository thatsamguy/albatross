<?php error_log(__FILE__);?>
<?php
/* 
* Albatross Manager
* 
* Default Page (menu)
* 
* Description:
*  Configures page title and menu
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$pt[]['logout.menu.php :: start'] = microtime(true);
$thispage['title'] = 'Logout';
$thispage['data'] = false;
$auth->logout();
header('Location: /login.php');
$pt[]['logout.menu.php :: end'] = microtime(true);
?>