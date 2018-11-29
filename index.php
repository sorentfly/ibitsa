<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 05.11.2018
 * Time: 14:42
 */


header('Content-Type: text/html; charset=utf-8');
session_start();
if($_SESSION['id'] != 0) {
    header('Location: profile.php');  // перенаправление на нужную страницу
    exit();
}
if(isset($_POST['signUp']))
    registration();
if(isset($_POST['signIn']))
    authorisation();
function authorisation()
{
    $pass = $_POST['password'];
    $mail = $_POST['mail'];
    if(isAut($pass,$mail)) {
        $user = isAut($pass, $mail);
        $mysqli=new mysqli("localhost","u656321276_dan","qwerty","u656321276_bitsa");
        $select =  $mysqli->query( "SELECT FirstName, SecondName, Id_User FROM users WHERE Id_User='$user'");
        $row= $select->fetch_assoc();
        $_SESSION['first'] =$row['FirstName'];
        $_SESSION['second'] =$row['SecondName'];
        $addDate=date("Y-m-d H:i:s");
        $as = $mysqli->query("UPDATE users SET LastAuth='$addDate' WHERE Id_User='$user'");
        $mysqli->close();
        $_SESSION['id'] = $user;
        header ('Location: profile.php');  // перенаправление на нужную страницу
        exit();
    }
    else
        echo 'Неправильная комбинация e-mail/пароль';
}
function registration()
{
        $mail = $_POST['newMail'];
        $password = $_POST['newPassword'];
        $name = $_POST['first_name'];
        $second = $_POST['last_name'];
        $sex = $_POST['sex'];
        $birth_day = $_POST['day'];
        $birth_month = $_POST['month'];
        $birth_year = $_POST['year'];
        $birth_date = $birth_year . '-' . $birth_month . '-' . $birth_day;
        $date = date_format(date_create($birth_date), 'Y-m-d H:i:s');
        if (isExist($mail)) {
            echo 'Пользователь с таким e-mail уже существует';
            return 0;
        }
        $mysqli = new mysqli("localhost", "u656321276_dan", "qwerty", "u656321276_bitsa");
        $mysqli->query("SET NAMES 'utf8'");
        $insert_row = $mysqli->query("INSERT INTO `u656321276_bitsa`.`users` ( FirstName, SecondName, Mail, Password, Sex, BirthDate) VALUES('$name', '$second', '$mail', '$password', '$sex', '$date')");
        $mysqli->close();
        echo 'Регистрация готова';

}

function isExist($mail)
    {
        $isEx = false;
        $mysqli=new mysqli("localhost","u656321276_dan","qwerty","u656321276_bitsa");

        $mysqli->query("SET NAMES 'utf8'");
        $select =  $mysqli->query( "SELECT Mail FROM users");
        while($row= $select->fetch_assoc())
        {
            if($row["Mail"] == $mail)
            {
                $isEx = true;
                break;
            }
        }
        $mysqli->close();
        return $isEx;
    }
function isAut($pass, $mail)
{
    $isCorr = false;
    $mysqli=new mysqli("localhost","u656321276_dan","qwerty","u656321276_bitsa");

    $mysqli->query("SET NAMES 'utf8'");
    $select =  $mysqli->query( "SELECT Mail, Password, Id_User FROM users");
    while($row= $select->fetch_assoc())
    {
        if($row['Password'] == $pass && $row["Mail"] == $mail)
        {
            $isCorr = $row['Id_User'];
            break;
        }
    }
    $mysqli->close();

    return $isCorr;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Добро пожаловать</title>
    <link type="text/css" rel="stylesheet" href="css/mainpage.css">

    <script>
        function cheking() {
            var day = $("#chkDay option:selected").text();
            var month = $("#chkMonth option:selected").text();
            var year =  $("#chkYear option:selected").text();
            if(day == "День")
                return false;
            if(month == "Месяц")
                return false;
            if(year == "Год")
                return false;
            return true;
        }
    </script>
</head>
<body>
<div id="divRegistration">
<form action="" method="post" id="fRegistration" onsubmit="return cheking()">
    <div class="item">
        <input type="text" required id="first_name" name="first_name" pattern="^[А-Яа-яЁё\s]+$" placeholder="Имя"/>
    </div>

    <div class="item">
        <input type="text" required id="last_name" name="last_name" pattern="^[А-Яа-яЁё\s]+$" placeholder="Фамилия" />
    </div>

    <div class="item">
        <input type="email" required id="txtNewMail" name="newMail" autocomplete="off" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" placeholder="Электронный адрес" />
    </div>

    <div class="item">
        <input type="password" required id="txtNewPassword" name="newPassword" placeholder="Пароль"  pattern=".{6,}" autocomplete="off" />
    </div>
    <div id="divSex">
        <input type="radio" required name="sex" value="m" id="rdnMale"/><label for="rdnMale">Мужской</label>
        <input type="radio" required name="sex" value="f" id="rdnFemale" /><label for="rdnFemale">Женский</label>
    </div>
    <div class="item">
        <div id="boxBirth">
            <select name="day" size="1" id="chkDay">
                <option value="День">День</option>
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
            <select name="month" size="1" id="chkMonth">
                <option value="Месяц">Месяц</option>
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
            <select name="year" size="1" id="chkYear">
                <option value="Год">Год</option>
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
            </select>
        </div>
    </div>
    <input type="submit" value="Регистрация" name="signUp" />
</form>
</div>
    <div id="divAuthorisation">
    <form action="" method="post" id="fAuthorisation">

        <div class="item">
            <input type="text" id="txtMail" name="mail" placeholder="Электронный адрес" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" required/>
        </div>

        <div class="item">
            <input type="password" id="txtPassword" name="password" placeholder="Пароль"  pattern=".{6,}" required />
        </div>
        <input type="submit" value="Войти" name="signIn"/>
    </form>
</div>
</body>
</html>
