<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 26.11.2018
 * Time: 15:27
 */

$stub = ($home_path = realpath(dirname(__FILE__))) . '/stub.php';
include_once
file_exists(($config = $home_path . '/config.php'))
    ? $config
    : $stub;

date_default_timezone_set('Europe/Moscow');
session_start();
$id=$_SESSION['id'];
$name = $_POST['name'];
$text =  $_POST['content'];
$oldName = $_POST['oldName'];
$addDate=date("Y-m-d H:i:s");

/* @var $mysqli \mysqli */
$mysqli = include CONNECT__DB;

$connect->query("SET NAMES 'utf8'");
if(  $connect->query("UPDATE Notes SET NoteName='$name', Content='$text', ChangeDate='$addDate' WHERE IdUser='$id' AND Id_Note='$oldName'"))
    echo 1;
else
    echo  $connect->error;
?>