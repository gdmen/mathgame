<?php require_once('components/session.php'); ?>
<?php
  require_once('dbconnect/Mysql.php');
  Mysql::connect();
  require_once("dbconnect/LearnMathDB.php");
  
  if(!empty($_POST['add_video'])){
    require_once('components/input_sanitizing.php');
    $form_fields = array('title' => '',
                         'youtube_link' => '',
                         'start' => '',
                         'end' => '');
    foreach($form_fields as $field => $value){
      if(!isset($_POST[$field])){
        $_POST[$field] = '';
      }
      $form_fields[$field] = get_request_var($field);
      $var = $field;
      $$var = $_POST[$field];
    }
    LearnMathDB::addVideo($title, $youtube_link, $start, $end);
  }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">

  <title></title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" type="text/css" href="css/styles.css">

  <script src="js/libs/modernizr-2.5.3.min.js"></script>
</head>
<body>
  <div id="pagewrap">

    <div id="non-footer">

      <header id="header">

        <hgroup>
          <h1 id="site-title">Add Video</h1>
          <a id="top-right-button" href="index.php">Menu</a>
        </hgroup>

      </header>

      <div id="content" role="main">
        <div id="menu">
          <form id="add_video" method="post">
            <div class="clearfix">
              <label>Title</label>
              <input type="text" maxlength="128" value="" name="title">
              <label>Youtube URL</label>
              <input type="text" maxlength="128" value="" name="youtube_link">
              <label>Start Time</label>
              <input type="text" maxlength="5" value="" name="start">
              <label>End Time</label>
              <input type="text" maxlength="5" value="" name="end">
            </div>
            <input type="submit" id="submit" name="add_video" value="add video">
          </form>
        <?php
        ?>
        </div>
      </div>

    </div>

  </div>

  <?php require("components/footer.php"); ?>

  <!-- JavaScript at the bottom for fast page loading -->
  <script src="js/libs/jquery-1.7.1.min.js"></script>
  <script src="js/index.js"></script>
  <!-- end scripts -->
</body>
</html>
<?php
  Mysql::disconnect();
?>
