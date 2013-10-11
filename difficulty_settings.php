<?php require_once('components/session.php'); require_once("models/header.php"); ?>
<?php
require_once("dbconnect/LearnMathDB.php");
if(!empty($_POST['settings'])){
  require_once('components/input_sanitizing.php');
  $form_fields = array('solve_for_reward' => '');
  for($i = 1; $i <= 4; $i++){
    $form_fields = array_merge($form_fields,
                               array('_' . $i . '_min_operand' => '',
                                     '_' . $i . '_max_operand' => '',
                                     '_' . $i . '_min_answer' => '',
                                     '_' . $i . '_max_answer' => ''));
  }
  foreach($form_fields as $field => $value){
    if(!isset($_POST[$field])){
      $_POST[$field] = '';
    }
    $form_fields[$field] = get_request_var($field);
    $var = $field;
    $$var = intval($_POST[$field]);
  }
  
  $_SESSION['settings']['solve_for_reward'] = $solve_for_reward;
  
  $_SESSION['settings'][1]['enabled'] = isset($_POST['_1_enabled']) ? 1 : 0;
  $_SESSION['settings'][2]['enabled'] = isset($_POST['_2_enabled']) ? 1 : 0;
  $_SESSION['settings'][3]['enabled'] = isset($_POST['_3_enabled']) ? 1 : 0;
  $_SESSION['settings'][4]['enabled'] = isset($_POST['_4_enabled']) ? 1 : 0;
  
  
  setSettings(1, $_1_min_operand, $_1_max_operand, $_1_min_answer, $_1_max_answer);
  setSettings(2, $_2_min_operand, $_2_max_operand, $_2_min_answer, $_2_max_answer);
  setSettings(3, $_3_min_operand, $_3_max_operand, $_3_min_answer, $_3_max_answer);
  setSettings(4, $_4_min_operand, $_4_max_operand, $_4_min_answer, $_4_max_answer);
  
  saveSettings();
}
?>
<div id="content" role="main">
  <div id="menu">
    <form id="settings" method="post">
      <div id="solve_number" class="clearfix">
        <label>Number to solve:</label>
        <input type="text" maxlength="4" value="<?php echo $_SESSION['settings']['solve_for_reward'];?>" name="solve_for_reward">
      </div>
      <?php foreach(LearnMathDB::getAllOperations() as $operation){ ?>
      <div id="<?php echo $operation['id']; ?>_subform" class="subform">
        <hr />
        <div class="toggle clearfix">
          <label>Enable <?php echo $operation['name']; ?>:</label>
          <input type="checkbox" name="_<?php echo $operation['id']; ?>_enabled" class="enabled"<?php if($_SESSION['settings'][$operation['id']]['enabled']) echo ' checked';?>>
        </div>
        <div class="equation-container clearfix">
          <div id="operand_one" class="equation">x</div>
          <div id="operator" class="equation"><?php echo $operation['operator']; ?></div>
          <div id="operand_two" class="equation">y</div>
          <div id="equals" class="equation">=</div>
          <div class="equation" id="answer">z</div>
        </div>
        <div class="inputs clearfix">
          <label class="left_col">min:</label>
          <input type="text" name="_<?php echo $operation['id']; ?>_min_operand" class="left_col" maxlength="3" value="<?php echo $_SESSION['settings'][$operation['id']]['min_operand'];?>">
          <label class="right_col">min:</label>
          <input type="text" name="_<?php echo $operation['id']; ?>_min_answer" class="right_col" maxlength="6" value="<?php echo $_SESSION['settings'][$operation['id']]['min_answer'];?>">
          <label class="left_col">max:</label>
          <input type="text" name="_<?php echo $operation['id']; ?>_max_operand" class="left_col" maxlength="3" value="<?php echo $_SESSION['settings'][$operation['id']]['max_operand'];?>">
          <label class="right_col">max:</label>
          <input type="text" name="_<?php echo $operation['id']; ?>_max_answer" class="right_col" maxlength="6" value="<?php echo $_SESSION['settings'][$operation['id']]['max_answer'];?>">
        </div>
      </div>
      <?php } ?>
      <input type="submit" id="submit" name="settings" value="save settings">
    </form>
  </div>
</div>

<?php require_once("components/footer.php"); ?>
</body>
</html>
