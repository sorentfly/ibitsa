<?php
/**
 * Created by PhpStorm.
 * User: Vlad
 * Date: 11.11.2018
 * Time: 17:09
 */
$stub = ($home_path = realpath(dirname(__FILE__))) . '/stub.php';
include_once
file_exists(($config = $home_path . '/config.php'))
    ? $config
    : $stub;

header('Content-Type: text/html; charset=utf-8');//asd
session_start();

$id = $_SESSION['id'];
$firstName=$_POST['inputName'];
$secondName=$_POST['inputSecondName'];
$city=$_POST['inputCity'];
$nick=$_POST['inputNick'];

/* @var $mysqli \mysqli */
$connect = include CONNECT__DB;

if($connect->query("UPDATE users SET FirstName = '$firstName',SecondName='$secondName',City='$city',Nick='$nick' WHERE Id_User = '$id'"))
    echo "OK";
else
    echo  $connect->error;
?>