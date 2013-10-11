<?php
class LearnMathDB{

  static function getGroupedResults($user_id){
    $method = 'getGroupedResults';
    $conn = Mysql::getConn();
    ($stmt = $conn->prepare("SELECT problem_id, seconds, timestamp, operator, operand_one, operand_two, answer
                             FROM results, problems, operators WHERE results.user_id = ? AND results.problem_id = problems.id AND problems.operator_id = operators.id
                             ORDER BY timestamp ASC, seconds ASC")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('i', $user_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($problem_id, $seconds, $timestamp, $operator, $operand_one, $operand_two, $answer) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    
    $data = array();
    $curr_data_point = NULL;
    
    //TODO: use this var in statistics.php to avoid de-sync
    $session_boundary = 10 * 60; // 10 minutes
    $curr_problem_id = -1;
    $curr_problem_start = -1;
    $curr_problem_seconds = 0;
    
    while($stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error)){
      $timestamp = strtotime($timestamp) - $seconds;
      if($problem_id !== $curr_problem_id || ($problem_id == $curr_problem_id && $timestamp - ($curr_problem_start + $curr_data_point['seconds']) > $session_boundary)){
        if($problem_id != $curr_problem_id){
          $curr_problem_id = $problem_id;
          if($curr_data_point != NULL){
            $data[$curr_problem_start] = $curr_data_point;
          }
        }
        $curr_problem_start = $timestamp;
        $curr_problem_seconds = $seconds;
        if($curr_problem_id > 0) {
          $curr_data_point = array('problem_id' => $curr_problem_id,
                                   'seconds' => $seconds,
                                   'timestamp' => $timestamp,
                                   'operator' => $operator,
                                   'operand_one' => $operand_one,
                                   'operand_two' => $operand_two,
                                   'answer' => $answer,
                                   'attempts' => 1);
        }
      }else{
        $curr_data_point['attempts'] = $curr_data_point['attempts'] + 1;
        $curr_data_point['seconds'] = ($timestamp - $curr_data_point['timestamp']) + $seconds;
      }
    }
    $stmt->close();
    // Last point
    if($curr_data_point != NULL){
      $data[$curr_problem_start] = $curr_data_point;
    }
    
    return array_values($data);
  }

  static function getRewards($user_id){
    $method = 'getRewards';
    $conn = Mysql::getConn();
    ($stmt = $conn->prepare("SELECT timestamp, problems_solved, title, (end - start) AS seconds
                             FROM rewards, videos WHERE rewards.user_id = ? AND rewards.video_id = videos.id
                             ORDER BY timestamp ASC")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('i', $user_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($timestamp, $problems_solved, $title, $seconds) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    
    $data = array();
    
    while($stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error)){
      $data[] = array('timestamp' => strtotime($timestamp),
                      'problems_solved' => $problems_solved,
                      'title' => $title,
                      'seconds' => $seconds);
    }
    $stmt->close();
    
    return $data;
  }
  
  static function saveUserSettings($settings, $user_id){
    $method = 'saveUserSettings';
    $conn = Mysql::getConn();
    
    ($stmt = $conn->prepare("UPDATE game_state SET solve_for_reward = ? WHERE user_id = ?")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('ii', $settings['solve_for_reward'], $user_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->close();
    
    for($i = 1; $i <= 4; $i++){
      ($stmt = $conn->prepare("UPDATE settings SET enabled = ?, min_operand = ?, max_operand = ?, min_answer = ?, max_answer = ? WHERE operator_id = ? AND user_id = ?")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
      $stmt->bind_param('iiiiiii', $settings[$i]['enabled'], $settings[$i]['min_operand'], $settings[$i]['max_operand'], $settings[$i]['min_answer'], $settings[$i]['max_answer'], $i, $user_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
      $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
      $stmt->close();
    }
  }

  static function getUserSettings($user_id){
    $method = 'getUserSettings';
    $conn = Mysql::getConn();

    $settings = array();
    ($stmt = $conn->prepare("SELECT operator_id, operator, enabled, min_operand, max_operand, min_answer, max_answer FROM settings, operators WHERE settings.user_id = ? AND operator_id = operators.id")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('i', $user_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($operator_id, $operator, $enabled, $min_operand, $max_operand, $min_answer, $max_answer) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    while($stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error)){
      $settings[$operator_id] = array('operator' => $operator,
                                      'enabled' => $enabled,
                                      'min_operand' => $min_operand,
                                      'max_operand' => $max_operand,
                                      'min_answer' => $min_answer,
                                      'max_answer' => $max_answer);
    }
    $stmt->close();

    return $settings;
  }

  static function getAllOperations(){
    $method = 'getAllOperations';
    $conn = Mysql::getConn();

    $operations = array();
    ($stmt = $conn->prepare("SELECT id, operator, name FROM operators")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($id, $operator, $name) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    while($stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error)){
      $operations[] = array('id' => $id,
                            'operator' => $operator,
                            'name' => $name);
    }
    $stmt->close();

    return $operations;
  }

  static function saveCurrentSolved($current_solved, $user_id){
    $method = 'saveCurrentSolved';
    $conn = Mysql::getConn();

    ($stmt = $conn->prepare("UPDATE game_state SET current_solved = ? WHERE user_id = ?")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('ii', $current_solved, $user_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->close();
  }

  static function saveCurrentProblem($current_problem, $user_id){
    $method = 'saveCurrentProblem';
    $conn = Mysql::getConn();

    ($stmt = $conn->prepare("UPDATE game_state SET current_problem = ? WHERE user_id = ?")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('ii', $current_problem, $user_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->close();
  }

  static function getUserData($user_id){
    $method = 'getUserData';
    $conn = Mysql::getConn();
    
    ($stmt = $conn->prepare("SELECT game_state.user_id, current_problem, current_solved, solve_for_reward FROM game_state WHERE game_state.user_id = ?")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('i', $user_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($user_id, $current_problem, $current_solved, $solve_for_reward) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    $stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error);
    $stmt->close();
    return array('user_id' => $user_id,
                 'current_problem' => $current_problem,
                 'current_solved' => $current_solved,
                 'solve_for_reward' => $solve_for_reward);
  }

  static function getUserName($user_id){
    $method = 'getUserName';
    $conn = Mysql::getConn();

    ($stmt = $conn->prepare("SELECT display_name FROM uc_users WHERE id = ?")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('i', $user_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($user_name) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    $stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error);
    $stmt->close();

    return $user_name;
  }

  private static function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0){ //Reference is required for PHP 5.3+
      $refs = array();
      foreach($arr as $key => $value)
        $refs[$key] = &$arr[$key];
      return $refs;
    }
    return $arr;
  }

  static function getRandomVideos($num_videos){
    $method = 'getRandomVideos';
    $conn = Mysql::getConn();

    ($stmt = $conn->prepare("SELECT id, title, youtube_id, start, end FROM videos WHERE enabled = 1 ORDER BY RAND() LIMIT ?")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('i', $num_videos) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($id, $title, $youtube_id, $start, $end) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    $videos = array();
    while($stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error)){
      $videos[$title] = array('youtube_id' => $youtube_id,
                              'start' => $start,
                              'end' => $end,
                              'id' => $id);
    }
    $stmt->close();
    
    return $videos;
  }
  
  static function getAllVideos(){
    $method = 'getAllVideos';
    $conn = Mysql::getConn();

    ($stmt = $conn->prepare("SELECT id, title, youtube_id, start, end FROM videos WHERE enabled = 1")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($id, $title, $youtube_id, $start, $end) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    $videos = array();
    while($stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error)){
      $videos[$title] = array('youtube_id' => $youtube_id,
                              'start' => $start,
                              'end' => $end,
                              'id' => $id);
    }
    $stmt->close();
    
    return $videos;
  }

  static function getRandomProblemID($settings, $previous_problem_id){
    $method = 'getRandomProblemID';
    $conn = Mysql::getConn();

    // construct query
    $query_string = "SELECT problems.id FROM problems, operators WHERE problems.id != ? AND (";
    $type_string = 'i';
    $bind_params = array($previous_problem_id);
    foreach($settings as $operator_id => $operator_data){
      if($operator_data['enabled']){
        $query_string .= " problems.operator_id = operators.id AND operator = ? AND operand_one >= ? AND operand_one <= ? AND operand_two >= ? AND operand_two <= ? AND answer >= ? AND answer <= ? OR";
        $bind_params = array_merge((array)$bind_params, array($operator_data['operator'], $operator_data['min_operand'], $operator_data['max_operand'], $operator_data['min_operand'], $operator_data['max_operand'], $operator_data['min_answer'], $operator_data['max_answer']));
        $type_string .= 'siiiiii';
      }
    }
    if(count($bind_params) == 0){
      return NULL;
    }
    $query_string .= " 0) ORDER BY RAND() LIMIT 1";
    
    array_unshift($bind_params, $type_string);
    
    ($stmt = $conn->prepare($query_string)) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    call_user_func_array(array($stmt, 'bind_param'), LearnMathDB::refValues($bind_params));
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($problem_id) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    $stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error);
    $stmt->close();
    return $problem_id;
  }

  static function getProblem($problem_id){
    $method = 'getProblem';
    $conn = Mysql::getConn();
    
    ($stmt = $conn->prepare("SELECT problems.id, operand_one, operand_two, operator, answer FROM problems, operators WHERE problems.id = ? AND problems.operator_id = operators.id")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('i', $problem_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($problem_id, $operand_one, $operand_two, $operator, $answer) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    $stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error);
    $stmt->close();
    return array('problem_id' => $problem_id,
                 'operand_one' => $operand_one,
                 'operand_two' => $operand_two,
                 'operator' => $operator,
                 'answer' => $answer);
  }
  
  private static function getSeconds($time='00:00'){
    list($mins, $secs) = explode(':', $time);
    return ($mins * 60 ) + $secs;
  }

  static function addVideo($title, $youtube_link, $start, $end){
    $method = 'addVideo';
    $conn = Mysql::getConn();
    
    $title = strtolower($title);
    
    $url = parse_url($youtube_link);
    parse_str($url['query'], $query_string);
    $youtube_id = $query_string['v'];
    
    if(strlen($start) == 0){
      $start = 0;
    }else if(strpos($start, ':')){
      $start = LearnMathDB::getSeconds($start);
    }
    
    if(strlen($end) == 0){
      $end = 99999;
    }else if(strpos($end, ':')){
      $end = LearnMathDB::getSeconds($end);
    }
      // insert video
      ($stmt = $conn->prepare("INSERT INTO videos (title, youtube_id, start, end) VALUES (?,?,?,?)")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
      $stmt->bind_param('ssss', $title, $youtube_id, $start, $end) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
      $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
      $stmt->close();
  }
  
  static function checkAnswer($user_id, $problem_id, $input_answer, $seconds){
    $method = 'checkAnswer';
    $conn = Mysql::getConn();
    
    // if no answer input, save answer as null
    if(strlen($input_answer) == 0){
      $input_answer = NULL;
    }

    // insert result
    ($stmt = $conn->prepare("INSERT INTO results (user_id, problem_id, input_answer, seconds) VALUES (?,?,?,?)")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('iiii', $user_id, $problem_id, $input_answer, $seconds) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->close();

    // check/return correctness
    if($input_answer == NULL){
      return false;
    }
    ($stmt = $conn->prepare("SELECT COUNT(*) FROM problems WHERE id = ? AND answer = ?")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('ii', $problem_id, $input_answer) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($correct) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    $stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error);
    $stmt->close();
    return $correct > 0;
  }
  
  static function recordReward($user_id, $problems_solved, $video_id){
    $method = 'recordReward';
    $conn = Mysql::getConn();

    // insert reward
    ($stmt = $conn->prepare("INSERT INTO rewards (user_id, problems_solved, video_id) VALUES (?,?,?)")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('iii', $user_id, $problems_solved, $video_id) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->close();
  }

  static function createUser($user_name){
    $method = 'createUser';
    $conn = Mysql::getConn();

    ($stmt = $conn->prepare("INSERT INTO game_state (name) VALUES (?)")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('s', $user_name) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->close();

    // get user_id
    ($stmt = $conn->prepare("SELECT game_state.user_id FROM game_state WHERE game_state.name = ?")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->bind_param('s', $user_name) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($user_id) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    $stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error);
    $stmt->close();

    foreach(LearnMathDB::getAllOperations() as $operation){
      //Array ( [id] => 1 [operator] => + [name] => addition ) Array ( [id] => 2 [operator] => - [name] => subtraction )
      print_r($operation);
      echo '<br>';
      ($stmt = $conn->prepare("INSERT INTO settings (user_id, operator_id) VALUES (?, ?)")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
      $stmt->bind_param('ii', $user_id, $operation['id']) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
      $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
      $stmt->close();
    }
  }

  /*
  * Regenerate database
  */
  static function populateOperators($operators = array('+' => 'addition', '-' => 'subtraction', '*' => 'multiplication', '/' => 'division')){
    $method = 'populateOperators';
    $conn = Mysql::getConn();
    
    ($stmt = $conn->prepare("DELETE FROM operators")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->close();
    ($stmt = $conn->prepare("ALTER TABLE operators AUTO_INCREMENT = 1")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->close();
    foreach($operators as $operator => $name){
      ($stmt = $conn->prepare("INSERT INTO operators (operator, name) VALUES (?,?)")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
      $stmt->bind_param('ss', $operator, $name) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
      $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
      $stmt->close();
    }

    $operators_return = array();
    ($stmt = $conn->prepare("SELECT id, operator FROM operators")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->bind_result($operator_id, $operator_value) || Mysql::fail($method . ' MySQL bind_result', $conn->error);
    while($stmt->fetch() || Mysql::fail($method . ' MySQL fetch', $conn->error)){
      $operators_return[] = array('id' => $operator_id,
                                  'value' => $operator_value);
    }
    $stmt->close();

    return $operators_return;
  }

  static function populateProblems($operators, $min_operand = 0, $max_operand = 99){
    $method = 'populateProblems';
    $conn = Mysql::getConn();
    
    function add($a, $b) { return $a + $b; }
    function sub($a, $b) { return $a - $b; }
    function mul($a, $b) { return $a * $b; }
    function div($a, $b) { return $a / $b; }
    $operator_map = array('+' => 'add', '-' => 'sub', '*' => 'mul', '/' => 'div');
    
    ($stmt = $conn->prepare("DELETE FROM problems")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->close();
    ($stmt = $conn->prepare("ALTER TABLE problems AUTO_INCREMENT = 1")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
    $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
    $stmt->close();

    foreach($operators as $operator){
      for($i = $min_operand; $i <= $max_operand; $i++){
        for($j = $min_operand; $j <= $max_operand; $j++){
          if($operator['value'] == '/' && $j == 0){
            continue;
          }
          $answer = $operator_map[$operator['value']]($i, $j);
          if((int)$answer != $answer){
            continue;
          }
          ($stmt = $conn->prepare("INSERT INTO problems (operator_id, operand_one, operand_two, answer) VALUES (?,?,?,?)")) || Mysql::fail($method . ' MySQL prepare', $conn->error);
          $stmt->bind_param('iiis', $operator['id'], $i, $j, $answer) || Mysql::fail($method . ' MySQL bind_param', $conn->error);
          $stmt->execute() || Mysql::fail($method . ' MySQL execute', $conn->error);
          $stmt->close();
        }
      }
    }
  }
}
?>
