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
  <div id="reward">
    <div id="video_wrapper">
  <?php
    $videos = LearnMathDB::getAllVideos();
    foreach ($videos as $title=>$video){
      echo $video['id'];
  ?>
      <div class="video" id="<?php echo $video['id'];?>" title="<?php echo $title; ?>" yt_id="<?php echo $video['youtube_id']; ?>" start="<?php echo $video['start']; ?>" end="<?php echo $video['end']; ?>"></div>
  <?php
    }
  ?>
    </div>
  </div>
</div>
<?php } ?>

<?php require_once("components/footer.php"); ?>
<script src="js/libs/swfobject.js"></script>
<script src="js/libs/YoutubePlayer.min.js"></script>
<script src="js/check_videos.js"></script>
<!-- end scripts -->
</body>
</html>
