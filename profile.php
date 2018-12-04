<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 05.11.2018
 * Time: 17:55
 */
$stub = ($home_path = realpath(dirname(__FILE__))) . '/stub.php';
include_once
file_exists(($config = $home_path . '/config.php'))
    ? $config
    : $stub;

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
    $arrayOfUsers = [];
    $arrayOfId = [];
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
    while($row = $selectUserInfo->fetch_assoc()) {
        $nameInfo       = $row['FirstName']     ?? '';
        $secondNameInfo = $row['SecondName']    ?? '';
        $birthDay       = $row['BirthDate']     ?? '';
        $cityInfo       = $row['City']          ?? '';
        $nickInfo       = $row['Nick']          ?? '';
    }

?>

<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/mainField.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
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
    <div id="content1">
        <div id="editingNewInfo">
            <div id="editindNewInfoDescraption">
                <p>Имя:</p>
                <p>Фамилия:</p>
                <p>Дата рождения:</p>
                <p>Пол:</p>
                <p>Город:</p>
                <p>Ник:</p>
            </div>
            <div id="editingNewInfoChange">
                <form method="post" action="" id="changeMyInfo">
                <p><input type="text" id="firstName" name="inputName" autocomplete="off" </p>
                <p><input type="text" id="secondName" name="inputSecondName" autocomplete="off"></p>
                <p><select name="dayChangeSelect" size="1" id="dayChange">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                    <option value="13">13</option>
                    <option value="14">14</option>
                    <option value="15">15</option>
                    <option value="16">16</option>
                    <option value="17">17</option>
                    <option value="18">18</option>
                    <option value="19">19</option>
                    <option value="20">20</option>
                    <option value="21">21</option>
                    <option value="22">22</option>
                    <option value="23">23</option>
                    <option value="24">24</option>
                    <option value="25">25</option>
                    <option value="26">26</option>
                    <option value="27">27</option>
                    <option value="28">28</option>
                    <option value="29">29</option>
                    <option value="30">30</option>
                    <option value="31">31</option>
                </select>
                <select name="monthChangeSelect" size="1" id="monthChangeMonth">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                </select>
                <select name="yearChangeSelect" size="1" id="yearChange">
                    <option value="2018">2018</option>
                    <option value="2017">2017</option>
                    <option value="2016">2016</option>
                    <option value="2015">2015</option>
                    <option value="2014">2014</option>
                    <option value="2013">2013</option>
                    <option value="2012">2012</option>
                    <option value="2011">2011</option>
                    <option value="2010">2010</option>
                    <option value="2009">2009</option>
                    <option value="2008">2008</option>
                    <option value="2007">2007</option>
                    <option value="2006">2006</option>
                    <option value="2005">2005</option>
                    <option value="2004">2004</option>
                    <option value="2003">2003</option>
                    <option value="2002">2002</option>
                    <option value="2001">2001</option>
                    <option value="2000">2000</option>
                    <option value="1999">1999</option>
                    <option value="1998">1998</option>
                    <option value="1997">1997</option>
                    <option value="1996">1996</option>
                    <option value="1995">1995</option>
                    <option value="1994">1994</option>
                    <option value="1993">1993</option>
                    <option value="1992">1992</option>
                    <option value="1991">1991</option>
                    <option value="1990">1990</option>
                    <option value="1989">1989</option>
                    <option value="1988">1988</option>
                    <option value="1987">1987</option>
                    <option value="1986">1986</option>
                    <option value="1985">1985</option>
                    <option value="1984">1984</option>
                    <option value="1983">1983</option>
                    <option value="1982">1982</option>
                    <option value="1981">1981</option>
                    <option value="1980">1980</option>
                </select></p>
                <p><input type="radio" name="sexChange" value="m" id="sexChangeMale"/><label for="sexChangeMale">Мужской</label>
                    <input type="radio" name="sexChange" value="f" id="sexChangeFemale" /><label for="sexChangeFemale">Женский</label></p>
                <p><input type="text" id="changeCity" name="inputChangeCity" autocomplete="off" </p>
                <p><input type="text" id="changeNick" name="inputChangeNick" autocomplete="off" </p>
                    <p><input type="submit" id="btnChangeInfo" value="Продолжить" name="submitBtnChangeInfo" onclick="changeInfo()"></p>
                </form>
            </div>
        </div>
        <div id="aboutMe">
            <div id="aboutMeInformation">
            </div>
            <div id="editingInfo">Редактировать</div>
        </div>

    </div>

    <script type="text/javascript">
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
    <script type="text/javascript">



        divMainField.onclick=function (e) {
            var e=e || window.event;
            var target=e.target || e.srcElement;
            if(this==target){
                $("#aboutMe").hide();
                $("#aboutMeInformation").hide();
                $("#edtingNewInfo").show();
                $("#editingNewInfo").show();
                $("#editingNewInfoChange").show();
            }

        }



        </script>

    <script type="text/javascript">

            var information = document.getElementById('aboutMeInformation'),
                name = "<?php echo $nameInfo;?>",  secondName = "<?php echo $secondNameInfo;?>", birth="<?php echo $birthDay;?>";
            information.innerHTML += "<div><h3>"+name+" "+secondName+"</h3></div>";
            information.innerHTML+="День рождения: "+birth
    </script>


    <script type="text/javascript">
        var inputName=document.getElementById('firstName');
        var inputSecondName=document.getElementById('secondName');
        var inputCity=document.getElementById('changeCity');
        var inputNick=document.getElementById('changeNick');
        inputName.value="<?php echo $nameInfo;?>";
        inputSecondName.value="<?php echo $secondNameInfo;?>";
        inputCity.value="<?php echo $cityInfo;?>";
        inputNick.value="<?php echo $nickInfo;?>";

        function changeInfo() {
            $.ajax(
                {
                    type: "POST",
                    url: "changeInfo.php",
                    data: 'inputName=' + inputName.value + '&inputSecondName=' + inputSecondName.value + '&inputCity=' + inputCity.value + '&inputNick=' + inputNick.value,
                    success: function (response) {
                        if (response == 1) {
                            alert("Сообщение отправлено");
                            //location.reload();
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
