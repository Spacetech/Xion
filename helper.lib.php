<?php

function EncryptPassword($password)
{
	global $password_salt;
	return hash("sha256", $password_salt.$password.$password_salt);
}

function CleanString($str)
{
	return trim(strtolower($str));
}

function RedirectTimer($page, $seconds)
{
	echo "<meta http-equiv=\"refresh\" content=\"$seconds;url=index.php?p=$page\">";
	ShowInfo("You will be redirected in $seconds seconds...");
}

function ShowError($msg, $hideGoBack = false)
{
	echo "<p class=\"bg-danger bigp\"><span class=\"glyphicon glyphicon-exclamation-sign\"></span> $msg</p>";
	if(!$hideGoBack)
	{
		ShowGoBack();
	}
}

function ShowAlert($msg)
{
	echo "<p class=\"bg-warning bigp\"><span class=\"glyphicon glyphicon-warning-sign\"></span> $msg</p>";
}

function ShowInfo($msg)
{
	echo "<p class=\"bg-info bigp\"><span class=\"glyphicon glyphicon-info-sign\"></span> $msg</p>";
}

function ShowGoBack()
{
	echo "<button type=\"button\" class=\"btn btn-default\" onclick=\"history.go(-1);\"><i class=\"fa fa-arrow-left\"></i> Go Back</button>";
}

function DisplayDatetime($time)
{
	if($time <= 0)
	{
		return "N/A";
	}
	return date("n/d/y h:i:s A", $time);
}

function DisplayLimited($str, $limit = 100)
{
	if(strlen($str) > $limit)
	{
		return substr($str, 0, $limit - 4)."...";
	}
	return $str;
}

function LogAction($action)
{
	if(!file_exists("log.data"))
	{
		file_put_contents("log.data", "{}", LOCK_EX);
	}
	
	$data = json_decode(file_get_contents("log.data"), true);
	
	array_push($data, array("Time" => time(), "User" => is_null($me) ? "N/A" : $me->GetUsername(), "Action" => $action, "RequestUri" => $_SERVER["REQUEST_URI"]));
	
	file_put_contents("log.data", json_encode($data), LOCK_EX);
}

function LogError($error)
{
	global $me;
	
	if(!file_exists("log.data"))
	{
		file_put_contents("log.data", "{}", LOCK_EX);
	}
	
	$data = json_decode(file_get_contents("log.data"), true);
	
	array_push($data, array("Time" => time(), "User" => is_null($me) ? "N/A" : $me->GetUsername(), "Error" => $error, "RequestUri" => $_SERVER["REQUEST_URI"]));
	
	file_put_contents("log.data", json_encode($data), LOCK_EX);
}

?>
