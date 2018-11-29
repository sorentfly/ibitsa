<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 24.11.2018
 * Time: 17:30
 */

session_start();
$id = $_SESSION['id'];
date_default_timezone_set('Europe/Moscow');
$name = $_POST['name'];
$text = $_POST['text'];
$createDate = date("Y-m-d H:i:s");
$mysqli = new mysqli("localhost", "root", "", "bitsa_tmp");
$mysqli->query("SET NAMES 'utf8'");
$insert_row = $mysqli->query("INSERT INTO `bitsa_tmp`.`Notes` ( Content, IdUser, NoteName, CreateDate, ChangeDate) VALUES('$text', '$id', '$name', '$createDate', '$createDate')");

$mysqli->close();
echo 1;