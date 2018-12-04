<?php
/**
 * Created by PhpStorm.
 * User: Vlad
 * Date: 03.11.2018
 * Time: 15:28
 */
$stub = ($home_path = realpath(dirname(__FILE__))) . '/stub.php';
include_once
file_exists(($config = $home_path . '/config.php'))
    ? $config
    : $stub;

    session_start();
    echo 'id = ';
    echo $_SESSION['id'];
    if(isset($_POST["create"]))
    {
        $id = $_SESSION['id'];
        $Content=$_POST["nameNotification"];
        $DateNotification=$_POST["dateNotification"];
        $HourNotification=$_POST["timeNotificationH"];
        $MinuteNotification=$_POST["timeNotificationM"];
        $DateBuild=$DateNotification. " " . $HourNotification. ":" .$MinuteNotification. ":". "00";
        $DateTime=date_format(date_create($DateBuild),'Y-m-d H:i:s');
        $CreateDate = date("Y.m.d");
        $StatusOn='1';

        /* @var $mysqli \mysqli */
        $mysqli = include CONNECT__DB;

        $mysqli->query("SET NAMES 'utf8'");
        $insert_row = $mysqli->query("INSERT INTO `Notifications` ( NameNotification, TimeNotification, IdUser, CreateDate) VALUES('$Content', '$DateTime', ' $id', '$CreateDate')");
        $update = $mysqli->query("UPDATE users SET StatusNotification = $StatusOn WHERE Id_User = $id");
        $mysqli->close();
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Уведомление</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/resources/demos/style.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>
        $( function() {
            $( "#datepicker" ).datepicker({
                changeMonth: true,
                changeYear: true
            });
        } );
    </script>

</head>
<body>
    <form id="fNotification" Name="notification" method="post" action="">
        <p><label for="txbNameNotification">Текст уведомления</label></p>
        <p><input type="text" id="txbNameNotification" name="nameNotification" placeholder="Введите текст"></p>
        <p><label for="datepicker">Дата уведомления</label></p>
        <p><input type="text" id="datepicker" name="dateNotification" placeholder="Введите дату"></p>
        <p><label>Время уведомления</label></p>
        <input type="text" id="txbTimeH" name="timeNotificationH" placeholder="h" style="width: 20px">
        <input type="text" id="txbTimeM" name="timeNotificationM" placeholder="m" style="width: 20px">
        <input type="submit" id="btnCreate" value="Создать" name="create">
    </form>
</body>
