<?php

class ParentChild
{
	private $child;
	private $parent;

	public function __construct($child, $parent)
	{
		$this->child = $child;
		$this->parent = $parent;
	}

	private static function GetTable()
	{
		switch(get_called_class())
		{
			case "Tag":
				return "tags";

			case "Building":
				return "buildings";

			default:
				return null;
		}
	}

	public static function Add($child, $parent)
	{
		global $database;

		$statement = $database->prepare("INSERT INTO ".self::GetTable()." (child, parent) VALUES(?, ?)");
		$statement->bindParam(1, $child, PDO::PARAM_STR);
		$statement->bindParam(2, $parent, PDO::PARAM_STR);
		$statement->execute();
	}

	public static function Remove($child)
	{
		global $database;

		$statement = $database->prepare("DELETE FROM ".self::GetTable()." WHERE child=?");
		$statement->bindParam(1, $child, PDO::PARAM_STR);
		$statement->execute();
	}

	public static function Exists($child)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM ".self::GetTable()." WHERE child=? LIMIT 1");
		$statement->bindParam(1, $child, PDO::PARAM_STR);
		$result = $statement->execute();

		$fetch = $statement->fetch(PDO::FETCH_ASSOC);

		return $result && is_array($fetch) && count($fetch) > 0;
	}

	public static function GetUniqueParents()
	{
		global $database;

		$statement = $database->prepare("SELECT parent FROM ".self::GetTable()." GROUP BY parent");
		$statement->execute();

		$all = $statement->fetchAll(PDO::FETCH_ASSOC);

		for($i=0; $i < count($all); $i++)
		{
			$all[$i] = $all[$i]["parent"];
		}

		return $all;
	}

	public static function GetAll()
	{
		global $database;

		$class = get_called_class();

		$statement = $database->prepare("SELECT * FROM ".self::GetTable());
		$statement->execute();

		$all = $statement->fetchAll(PDO::FETCH_ASSOC);

		for($i=0; $i < count($all); $i++)
		{
			$all[$i] = new $class($all[$i]["child"], $all[$i]["parent"]);
		}

		return $all;
	}

	public static function GetRealParent($child)
	{
		global $database;

		$class = get_called_class();

		$statement = $database->prepare("SELECT parent FROM ".self::GetTable()." WHERE child=? LIMIT 1");
		$statement->bindParam(1, $child, PDO::PARAM_STR);
		$result = $statement->execute();

		$row = $statement->fetch(PDO::FETCH_ASSOC);

		if($result && is_array($row))
		{
			return new $class($child, $row["parent"]);
		}

		return new $class($child, "N/A");
	}

	public static function GetChildren($parent)
	{
		global $database;

		$class = get_called_class();

		$statement = $database->prepare("SELECT * FROM ".self::GetTable()." WHERE parent=?");
		$statement->bindParam(1, $parent, PDO::PARAM_STR);
		$statement->execute();

		$all = $statement->fetchAll(PDO::FETCH_ASSOC);

		for($i=0; $i < count($all); $i++)
		{
			$all[$i] = new $class($all[$i]["child"], $all[$i]["parent"]);
		}

		return $all;
	}

	public function GetChild()
	{
		return $this->child;
	}

	public function GetParent()
	{
		return $this->parent;
	}
	
}

?>
