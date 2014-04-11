<?php

$openTickets = Ticket::GetByStaffIDWithStatusCount($me->GetID(), STATUS_OPENED);
$closedTickets = Ticket::GetByStaffIDWithStatusCount($me->GetID(), STATUS_CLOSED);

?>

<div class="row">
	<div class="col-lg-6">
		<div class="panel panel-danger">
			<div class="panel-heading">
				<i class="fa fa-circle-o fa-fw"></i> Open Tickets
			</div>
			<div class="panel-body nopadding">
				<table class="table table-bordered table-striped table-hover dt-tickets" data-q="-1">
					<thead>
						<tr>
							<th width="25px">#</th>
							<th width="55px">Client</th>
							<th width="120px">Opened Date</th>
							<th width="55px">Description</th>
						</tr>
					</thead>
					<tbody class="searchable rowlink" data-link="row">
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel panel-success">
			<div class="panel-heading">
				<i class="fa fa-circle fa-fw"></i> Recently Closed Tickets
			</div>
			<div class="panel-body nopadding">
				<table class="table table-bordered table-striped table-hover">
					<thead>
						<tr>
							<th>#</th>
							<th>Client</th>
							<th>Opened Date</th>
							<th>Closed Date</th>
						</tr>
					</thead>
					<tbody class="searchable rowlink" data-link="row">
						<?php
						$tickets = Ticket::GetByStaffIDWithStatusOrderLimit($me->GetID(), STATUS_CLOSED, "closed_date", "DESC", 20);

						foreach($tickets as $ticket)
						{
							$client = Client::Load($ticket->GetClientID());

							echo "<tr class='linkrow success' href='index.php?p=ticket&amp;id=".$ticket->GetID()."'>";
							echo "<td>".$ticket->GetID()."</td>";
							echo "<td>".$client->GetUsername()."</td>";
							echo "<td>".DisplayDatetime($ticket->GetCreationDate())."</td>";
							echo "<td>".DisplayDatetime($ticket->GetClosedDate())."</td>";
							echo "</tr>";
						}

						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> Lifetime Ticket Stats
			</div>
			<div class="panel-body nopadding">
				<div id="morris-lifetime-ticket-stats"></div>
				<?php
				if($openTickets == 0 && $closedTickets == 0)
				{
					echo "No stats available.";
				}
				?>
			</div>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> Open Tickets per Community
			</div>
			<div class="panel-body nopadding">
				<div id="morris-calls-open-per-community"></div>
			</div>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> Leaderboards
			</div>
			<div class="panel-body nopadding">
				<table class="table table-bordered table-striped table-hover nodatatable">
					<thead>
						<tr>
							<th>#</th>
							<th>Username</th>
							<th>Name</th>
							<th>Points</th>
						</tr>
					</thead>
					<tbody>
						<?php

						$num = 1;
						$staffs = Staff::GetTop10();

						foreach($staffs as $staff)
						{
							echo "<tr>";
							echo "<td>".$num."</td>";
							echo "<td>".$staff->GetUsername()."</td>";
							echo "<td>".$staff->GetName()."</td>";
							echo "<td>".$staff->GetPoints()."</td>";
							echo "</tr>";

							$num++;
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	Morris.Donut({
		element: 'morris-lifetime-ticket-stats',
		data: [
		<?php 
		if($openTickets > 0)
		{
		?>
			{
				label: "Open Tickets",
				value: <?php echo $openTickets; ?>
			},
		<?php
		}
		if($closedTickets > 0)
		{
		?>
		{
			label: "Closed Tickets",
			value: <?php echo $closedTickets; ?>
		}
		<?php
		}
		?>
		],
		colors: [
		<?php

		if($openTickets > 0)
		{
			echo '"#ff0000",';
		}

		if($closedTickets > 0)
		{
			echo '"#00ff00",';
		}
		?>
		],
		resize: true
	});

	Morris.Bar({
		element: 'morris-calls-open-per-community',
		data: [
<?php

$statement = $database->prepare("SELECT community, COUNT(*) FROM tickets GROUP BY community");
$statement->execute();

$all = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach($all as $community)
{
	echo "{ x: \"".$community["community"]. "\", y: ".$community["COUNT(*)"]." },";
}

?>
		],
		xkey: 'x',
		ykeys: ['y'],
		labels: ['Open Tickets'],
		hideHover: 'auto',
		resize: true
	});

});
</script>
