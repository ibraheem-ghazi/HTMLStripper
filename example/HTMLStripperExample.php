<?php

// require '../src/HTMLStripper.php';
require 'vendor/autoload.php';

use IbraheemGhazi\HTMLStripper\HTMLStripper;

$str= require 'input_str.php'; //get string from input_str.php and put it in $str variable



$hss= new HTMLStripper();

$hss->addStyle('margin-right','40px',HTMLStripper::LESS_THAN);//39 and less are allowed
$hss->addStyle('text-align','*',HTMLStripper::EQUAL);
$hss->addStyle('height','30px',HTMLStripper::LESS_THAN);
$hss->addStyle('width','5%',HTMLStripper::GREATER_THAN);
// ... etc

// $new_str = $hss->strip($str,true,['strong','em','p']);
$new_str = $hss->strip($str,true,['table','tr','td','th','p','br']);
echo $new_str;

