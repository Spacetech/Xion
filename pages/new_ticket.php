<?php

if(isset($_POST["ticket"]))
{
	$clientid = CleanString($_POST["clientid"]);
	$description = $_POST["description"];

	if(!isset($_POST["tags"]))
	{
		ShowError("You must specify at least one tag!");
	}
	else
	{
		$tags = $_POST["tags"];

		$client = Client::GetByUsername($clientid);

		if(empty($clientid) || empty($description) || empty($tags))
		{
			ShowError("One or more fields were empty!");
		}
		elseif(!$client->IsValid())
		{
			ShowError("Invalid Client ID.");
		}
		else
		{
			$ticket = Ticket::Add($client->GetID(), $me->GetID(), $description, STATUS_OPENED, $tags, $client->GetBuilding(), Building::GetCommunity($client->GetBuilding()));

			$me->IncrementPoints(5);

			ShowInfo("Created Ticket Successfully");

			RedirectTimer("ticket&amp;id=".$ticket->GetID(), 0);
		}
	}
}
else
{

?>

<form class="form-horizontal" role="form" method="post">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					Client Information
				</div>
				<div class="panel-body">
					<div class="form-group">
						<label for="clientid" class="col-sm-2 control-label">Client ID</label>
						<div class="col-sm-10">
							<input  class="form-control" id="clientid" name="clientid" placeholder="Enter the client's username" autocomplete="off" spellcheck="off">
						</div>
					</div>
					<div class="form-group">
						<label for="name" class="col-sm-2 control-label">Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="name" disabled>
						</div>
					</div>
					<div class="form-group">
						<label for="community" class="col-sm-2 control-label">Community</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="community" disabled>
						</div>
					</div>
					<div class="form-group">
						<label for="Building" class="col-sm-2 control-label">Building</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" id="building" disabled>
						</div>
						<label for="Room" class="col-sm-1 control-label">Room</label>
						<div class="col-sm-3">
							<input type="text" class="form-control" id="room" disabled>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-7">
			<div class="panel panel-default">
				<div class="panel-heading">
					Description
				</div>
				<div class="panel-body">
					<textarea class="form-control" rows="5" id="description" name="description" style="max-width: 100%; width: 100%;"></textarea>
				</div>
			</div>
		</div>
		<div class="col-lg-5">
			<div class="panel panel-default">
				<div class="panel-heading">
					Tags
				</div>
				<div class="panel-body">
					<select id="tags" name="tags[]" multiple>
						<?php
						$parents = Tag::GetUniqueParents();
						foreach($parents as $parent)
						{
							$children = Tag::GetChildren($parent);
							echo "<optgroup label=\"".$parent."\">";
							for($i=0; $i < count($children); $i++)
							{
								echo "<option>".$children[$i]->GetChild()."</option>";
							}
							echo "</optgroup>";
						}
						?>
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<button type="submit" name="ticket" class="btn btn-lg btn-success btn-block">Submit Ticket</button>
		</div>
	</div>
</form>

<?php

}

?>
