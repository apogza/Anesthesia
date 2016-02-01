<?php
	require_once "Api/Api.php";
	
	$api = new Api("Anesthesia");
	echo $api->getResult();
?>