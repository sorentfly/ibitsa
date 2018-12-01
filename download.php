<?php
/**
 * Created by PhpStorm.
 * User: Vlad
 * Date: 18.11.2018
 * Time: 21:25
 */

$file = $_GET['file'];
header ("Content-Type: application/octet-stream");
header ("Accept-Ranges: bytes");
header ("Content-Length: ".filesize($file));
header ("Content-Disposition: attachment; filename=".$file);
readfile($file);