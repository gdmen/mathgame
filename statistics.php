<?php require_once('components/session.php'); require_once("models/header.php"); ?>
<?php
  require_once("dbconnect/LearnMathDB.php");
?>
<?php
  $results = LearnMathDB::getGroupedResults($loggedInUser->user_id);
  $rewards = LearnMathDB::getRewards($loggedInUser->user_id);
  
  $test_count = 0;
  $split_index = 0;
  // Find split point between old and new results data
  for($i = $rewards[0]['problems_solved'] - 1; $i < count($results) - 1; $i++) {
    $test_count += 1;
    if($results[$i]['timestamp'] >= $rewards[0]['timestamp']) {
      $split_index = $i - $rewards[0]['problems_solved'];
      break;
    }
  }
  
  $post_fix_results = array_slice($results, $split_index);
  $pre_fix_results = array_slice($results, 0, $split_index);
  /*
  print("-------------------------<br>");
  print($split_index);
  print("<br>");
  print($test_count);
  print("<br>");
  foreach($results as $i => $v){
    //print($i . " => " . $v['timestamp']);
    //print("<br>");
  }
  print(date('Y-m-d H:i:s', $rewards[0]['timestamp']));
  print("<br>");
  print(date('Y-m-d H:i:s', $results[$split_index]['timestamp']));
  print("<br>-------------------------<br>");
  print("-------------------------<br>");
  print("-------------------------<br>");
  print(date('Y-m-d H:i:s', $post_fix_results[0]['timestamp']));
  print("<br>");
  print(date('Y-m-d H:i:s', $pre_fix_results[count($pre_fix_results)-1]['timestamp']));
  print("<br>");
  print("post-fix: " . count($post_fix_results));
  print("<br>");
  print("pre-fix: " . count($pre_fix_results));
  */
  
  function difficultyAsc($x, $y) {
    if($x['seconds'] == $y['seconds']) {
      if($x['attempts'] == $y['attempts']) {
        if($x['count'] == $y['count']) {
          if($x['answer'] == $y['answer']) {
            return 0;
          } else if($x['answer'] < $y['answer']) {
            return -1;
          } else {
            return 1;
          }
        } else if($x['count'] > $y['count']) {
          return -1;
        } else {
          return 1;
        }
      } else if($x['attempts'] < $y['attempts']) {
        return -1;
      } else {
        return 1;
      }
    } else if($x['seconds'] < $y['seconds']) {
      return 1;
    } else {
      return 1;
    }
  }
  function difficultyDesc($x, $y) {
    if($x['seconds'] == $y['seconds']) {
      if($x['attempts'] == $y['attempts']) {
        if($x['count'] == $y['count']) {
          return 0;
        } else if($x['count'] > $y['count']) {
          return -1;
        } else {
          return 1;
        }
      } else if($x['attempts'] > $y['attempts']) {
        return -1;
      } else {
        return 1;
      }
    } else if($x['seconds'] > $y['seconds']) {
      return -1;
    } else {
      return 1;
    }
  }
  
  $session_boundary = 10 * 60; // 10 minutes
  $reward_boundary = 30; // half minute
  $previous_ended_timestamp = 0;
  $sum_work = 0;
  $sum_reward = 0;
  $reset_session = false;
  $efficiency_data = [];
  $num_problems_data = [];
  
  $difficult_problems_table = [];
  $difficult_problems_data = [];
  $easy_problems_table = [];
  $easy_problems_data = [];
  // Number of problems to select for each list
  $problem_list_count = min(10, count($results) / 2);
  $min_count_for_list = 2;
  
  // Operators pie chart
  $operators_table = [];
  $operators_data = [];
  
  // Totals
  $total_problems = 0;
  $problems_split = 0;
  $total_work_time = 0;
  $total_reward_time = 0;
  
  foreach($pre_fix_results as $problem_data) {
    $time_diff = $problem_data['timestamp'] - $previous_ended_timestamp;
    if($time_diff > $session_boundary) {
      // Assume 1 minute video from end of previous session
      //$sum_reward += 60;
      $reset_session = true;
      //print("<br><br>SESSION<br><br>");
    } else if($time_diff > $reward_boundary) {
      //print(date('Y-m-d H:i:s', $problem_data['timestamp']));
      $sum_reward += $time_diff;
      // Do not reset for sets, just sessions (above)
      $reset_session = false;
      //print("<br>EST: " . $time_diff . ' seconds<br>');
    }
    //print_r($problem_data);
    //print("<br>");
    if($reset_session && $sum_work != 0 && $sum_reward != 0) {
      $efficiency_data[] = [$problem_data['timestamp'] * 1000, round($sum_work * 100.0 / ($sum_work + $sum_reward)), $total_problems - $problems_split];
      $total_work_time += $sum_work;
      $total_reward_time += $sum_reward;
      $sum_work = 0;
      $sum_reward = 0;
      $problems_split = $total_problems;
      $reset_session = false;
    }
    if(!isset($difficult_problems_table[$problem_data['problem_id']])) {
      $difficult_problems_table[$problem_data['problem_id']] = ['seconds' => $problem_data['seconds'],
                                                                'operator' => $problem_data['operator'],
                                                                'operand_one' => $problem_data['operand_one'],
                                                                'operand_two' => $problem_data['operand_two'],
                                                                'answer' => $problem_data['answer'],
                                                                'attempts' => $problem_data['attempts'],
                                                                'problem_id' => $problem_data['problem_id'],
                                                                'count' => 1,
                                                               ];
    } else {
      // Not really sure how references work in php. . . not even sure if I can +=
      $table_entry = $difficult_problems_table[$problem_data['problem_id']];
      $table_entry['seconds'] = $table_entry['seconds'] + $problem_data['seconds'];
      $table_entry['attempts'] = $table_entry['attempts'] + $problem_data['attempts'];
      $table_entry['count'] = $table_entry['count'] + 1;
      $difficult_problems_table[$problem_data['problem_id']] = $table_entry;
    }
    if(!isset($easy_problems_table[$problem_data['problem_id']])) {
      $easy_problems_table[$problem_data['problem_id']] = ['seconds' => $problem_data['seconds'],
                                                           'operator' => $problem_data['operator'],
                                                           'operand_one' => $problem_data['operand_one'],
                                                           'operand_two' => $problem_data['operand_two'],
                                                           'answer' => $problem_data['answer'],
                                                           'attempts' => $problem_data['attempts'],
                                                           'problem_id' => $problem_data['problem_id'],
                                                           'count' => 1,
                                                          ];
    } else {
      // Not really sure how references work in php. . . not even sure if I can +=
      $table_entry = $easy_problems_table[$problem_data['problem_id']];
      $table_entry['seconds'] = $table_entry['seconds'] + $problem_data['seconds'];
      $table_entry['attempts'] = $table_entry['attempts'] + $problem_data['attempts'];
      $table_entry['count'] = $table_entry['count'] + 1;
      $easy_problems_table[$problem_data['problem_id']] = $table_entry;
    }
    $total_problems += 1;
    if(!isset($operators_table[$problem_data['operator']])) {
      $operators_table[$problem_data['operator']] = 0;
    }
    $operators_table[$problem_data['operator']] = $operators_table[$problem_data['operator']] + 1;
    $sum_work += $problem_data['seconds'];
    $previous_ended_timestamp = $problem_data['timestamp'] + $problem_data['seconds'];
    $num_problems_data[] = [$previous_ended_timestamp * 1000, $total_problems];
  }
  
  
  $reset_session = false;
  $previous_ended_timestamp = 0;
  $sum_work = 0;
  $sum_reward = 0;
  
  $reward_index = 0;
  foreach($post_fix_results as $problem_data) {
    $time_diff = $problem_data['timestamp'] - $previous_ended_timestamp;
    if($time_diff > $session_boundary) {
      //print("<br><br>SESSION<br><br>");
      $reset_session = true;
    } else if(isset($rewards[$reward_index]) && $rewards[$reward_index]['timestamp'] < $problem_data['timestamp']) {
      $sum_reward += $rewards[$reward_index]['seconds'];
      //print(date('Y-m-d H:i:s', $problem_data['timestamp']));
      // Do not reset for sets, just sessions (above)
      $reset_session = false;
      //print("<br>REAL: " . $rewards[$reward_index]['seconds'] . ' seconds<br>');
      //print($rewards[$reward_index]['problems_solved'] . ' problems<br><br>');
      $reward_index += 1;
    }
    //print_r($problem_data);
    //print("<br>");
    if($reset_session && $sum_work != 0 && $sum_reward != 0) {
      $efficiency_data[] = [$problem_data['timestamp'] * 1000, round($sum_work * 100.0 / ($sum_work + $sum_reward)), $total_problems - $problems_split];
      //print("<br>PRODUCTIVITY: " . round($sum_work * 100.0 / ($sum_work + $sum_reward)) . "%<br><br>");
      $total_work_time += $sum_work;
      $total_reward_time += $sum_reward;
      $sum_work = 0;
      $sum_reward = 0;
	  $problems_split = $total_problems;
      $reset_session = false;
    }
    if(!isset($difficult_problems_table[$problem_data['problem_id']])) {
      $difficult_problems_table[$problem_data['problem_id']] = ['seconds' => $problem_data['seconds'],
                                                                'operator' => $problem_data['operator'],
                                                                'operand_one' => $problem_data['operand_one'],
                                                                'operand_two' => $problem_data['operand_two'],
                                                                'answer' => $problem_data['answer'],
                                                                'attempts' => $problem_data['attempts'],
                                                                'problem_id' => $problem_data['problem_id'],
                                                                'count' => 1,
                                                               ];
    } else {
      // Not really sure how references work in php. Not even sure if I can use the += operator. . .
      $table_entry = $difficult_problems_table[$problem_data['problem_id']];
      $table_entry['seconds'] = $table_entry['seconds'] + $problem_data['seconds'];
      $table_entry['attempts'] = $table_entry['attempts'] + $problem_data['attempts'];
      $table_entry['count'] = $table_entry['count'] + 1;
      $difficult_problems_table[$problem_data['problem_id']] = $table_entry;
    }
    if(!isset($easy_problems_table[$problem_data['problem_id']])) {
      $easy_problems_table[$problem_data['problem_id']] = ['seconds' => $problem_data['seconds'],
                                                           'operator' => $problem_data['operator'],
                                                           'operand_one' => $problem_data['operand_one'],
                                                           'operand_two' => $problem_data['operand_two'],
                                                           'answer' => $problem_data['answer'],
                                                           'attempts' => $problem_data['attempts'],
                                                           'problem_id' => $problem_data['problem_id'],
                                                           'count' => 1,
                                                          ];
    } else {
      // Not really sure how references work in php. Not even sure if I can use the += operator. . .
      $table_entry = $easy_problems_table[$problem_data['problem_id']];
      $table_entry['seconds'] = $table_entry['seconds'] + $problem_data['seconds'];
      $table_entry['attempts'] = $table_entry['attempts'] + $problem_data['attempts'];
      $table_entry['count'] = $table_entry['count'] + 1;
      $easy_problems_table[$problem_data['problem_id']] = $table_entry;
    }
    $total_problems += 1;
    if(!isset($operators_table[$problem_data['operator']])) {
      $operators_table[$problem_data['operator']] = 0;
    }
    $operators_table[$problem_data['operator']] = $operators_table[$problem_data['operator']] + 1;
    $sum_work += $problem_data['seconds'];
    $previous_ended_timestamp = $problem_data['timestamp'] + $problem_data['seconds'];
    $num_problems_data[] = [$previous_ended_timestamp * 1000, $total_problems];
  }
  // Last reward
  if($reward_index <= count($rewards) - 1){
    $sum_reward += $rewards[$reward_index]['seconds'];
    //print(date('Y-m-d H:i:s', $problem_data['timestamp']));
    // Do not reset for sets, just sessions (above)
    $reset_session = false;
    //print("<br>REAL: " . $rewards[$reward_index]['seconds'] . ' seconds<br>');
    //print($rewards[$reward_index]['problems_solved'] . ' problems<br><br>');
    $efficiency_data[] = [$problem_data['timestamp'] * 1000, round($sum_work * 100.0 / ($sum_work + $sum_reward)), $total_problems - $problems_split];
    //print("<br>PRODUCTIVITY: " . round($sum_work * 100.0 / ($sum_work + $sum_reward)) . "%<br><br>");
    $total_work_time += $sum_work;
    $total_reward_time += $sum_reward;
  }
  
  /**
   * Sort num_problems_data for highcharts
   **/
  function timeAsc($x, $y) {
    if($x[0] == $y[0]) {
      return 0;
    } else if($x[0] < $y[0]) {
      return -1;
    } else {
      return 1;
    }
  }
  usort($num_problems_data, 'timeAsc');
  
  /**
   * Post process problems lists
   **/
  foreach($difficult_problems_table as $problem_id => $problem_data) {
    if($problem_data['count'] < $min_count_for_list) {
      continue;
    }
    $problem_data['seconds'] = round($problem_data['seconds'] / $problem_data['count']);
    $problem_data['attempts'] = round($problem_data['attempts'] / $problem_data['count']);
    $add_problem = False;
    if(count($difficult_problems_data) < $problem_list_count) {
      $add_problem = True;
    } else if($problem_data['seconds'] >= $difficult_problems_data[0]['seconds']) {
      $add_problem = True;
    }
    if($add_problem) {
      $difficult_problems_data[] = $problem_data;
      usort($difficult_problems_data, 'difficultyDesc');
      $difficult_problems_data = array_slice($difficult_problems_data, 0, $problem_list_count);
      usort($difficult_problems_data, 'difficultyDesc');
    }
  }
  //print_r($difficult_problems_data);
  
  foreach($easy_problems_table as $problem_id => $problem_data) {
    if($problem_data['count'] < $min_count_for_list) {
      continue;
    }
    $problem_data['seconds'] = round($problem_data['seconds'] / $problem_data['count']);
    $problem_data['attempts'] = round($problem_data['attempts'] / $problem_data['count']);
    $add_problem = False;
    if(count($easy_problems_data) < $problem_list_count) {
      $add_problem = True;
    } else if($problem_data['seconds'] <= $easy_problems_data[0]['seconds']) {
      $add_problem = True;
    }
    if($add_problem) {
      $easy_problems_data[] = $problem_data;
      usort($easy_problems_data, 'difficultyAsc');
      $easy_problems_data = array_slice($easy_problems_data, 0, $problem_list_count);
      usort($easy_problems_data, 'difficultyAsc');
    }
  }
  //print "<br><br>";
  //print_r($easy_problems_data);
  
  $operators = LearnMathDB::getAllOperations();
  foreach($operators_table as $symbol => $count) {
    foreach($operators as $op) {
      if($op['operator'] != $symbol) {
        continue;
      }
      $operators_data[] = [$op['name'], round($count / $total_problems * 100.0, 1)];
    }
  }
  
  /*
  $cumulative_improvement = 0;
  $improvement_data = [];
  $problem_map = array();
  foreach($results as $problem_data) {
    if(!isset($problem_map[$problem_data['problem_id']])) {
      $problem_map[$problem_data['problem_id']] = [$problem_data['seconds'], 1];
    } else {
      $base = $problem_map[$problem_data['problem_id']];
      $avg = $base[0] / $base[1];
      $new = $problem_data['seconds'];
      $cumulative_improvement -= ($new - $avg);
      $improvement_data[] = [$problem_data['timestamp'], round($cumulative_improvement, 1)];
      $problem_map[$problem_data['problem_id']] = [$base[0] + $problem_data['seconds'], $base[1] + 1];
    }
  }*/
  /*
    echo date('Y-m-d @ g:i a', intval($session_start)) . '<br />';
    echo 'Session duration: ' . round(($session_end - $session_start) / 60, 0) . ' minutes' . '<br />';
    echo 'Average seconds/problem: ' . round($session_num_seconds / $session_num_problems, 1) . '<br />';
  }*/
  function listProblems($problems, $id, $label) {
  ?>
    <table id=<?php echo $id; ?> class="list-table">
      <tr><th class="title" colspan="5"><?php echo $label; ?></th></tr>
      <tr><th>#</th><th>problem</th><th>avg time</th><th>avg tries</th><th>times seen</th></tr>
      <?php foreach($problems as $i => $problem) {?>
        <tr>
          <td><?php echo $i + 1; ?>)</td>
          <td class="align-right"><?php echo $problem['operand_one'] . ' ' . $problem['operator'] . ' ' . $problem['operand_two']; ?></td>
          <td><?php echo $problem['seconds']; ?>s</td>
          <td><?php echo $problem['attempts']; ?></td>
          <td><?php echo $problem['count']; ?></td>
        </tr>
      <?php } ?>
    </table>
