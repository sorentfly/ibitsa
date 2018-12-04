<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 09.11.2018
 * Time: 0:39
 */

$stub = ($home_path = realpath(dirname(__FILE__))) . '/stub.php';
include_once
file_exists(($config = $home_path . '/config.php'))
    ? $config
    : $stub;

session_start();
$id=$_SESSION['id'];
$idAdd = $_POST['idFriend'];
$addDate=date("Y-m-d");

/* @var $mysqli \mysqli */
$connect = include CONNECT__DB;

if($connect->query("insert into Friends (IdUserFrom,IdUserTo,DateAdd) values ('$id','$idAdd','$addDate')"))
    echo "1";
else
    echo  $connect->error;

?>

