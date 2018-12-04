<?

$stub = ($home_path = realpath(dirname(__FILE__))) . '/stub.php';
include_once
file_exists(($config = $home_path . '/config.php'))
    ? $config
    : $stub;

define('TIMEZONE', 'Europe/Paris');
date_default_timezone_set('Europe/Moscow');
session_start();
$id=$_SESSION['id'];
$idAdd = $_POST['idFriend'];
$text =  $_POST['text'];
$hour = date("h");
//$hour = $hour;
$addDate=date("Y-m-d H:i:s");

/* @var $mysqli \mysqli */
$mysqli = include CONNECT__DB;

$connect->query("SET NAMES 'utf8'");
if($connect->query("insert into Messages (IdUserFrom,IdUserTo,Text,DateAdd) values ('$id','$idAdd','$text','$addDate')"))
    echo 1;
else
    echo  $connect->error;

?>

