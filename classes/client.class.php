<?php

class Client extends Base
{
	//private $markForDeletion = true;

	public static function Add($name, $username, $building, $location, $phone_number)
	{
		global $database;

		$statement = $database->prepare("INSERT INTO clients (name, username, building, location, phone_number) VALUES(?, ?, ?, ?, ?)");
		$statement->bindParam(1, $name, PDO::PARAM_STR);
		$statement->bindParam(2, $username, PDO::PARAM_STR);
		$statement->bindParam(3, $building, PDO::PARAM_STR);
		$statement->bindParam(4, $location, PDO::PARAM_STR);
		$statement->bindParam(5, $phone_number, PDO::PARAM_STR);
		$statement->execute();

		return self::Load($database->lastInsertId());
	}

	public static function GetByUsername($username)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM clients WHERE username=? LIMIT 1");
		$statement->bindParam(1, $username, PDO::PARAM_STR);
		$result = $statement->execute();

		return self::LoadData($result ? $statement->fetch(PDO::FETCH_ASSOC) : null);
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

	public function Edit($name, $building, $location, $phone_number)
	{
		global $database;

		$id = $this->GetID();

		$statement = $database->prepare("UPDATE clients SET name=?, building=?, location=?, phone_number=? WHERE id=?");
		$statement->bindParam(1, $name, PDO::PARAM_STR);
		$statement->bindParam(2, $building, PDO::PARAM_STR);
		$statement->bindParam(3, $location, PDO::PARAM_STR);
		$statement->bindParam(4, $phone_number, PDO::PARAM_STR);
		$statement->bindParam(5, $id, PDO::PARAM_INT);
		$statement->execute();

		//$this->Reload();
	}
}

?>
