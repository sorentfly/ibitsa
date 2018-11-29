<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 16.11.2018
 * Time: 19:27
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
$mysqli=new mysqli("localhost","root","","bitsa_tmp");
$select =  $mysqli->query( "SELECT NameCategory, Count FROM Category");
while ($row= $select->fetch_assoc()) {
    $categorys[] = $row['NameCategory'];
    if($row['Count'] != null)
    $counts[] = $row['Count'];
    else
        $counts[] = 0;
}
$jsonCateg = json_encode($categorys);
$jsonCount = json_encode($counts);
$mysqli->close();

$mysqli=new mysqli("localhost","root","","bitsa_tmp");
$select =  $mysqli->query( "SELECT NameProduct, Price, Image FROM Products");
while ($row= $select->fetch_assoc()) {
    $product[] = $row['NameProduct'];
    $price[] = $row['Price'];
    $img[] = $row['Image'];
}
$price = json_encode($price);
$img = json_encode($img);
$product = json_encode($product);
$mysqli->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="css/shopMain.css" >
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/profile.css">
    <title>Магазин</title>
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
    <form method="post" action="">

        <div id="shopContent">
            <div id="shopCategory"><div id="logoCategory">Каталог товаров</div></div>
            <div id="shopNews">
                <div class="newsContent">
                <div class="newsName">Хиты</div>
                <div class="newsItem" id="topSale"></div>
                </div>
                <div class="newsContent">
                <div class="newsName">Специальные предложения</div>
                <div class="newsItem" id="special"></div>
                </div>
                <div class="newsContent">
                <div class="newsName">Новинки</div>
                <div class="newsItem" id="newProduct"></div>
                </div>
                <div class="newsContent">
                <div class="newsName">Суперцены</div>
                <div class="newsItem" id="topPrice"></div>
                </div>
            </div>
        </div>
    </form>
<script>
    $(document).ready( function(){
        var text =  <?php  echo $jsonCateg ?>;
        var counts = <? echo $jsonCount ?>;
        for (var i = 0; i<text.length;i++)
        {
            $('#shopCategory').html($('#shopCategory').html()+ '<div class="categoryItem">'+ text[i] + '  [' + counts[i] + ']' + '</div>');
        }

        var name =  <?php  echo $product ?>;
        var price = <? echo $price ?>;
        var img = <? echo $img ?>;
        for(var i = 0; i < 5; i++)
        {
            $('#topSale').html($('#topSale').html() + '<div id="'+img[i]+ '" class="newsItemItem"  onClick="getProduct(this)"><img  width="150" height="140" src="shop/product/img/' + img[i] +'"><br>' + name[i] + '<br>' + price[i] + ' руб.</div>')
        }
        for(var i = 5; i < 10; i++)
        {
            $('#special').html($('#special').html() + '<div id="'+img[i]+ '" class="newsItemItem"  onClick="getProduct(this)"><img  width="150" height="140" src="shop/product/img/' + img[i] +'"><br>' + name[i] + '<br>' + price[i] + ' руб.</div>')
        }
        for(var i = 10; i < 15; i++)
        {
            $('#newProduct').html($('#newProduct').html() + '<div id="'+img[i]+ '" class="newsItemItem"  onClick="getProduct(this)"><img  width="150" height="140" src="shop/product/img/' + img[i] +'"><br>' + name[i] + '<br>' + price[i] + ' руб.</div>')
        }
        for(var i = 15; i < 20; i++)
        {
            $('#topPrice').html($('#topPrice').html() + '<div id="'+img[i]+ '" class="newsItemItem"  onClick="getProduct(this)"><img  width="150" height="140" src="shop/product/img/' + img[i] +'"><br>' + name[i] + '<br>' + price[i] + ' руб.</div>')
        }
    });

    function getProduct(obj) {
        $.ajax(
            {
                type: "POST",
                url: "getProduct.php",
                data: 'img='+obj.id,
                success: function(response)
                {

                }
            }
        );
        document.location='product.php';
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
