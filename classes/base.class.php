<?php

class Base
{
	protected $data = null;

	private function __construct()
	{

	}

	public function __destruct()
	{

	}

	private static function GetTable()
	{
		switch(get_called_class())
		{
			case "Client":
				return "clients";

			case "Staff":
				return "staff";

			case "Ticket":
				return "tickets";

			default:
				return null;
		}
	}

	public static function Remove($id)
	{
		global $database;

		$statement = $database->prepare("DELETE FROM ".self::GetTable()." WHERE id=? LIMIT 1");
		$statement->bindParam(1, $id, PDO::PARAM_INT);
		$statement->execute();
	}

	public static function LoadRaw($id)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM ".self::GetTable()." WHERE id=? LIMIT 1");
		$statement->bindParam(1, $id, PDO::PARAM_INT);
		$result = $statement->execute();

		return $result ? $statement->fetch(PDO::FETCH_ASSOC) : null;
	}

	public static function Load($id)
	{
		return self::LoadData(self::LoadRaw($id));
	}

	public static function LoadData($data)
	{
		$class = get_called_class();
		$instance = new $class();
		$instance->data = $data;
		return $instance;
	}

	public function Reload()
	{
		$this->data = self::LoadRaw($this->GetID());
	}

	public function IsValid()
	{
		return is_array($this->data);
	}

	public static function GetAll()
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM ".self::GetTable());
		$statement->execute();

		//$all = $statement->fetchAll(PDO::FETCH_ASSOC);
		$ret = array();
		
		while($data = $statement->fetch(PDO::FETCH_ASSOC))
		{
			array_push($ret, self::LoadData($data));
		}

		return $ret;
	}

	public function GetID()
	{
		return $this->GetSafe("id");
	}

	public function GetSafe($key)
	{
		return is_null($this->data) ? "N/A" : $this->data[$key];
	}
}

?>
