<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 03.11.2018
 * Time: 15:23
 */
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

/* @var $mysqli \mysqli */
$mysqli = include CONNECT__DB;

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
$id = $_SESSION['id'];
date_default_timezone_set('Europe/Moscow');

/* @var $mysqli \mysqli */
$mysqli = include CONNECT__DB;

$mysqli->query("SET NAMES 'utf8'");
$select =  $mysqli->query( "SELECT * FROM Notes WHERE IdUser = '$id'");
while($row= $select->fetch_assoc()) {
    $textNote[] = $row['Content'];
    $nameNote[] = $row['NoteName'];
    $dateCreate[] = $row['CreateDate'];
    $dateChange[] = $row['ChangeDate'];
    $idNote[] = $row['Id_Note'];
}
$mysqli->close();
$jsonContent = json_encode($textNote, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE  );
$jsonName = json_encode($nameNote, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE  );
$jsonCreate = json_encode($dateCreate, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE  );
$jsonChange = json_encode($dateChange, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE  );
$jsonId = json_encode($idNote, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE  );
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Создать заметку</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="css/note.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/profile.css">
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
<div id="noteContent" >
<div id="listOfNotes"></div>
    <div id="note" style="display: none">
        <div id="contentOfNote"></div>
        <div class="change" id="asd" onclick="changeNote(this)">Изменить</div>
        <div id="dateChange"></div>
        <div id="dateCreate"></div>
    </div>
<div id="addNote" style="display: none">
<form method="post" action="">
    <p><label for="txtName">Название заметки</label></p>
    <p><input type="text" placeholder="Введите название" id="txtName" name="name"></p>
    <p><label for="txtText">Текст заметки</label></p>
    <p><textarea rows="10" cols="45" name="text" id="txtText" placeholder="Введите текст заметки"></textarea></p>
    <input type="button" onclick="addNote()" name="send" id="btnSend" value="Записать">
</form>
</div>
</div>
<script>
    $(document).ready( function () {

        var name1 = '<? echo $jsonName ?>';
        var name = JSON.parse(name1);
        for (var i = 0; i < name.length; i++) {
            $('#listOfNotes').html($('#listOfNotes').html() + '<div class="note" onclick="showNote(this)" id="'+[i] +'">' +name[i]+ '</div>');
        }
        $('#listOfNotes').html($('#listOfNotes').html() + '<div onclick="showFormNote()" style="background-color: #13c62b" id="btnAddNote" class="note">' +'+Добавить заметку+'+ '</div>');

    })
    function showFormNote(){
        $('#txtName').val( '');
        $('#txtText').val('');
        if($('#addNote').is(":visible"))
            $('#addNote').hide('fast');
        $('#note').hide('slow');
        $("#addNote").show("slow");
    }
    function changeNote(obj) {
        showFormNote();
        var name1 = '<? echo $jsonName ?>';
        var name = JSON.parse(name1);
        var content1 = '<? echo $jsonContent ?>';
        var content = JSON.parse(content1);
        var idNote = JSON.parse('<? echo $jsonId ?>');
        var id = Number( $('.change').attr("name"));
        $('#txtName').val( name[id]);
        $('#txtName').attr("name",idNote[id]);
        $('#txtText').val( $('#contentOfNote').html());
        $('#btnSend').attr("onclick","updateNote()");
    }
    function updateNote() {
        $.ajax(
            {
                type: "POST",
                url: "updateNote.php",
                data: 'name='+  $('#txtName').val()+'&content='+$('#txtText').val()+'&oldName='+$('#txtName').attr("name"),
                success: function(response)
                {
                    if(response == 1)
                    {
                        var a;
                        alert("Запись изменена");
                        location.reload();
                    }
                    else
                        alert("Ошибка в запросе! Сервер вернул вот что: " + response);
                }
            }
        );
    }
    function showNote(obj) {
        $('#contentOfNote').html('');
        $('#dateCreate').html('Дата создания <br>');
        $('#dateChange').html('Дата редактирования <br>');
        if($('#note').is(":visible"))
            $('#note').hide('fast');
        $('#addNote').hide('slow');
        $('#note').show('slow');
        var change1 = '<? echo $jsonChange ?>';
        var create1 = '<? echo $jsonCreate?>';
        var change = JSON.parse(change1);
        var create = JSON.parse(create1);
        var content1 = '<? echo $jsonContent ?>';
        var content = JSON.parse(content1);
        var id = Number(obj.id);
        $('.change').attr("name",id);
        $('#contentOfNote').html($('#contentOfNote').html() + content[id]);
        $('#dateChange').html($('#dateChange').html() + change[id]);
        $('#dateCreate').html($('#dateCreate').html() + create[id]);
    }
    function addNote() {
        $.ajax(
            {

                type: "POST",
                url: "addNote.php",
                data: 'text=' + $('#txtText') .val() + '&name=' + $('#txtName').val(),
                success: function (response) {
                    if (response == 1) {
                        alert("Запись добавлена");
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
