<?php

	$staffs = Staff::GetAll();

	$correct = null;

	foreach($staffs as $staff)
	{
		if($staff->IsValid())
		{
			$phoneNumber = $staff->GetPhoneNumber();
			$phoneNumber = preg_replace('/[^0-9]/','', $phoneNumber);

			if(strlen($phoneNumber) === 10)
			{
				$phoneNumber = "1".$phoneNumber;
			}

			$phoneNumber = "+".$phoneNumber;

			if($phoneNumber === $_REQUEST['From'])
			{
				$correct = $staff;
				break;
			}
		}
	}

	if($correct === null)
	{
		exit;
	}

	$lastTicket = Ticket::GetByStaffIDOrderSingle($staff->GetID(), "last_modified_date", "DESC");
	if(!$lastTicket->IsValid())
	{
		exit;
	}

	$client = Client::Load($lastTicket->GetClientID());
	if(!$client->IsValid())
	{
		exit;
	}

	$body = "Client Information\nID: ".$client->GetUsername()."\nName: ".$client->GetName()."\nCommunity: ".Building::GetCommunity($client->GetBuilding())."\nBuilding: ".$client->GetBuilding()."\nRoom: ".$client->GetLocation();

	$remaining = 255 - strlen($body);

	if($remaining > 50)
	{
		$body .= "\nDescription: ";

		$remaining = 255 - strlen($body);

		$body .= DisplayLimited($lastTicket->GetDescription());
	}

    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <Sms><?php echo $body; ?></Sms>
</Response><?php

?>
