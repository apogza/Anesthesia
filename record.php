<?php
	require_once "Api/Api.php";
	
	$api = new Api("Record");
	echo $api->getResult();
?>