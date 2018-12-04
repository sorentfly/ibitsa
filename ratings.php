<?php
/**
 * Created by PhpStorm.
 * User: Vlad
 * Date: 17.11.2018
 * Time: 17:18
 */
$stub = ($home_path = realpath(dirname(__FILE__))) . '/stub.php';
include_once
file_exists(($config = $home_path . '/config.php'))
    ? $config
    : $stub;

$string = "This is a test";
echo preg_replace (" is", " was", $string);
echo preg_replace ("( )is", "\\1was", $string);
echo preg_replace ("(( )is)", "\\2was", $string);