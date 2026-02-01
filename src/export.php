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
			
			$output = json_to_xml($loadjson);
			$success = 1;
		}
	}
	finally{
		if(!$success || substr($output, 0, 2) == "e="){
			http_response_code(303);
			header("Location: .?".($output?$output:'e'));
		}
		else{
			header("Content-Type: application/xml; charset=UTF-8");
			header("Content-Disposition: attachment; filename=\"{$user}.{$type}.xml\"");
			echo $output;
		}
	}

	
}

if(isset($_GET["e"])){
	echo "<p>";
	switch($_GET["e"]){
		case "notpublic":
			echo "Error: User's " . $listtype . " list is not public.\n<br>" .
				"Do you have access to it?
				<b><i>Try the <a href=\"https://greasyfork.org/en/scripts/563051-mal-list-exporter\">userscript</a> instead!\n<br></i></b>";
			break;
		case "404user":
			echo "Error: User does not exist.\n";
			break;
		case "weirdmal":
			if(isset($_GET["s"])){
				echo "Error: MAL returned HTTP {$_GET["s"]}.\n";
				break;
			}
		case "invalidmalname":
			echo "MAL usernames must be between 2 and 16 characters; and contain only letters, " .
				"digits, underscores and hyphens.";
			break;
		default:
			echo "Unknown Error.";
	}
	echo "</p>";
}

?>
