<?php

require_once("requires.php");

if(Pages::GetCurrentPage() == "SMS")
{
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
	exit;
}

if(is_null($me))
{
	Pages::SetPage("Login");

	if(isset($_POST["login"]))
	{
		$username = CleanString($_POST["username"]);
		$password = $_POST["password"];

		if(empty($username) || empty($password))
		{
			$error = "Invalid username or password";
		}
		else
		{
			$staff = Staff::GetByUsername($username);
			if($staff->IsValid() && $staff->IsActive())
			{
				if($staff->GetPassword() == EncryptPassword($password))
				{
					Session::Set("sid", $staff->GetID());
					Session::Set("secret", $staff->GetPassword());

					Pages::SetPage("Dashboard");

					UpdateLoggedIn();
				}
				else
				{
					$error = "Invalid username or password";
				}
			}
			else
			{
				$error = "Invalid username or password";
			}
		}
	}
}
else if(Pages::GetCurrentPage() == "Typeahead")
{
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

	exit;
}
else if(Pages::GetCurrentPage() == "DT Clients")
{
	// Called when DataTables are updated
	// see https://datatables.net/release-datatables/examples/data_sources/server_side.html

	if(!isset($_GET['iDisplayStart']) || !isset($_GET['iDisplayLength']))
	{
		exit;
	}

	$aColumns = array("id", "name", "username", "building", "location", "phone_number");

	/*
		Paging
	*/
	$iDisplayLength = intval($_GET['iDisplayLength']);
	$iDisplayLength = $iDisplayLength > 100 ? 100 : $iDisplayLength;
	$sLimit = "LIMIT ".intval($_GET['iDisplayStart']).", ".$iDisplayLength;

	/*
	* Ordering
	*/
	$sOrder = "";
	if(isset($_GET['iSortCol_0']))
	{
		$sOrder = "ORDER BY ";
		for($i=0 ; $i < intval($_GET['iSortingCols']) ; $i++)
		{
			if($_GET['bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true")
			{
				$sOrder .= "`".$aColumns[intval( $_GET['iSortCol_'.$i])]."` ".($_GET['sSortDir_'.$i] === 'asc' ? 'asc' : 'desc') .", ";
			}
		}

		$sOrder = substr_replace($sOrder, "", -2);
		if($sOrder == "ORDER BY")
		{
			$sOrder = "";
		}
	}

	/*
		Searching
	*/
	$sWhere = "";
	if(isset($_GET['sSearch']) && $_GET['sSearch'] != "")
	{
		$sWhere = "WHERE (";

		for($i=0 ; $i < count($aColumns); $i++)
		{
			$sWhere .= $aColumns[$i]." LIKE ".$database->quote("%".$_GET['sSearch']."%")." OR ";
		}

		$sWhere = substr_replace($sWhere, "", -3);
		$sWhere .= ')';
	}

	/*
		Query
	*/
	$statement = $database->prepare("SELECT COUNT(*) FROM clients");
	$statement->execute();
	$totalNumRows = $statement->fetch(PDO::FETCH_NUM);

	$statement = $database->prepare("SELECT SQL_CALC_FOUND_ROWS ".implode(",", $aColumns)." FROM clients ".$sWhere." ".$sOrder." ".$sLimit);
	$statement->execute();

	$statement2 = $database->prepare("SELECT FOUND_ROWS()");
	$statement2->execute();
	$filteredNumRows = $statement2->fetch(PDO::FETCH_NUM);

	$rows = $statement->fetchAll(PDO::FETCH_NUM);

	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $totalNumRows[0],
		"iTotalDisplayRecords" => $filteredNumRows[0],
		"aaData" => $rows
	);

	echo json_encode($output);

	exit;
}
else if(Pages::GetCurrentPage() == "DT Tickets")
{
	// Called when DataTables are updated
	// see https://datatables.net/release-datatables/examples/data_sources/server_side.html

	if(!isset($_GET['iDisplayStart']) || !isset($_GET['iDisplayLength']) || !isset($_GET["q"]))
	{
		exit;
	}

	$q = intval($_GET["q"]);

	if($q == -1)
	{
		// dashboard open tickets panel
		$aColumns = array("id", "cid", "creation_date", "description");
	}
	else
	{
		$aColumns = array("id", "status", "cid", "creation_date", "description", "tags", "sid", "closed_date");
	}

	/*
		Paging
	*/
	$iDisplayLength = intval($_GET['iDisplayLength']);
	$iDisplayLength = $iDisplayLength > 100 ? 100 : $iDisplayLength;
	$sLimit = "LIMIT ".intval($_GET['iDisplayStart']).", ".$iDisplayLength;

	/*
	* Ordering
	*/
	$sOrder = "";
	if(isset($_GET['iSortCol_0']))
	{
		$sOrder = "ORDER BY ";
		for($i=0 ; $i < intval($_GET['iSortingCols']) ; $i++)
		{
			if($_GET['bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true")
			{
				$sOrder .= "`".$aColumns[intval( $_GET['iSortCol_'.$i])]."` ".($_GET['sSortDir_'.$i] === 'asc' ? 'asc' : 'desc') .", ";
			}
		}

		$sOrder = substr_replace($sOrder, "", -2);
		if($sOrder == "ORDER BY")
		{
			$sOrder = "";
		}
	}

	/*
		Searching
	*/
	$sWhere = "";
	if(isset($_GET['sSearch']) && $_GET['sSearch'] != "")
	{
		$sWhere = "WHERE (";

		for($i=0 ; $i < count($aColumns); $i++)
		{
			$sWhere .= $aColumns[$i]." LIKE ".$database->quote("%".$_GET['sSearch']."%")." OR ";
		}

		$sWhere = substr_replace($sWhere, "", -3);
		$sWhere .= ')';
	}

	if(strlen($sWhere) > 0)
	{
		$sWhere .= " AND ";
	}
	else
	{
		$sWhere = "WHERE ";
	}

	switch($q)
	{
		case -1: // dashboard open tickets panel
		case QUERY_MY_OPEN_TICKETS:
			$sWhere .= "sid=".$me->GetID()." AND status=".STATUS_OPENED."";
			break;

		case QUERY_ALL_MY_TICKETS:
			$sWhere .= "sid=".$me->GetID();
			break;

		case QUERY_OPEN_TICKETS_IN_MY_BUILDING:
			$sWhere .= "building=".$database->quote($me->GetBuilding())." AND status=".STATUS_OPENED."";
			break;

		case QUERY_OPEN_TICKETS_IN_MY_COMMUNITY:
			$inCommunity = true;
			$sWhere .= "community=".$database->quote(Building::GetCommunity($me->GetBuilding()))." AND status=".STATUS_OPENED."";
			break;

		case QUERY_ALL_TICKETS:
		default:
			$sWhere = substr_replace($sWhere, "", -4);
			break;

	}

	/*
		Query
	*/
	$statement = $database->prepare("SELECT COUNT(*) FROM tickets");
	$statement->execute();
	$totalNumRows = $statement->fetch(PDO::FETCH_NUM);

	$statement = $database->prepare("SELECT SQL_CALC_FOUND_ROWS ".implode(",", $aColumns)." FROM tickets ".$sWhere." ".$sOrder." ".$sLimit);
	$statement->execute();

	$statement2 = $database->prepare("SELECT FOUND_ROWS()");
	$statement2->execute();
	$filteredNumRows = $statement2->fetch(PDO::FETCH_NUM);

	$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

	$rowCount = count($rows);

	for($i=0; $i < $rowCount; $i++)
	{
		$row = $rows[$i];

		$color = "";

		if($q == -1)
		{
			$color = " danger";

			$rows[$i] = array(
				$row["id"],
				Client::Load($row["cid"])->GetUsername(),
				DisplayDatetime($row["creation_date"]),
				DisplayLimited($row["description"])
			);
		}
		else
		{
			$sid = $row["sid"];
			$status = $row["status"];

			if($sid == $me->GetID())
			{
				$color = " ".($status == STATUS_OPENED ? "danger" : "success");
			}

			$rows[$i] = array(
				$row["id"],
				($status == STATUS_OPENED ? "Opened" : "Closed"),
				Client::Load($row["cid"])->GetUsername(),
				DisplayDatetime($row["creation_date"]),
				DisplayLimited($row["description"]),
				implode(", ", json_decode($row["tags"], true)),
				Staff::Load($sid)->GetUsername(),
				DisplayDatetime($row["closed_date"])
			);
		}

		$rows[$i]["DT_RowClass"] = "linkrow".$color;		
	}

	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $totalNumRows[0],
		"iTotalDisplayRecords" => $filteredNumRows[0],
		"aaData" => $rows
	);

	echo json_encode($output);

	exit;
}

// TODO: Only load scripts on pages they are needed

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $system_name." :: ".Pages::GetCurrentPage(); ?></title>
	<meta name="description" content="<?php echo $system_name." Help Desk System"; ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="favicon.ico">

	<link rel="stylesheet" href="resources/bootstrap.min.css">
	<link rel="stylesheet" href="resources/sb-admin.css">
	<link rel="stylesheet" href="resources/font-awesome.min.css">
	<link rel="stylesheet" href="resources/bootstrap-select.min.css">
	<link rel="stylesheet" href="resources/jasny-bootstrap.min.css">
	<link rel="stylesheet" href="resources/dataTables.bootstrap.css">
	<link rel="stylesheet" href="resources/morris.css">
	<link rel="stylesheet" href="resources/style.css">	

	<script src="resources/jquery.min.js"></script>
	<script src="resources/bootstrap.min.js"></script>
	<script src="resources/jquery.metisMenu.js"></script>
	<script src="resources/bootstrap-select.min.js"></script>
	<script src="resources/typeahead.bundle.js"></script>
	<script src="resources/jasny-bootstrap.min.js"></script>
	<script src="resources/jquery.dataTables.js"></script>
	<script src="resources/dataTables.bootstrap.js"></script>
	<script src="resources/raphael-min.js"></script>
	<script src="resources/morris.min.js"></script>

	<script src="resources/script.js"></script>
</header>

<body>
	<div id="wrapper">

		<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="navbar-header">
			<?php
			if(!is_null($me))
			{
			?>
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			<?php
			}
			?>
				<a class="navbar-brand" href="index.php">
					<img src="images/logo.png" width="42" alt="<?php echo $system_name; ?> Logo" />
					<?php echo $system_name;?>
				</a>
			</div>

			<?php
			if(!is_null($me))
			{
			?>
				<ul class="nav navbar-top-links navbar-right">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<?php echo $me->GetName(); ?> [<?php echo $me->GetPoints(); ?>] <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
						</a>
						<ul class="dropdown-menu dropdown-messages">
							<li>
								<a href="index.php?p=profile"><i class="fa fa-user fa-fw"></i> User Profile</a>
							</li>
							<li>
								<a href="index.php?p=settings"><i class="fa fa-gear fa-fw"></i> Settings</a>
							</li>
							<li class="divider"></li>
							<li>
								<a href="index.php?p=logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
							</li>
						</ul>
					</li>
				</ul>

				<div class="navbar-default navbar-static-side" role="navigation">
					<div class="sidebar-collapse">
						<ul class="nav" id="side-menu">
							<li>
								<a href="index.php?p=dashboard"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
							</li>
							<li>
								<a href="index.php?p=new_ticket"><i class="fa fa-ticket fa-fw"></i> New Ticket</a>
							</li>
							<li>
								<a href="index.php?p=tickets"><i class="fa fa-search fa-fw"></i> View Tickets</a>
							</li>

							<?php
							if($me->GetType() == TYPE_SUPERSTAFF)
							{
								?>
								<li>
									<a href="index.php?p=admin"><i class="fa fa-lock fa-fw"></i> Administration Panel</a>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
				</div>
			<?php
			}
			?>
		</nav>

		<?php
		if(!is_null($me))
		{
			?>
			<div id="page-wrapper">
				<?php
				if(Pages::GetCurrentPage() !== "Ticket")
				{
				?>
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header"><?php echo Pages::GetCurrentPage(); ?></h1>
					</div>
				</div>
				<?php
			}
		}
		
		include("pages/".Pages::CleanPage(Pages::GetCurrentPage()).".php");

		if(!is_null($me))
		{
			?>
			</div>
			<?php
		}
		?>

	</div>

</body>

</html>
