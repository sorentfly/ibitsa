<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 10.11.2018
 * Time: 0:57
 */

header('Content-Type: text/html; charset=utf-8');//asd
session_start();
$mysqli=new mysqli("localhost","u656321276_dan","qwerty","u656321276_bitsa");

$id = $_SESSION['id'];
$arr = array();
$arr1 = array();
$idFriend = $_POST['idFriend'];
$select =  $mysqli->query( "SELECT IdUserTo,IdUserFrom,Text, DateAdd FROM Messages WHERE IdUserFrom = '$id' AND IdUserTo = '$idFriend' OR IdUserTo = '$id' AND IdUserFrom = '$idFriend'");
while($row= $select->fetch_assoc())
{
    if($row['IdUserFrom'] == $id)
        $arr[$row['DateAdd'] . '/s'] = $row['Text'];
    else
        $arr[$row['DateAdd'] . '/r'] = $row['Text'];
    //$sendedMessage[] = $row['Text'];
}
//$select1 =  $mysqli->query( "SELECT IdUserTo,IdUserFrom,Text, DateAdd FROM Messages WHERE IdUserFrom = '$idFriend' AND IdUserTo = '$id'");
//while($row1= $select1->fetch_assoc())
//{
  //  $arr1[$row1['DateAdd']] = $row1['Text'];
    //$recievedMessage[] = $row1['Text'];
//}
$mysqli->close();
$jsonSend = json_encode($arr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE  );
//$jsonRecieve = json_encode($recievedMessage, JSON_UNESCAPED_UNICODE);
echo json_encode( $arr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ;

?>