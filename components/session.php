<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
require_once('dbconnect/Mysql.php');
Mysql::connect();

function unsetUser(){
  unset($_SESSION['settings']);
  unset($_SESSION['current_problem']);
  unset($_SESSION['current_solved']);
}

function setSettings($operator_id, $min_operand, $max_operand, $min_answer, $max_answer){
  $_SESSION['settings'][$operator_id]['min_operand'] = $min_operand;
  $_SESSION['settings'][$operator_id]['max_operand'] = $max_operand;
  $_SESSION['settings'][$operator_id]['min_answer'] = $min_answer;
  $_SESSION['settings'][$operator_id]['max_answer'] = $max_answer;
}

function setNewProblem(){
  require_once("dbconnect/LearnMathDB.php");
  $_SESSION['current_problem'] = LearnMathDB::getRandomProblemID($_SESSION['settings'], $_SESSION['current_problem']);
  LearnMathDB::saveCurrentProblem($_SESSION['current_problem'], $_SESSION['user_id']);
  $_SESSION['current_solved'] = $_SESSION['current_solved'] + 1;
  LearnMathDB::saveCurrentSolved($_SESSION['current_solved'], $_SESSION['user_id']);
}

function resetSolved(){
  require_once("dbconnect/LearnMathDB.php");
  $_SESSION['current_solved'] = 0;
  LearnMathDB::saveCurrentSolved($_SESSION['current_solved'], $_SESSION['user_id']);
}

function setUser($user_id){
  require_once("dbconnect/LearnMathDB.php");
  $_SESSION['user_id'] = $user_id;
  $user_data = LearnMathDB::getUserData($_SESSION['user_id']);
  $_SESSION['current_problem'] = $user_data['current_problem'];
  $_SESSION['current_solved'] = $user_data['current_solved'];
  $_SESSION['settings'] = LearnMathDB::getUserSettings($_SESSION['user_id']);
  $_SESSION['settings']['solve_for_reward'] = $user_data['solve_for_reward'];
}

function saveSettings(){
  require_once("dbconnect/LearnMathDB.php");
  LearnMathDB::saveUserSettings($_SESSION['settings'], $_SESSION['user_id']);
}
?>