<?php
  }
?>
<div id="content" role="main">
  <div id="statistics">
    <div id="highlight">
    <?php if($total_problems == 0) { ?>
      You haven't done any math yet - <a href="problem.php">Do some now!</a>
    <?php } else { ?>
      You have answered <span class="number"><?php echo $total_problems; ?></span> questions in the
      <span class="number"><?php echo round(($total_work_time + $total_reward_time) / 3600.0, 1); ?></span> hours you have played The Math Game!
      <br />
      <?php if(($total_work_time + $total_reward_time) > 0) { ?>
      Efficiency is the percentage of your time you spend doing math. Your average efficiency has been
      <span class="number"><?php echo round($total_work_time * 100.0 / ($total_work_time + $total_reward_time), 1); ?>%</span>!
      <?php } else { ?>
        <a href="problem.php">Do more math now!</a>
      <?php } ?>
    </div>
    <div id="tables">
      <?php
        if(count($difficult_problems_data) > 0) {
          listProblems($difficult_problems_data, "difficult-problems", count($difficult_problems_data) . " most difficult problems");
        }
      ?>
      <?php
        if(count($difficult_problems_data) > 0) {
          listProblems($easy_problems_data, "easy-problems", count($easy_problems_data) . " easiest problems");
        }
      ?>
    </div>
    <div id="graphs">
      <div id="efficiency_data" style="display:none"><?php echo json_encode($efficiency_data); ?></div>
      <div id="efficiency" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
      <div id="row">
        <div id="num_problems_data" style="display:none"><?php echo json_encode($num_problems_data); ?></div>
        <div id="num_problems"></div>
        <div id="operators_data" style="display:none"><?php echo json_encode($operators_data); ?></div>
        <div id="operators"></div>
      </div>
    <?php } ?>
    </div>
  </div>
</div>

<?php require_once("components/footer.php"); ?>
  <script src="js/libs/Highcharts-3.0.6/js/highcharts.js"></script>
  <script src="js/libs/Highcharts-3.0.6/js/highcharts-more.js"></script>
<script src="js/statistics.js"></script>
<!-- end scripts -->
</body>
</html>
