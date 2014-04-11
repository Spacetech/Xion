<?php

class Client extends Base
{
	private $edited = false;

	public static function Add($name, $username, $building, $location, $phone_number)
	{
		global $database;

		$active = 1;

		$statement = $database->prepare("INSERT INTO clients (name, username, building, location, phone_number, active) VALUES(?, ?, ?, ?, ?, ?)");
		$statement->bindParam(1, $name, PDO::PARAM_STR);
		$statement->bindParam(2, $username, PDO::PARAM_STR);
		$statement->bindParam(3, $building, PDO::PARAM_STR);
		$statement->bindParam(4, $location, PDO::PARAM_STR);
		$statement->bindParam(5, $phone_number, PDO::PARAM_STR);
		$statement->bindParam(6, $active, PDO::PARAM_INT);

		$statement->execute();
	}

	public static function SetAllInactive()
	{
		global $database;

		$active = 0;

		$statement = $database->prepare("UPDATE clients SET active=?");
		$statement->bindParam(1, $active, PDO::PARAM_INT);

		$statement->execute();
	}

	public static function GetUsernames()
	{
		global $database;

		$statement = $database->prepare("SELECT username FROM clients");
		$result = $statement->execute();

		return $result ? $statement->fetchAll(PDO::FETCH_COLUMN, 0) : null;
	}

	public static function GetUsernameBeginsWith($username)
	{
		global $database;

		$active = 1;
		$query = $username."%";

		$statement = $database->prepare("SELECT username FROM clients WHERE active=? AND username LIKE ? LIMIT 10");
		$statement->bindParam(1, $active, PDO::PARAM_INT);
		$statement->bindParam(2, $query, PDO::PARAM_STR);
		$result = $statement->execute();

		return $result ? $statement->fetchAll(PDO::FETCH_COLUMN, 0) : null;
	}

	public static function GetByUsername($username)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM clients WHERE username=? LIMIT 1");
		$statement->bindParam(1, $username, PDO::PARAM_STR);
		$result = $statement->execute();

		return self::LoadData($result ? $statement->fetch(PDO::FETCH_ASSOC) : null);
	}

	public static function EditByUsername($username, $name, $building, $location, $phone_number)
	{
		global $database;

		$active = 1;

		$statement = $database->prepare("UPDATE clients SET name=?, building=?, location=?, phone_number=?, active=? WHERE username=?");
		$statement->bindParam(1, $name, PDO::PARAM_STR);
		$statement->bindParam(2, $building, PDO::PARAM_STR);
		$statement->bindParam(3, $location, PDO::PARAM_STR);
		$statement->bindParam(4, $phone_number, PDO::PARAM_STR);
		$statement->bindParam(5, $active, PDO::PARAM_INT);
		$statement->bindParam(6, $username, PDO::PARAM_STR);

		$statement->execute();
	}

	public function GetName()
	{
		return $this->GetSafe("name");
	}

	public function GetUsername()
	{
		return $this->GetSafe("username");
	}

	public function GetBuilding()
	{
		return $this->GetSafe("building");
	}

	public function GetLocation()
	{
		return $this->GetSafe("location");
	}

	public function GetPhoneNumber()
	{
		return $this->GetSafe("phone_number");
	}

	public function IsActive()
	{
		return intval($this->GetSafe("active")) === 1;
	}
}

?>
