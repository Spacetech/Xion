<?php

$profile = $me;

/*
if(isset($_GET["id"]))
{
	$profile = Staff::Load($_GET["id"]);
}
*/

if(!$profile->IsValid())
{
	ShowError("Invalid profile.");
}
else
{
	?>
	<h2><?php echo $profile->GetName()." (".$profile->GetUsername().")"; ?></h2>

	<p>Account Type: <?php echo Staff::GetTypeReal($profile->GetType()); ?></p>

	<p>Community: <?php echo Building::GetCommunity($profile->GetBuilding()); ?></p>

	<p>Building: <?php echo $profile->GetBuilding(); ?></p>

	<?php
}

?>
