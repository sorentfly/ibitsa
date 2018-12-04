<?

$stub = ($home_path = realpath(dirname(__FILE__))) . '/stub.php';
include_once
file_exists(($config = $home_path . '/config.php'))
    ? $config
    : $stub;

session_start();
$id = $_SESSION['id'];
date_default_timezone_set('Europe/Moscow');
$name = $_POST['name'];
$text = $_POST['text'];
$createDate = date("Y-m-d H:i:s");

/* @var $mysqli \mysqli */
$mysqli = include CONNECT__DB;

$mysqli->query("SET NAMES 'utf8'");
$insert_row = $mysqli->query("INSERT INTO `bitsa_dev`.`Notes` ( Content, IdUser, NoteName, CreateDate, ChangeDate) VALUES('$text', '$id', '$name', '$createDate', '$createDate')");

$mysqli->close();
echo 1;