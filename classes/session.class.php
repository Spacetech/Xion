<?php

class Session
{
	public static function Get($key)
	{
		return $_SESSION[$key];
	}
	
	public static function Set($key, $value)
	{
		$_SESSION[$key] = $value;
	}
	
	public static function Is_Set($key)
	{
		return isset($_SESSION[$key]);
	}
	
	public static function Equals($key, $value)
	{
		if(self::Is_Set($key))
		{
			return self::Get($key) == $value;
		}
		return false;
	}
	
	public static function Destroy()
	{
		$_SESSION = array();
		session_destroy();
	}

	public static function GetStaff()
	{
		if(!self::Is_Set("sid") || !self::Is_Set("secret"))
		{
			return null;
		}
		return Staff::Load(self::GetStaffID());
	}

	public static function GetStaffID()
	{
		return self::Get("sid");
	}
}

?>
