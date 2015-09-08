<?php
/*
* Albatross Manager
*
* Index Page
*
* Description:
*  Base page that manages all functions
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php $pt[]['index.php :: start'] = microtime(true); ?>
<?php
set_include_path(realpath('lib').':'.realpath('conf').':'.realpath('pages'));
?>
<?php
// Check authentication
include_once 'auth.class.php';
include_once 'config.class.php';
$auth = new auth();
$pt[]['index.php :: include auth, config'] = microtime(true);

if ($_COOKIE['SESSION']) {
    $login = $auth->authenticate_session();
    if (!$login[0]) {
        header('Location: /login.php');
    }
} else {
    header('Location: /login.php');
}
unset($login);
$pt[]['index.php :: check auth'] = microtime(true);
?>
<?php
$uri = trim($_SERVER['REQUEST_URI'], '/');
$uri = explode('/', $uri);
$uri[0] = strtolower($uri[0]);
if (count($uri) > 1) {
    $uri_fullcase[1] = $uri[1];
    $uri[1] = strtolower($uri[1]);
} else {
    $uri[1] = '';
}
if (count($uri) > 2) {
    $uri_fullcase[2] = $uri[2];
    $uri[2] = strtolower($uri[2]);
} else {
    $uri[2] = '';
}
if (count($uri) > 3) {
    $uri_fullcase[3] = $uri[3];
    $uri[3] = strtolower($uri[3]);
} else {
    $uri[3] = '';
}
if (count($uri) > 4) {
    $uri_fullcase[4] = $uri[4];
    $uri[4] = strtolower($uri[4]);
} else {
    $uri[4] = '';
}
$page = $uri[0];
$subpage = $uri[1];
if ($page == '') {
    $page = 'default';
}
$errormsg = '';
$pt[]['index.php :: setup uri and global vars'] = microtime(true);
?><!DOCTYPE html>
<html>
<head>
  <title>Albatross Manager</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <link type="text/css" href="/style.css" rel="stylesheet" media="screen,projection" />
</head>
<body>
<div id="container">
  <div id="header">
    <a href="/"><img class="logo" src="/images/albatross_logo.png" title="Albatross Manager" alt="Albatross Manager"></a>
    <div class="menu">
      <div class="right"><a href="/logout"><img src="/images/system-log-out.png" alt=""><br>Logout</a></div>
      <div class="right">Username<br><?php echo $auth->uname;?><br>AccountID<br style="line-height:1.6em">z<?php echo $auth->acc_id;?></div>
      <div><a href="/"><img src="/images/preferences-contact-list.png" alt=""><br>My Account</a></div>
      <div><a href="/"><img src="/images/document-sign.png" alt=""><br>Pay Invoices</a></div>
      <div><a href="/sites"><img src="/images/document-open-remote.png" alt=""><br>Manage Sites</a></div>
      <div><a href="/email"><img src="/images/internet-mail.png" alt=""><br>Email Addresses</a></div>
      <div><a href="/dns"><img src="/images/view-web-browser-dom-tree.png" alt=""><br>Domain Names</a></div>
      <div><a href="/database"><img src="/images/server-database.png" alt=""><br>Databases</a></div>
      <div><a href="/stats"><img src="/images/office-chart-bar-stacked.png" alt=""><br>Statistics</a></div>
      <?php /*<div><a href="/logs"><img src="/images/text-x-log.png" alt=""><br>Logs</a></div>*/ ?>
      <div><a href="/archive"><img src="/images/utilities-file-archiver.png" alt=""><br>Archives &amp; Backups</a></div>
      <div><a href="/"><img src="/images/help-about.png" alt=""><br>Support</a></div>
      <div><a href="<?php echo $conf->webmail;?>" target="_new"><img src="/images/mail-read.png" alt=""><br>Webmail</a></div>
    </div>
  </div>
  <div id="main">
<?php
/*  List of hardcoded dynamic url roots */
$pages = array('logout', 'dns', 'email', 'stats', 'logs', 'database', 'sites', 'archive');
$thispage['menu'] = array();
$thispage['menu2'] = array();
$pt[]['index.php :: menu and page array'] = microtime(true);

if (in_array($page, $pages)) {
    // Page Exists
  include $page.'.menu.php';
} elseif ($page == 'default') {
    // Default Page
  include $page.'.menu.php';
} else {
    // Page does not exist... anywhere!
  header('Location: /');
}
unset($pages);
$pt[]['index.php :: post include page'] = microtime(true);
?>
<?php // Start individual page title and menu ?>
    <div class="pagetitle fullwidth"><h1><?php echo $thispage['title'];?></h1></div>
    <?php
      if (count($thispage['menu']) > 0) {
          echo "<div class=\"minormenu fullwidth\">\n";
          echo "\t<div class=\"menu\">\n";
          foreach ($thispage['menu'] as $item) {
              if (array_key_exists('new_window', $item) and $item['new_window'] == true) {
                  $newwindow = ' target="_new"';
              } else {
                  $newwindow = '';
              }
              echo "\t\t<div><a href=\"".$item['link'].'"'.$newwindow.'><img src="/images/'.$item['image'].'" alt=""><br>'.$item['title']."</a></div>\n";
          }
          echo "\t</div>\n";
          echo "</div>\n";
      }
      if (count($thispage['menu2']) > 0) {
          echo "<div class=\"minormenu fullwidth\">\n";
          if (strlen($thispage['menu2']['title']) > 0) {
              echo "\t<h3>&nbsp;".$thispage['menu2']['title']."</h3>\n";
              unset($thispage['menu2']['title']);
          }
          echo "\t<div class=\"menu\">\n";
          foreach ($thispage['menu2'] as $item) {
              echo "\t\t<div><a href=\"".$item['link'].'"><img src="/images/'.$item['image'].'" alt=""><br>'.$item['title']."</a></div>\n";
          }
          echo "\t</div>\n";
          echo "</div>\n";
      }
    ?>
<?php // End individual page title and menu ?>
<?php $pt[]['index.php :: post included page menus'] = microtime(true); ?>
<?php // Start individual page data ?>
<?php if ($thispage['data']) {
    include $thispage['data'];
}?>
<?php // End individual page data ?>
<?php $pt[]['index.php :: post include thispage data'] = microtime(true); ?>
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
</html><?php $pt[]['index.php :: end'] = microtime(true); ?><?php
// If enabled, display page tracing in hidden html
if ($conf->pt) {
    echo "<!--\n";
    echo "Page Trace\n";
    echo "Order\tTime(s)\tSplit(ms)\tTrace\n";
    $ptkey = array_keys($pt[0]);
    $starttime = $pt[0][$ptkey[0]];
    $prevtime = $starttime;
    foreach ($pt as $key => $array) {
        foreach ($array as $key2 => $value) {
            $pt[$key][$key2] = $value - $starttime;
            echo $key.":\t".round($pt[$key][$key2], 3)."\t".round(($value - $prevtime) * 1000, 3)."\t".$key2."\n";
            $prevtime = $value;
        }
    }
    echo 'Totaltime: '.round(($prevtime - $starttime), 3)."s\n";
    echo "-->\n";
    unset($ptkey, $starttime, $totaltime, $key, $key2, $array, $value, $prevtime);
}
unset($pt);
?>
