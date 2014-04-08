<?php

class Ticket extends Base
{
	public static function Add($cid, $sid, $description, $status, $tags)
	{
		global $database;

		$current_time = time();

		$statement = $database->prepare("INSERT INTO tickets (cid, sid, creatorid, creation_date, last_modified_date, closed_date, description, status, tags, updates) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

		$zero = 0;

		$statement->bindParam(1, $cid, PDO::PARAM_INT);
		$statement->bindParam(2, $sid, PDO::PARAM_INT);
		$statement->bindParam(3, $sid, PDO::PARAM_INT);
		$statement->bindParam(4, $current_time, PDO::PARAM_INT);
		$statement->bindParam(5, $current_time, PDO::PARAM_INT);
		$statement->bindParam(6, $zero, PDO::PARAM_INT);
		$statement->bindParam(7, $description, PDO::PARAM_STR);
		$statement->bindParam(8, $status, PDO::PARAM_INT);
		$statement->bindParam(9, json_encode($tags), PDO::PARAM_STR);
		$statement->bindParam(10, json_encode(null), PDO::PARAM_STR);

		$statement->execute();

		return self::Load($database->lastInsertId());
	}

	public static function GetByStaffID($sid)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM tickets WHERE sid=?");
		$statement->bindParam(1, $sid, PDO::PARAM_INT);
		$statement->execute();

		$all = $statement->fetchAll(PDO::FETCH_ASSOC);

		for($i=0; $i < count($all); $i++)
		{
			$all[$i] = self::LoadData($all[$i]);
		}

		return $all;
	}

	public static function GetByStaffIDOrder($sid, $column, $dir)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM tickets WHERE sid=? ORDER BY ".$column." ".$dir." LIMIT 1");
		$statement->bindParam(1, $sid, PDO::PARAM_INT);
		$statement->execute();
		
