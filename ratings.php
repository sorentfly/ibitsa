<?php
/**
 * Created by PhpStorm.
 * User: Vlad
 * Date: 17.11.2018
 * Time: 17:18
 */

$string = "This is a test";
echo ereg_replace (" is", " was", $string);
echo ereg_replace ("( )is", "\\1was", $string);
echo ereg_replace ("(( )is)", "\\2was", $string);

?>