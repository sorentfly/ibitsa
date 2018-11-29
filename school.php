<?php

$mysqli=new mysqli("localhost","root","","bitsa_tmp");
$select =  $mysqli->query( "SELECT Name_object FROM Objects");
while($row= $select->fetch_assoc())
{
    $arrayOfObjects[] = $row['Name_object'];
    $arrayOfIdObj[]=$row['Id_object'];
}
$mysqli->close();


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Простая форма загрузки файла</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="script/selectValue.js"></script>
</head>
<body>
<?php if (!isset($_FILES['upload']['tmp_name'])) : ?>
    <!-- Данная форма будет показана, если не было загрузок -->
    <form method="POST" enctype="multipart/form-data">
        <select name="object" size="1" id="chkObject">
            <?php
            for($i=0;$i<count($arrayOfObjects);$i++) {
                echo "<option>" . $arrayOfObjects[$i] . "</option>";
            }
           ?>

        </select>
        <input name="upload" type="file">
        <br><br>
        <input type="submit" value="Отправить" onclick="getValues()">
    </form>

<?php else: ?>
    <?php
    $newFilename = $_SERVER['DOCUMENT_ROOT']. '/school/homework/';
    echo "$newFilename";
    $uploadInfo = $_FILES['upload'];
    $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
    $numChars = strlen($chars);
    $string = '';
    for ($i = 0; $i < 20; $i++) {
        $string .= substr($chars, rand(1, $numChars) - 1, 1);
    }

    $newFilename.=$string;

    //Проверяем тип загруженного файла и дописываем расширение
    switch ($uploadInfo['type']) {
        case 'image/jpeg':
            $newFilename .= '.jpg';
            $string.='.jpg';
            break;

        case 'image/png':
            $newFilename .= '.png';
            $string.='.png';
            break;
        case 'text/plain':
            $newFilename.='.txt';
            $string.='.txt';
            break;

        default:
            echo 'Файл неподдерживаемого типа';
            exit;
    }

    //Перемещаем файл из временной папки в указанную
    if (!move_uploaded_file($uploadInfo['tmp_name'], $newFilename)) {
        echo 'Не удалось осуществить сохранение файла';
    }


    

    $connect =new mysqli("localhost","root","","bitsa_tmp");

    if($connect->query("insert into Homework (Home_File) values ('$string')"))
        echo "1";
    else
        echo  $connect->error;

    ?>

    <!-- Выводим разметку, содержащую информацию о файле -->

    <ul>
        <li>Размер файла: <?php echo $uploadInfo['size'] ?>байт</li>
        <li>Имя до загрузки: <?php echo $uploadInfo['name'] ?></li>
        <li>MIME-тип: <?php echo $uploadInfo['type'] ?></li>
    </ul>
<?php endif; ?>



</body>
</html>


