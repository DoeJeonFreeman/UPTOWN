<?php
//$filename = $_POST["fileName"];
$filename = $_REQUEST["fileName"];

if (file_exists($filename)) {
	echo "exist";
}else{
	echo "REQData may NOT Exist haha";
}

?>