<?php

class Pages
{
	private static $Pages = array("Dashboard", "Tickets", "Ticket", "New Ticket", "Login", "Logout", "Settings", "Profile", "Typeahead", "SMS", "DT Clients", "DT Tickets", "Admin");
	private static $CurrentPage = "Dashboard";
	
	public static function Init()
	{
		if(isset($_GET["p"]))
		{
			Pages::SetPage($_GET["p"]);
		}
	}
	
	public static function SetPage($name)
	{
		$cleaned = self::CleanPage($name);

		foreach (self::$Pages as $page)
		{
			if($cleaned == self::CleanPage($page))
			{
				self::$CurrentPage = $page;
				return;
			}
		}
	}
	
	public static function CleanPage($name)
	{
		return strtolower(str_replace(" ", "_", $name));
	}

	public static function GetCurrentPage()
	{
		return self::$CurrentPage;
	}
}

?>