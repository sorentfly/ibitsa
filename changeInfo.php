<?php
/**
 * Created by PhpStorm.
 * User: Vlad
 * Date: 11.11.2018
 * Time: 17:09
 */
header('Content-Type: text/html; charset=utf-8');//asd
session_start();

$id = $_SESSION['id'];
$firstName=$_POST['inputName'];
$secondName=$_POST['inputSecondName'];
$city=$_POST['inputCity'];
$nick=$_POST['inputNick'];

/* @var $mysqli \mysqli */
$mysqli = include CONNECT__DB;

if($connect->query("UPDATE users SET FirstName = '$firstName',SecondName='$secondName',City='$city',Nick='$nick' WHERE Id_User = '$id'"))
    echo "OK";
else
    echo  $connect->error;
?>