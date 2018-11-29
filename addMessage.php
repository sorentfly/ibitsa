<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 09.11.2018
 * Time: 0:39
 */
define('TIMEZONE', 'Europe/Paris');
date_default_timezone_set('Europe/Moscow');
session_start();
$id=$_SESSION['id'];
$idAdd = $_POST['idFriend'];
$text =  $_POST['text'];
$hour = date("h");
//$hour = $hour;
$addDate=date("Y-m-d H:i:s");

$connect =new mysqli("localhost","u656321276_dan","qwerty","u656321276_bitsa");
$connect->query("SET NAMES 'utf8'");
if($connect->query("insert into Messages (IdUserFrom,IdUserTo,Text,DateAdd) values ('$id','$idAdd','$text','$addDate')"))
    echo 1;
else
    echo  $connect->error;

?>

