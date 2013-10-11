<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
  <meta charset="utf-8">

  <title></title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>

<body>
  <div id="pagewrap">

    <div id="non-footer">
<header id="header" class="clearfix">
  <hgroup>
    <a href='index.php'><h1 id="site-title"><?php echo $websiteName; ?></h1></a>
<?php
if(!isUserLoggedIn()){
?>
  </hgroup>
</header>
<?php
} else {
?>
    <div id="options">
      <a href='logout.php'>logout</a>
      <a href='index.php'><i class="icon-home"></i></a>
      <a href='difficulty_settings.php'><i class="icon-cog"></i></a>
    </div>
<?php
if (isUserLoggedIn() && $loggedInUser->checkPermission(array(2))){
?>
	<div id="admin-menu">
    <a href='admin_configuration.php'>Configuration</a>|
    <a href='admin_users.php'>Users</a>|
    <a href='admin_permissions.php'>Permissions</a>|
    <a href='admin_pages.php'>Pages</a>
  </div>
<?php } ?>
  </hgroup>
</header>
<?php } ?>