<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 26.11.2018
 * Time: 15:27
 */


date_default_timezone_set('Europe/Moscow');
session_start();
$id=$_SESSION['id'];
$name = $_POST['name'];
$text =  $_POST['content'];
$oldName = $_POST['oldName'];
$addDate=date("Y-m-d H:i:s");

$connect =new mysqli("localhost","root","","bitsa_tmp");
$connect->query("SET NAMES 'utf8'");
if(  $connect->query("UPDATE Notes SET NoteName='$name', Content='$text', ChangeDate='$addDate' WHERE IdUser='$id' AND Id_Note='$oldName'"))
    echo 1;
else
    echo  $connect->error;
?>