<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 09.11.2018
 * Time: 0:39
 */

session_start();
$id=$_SESSION['id'];
$idAdd = $_POST['idFriend'];
$addDate=date("Y-m-d");

/* @var $mysqli \mysqli */
$mysqli = include CONNECT__DB;

if($connect->query("insert into Friends (IdUserFrom,IdUserTo,DateAdd) values ('$id','$idAdd','$addDate')"))
    echo "1";
else
    echo  $connect->error;

?>

