<?php require_once('components/session.php'); require_once("models/header.php"); ?>
<?php
if(!isUserLoggedIn()){
?>
<div id="content" role="main">
  <?php require_once("login.php"); ?>
</div>
<?php
} else {
  require_once("dbconnect/LearnMathDB.php");
?>
<div id="content" role="main">
  <div id="menu">
    <div id="greeting">Hi, <?php echo LearnMathDB::getUserName($loggedInUser->user_id); ?>!</div>
    <a href="problem.php">do math!</a>
    <a href="statistics.php">track progress</a>
  </div>

</div>
<?php } ?>

<?php require_once("components/footer.php"); ?>
</body>
</html>