		return self::LoadData($statement->fetch(PDO::FETCH_ASSOC));
	}

	public static function GetByStatus($status)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM tickets WHERE status=?");
		$statement->bindParam(1, $status, PDO::PARAM_INT);
		$statement->execute();

		$all = $statement->fetchAll(PDO::FETCH_ASSOC);

		for($i=0; $i < count($all); $i++)
		{
			$all[$i] = self::LoadData($all[$i]);
		}

		return $all;
	}

	public static function GetByStaffIDWithStatus($sid, $status)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM tickets WHERE sid=? AND status=?");
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

	public static function GetByStaffIDWithStatusRaw($sid, $status)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM tickets WHERE sid=? AND status=?");
		$statement->bindParam(1, $sid, PDO::PARAM_INT);
		$statement->bindParam(2, $status, PDO::PARAM_INT);
		$statement->execute();

		$all = $statement->fetchAll(PDO::FETCH_ASSOC);

		return $all;
	}

	public static function GetOpenTicketsInBuilding($building)
	{
		$tickets = array();

		$openTickets = self::GetByStatus(STATUS_OPENED);

		foreach($openTickets as $ticket)
		{
			$client = Client::Load($ticket->GetClientID());

			if($client->IsValid() && $client->GetBuilding() === $building)
			{
				array_push($tickets, $ticket);
			}
		}

		return $tickets;
	}

	public static function GetOpenTicketsInCommunity($building)
	{
		$community = Building::GetRealParent($building)->GetParent();

		$tickets = array();

		$openTickets = self::GetByStatus(STATUS_OPENED);

		foreach($openTickets as $ticket)
		{
			$client = Client::Load($ticket->GetClientID());

			if($client->IsValid() && Building::GetRealParent($client->GetBuilding())->GetParent() === $community)
			{
				array_push($tickets, $ticket);
			}
		}

		return $tickets;
	}

	public static function GetQuery($q)
	{
		global $me;

		switch($q)
		{
			case QUERY_MY_OPEN_TICKETS:
				return self::GetByStaffIDWithStatus($me->GetID(), STATUS_OPENED);

			case QUERY_ALL_MY_TICKETS:
				return self::GetByStaffID($me->GetID());

			case QUERY_OPEN_TICKETS_IN_MY_BUILDING:
				return self::GetOpenTicketsInBuilding($me->GetBuilding());

			case QUERY_OPEN_TICKETS_IN_MY_COMMUNITY:
				return self::GetOpenTicketsInCommunity($me->GetBuilding());

			case QUERY_ALL_TICKETS:
			default:
				return self::GetAll();

		}
	}

	public function GetClientID()
	{
		return $this->data["cid"];
	}

	public function GetStaffID()
	{
		return $this->data["sid"];
	}

	public function GetCreatorID()
	{
		return $this->data["creatorid"];
	}

	public function GetCreationDate()
	{
		return $this->data["creation_date"];
	}

	public function GetLastModifiedDate()
	{
		return $this->data["last_modified_date"];
	}

	public function GetClosedDate()
	{
		return $this->data["closed_date"];
	}

	public function GetDescription()
	{
		return $this->data["description"];
	}

	public function GetStatus()
	{
		return $this->data["status"];
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

		$current_time = time();

		$statement = $database->prepare("UPDATE tickets SET last_modified_date=?, closed_date=?, status=? WHERE id=?");
		$statement->bindParam(1, $current_time, PDO::PARAM_INT);
		self::SetStatusParam($statement, 2, $status);
		$statement->bindParam(3, $status, PDO::PARAM_INT);
		$statement->bindParam(4, self::GetID(), PDO::PARAM_INT);
		$statement->execute();

		$this->Reload();
	}

	public function SetStaffID($sid)
	{
		global $database;

		$current_time = time();

		$statement = $database->prepare("UPDATE tickets SET last_modified_date=?, sid=? WHERE id=?");
		$statement->bindParam(1, $current_time, PDO::PARAM_INT);
		$statement->bindParam(2, $sid, PDO::PARAM_INT);
		$statement->bindParam(3, self::GetID(), PDO::PARAM_INT);
		$statement->execute();

		$this->Reload();
	}

	public function Edit($sid, $description, $status, $tags)
	{
		global $database;

		$current_time = time();

		$statement = $database->prepare("UPDATE tickets SET sid=?, last_modified_date=?, closed_date=?, description=?, status=?, tags=? WHERE id=?");
		$statement->bindParam(1, $sid, $sid);
		$statement->bindParam(2, $current_time, PDO::PARAM_INT);
		self::SetStatusParam($statement, 3, $status);
		$statement->bindParam(4, $description, PDO::PARAM_STR);
		$statement->bindParam(5, $status, PDO::PARAM_INT);
		$statement->bindParam(6, json_encode($tags), PDO::PARAM_STR);
		$statement->bindParam(7, self::GetID(), PDO::PARAM_INT);
		$statement->execute();

		$this->Reload();
	}

	public function AddUpdate($id, $update)
	{
		global $database;

		$current_time = time();

		$updates = $this->GetUpdates();

		$newUpdate = array(
			"id" => $id,
			"time" => $current_time,
			"description" => strip_tags($update)
		);

		if(is_null($updates))
		{
			$updates = array();
		}
		
		array_push($updates, $newUpdate);

		$statement = $database->prepare("UPDATE tickets SET last_modified_date=?, updates=? WHERE id=?");
		$statement->bindParam(1, $current_time, PDO::PARAM_INT);
		$statement->bindParam(2, json_encode($updates), PDO::PARAM_STR);
		$statement->bindParam(3, self::GetID(), PDO::PARAM_INT);
		$statement->execute();

		$this->Reload();
	}

	public function GetEmailBody($clientid, $name, $community, $building, $room)
	{
		return "\n\nTicket #".$this->GetID()."\nClient ID: ".$clientid."\nClient Name: ".$name."\nCommunity: ".$community."\nBuilding: ".$building."\nRoom: ".$room."\nOpen Date: ".DisplayDatetime($this->GetCreationDate())."\nDescription: ".$this->GetDescription();
	}
}

?>
