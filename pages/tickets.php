<?php

$q = QUERY_OPEN_TICKETS_IN_MY_COMMUNITY;

if(isset($_GET["q"]))
{
	$q = $_GET["q"];
}

function DisplayQueryRow($q, $retValue = false)
{
	$value = "";

	switch($q)
	{
		case QUERY_MY_OPEN_TICKETS:
			$value = "My Open Tickets";
			break;

		case QUERY_ALL_MY_TICKETS:
			$value = "All my Tickets";
			break;

		case QUERY_OPEN_TICKETS_IN_MY_BUILDING:
			$value = "Open Tickets in my Building";
			break;

		case QUERY_OPEN_TICKETS_IN_MY_COMMUNITY:
			$value = "Open Tickets in my Community";
			break;

		case QUERY_ALL_TICKETS:
			$value = "All Tickets";
			break;

		default:
			$value = "N/A";
			break;
	}

	if($retValue)
	{
		return $value;
	}

	echo "<li><a href=\"index.php?p=tickets&amp;q=".$q."\">".$value."</a></li>";
}

?>

<form role="form" action="index.php" method="get">

<div class="row">
	<div class="col-sm-4">
		<div class="form-group less-margin-bottom">
			<label for="query" class="control-label fixlabel">Query</label>
			<div class="input-group">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?php echo DisplayQueryRow($q, true); ?> <span class="caret"></span></button>
				<ul class="dropdown-menu">
					<?php
					for($i=QUERY_FIRST; $i <= QUERY_LAST; $i++)
					{
						DisplayQueryRow($i);
					}
					?>
				</ul>
			</div>
		</div>
	</div>
	<div class="col-sm-4">
	</div>
	<div class="col-md-4">	
		<div class="form-group">
			<label for="ticket_number" class="control-label fixlabel">Go To Ticket</label>
			<div class="input-group">
				<input type="hidden" name="p" value="ticket" />
				<input type="number" class="form-control" id="ticket_number" name="id" min="0" placeholder="Enter Ticket ID">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-default">Go</button>
				</span>
			</div>
		</div>
	</div>
</div>
</form>

<?php

$tickets = Ticket::GetQuery($q);

if(count($tickets) == 0)
{
	ShowInfo("No tickets found");
}
else
{

?>

<table class="table table-bordered table-striped table-hover">
	<thead>
		<tr>
			<th style="min-width:30px;">#</th>
			<th style="min-width:55px;">Status</th>
			<th style="min-width:70px;">Client ID</th>
			<th>Opened Date</th>
			<th>Description</th>
			<th>Assigned To</th>
			<th>Closed</th>
		<!--
			<th style="min-width:30px;">#</th>
			<th style="min-width:55px;">Status</th>
			<th style="min-width:70px;">Client ID</th>
			<th style="min-width:145px;">Opened Date</th>
			<th>Description</th>
			<th style="min-width:100px;">Assigned To</th>
			<th style="min-width:145px;">Closed</th>
			-->
		</tr>
	</thead>
	<tbody class="searchable rowlink" data-link="row">
		<?php

		foreach($tickets as $ticket)
		{
			$client = Client::Load($ticket->GetClientID());
			$staff = Staff::Load($ticket->GetStaffID());

			$color =  "";

			if($ticket->GetStaffID() == $me->GetID())
			{
				$color = " ".($ticket->GetStatus() == STATUS_OPENED ? "danger" : "success");
			}

			echo "<tr class='linkrow".$color."' href='index.php?p=ticket&amp;id=".$ticket->GetID()."'>";
			echo "<td>".$ticket->GetID()."</td>";
			echo "<td>".($ticket->GetStatus() == STATUS_OPENED ? "Opened" : "Closed")."</td>";
			echo "<td>".($client->IsValid() ? $client->GetUsername() : "N/A")."</td>";
			echo "<td>".DisplayDatetime($ticket->GetCreationDate())."</td>";
			echo "<td>".DisplayLimited($ticket->GetDescription())."</td>";
			echo "<td>".($staff ? $staff->GetUsername() : "N/A")."</td>";
			echo "<td>".DisplayDatetime($ticket->GetClosedDate())."</td>";
			echo "</tr>";
		}
		?>
	</tbody>
</table>

<?php

}

?>
