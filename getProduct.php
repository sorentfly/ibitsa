<?php
/**
 * Created by PhpStorm.
 * User: Wasyan
 * Date: 18.11.2018
 * Time: 15:26
 */

    session_start();
if($_SESSION['imgProduct']!=0)
{
    unset($_SESSION['imgProduct']);
}
    $_SESSION['imgProduct'] = $_POST['img'];
    echo  $_SESSION['imgProduct'];
?>