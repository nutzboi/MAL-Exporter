<?php

if(isset($_POST["type"]) && isset($_POST["user"])){
	$type = $_POST["type"];
	$user = trim($_POST["user"]);
	$update = ($_POST["update"]=="on"?1:0);
	$success = 0;
	$output = "";
	try{
		if(substr($type, 0, 3) == "mal"){
			require_once "mal.php";
			$username = $user;
			$update_on_import = $update;
			$loadjson = "";
			if($type == "malmanga")
				$loadjson = getdata($user, "manga");
			else
				$loadjson = getdata($user);
			if(!empty($loadjson)){
				$output = json_to_xml($loadjson);
				$success = 1;
			}
		}
	}
	finally{
		if(!$success) echo "<br>Export failed.";
	}

	if($success){
		header("Content-Type: application/xml; charset=UTF-8");
		header("Content-Disposition: attachment; filename=\"{$user}.{$type}.xml\"");
		echo $output;
	}
}

?>
