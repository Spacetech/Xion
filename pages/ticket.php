<?php

$ticket = null;

if(isset($_GET["id"]))
{
	$ticket = Ticket::Load($_GET["id"]);
}

if(is_null($ticket) || !$ticket->IsValid())
{
	ShowError("Invalid ticket.");
}
else
{

$creator = Staff::Load($ticket->GetCreatorID());
if($creator->IsValid())
{
	$creator = $creator->GetUsername();
}
else
{
	$creator = "N/A";
}

$client = Client::Load($ticket->GetClientID());

$clientid = "N/A";
$name = "N/A";
$community = "N/A";
$building = "N/A";
$room = "N/A";

if($client->IsValid())
{
	$clientid = $client->GetUsername();
	$name = $client->GetName();
	$community = Building::GetRealParent($client->GetBuilding())->GetParent();
	$building = $client->GetBuilding();
	$room = $client->GetLocation();
}

?>

<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header"><?php echo Pages::GetCurrentPage()." #".$ticket->GetID(); ?></h1>
	</div>
</div>

<?php

if(isset($_POST["update"]))
{
	$update_description = $_POST["update_description"];
	
	if(empty($update_description))
	{
		ShowError("You must enter an update description.", true);
	}
	else
	{
		$me->IncrementPoints(2);
		$ticket->AddUpdate($me->GetID(), $update_description);
	}
}
elseif(isset($_POST["reassign"]))
{
	$staff = Staff::GetByUsername($_POST["staffusername"]);
	if($staff->IsValid())
	{
		if($staff->GetID() == $ticket->GetStaffID())
		{
			ShowError("You can not reassign a ticket to the same person!", true);
		}
		else
		{
			$body = $me->GetName(). " (".$me->GetUsername().") has assigned you a ticket!";

			$staff->SendTextMessage($body." Reply for more information.");

			$body .= $ticket->GetEmailBody($clientid, $name, $community, $building, $room);

			$staff->SendEmail("Ticket Assignment", $body);

			$ticket->SetStaffID($staff->GetID());
			ShowInfo("Reassigned Successfully");
		}
	}
	else
	{
		ShowError("Invalid staff member username", true);
	}
}
elseif(isset($_GET["status"]))
{
	$status = $_GET["status"];

	if(($status == STATUS_OPENED || $status == STATUS_CLOSED) && $status != $ticket->GetStatus())
	{
		ShowInfo("The ticket has been ".($status == STATUS_OPENED ? "Opened" : "Closed").".");
		$ticket->SetStatus($status);
	}
}

$staff = Staff::Load($ticket->GetStaffID());

$opened = $ticket->GetStatus() == STATUS_OPENED;

$assignedTo = "N/A";
$assignedToMe = false;

if($staff->IsValid())
{
	$assignedTo = $staff->GetUsername();
	if($staff->GetID() == $me->GetID())
	{
		$assignedToMe = true;
	}
}

?>

<div class="row" style="padding-bottom: 10px;">
	<div class="col-lg-12">
		<a href="index.php?p=tickets" class="btn btn-default"><i class="fa fa-arrow-left"></i> View all Tickets</a>

		<span style="float: right;">
			<?php
			if($opened)
			{
				?>
				<a class="btn btn-default" href="#reassign_ticket" data-toggle="modal" ><i class="fa fa-reply"></i> Reassign Ticket</a>

				<a href="index.php?p=ticket&amp;id=<?php echo $ticket->GetID(); ?>&amp;status=<?php echo STATUS_CLOSED; ?>" class="btn btn-default"><i class="fa fa-folder"></i> Close Ticket</a>
				<?php
			}
			else
			{
				?>
				<a href="index.php?p=ticket&amp;id=<?php echo $ticket->GetID(); ?>&amp;status=<?php echo STATUS_OPENED; ?>" class="btn btn-default"><i class="fa fa-folder-open"></i> Re-Open</a>
				<?php
			}
			?>
		</span>

		<div class="modal fade" id="reassign_ticket" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<form class="form" role="form" method="post">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">Reassign Ticket</h4>
						</div>
						<div class="modal-body">
							<p>Who do you want to reassign this ticket to?</p>
							<div class="form-group">
								<input  class="form-control" id="staffusername" name="staffusername" placeholder="Enter the staff member's username" autocomplete="off" spellcheck="off">
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							<button type="submit" name="reassign" class="btn btn-default">Reassign</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-7">
		<div class="panel panel-default">
			<div class="panel-heading">
				Client Information
			</div>
			<div class="panel-body">
				<form class="form-horizontal" role="form">
					<div class="form-group">
						<label for="clientid" class="col-sm-2 control-label">Client ID</label>
						<div class="col-sm-10">
							<input class="form-control" id="clientid" name="clientid" value="<?php echo $clientid; ?>" disabled>
						</div>
					</div>
					<div class="form-group">
						<label for="Name" class="col-sm-2 control-label">Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="name" value="<?php echo $name; ?>" disabled>
						</div>
					</div>
					<div class="form-group">
						<label for="community" class="col-sm-2 control-label">Community</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="community" value="<?php echo $community; ?>" disabled>
						</div>
					</div>
					<div class="form-group">
						<label for="Building" class="col-sm-2 control-label">Building</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="building" value="<?php echo $building; ?>" disabled>
						</div>
						<label for="Room" class="col-sm-1 control-label">Room</label>
						<div class="col-sm-3">
							<input type="text" class="form-control" id="room" value="<?php echo $room; ?>" disabled>
						</div>
					</div>
				</form>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				Description
			</div>
			<div class="panel-body">
				<textarea class="form-control" rows="7" id="description" name="description" style="max-width: 100%; width: 100%;" disabled><?php echo $ticket->GetDescription(); ?></textarea>
			</div>
		</div>
	</div>
	<div class="col-lg-5">
		<div class="panel panel-default">
			<div class="panel-heading">
				Incident Info
			</div>
			<div class="panel-body">
				<form class="form-horizontal" role="form">
					<div class="form-group">
						<label for="incidentID" class="col-sm-4 control-label">Incident ID</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="incidentid" value="<?php echo $ticket->GetID(); ?>" disabled>
						</div>
					</div>
					<div class="form-group<?php echo ($creator === $me->GetUsername() ? " has-success": ""); ?>">
						<label for="opened_by" class="col-sm-4 control-label">Opened By</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="opened_by" value="<?php echo $creator; ?>" disabled>
						</div>
					</div>
					<div class="form-group">
						<label for="opened" class="col-sm-4 control-label">Opened Date</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="opened" value="<?php echo DisplayDatetime($ticket->GetCreationDate()); ?>" disabled>
						</div>
					</div>
					<div class="form-group">
						<label for="modified" class="col-sm-4 control-label">Last Modified</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="modified" value="<?php echo DisplayDatetime($ticket->GetLastModifiedDate()); ?>" disabled>
						</div>
					</div>
					<div class="form-group">
						<label for="closed" class="col-sm-4 control-label">Closed Date</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="closed" value="<?php echo DisplayDatetime($ticket->GetClosedDate()); ?>" disabled>
						</div>
					</div>
					<div class="form-group<?php echo ($opened ? " has-error": " has-success"); ?>">
						<label for="status" class="col-sm-4 control-label">Status</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="status" value="<?php echo $opened ? "Open" : "Closed"; ?>" disabled>
						</div>
					</div>
					<div class="form-group<?php echo ($assignedToMe ? " has-success" : ""); ?>">
						<label for="assignedto" class="col-sm-4 control-label">Assigned To</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="assignedto" value="<?php echo $assignedTo; ?>" disabled>
						</div>
					</div>
				</form>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				Tags
			</div>
			<div class="panel-body">
				<textarea class="form-control" rows="2" id="description" name="description" style="max-width: 100%; width: 100%;" disabled><?php echo !is_array($ticket->GetTags()) ? $ticket->GetTags() : implode(", ", $ticket->GetTags()); ?></textarea>
			</div>
		</div>
	</div>
</div>

<?php

$updates = $ticket->GetUpdates();

if(!is_null($updates))
{
?>
<div class="row">
	<div class="col-lg-12">
		<div class="panel-group" id="accordion">
		<?php
		for($i=0; $i < count($updates); $i++)
		{
			$update = $updates[$i];

			$id = $update["id"];
			$time = $update["time"];
			$description = $update["description"];
			
			$update_staff = Staff::Load($id);

			?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-target="#collapse_<?php echo $i; ?>">Update #<?php echo $i + 1; ?> - <?php echo $update_staff->IsValid() ? $update_staff->GetUsername() : "N/A"; ?> - <?php echo DisplayDatetime($time); ?></a>
					</h4>
				</div>
				<div id="collapse_<?php echo $i; ?>" class="panel-collapse collapse in">
					<div class="panel-body">
						<?php echo $description; ?>
					</div>
				</div>
			</div>
			<!--
					<div class="panel panel-default">
						<div class="panel-heading">
							Update #<?php echo $i; ?> - <?php echo $update_staff->IsValid() ? $update_staff->GetUsername() : "N/A"; ?> - <?php echo DisplayDatetime($time); ?>
						</div>
						<div class="panel-body">
							<?php echo $description; ?>
						</div>
					</div>-->
			<?php
		}
		?>
		</div>
	</div>
</div>

<?php
}

if($opened)
{
	?>
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					Submit Update
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="post">
						<textarea class="form-control" rows="3" id="update_description" name="update_description" style="max-width: 100%; width: 100%; margin-bottom: 10px;" placeholder="Enter your update description here..."></textarea>
						<button type="submit" id="update" name="update" class="btn btn-lg btn-success btn-block">Submit Update</button>
					</form>
				</div>
			</div>
			
		</div>
	</div>
	<?php
}

}
?>
