# html-stripper

php parser for html tags and html inline styles , to allow or prevent some css styles with any value or specific value

Install
========

```
 $ composer require ibraheem-ghazi/html-stripper
```

Functions
=========
**strip($str, $useStripTags=true, $allowedTags= ['p','span','b','strong','u','ins','i','em','s','del','ul','li','ol','table','thead','tfoot','tbody','tr','th','td','br'])**
```
paramters:
$str           // **(string)** the html code for topic or post
$useStripTags  // **(bool)**   should use strip tags alongside css stripper
$allowedTags   // **(array)**  if strip tags is used , then which tags are allowed 

return clean clone of input string
```


**addStyle($attribute,$value='\*',$condition='=')**
```
paramters:
$attribute           // **(string)** which css attribute should allow (Ex: width, height, text-align, font-size, direction, ...etc)
$value      		 // **(string)**   which value should allow for this css attribute ('*' mean any value is allowed for this attribute)
$condition   		 // **(string)**  allowed value should be equal, less than, or greather than specified value 

**use these constants for `` $condition `` : ** 
HTMLStripper::EQUAL
HTMLStripper::LESS_THAN
HTMLStripper::GREATER_THAN


```



**Example:**

```
<?php
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

```