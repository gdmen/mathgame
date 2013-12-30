<?php
require_once('dbconnect/Mysql.php');
Mysql::connect();
require_once("dbconnect/LearnMathDB.php");
  ini_set('max_execution_time', 300);
  LearnMathDB::populateProblems(LearnMathDB::populateOperators());
Mysql::disconnect();
?>
