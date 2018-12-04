<?php
/**
 * Created by PhpStorm.
 * User: Vlad
 * Date: 18.11.2018
 * Time: 12:07
 */

$stub = ($home_path = realpath(dirname(__FILE__))) . '/stub.php';
include_once
file_exists(($config = $home_path . '/config.php'))
    ? $config
    : $stub;

session_start();
$id = $_SESSION['id'];
$j=0;
$k=1;

/* @var $mysqli \mysqli */
$mysqli = include CONNECT__DB;

$select =  $mysqli->query( "SELECT Name_object FROM Objects");
while($row= $select->fetch_assoc())
{
    $arrayOfObjects[] = $row['Name_object'];
    $arrayOfIdObj[]=$row['Id_object'];
}
$mysqli->close();

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic table</title>
</head>
<body>

<form method="post" action="">
    <table width="650" border="1" cellspacing="0" cellpadding="5">

        <!-- Заголовки //-->
        <thead>

            <?php
            for($i=0;$i<count($arrayOfObjects);$i++)
            {
                echo "<tr>";
                echo "<th scope=\"col\">".$arrayOfObjects[$i]."</th>";

                /* @var $mysqli \mysqli */
                $mysqli = include CONNECT__DB;

                $select =  $mysqli->query( "SELECT Grade FROM Grades,Objects WHERE Grades.IdObject='$k' AND .Objects.Id_object='$k'");
                $k++;
                while($row= $select->fetch_assoc())
                {
                    $arrayOfGrade[] = $row['Grade'];


                }
                $mysqli->close();

                while($j<(count($arrayOfGrade)))
                {
                    $result=count($arrayOfGrade);
                    echo "$result";
                    echo "<td>".$arrayOfGrade[$j]."</td>";
                    $j++;
                }
                $arrayOfGrade=array();

                $j=0;
                echo "</tr>";
            }
            ?>

        </thead>
    </table>

</form>

</body>
</html>
