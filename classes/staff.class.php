<?php

require_once "Mail.php";

class Staff extends Base
{
	public static function Add($name, $type, $username, $password, $building, $email, $phone_number)
	{
		global $database;

		$points = 0;

		$statement = $database->prepare("INSERT INTO staff (name, type, username, password, building, email, phone_number, points) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
		$statement->bindParam(1, $name, PDO::PARAM_STR);
		$statement->bindParam(2, $type, PDO::PARAM_INT);
		$statement->bindParam(3, $username, PDO::PARAM_STR);
		$statement->bindParam(4, $password, PDO::PARAM_STR);
		$statement->bindParam(5, $building, PDO::PARAM_STR);
		$statement->bindParam(6, $email, PDO::PARAM_STR);
		$statement->bindParam(7, $phone_number, PDO::PARAM_STR);
		$statement->bindParam(8, $points, PDO::PARAM_INT);
		$statement->execute();

		return self::Load($database->lastInsertId());
	}

	public static function GetByUsername($username)
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM staff WHERE username=? LIMIT 1");
		$statement->bindParam(1, $username, PDO::PARAM_STR);
		$result = $statement->execute();

		return self::LoadData($result ? $statement->fetch(PDO::FETCH_ASSOC) : null);
	}

	public static function GetTop10()
	{
		global $database;

		$statement = $database->prepare("SELECT * FROM staff ORDER BY points DESC LIMIT 10");
		$result = $statement->execute();

		$all = $statement->fetchAll(PDO::FETCH_ASSOC);

		for($i=0; $i < count($all); $i++)
		{
			$all[$i] = self::LoadData($all[$i]);
		}

		return $all;
	}

	public static function GetTypeReal($type)
	{
		switch($type)
		{
			case TYPE_SUPERSTAFF:
				return "Administrator";

			case TYPE_STAFF:
				return "Normal Staff";

			case TYPE_RESCON:
				return "Rescon";

			default:
				return "Unknown";
		}
	}

	public function GetName()
	{
		return $this->GetSafe("name");
	}

	public function GetUsername()
	{
		return $this->GetSafe("username");
	}

	public function GetPassword()
	{
		return $this->GetSafe("password");
	}

	public function GetBuilding()
	{
		return $this->GetSafe("building");
	}

	public function GetEmail()
	{
		return $this->GetSafe("email");
	}

	public function GetPhoneNumber()
	{
		return $this->GetSafe("phone_number");
	}

	public function GetType()
	{
		return $this->GetSafe("type");
	}

	public function GetPoints()
	{
		return $this->GetSafe("points");
	}

	public function IncrementPoints($amount)
	{
		global $database;

		$id = $this->GetID();
		$newPoints = $this->GetPoints() + $amount;

		$statement = $database->prepare("UPDATE staff SET points=? WHERE id=?");
		$statement->bindParam(1, $newPoints, PDO::PARAM_INT);
		$statement->bindParam(2, $id, PDO::PARAM_INT);
		$statement->execute();

		$this->Reload();
	}

	public function SendTextMessage($body)
	{
		global $twilio;

		try
		{
			$twilio->account->messages->create(array(
				'To' => $this->GetPhoneNumber(),
				'From' => "+15167143229",
				'Body' => $body
			));
		}
		catch (Exception $ex)
		{
			// blah
		}
	}

	public function SendEmail($title, $body)
	{
		global $system_name;
		global $emailArray;
		
		try
		{
			$headers = array(
				'From' => $emailArray["username"],
				'To' => $this->GetEmail(),
				'Subject' => "[".$system_name."] ".$title
			);

			$smtp = Mail::factory('smtp', $emailArray);

			$mail = $smtp->send($this->GetEmail(), $headers, $body);
		}
		catch (InvalidArgumentException $e)
		{
			//ShowError("Unable to send email", true);
		}
	}

	public function SetPassword($password)
	{
		global $database;

		$id = $this->GetID();

		$statement = $database->prepare("UPDATE staff SET password=? WHERE id=?");
		$statement->bindParam(1, $password, PDO::PARAM_STR);
		$statement->bindParam(2, $id, PDO::PARAM_INT);
		$statement->execute();

		//$this->Reload();
	}

	public function Edit($name, $type, $building, $email, $phone_number)
	{
		global $database;

		$id = $this->GetID();

		$statement = $database->prepare("UPDATE staff SET name=?, type=?, building=?, email=?, phone_number=? WHERE id=?");
		$statement->bindParam(1, $name, PDO::PARAM_STR);
		$statement->bindParam(2, $type, PDO::PARAM_INT);
		$statement->bindParam(3, $building, PDO::PARAM_STR);
		$statement->bindParam(4, $email, PDO::PARAM_STR);
		$statement->bindParam(5, $phone_number, PDO::PARAM_STR);
		$statement->bindParam(6, $id, PDO::PARAM_INT);
		$statement->execute();

		$this->Reload();
	}

	public function GetCountClosedTickets()
	{
		return count(Ticket::GetByStaffIDWithStatusRaw($this->GetID(), STATUS_CLOSED));
	}
}

?>
