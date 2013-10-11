<?php
$debug = TRUE;

class Mysql{
  private static $conn = null;
  
  public static function connect(){
    include_once('models/db-settings.php');
    GLOBAL $mysqli;
    Mysql::$conn = $mysqli;
    if (mysqli_connect_errno()) {
      fail('MySQL connect', mysqli_connect_error());
    }
  }
  public static function getConn(){
    return Mysql::$conn;
  }
  public static function disconnect(){
    mysqli_close(Mysql::$conn);
  }
  public static function fail($pub, $pvt = ''){
    global $debug;
    $msg = $pub;
    if ($debug && $pvt !== '')
      $msg .= ": $pvt";
  }
}
?>
