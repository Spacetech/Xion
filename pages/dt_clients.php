<?php

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

?>
