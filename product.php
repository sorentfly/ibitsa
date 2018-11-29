<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 18.11.2018
 * Time: 15:02
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
$image = $_SESSION['imgProduct'];

$mysqli=new mysqli("localhost","root","","bitsa_tmp");
$select =  $mysqli->query( "SELECT Id_Product, NameProduct, Description, Price, RatingProduct, IdCategory FROM Products WHERE Image = '$image'");
$row= $select->fetch_assoc();
    $idProduct = $row['Id_Product'];
   $name =  $row['NameProduct'];
   $desc = $row['Description'];
   $price = $row['Price'];
   $rating = $row['RatingProduct'];
   if($rating == 0)
       $rating = 'Нет оценок';
$mysqli->close();
if(isset($_POST['addComment']) && $_POST['+']!='')
{
    $plus = $_POST['+'];
    $minus = $_POST['-'];
    $summ = $_POST['='];
    $rate = $_POST['rate'];
    $tUs = $_POST['timeUsing'];
    $mysqli = new mysqli("localhost", "root", "", "bitsa_tmp");
    $mysqli->query("SET NAMES 'utf8'");
    $insert_row = $mysqli->query("INSERT INTO `bitsa_tmp`.`Product_Comment` ( IdUser, IdProduct, Likes, Dislikes, Sumary, Rating,TimeUse) VALUES('$id', '$idProduct', '$plus', '$minus', '$summ', '$rate','$tUs')");
    $mysqli->close();
    echo "<meta http-equiv='refresh' content='0'>";
}
$mysqli=new mysqli("localhost","root","","bitsa_tmp");
$select2 =  $mysqli->query( "SELECT * FROM Product_Comment WHERE IdProduct = '$idProduct'");
while($row2= $select2->fetch_assoc()) {
    $userId = $row2['IdUser'];
    $mysqli=new mysqli("localhost","root","","bitsa_tmp");
    $select3 =  $mysqli->query( "SELECT * FROM users WHERE Id_User = '$userId'");
    $row3 = $select3->fetch_assoc();
    $user[] = $row3['FirstName'].' '.$row3['SecondName'];
    $like[] = $row2['Likes'];
    $dislike[] = $row2['Dislikes'];
    $sum[] = $row2['Sumary'];
    $ratings[] = $row2['Rating'];
    $time[] = $row2['TimeUse'];
}
$jsonUser = json_encode($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
$jsonLike = json_encode($like, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
$jsonDislike = json_encode($dislike, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
$jsonSum = json_encode($sum, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
$jsonTime = json_encode($time, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
$jsonRating = json_encode($ratings, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
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
    <link rel="stylesheet" href="css/product.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/profile.css">
    <title>Продукт</title>
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
        <div id="productPage">
        <div id="productContent">
            <div id="image"></div>
            <div id="description">Описания нету</div>
            <div id="buy">
                <div id="price"> Нет цены</div>
                <input type="submit" id="bBuy" value="Купить">
                <div id="activity"> <span>В наличии</span><br>Сейчас в 1 центре исполнения заказов<br>Под заказ в 2 центрах исполнения заказов<br>Возможна доставка от 350 руб.</div>
            </div>
        </div>
    </form>
    <form method="post" action="">
            <div id="commentary">

            </div>
    </form>



<script>
    var img = '<? echo $image ?>';
    var desc = '<? echo $desc ?>';
    var price = '<? echo $price ?>';
    $('#image').html('<img  width="410" height="414" src="shop/product/img/' + img + '">');
    $('#description').html(desc);
    $('#price').html(price + ' руб.');
</script>
<script>



    var idUser = JSON.parse('<? echo $jsonUser ?>');
    var like = JSON.parse('<? echo $jsonLike ?>');
    var dislike = JSON.parse('<? echo $jsonDislike ?>');
    var sum = JSON.parse('<? echo $jsonSum ?>');
    var rating = JSON.parse('<? echo $jsonRating ?>');
    var time = JSON.parse('<? echo $jsonTime ?>');
    if(idUser!=null)
    for (var i = 0; i < idUser.length; i++)
    {

        $('#commentary').html($('#commentary').html()+'<div id="commentItem">\n' +
            '                <div id="userC" name ="'+ idUser[i]+ '"></div>\n' + idUser[i]+
            '                <div id="comment">\n' +
            '                    <div id="statistic">Рейтинг: '+rating[i]+' , Опыт использования: '+time[i] +' </div>\n' +
            '                    <div id="contentComment">\n' +
            '                        <span>Достоинства: </span>'+ like[i] +'<br>\n' +
            '                        <span>Недостатки: </span>'+ dislike[i] +'<br>\n' +
            '                        <span>Общие впечатления: </span>'+ sum[i] +'\n' +
            '                    </div>\n' +
            '                </div>\n' +
            '                </div>');



    }
    $('#commentary').html($('#commentary').html()+'<div id="newComment">Оставить комментарий<br> <input type="text" name="+" id="+" placeholder="Плюсы"><br><input type="text" id="-" name="-" placeholder="Минусы"><br><input type="text" id="=" name="=" placeholder="Общие впечатления"><br>'+
        '<label for="rate">Оценка:</label> <select size="1" name="rate" id="rate">  <option value="1">1</option><option value="1.5">1.5</option><option value="2">2</option><option value="2.5">2.5</option><option value="3">3</option><option value="3.5">3.5</option><option value="4">4</option><option value="4.5">4.5</option><option value="5">5</option> </select><br>'+
        '<label for="timeUsing">Опыт использования:</label><br> <select size="1" name="timeUsing" id="timeUsing">  <option value="Меньше месяца">Меньше месяца</option><option value="От одного месяца до двух">От одного месяца до двух</option><option value="Больше двух месяцев">Больше двух месяцев</option></select><br><input type="submit" value="Отправить" name="addComment" id="addComment"></div>');

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
