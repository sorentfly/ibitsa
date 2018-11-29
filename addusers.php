<?php
/**
 * Created by PhpStorm.
 * User: Vlad
 * Date: 07.11.2018
 * Time: 23:32
 */

$count = 0;
session_start();
if(isset($_POST['signOut'])) {
    echo 'asd';
    $_SESSION = array();
    session_destroy();
    header ('Location: index.php');  // перенаправление на нужную страницу
    exit();
}
if($_SESSION['id']==0)
{
    header ('Location: index.php');  // перенаправление на нужную страницу
    exit();
}
$count = 0;
$mysqli=new mysqli("localhost","root","","bitsa_tmp");
$mysqli->query("SET NAMES 'utf8'");
$id = $_SESSION['id'];
$select =  $mysqli->query( "SELECT IdUserTo,IdUserFrom FROM Friends WHERE IdUserFrom = '$id'");
$selectUserInfo=$mysqli->query( "SELECT FirstName,SecondName,BirthDate,Sex,City FROM users WHERE Id_User = '$id'");
while($row= $select->fetch_assoc())
{

    $count += 1;
    $t = $row['IdUserTo'];
    $arrayOfId[] = $t;
    $select1 =  $mysqli->query( "SELECT FirstName,SecondName FROM users WHERE Id_User = '$t'");
    while($row= $select1->fetch_assoc())
    {

        //echo  $row['FirstName'] . " " . $row['SecondName']. '<br>';
        $arrayOfUsers[] = $row['FirstName'] . " " . $row['SecondName'];
    }
}
$json = json_encode($arrayOfUsers);
$jsonIndex = json_encode($arrayOfId);
$mysqli->close();
$idUserTo = array();
$id = $_SESSION['id'];
$mysqli=new mysqli("localhost","root","","bitsa_tmp");
$mysqli->query("SET NAMES 'utf8'");
$select1 =  $mysqli->query( "SELECT IdUserTo, IdUserFrom FROM Friends WHERE IdUserFrom = '$id'");
$idUserTo[] = 0;
$idUserTo[] = 0;
while($row1= $select1->fetch_assoc()) {
    $idUserTo[] = $row1['IdUserTo'];
}
    $select = $mysqli->query("SELECT FirstName,SecondName,Mail,Id_User FROM users WHERE Id_User <> '$id'");

    while ($row = $select->fetch_assoc()) {
        if(!array_search($row['Id_User'], $idUserTo))

        {
        //echo  $row['FirstName'] . " " . $row['SecondName'] ." (". $row['Mail']. ")". '<br>';
        $arrayOfUsers[] = $row['FirstName'] . " " . $row['SecondName'];
        $arrayOfIndex[] = $row['Id_User'];
        $count = $count + 1;
    }
    }


$json = json_encode($arrayOfUsers);
$jsonIndex=json_encode( $arrayOfIndex);

$mysqli->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Вывод списка пользователей</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/profile.css">
    <style>
        #form{
            display: flex;
            font-size: 13pt;
        }
        #form input[type="button"]{
            height: 13pt;
            font-size: 9pt;
            background-color: #7dd771;
        }
    </style>
</head>
<body>
<div id="content">
    <div class="header">
        <div class="headerlogo"><a href="profile.php">iBitsa</a></div>
        <div class="header_section">
            <div class="header_item">
                <div class="headerButton">
                    <a href="shop.php">Магазин</a>
                </div>
            </div>
            <div class="header_item">
                <div class="headerButton">
                    <a href="notes.php">Записки</a>
                </div>
            </div>
            <div class="header_item">
                <div class="headerButton">
                    <a href="school.php">Школа</a>
                </div>
            </div>
            <div class="header_item">
                <div class="headerButton">
                    <a href="notifications.php">Уведомления</a>
                </div>
            </div>
            <div class="header_item">
                <div class="headerButton">
                    <a href="addusers.php">Добавить в друзья</a>
                </div>
            </div>
            <div class="header_item">
                <div class="headerButton" onclick="">
                    <form action="" method="post">
                        <input type="submit" id="btnSignOut" name="signOut" value="Выйти">
                    </form>
                </div>
            </div>
        </div>
        <div class="header_section" id="user" onclick="showFriends()">
            <div class="header_item" >
                <div  class="headerButton">
                    <span id="sNick"> </span>
                    <img src=""id="imgAva" width="30" height="30">
                </div>
            </div>
        </div>
    </div>
    <div id="listOfFriends"></div>
    <div id="addMessage"  style="display: none">
        <div id="message"></div>
        <div id="closeButton"></div>
        <form action="" method="post" id="form">
            <div id="listOfMessages">
            </div>
            <textarea placeholder="Введите сообщение" rows="8" cols="45" id="me"  style=" resize: none;"></textarea>
            <br>
            <input type="button" value="Отправить" onclick="sendMessage()">
        </form>
    </div>

</div>
<form action="" method="post">
    <div id="form">
    <div id="listOfUsers" >

        <!-- <input type="text" data-id="id0" name="value[]"> -->

    </div>
    <div id="listOfButtons" >

        <!-- <input type="text" data-id="id0" name="value[]"> -->

    </div>
    </div>
    <input type="button" id="button" value="Создать">
