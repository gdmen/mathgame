<?php require_once('components/session.php'); require_once("models/header.php"); ?>
<?php
  require_once("dbconnect/LearnMathDB.php");

  // Check if reward is earned, redirect if so
  function checkReward(){
    if($_SESSION['current_solved'] >= $_SESSION['settings']['solve_for_reward']){
      header('Location: reward.php');
    }
  }
  // Redirect to rewards page if user is manually returning to the problems page after earning a reward
  checkReward();

  $form_fields = array('input_answer' => '',
                       'operand_one' => '',
                       'operator' => '',
                       'operand_two' => '',
                       'start_time' => '',
                       'form_id' => '');
  
    
  $continuation = False;
  if(!empty($_POST['submit'])){
    require_once('components/input_sanitizing.php');

    foreach($form_fields as $field => $value){
      if(!isset($_POST[$field])){
        $_POST[$field] = '';
      }
      $form_fields[$field] = get_request_var($field);
      $var = $field;
      $$var = $_POST[$field];
    }
    
    if(!isset($_SESSION['form_id']) || $_SESSION['form_id'] != $form_id){
      $correct = LearnMathDB::checkAnswer($loggedInUser->user_id, $_SESSION['current_problem'], $input_answer, time() - $start_time);
      if($correct){
        setNewProblem();
        // Make sure this problem is not resubmitted
        $_SESSION['form_id'] = $form_id;
        // If reward earned, redirect to reward page
        checkReward();
      }
    } else if(isset($start_time)){
      $continuation = True;
    }
  }

  $problem = LearnMathDB::getProblem($_SESSION['current_problem']);//LearnMathDB::getRandomProblemID($_SESSION['settings'], -1);
?>

<div id="content" role="main">
  <!--<h1 id="site-title">Solve <?php echo $_SESSION['settings']['solve_for_reward'] - $_SESSION['current_solved']; ?> more!</h1>-->
  <div id="progress">
    <div id="bar" style="width:<?php echo 100 * $_SESSION['current_solved'] / $_SESSION['settings']['solve_for_reward']; ?>%">&nbsp;</div>
  </div>
<?php
  if(isset($correct) && !$correct){
?>
  <?php if(strlen($input_answer) > 0) {?><div id="not-correct"><span id="number"><?php echo $input_answer; ?></span> is not correct. Try again.</div><?php } ?>
<?php
  }
?>
  <form id="problem" method="post">
    <div id="operand_one" class="equation"><?php echo $problem['operand_one']; ?></div>
    <div id="operator" class="equation"><?php echo $problem['operator']; ?></div>
    <div id="operand_two" class="equation"><?php echo $problem['operand_two']; ?></div>
    <div id="equals" class="equation">=</div>
    <input type="hidden" name="problem_id" value="<?php echo $problem['problem_id']; ?>">
    <input type="hidden" name="operand_one" value="<?php echo $problem['operand_one']; ?>">
    <input type="hidden" name="operator" value="<?php echo $problem['operator']; ?>">
    <input type="hidden" name="operand_two" value="<?php echo $problem['operand_two']; ?>">
    <input type="hidden" name="start_time" value="<?php echo $continuation ? $start_time : time(); ?>">
    <input type="hidden" name="form_id" value="<?php echo md5(uniqid(rand(), true)); ?>">
    <input type="text" name="input_answer" maxlength="3" class="equation" id="input_answer">
    <input type="submit" name="submit" value="ok" class="submit">
  </form>
<?php
?>
</div>

<?php require_once("components/footer.php"); ?>
<script src="js/problem.js"></script>
<!-- end scripts -->
</body>
</html>
