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
$connect =new mysqli("localhost","u656321276_dan","qwerty","u656321276_bitsa");

if($connect->query("insert into Friends (IdUserFrom,IdUserTo,DateAdd) values ('$id','$idAdd','$addDate')"))
    echo "1";
else
    echo  $connect->error;

?>