</form>

<script type="text/javascript">
    var count = 1;
    var a = Number("<? echo $count ?>");
    createButton(a);
    function createButton(id) {
        var tmp = <?php echo $json;?>;
        var idUser=<?php echo $jsonIndex;?>;
        for (var i = 0; i < id; i++) {
            var button= $('<input/>').attr({ type: 'button', name:idUser[i], id:idUser[i], value:'AddFriends', onclick:'addFriends(this)'});
            $("#listOfUsers").html($("#listOfUsers").html()+tmp[i]+"   ");
            $("#listOfButtons").append(button);
            $("#listOfUsers").html($("#listOfUsers").html()+"<br>");
            $("#listOfButtons").html($("#listOfButtons").html()+"<br>");
            count++;

        }
    }
</script>
<script>
    function addFriends(obj)
    {
        var idFriends = Number( obj.name);
        // отправляем AJAX запрос
        $.ajax(
            {
                type: "POST",
                url: "addusers1.php",
                data: "idFriend=" + idFriends,
                success: function(response)
                {
                    if(response == 1)
                    {
                        alert("Пользователь  добавлен!");
                        location.reload();
                    }
                    else
                        alert("Ошибка в запросе! Сервер вернул вот что: " + response);
                }
            }
        );
    }
</script>
<script id="friends" type="text/javascript">
    var first = "<? echo $_SESSION['first'] ?>";
    var second = "<? echo $_SESSION['second'] ?>";
    $('#sNick').html(first + ' ' + second);
    $('#imgAva').attr('src','img/user.png');

    var a = Number("<? echo $count ?>");
    showListOfFriends(a);
    public: var flag = false;
    function showFriends() {
        if(!flag) {
            $('#listOfFriends').animate({height: "show"}, 500);
            flag = true;
        }
        else {
            $('#listOfFriends').animate({height: "hide"}, 500);
            flag = false;
        }
    }
    function showListOfFriends(id) {
        var tmp = <?php echo $json;?>;
        var idUser = <?php echo $jsonIndex;?>;
        $("#listOfFriends").html($("#listOfFriends").html()+"<div class='friends' onclick='addMessage(this)' id='"+'asdasd'+"'>" + 'tmp[i]'+"</div>");
        if(id == 0) {
            $("#listOfFriends").html("<div id='bibbleThum'>У тебя нету друзей:(</div>");
        }
        else
            for (var i = 0; i < id; i++) {
                $("#listOfFriends").html($("#listOfFriends").html()+"<div class='friends' onclick='addMessage(this)' id='"+idUser[i]+"'>" + tmp[i]+"</div>");
            }
        $('#listOfFriends').hide();

    }
    public: var idFriends;
    var div = document.getElementById("closeButton");
    var divMainField=document.getElementById("editingInfo");
    div.onclick = function (e) {
        var e = e || window.event;
        var target = e.target || e.srcElement;
        if (this == target)    $("#addMessage").animate({height: "hide"}, 500);
    }
    function addMessage(obj)
    {
        $("#addMessage").hide();
        $("#addMessage").animate({height: "show"}, 500);
        idFriends = Number(obj.id);
        showMessage();
    }

    function sendMessage() {
        var text  = $("#me").val();
        var date = new Date();
        var data = date.getHours()+ ":"+ date.getMinutes()+":"+ date.getSeconds()
        //$("#listOfMessages").html($("#listOfMessages").html() +text + "<br>");
        $("#listOfMessages").html($("#listOfMessages").html() + "<div class='messages' style='text-align: right'>[" + data + "] " + text + "</div>");
        document.getElementById('me').value='';
        $.ajax(
            {
                type: "POST",
                url: "addMessage.php",
                data: 'text='+text+'&idFriend='+idFriends,
                success: function(response)
                {
                    if(response == 1)
                    {
                        var a;
                        //alert("Сообщение отправлено");
                        //location.reload();
                    }
                    else
                        alert("Ошибка в запросе! Сервер вернул вот что: " + response);
                }
            }
        );
    }
    function showMessage()
    {

        $.ajax(
            {
                type: "POST",
                url: "showMessage.php",
                data: 'idFriend='+idFriends,
                success: function(response)
                {
                    if(response != 0)
                    {

                        $("#listOfMessages").html("");
                        var tmp = response;
                        var sendedMessage = JSON.parse( tmp);
                        //alert(tmp + "   ");
                        $.each(sendedMessage, function(key, value) {
                            //alert(key);
                            // $.each(value, function(key1, value1) {
                            //alert(key1 + ' ' + value1);
                            var type = key.split("/")[1];
                            if(type=="s")
                                $("#listOfMessages").html($("#listOfMessages").html() + "<div class='messages' style='text-align: right'>[" + key.split(" ")[1].split("/")[0] + "] " + value + "</div>");
                            if(type=="r")
                                $("#listOfMessages").html($("#listOfMessages").html() +  "<div class='messages' style='text-align: left'>[" + key.split(" ")[1].split("/")[0] + "] " + value + "</div>");
                            //});
                        });
                    }
                    else
                        alert("Ошибка в запросе! Сервер вернул вот что: " + response);
                }
            }
        );
    }
</script>
</body>
</html>

