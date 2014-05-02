<?php
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

?>
