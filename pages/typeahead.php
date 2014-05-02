<?php

	// Called when TypeAhead autocomplete are updated

	$data = array();

	if(isset($_GET["un"]))
	{
		$un = CleanString($_GET["un"]);

		$client = Client::GetByUsername($un);

		if($client->IsValid() && $client->IsActive())
		{
			$data["name"] = $client->GetName();
			$data["building"] = $client->GetBuilding();
			$data["location"] = $client->GetLocation();
			$data["phone_number"] = $client->GetPhoneNumber();
			$data["community"] = Building::GetCommunity($client->GetBuilding());
		}
	}
	else if(isset($_GET["sq"]))
	{
		$query = CleanString($_GET["sq"]);

		$staffs = Staff::GetAll();

		foreach($staffs as $staff)
		{
			if($staff->IsActive() && strpos($staff->GetUsername(), $query) === 0)
			{
				array_push($data, $staff->GetUsername());
			}
		}
	}
	else
	{
		$query = CleanString($_GET["q"]);

		$data = Client::GetUsernameBeginsWith($query);
	}

	echo json_encode($data);
	
?>
