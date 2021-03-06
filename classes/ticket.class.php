<?php

class Ticket extends Base
{
	public static function Add($cid, $sid, $description, $status, $tags, $building, $community)
	{
		global $database;

		$zero = 0;
		$current_time = time();
		$encodedTags = json_encode($tags);
		$encodedNull = json_encode(null);

		$statement = $database->prepare("INSERT INTO tickets (cid, sid, creatorid, creation_date, last_modified_date, closed_date, description, status, tags, updates, building, community) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

		$statement->bindParam(1, $cid, PDO::PARAM_INT);
		$statement->bindParam(2, $sid, PDO::PARAM_INT);
		$statement->bindParam(3, $sid, PDO::PARAM_INT);
		$statement->bindParam(4, $current_time, PDO::PARAM_INT);
		$statement->bindParam(5, $current_time, PDO::PARAM_INT);
		$statement->bindParam(6, $zero, PDO::PARAM_INT);
		$statement->bindParam(7, $description, PDO::PARAM_STR);
		$statement->bindParam(8, $status, PDO::PARAM_INT);
		$statement->bindParam(9, $encodedTags, PDO::PARAM_STR);
		$statement->bindParam(10, $encodedNull, PDO::PARAM_STR);
		$statement->bindParam(11, $building, PDO::PARAM_STR);
		$statement->bindParam(12, $community, PDO::PARAM_STR);

		$statement->execute();

		return self::Load($database->lastInsertId());
	}

	public static function GetByStaffIDOrderSingle($sid, $column, $dir)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM tickets WHERE sid=? ORDER BY ".$column." ".$dir." LIMIT 1");
		$statement->bindParam(1, $sid, PDO::PARAM_INT);
		$statement->execute();
		
		return self::LoadData($statement->fetch(PDO::FETCH_ASSOC));
	}

	public static function GetByStaffIDWithStatusCount($sid, $status)
	{
		global $database;

		$statement = $database->prepare("SELECT COUNT(*) FROM tickets WHERE sid=? AND status=?");
		$statement->bindParam(1, $sid, PDO::PARAM_INT);
		$statement->bindParam(2, $status, PDO::PARAM_INT);
		$result = $statement->execute();

		return $result ? $statement->fetch(PDO::FETCH_NUM)[0] : 0;
	}

	public static function GetByStaffIDWithStatusOrderLimit($sid, $status, $column, $dir, $limit)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM tickets WHERE sid=? AND status=? ORDER BY ".$column." ".$dir." LIMIT ".$limit);
		$statement->bindParam(1, $sid, PDO::PARAM_INT);
		$statement->bindParam(2, $status, PDO::PARAM_INT);
		$statement->execute();

		$all = $statement->fetchAll(PDO::FETCH_ASSOC);

		for($i=0; $i < count($all); $i++)
		{
			$all[$i] = self::LoadData($all[$i]);
		}

		return $all;
	}

	public function GetClientID()
	{
		return $this->GetSafe("cid");
	}

	public function GetStaffID()
	{
		return $this->GetSafe("sid");
	}

	public function GetCreatorID()
	{
		return $this->GetSafe("creatorid");
	}

	public function GetCreationDate()
	{
		return $this->GetSafe("creation_date");
	}

	public function GetLastModifiedDate()
	{
		return $this->GetSafe("last_modified_date");
	}

	public function GetClosedDate()
	{
		return $this->GetSafe("closed_date");
	}

	public function GetDescription()
	{
		return $this->GetSafe("description");
	}

	public function GetStatus()
	{
		return $this->GetSafe("status");
	}

	public function GetTags()
	{
		return json_decode($this->data["tags"], true);
	}

	public function GetUpdates()
	{
		return json_decode($this->data["updates"], true);
	}

	private function SetStatusParam($statement, $num, $status)
	{
		$zero = 0;
		$current_time = time();

		if(self::GetStatus() == STATUS_OPENED && self::GetClosedDate() == 0 && $status == STATUS_CLOSED)
		{
			$statement->bindParam($num, $current_time, PDO::PARAM_INT);
		}
		else
		{
			$statement->bindParam($num, $zero, PDO::PARAM_INT);
		}
	}
	
	public function SetStatus($status)
	{
		global $database;

		$id = self::GetID();
		$current_time = time();

		$statement = $database->prepare("UPDATE tickets SET last_modified_date=?, closed_date=?, status=? WHERE id=?");
		$statement->bindParam(1, $current_time, PDO::PARAM_INT);
		self::SetStatusParam($statement, 2, $status);
		$statement->bindParam(3, $status, PDO::PARAM_INT);
		$statement->bindParam(4, $id, PDO::PARAM_INT);
		$statement->execute();

		$this->Reload();
	}

	public function SetStaffID($sid)
	{
		global $database;

		$id = self::GetID();
		$current_time = time();

		$statement = $database->prepare("UPDATE tickets SET last_modified_date=?, sid=? WHERE id=?");
		$statement->bindParam(1, $current_time, PDO::PARAM_INT);
		$statement->bindParam(2, $sid, PDO::PARAM_INT);
		$statement->bindParam(3, $id, PDO::PARAM_INT);
		$statement->execute();

		$this->Reload();
	}

	public function Edit($sid, $description, $status, $tags)
	{
		global $database;

		$id = self::GetID();
		$current_time = time();
		$encodedTags = json_encode($tags);

		$statement = $database->prepare("UPDATE tickets SET sid=?, last_modified_date=?, closed_date=?, description=?, status=?, tags=? WHERE id=?");
		$statement->bindParam(1, $sid, $sid);
		$statement->bindParam(2, $current_time, PDO::PARAM_INT);
		self::SetStatusParam($statement, 3, $status);
		$statement->bindParam(4, $description, PDO::PARAM_STR);
		$statement->bindParam(5, $status, PDO::PARAM_INT);
		$statement->bindParam(6, $encodedTags, PDO::PARAM_STR);
		$statement->bindParam(7, $id, PDO::PARAM_INT);
		$statement->execute();

		$this->Reload();
	}

	public function AddUpdate($sid, $update)
	{
		global $database;

		$id = self::GetID();
		$current_time = time();

		$updates = $this->GetUpdates();

		$newUpdate = array(
			"id" => $sid,
			"time" => $current_time,
			"description" => strip_tags($update)
		);

		if(is_null($updates))
		{
			$updates = array();
		}
		
		array_push($updates, $newUpdate);

		$encodedUpdates = json_encode($updates);

		$statement = $database->prepare("UPDATE tickets SET last_modified_date=?, updates=? WHERE id=?");
		$statement->bindParam(1, $current_time, PDO::PARAM_INT);
		$statement->bindParam(2, $encodedUpdates, PDO::PARAM_STR);
		$statement->bindParam(3, $id, PDO::PARAM_INT);
		$statement->execute();

		$this->Reload();
	}

	public function GetEmailBody($clientid, $name, $community, $building, $room)
	{
		return "\n\nTicket #".$this->GetID()."\nClient ID: ".$clientid."\nClient Name: ".$name."\nCommunity: ".$community."\nBuilding: ".$building."\nRoom: ".$room."\nOpen Date: ".DisplayDatetime($this->GetCreationDate())."\nDescription: ".$this->GetDescription();
	}
}

?>
