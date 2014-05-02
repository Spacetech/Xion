<?php

require_once("requires.php");

if(Pages::GetCurrentPage() == "SMS")
{
	include("pages/sms.php");
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
elseif(Pages::GetCurrentPage() == "Typeahead")
{
	include("pages/typeahead.php");
	exit;
}
elseif(Pages::GetCurrentPage() == "DT Clients")
{
	include("pages/dt_clients.php");
	exit;
}
elseif(Pages::GetCurrentPage() == "DT Tickets")
{
	include("pages/dt_tickets.php");
	exit;
}
elseif(Pages::GetCurrentPage() == "Admin" && $me->GetType() != TYPE_SUPERSTAFF)
{
	Pages::SetPage("Dashboard");
}

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

		?>

		<div class="row global-footer">
			<div class="col-lg-12">
				<a href="https://github.com/Spacetech/Xion">Open Source on GitHub</a>
			</div>
		</div>

		<?php

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
