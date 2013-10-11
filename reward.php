<?php require_once('components/session.php'); require_once("models/header.php"); ?>
<?php
  if($_SESSION['current_solved'] < $_SESSION['settings']['solve_for_reward']){
    header('Location: problem.php');
  }
  require_once("dbconnect/LearnMathDB.php");
				  
  $form_fields = array('chosen' => '',
                       'id' => '',
                       'yt_id' => '',
                       's' => '',
                       'e' => '');
  if(!empty($_GET['chosen'])){
    require_once('components/input_sanitizing.php');

    foreach($form_fields as $field => $value){
      if(!isset($_GET[$field])){
        $_GET[$field] = '';
      }
      $form_fields[$field] = get_request_var($field);
      $var = $field;
      $$var = $_GET[$field];
    }
  }
?>
<div id="content" role="main">
<!--<h1 id="site-title"><?php echo isset($chosen) ? $chosen : 'Choose a video!'; ?></h1>-->
<div id="progress">
  <div id="bar" style="width:<?php echo 100 * $_SESSION['current_solved'] / $_SESSION['settings']['solve_for_reward']; ?>%">&nbsp;</div>
</div>
  <div id="reward">
  <?php
  if(!isset($chosen)){
    $videos = LearnMathDB::getRandomVideos(3);
    foreach($videos as $title => $video_data){
  ?>
    <a href="reward.php?chosen=<?php echo $title; ?>&id=<?php echo $video_data['id']; ?>&yt_id=<?php echo $video_data['youtube_id']; ?>&s=<?php echo $video_data['start']; ?>&e=<?php echo $video_data['end']; ?>"><?php echo $title; ?></a>
  <?php
    }
  }else{
    // PROBLEM: currently could be subverted via refreshes
    LearnMathDB::recordReward($loggedInUser->user_id, $_SESSION['settings']['solve_for_reward'], $id);
  ?>
    <div id="video_wrapper">
      <div id="player"></div>
      <div id="video" title="<?php echo $chosen; ?>" yt_id="<?php echo $yt_id; ?>" start="<?php echo $s; ?>" end="<?php echo $e; ?>"></div>
    </div>
  <?php
  }
  ?>
  </div>
</div>

<?php require_once("components/footer.php"); ?>
<script src="http://www.youtube.com/player_api"></script>
<script src="js/reward.js"></script>
<!-- end scripts -->
</body>
</html>