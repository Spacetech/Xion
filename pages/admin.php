<p>
	<a class="btn btn-default" href="index.php?p=admin&amp;clients"><span class="glyphicon glyphicon-book"></span> Clients</a>
	<a class="btn btn-default" href="index.php?p=admin&amp;tags"><span class="glyphicon glyphicon-tags"></span> Tags</a>
	<a class="btn btn-default" href="index.php?p=admin&amp;buildings"><span class="glyphicon glyphicon-tower"></span> Buildings</a>
	<a class="btn btn-default" href="index.php?p=admin&amp;staff"><span class="glyphicon glyphicon-user"></span> Staff Accounts</a>
</p>

<?php

if(isset($_GET["staff"]))
{
	if(isset($_POST["add"]))
	{
		$username = CleanString($_POST["username"]);
		$password = $_POST["password"];
		$password_confirm = $_POST["password_confirm"];
		$name = $_POST["name"];
		$building = $_POST["building"];
		$email = CleanString($_POST["email"]);
		$phone_number = CleanString($_POST["phone_number"]);
		$type = $_POST["type"];

		if(empty($username) || empty($password) || empty($name) || empty($building) || empty($email) || empty($phone_number))
		{
			ShowError("One or more fields were empty!");
		}
		elseif($password != $password_confirm)
		{
			ShowError("Passwords did not match! Go back and try again.");
		}
		elseif(!Building::Exists($building) && $building !== "N/A")
		{
			ShowError("Invalid building.");
		}
		else
		{
			$staff = Staff::GetByUsername($username);

			if($staff->IsValid())
			{
				if($staff->IsActive())
				{
					ShowError("A staff member with that username already exists");
				}
				else
				{
					$staff->SetPassword(EncryptPassword($password));

					$staff->Edit($name, $type, $building, $email, $phone_number);

					ShowInfo("Created Staff Member Successfully");

					RedirectTimer("admin&amp;staff", 3);
				}
			}
			else
			{
				Staff::Add($name, $type, $username, EncryptPassword($password), $building, $email, $phone_number);

				ShowInfo("Created Staff Member Successfully");

				RedirectTimer("admin&amp;staff", 3);
			}
		}
	}
	elseif(isset($_GET["add"]))
	{
		?>
		<form class="form-horizontal" role="form" method="post">
			<div class="form-group">
				<label for="username">Username</label>
				<input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
			</div>
			<div class="form-group">
				<label for="password">Password</label>
				<input type="password" class="form-control" id="password" name="password" placeholder="Password">
			</div>
			<div class="form-group">
				<label for="password_confirm">Confirm Password</label>
				<input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Re-enter password">
			</div>
			<div class="form-group">
				<label for="name">Full Name</label>
				<input type="text" class="form-control" id="name" name="name" placeholder="Enter name">
			</div>

			<div class="form-group">
				<label for="building">Building</label>
				<select class="form-control" id="building" name="building">
					<option value="N/A">N/A</option>
					<?php
					$parents = Building::GetUniqueParents();
					foreach($parents as $parent)
					{
						$children = Building::GetChildren($parent);
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

			<div class="form-group">
				<label for="email">Email</label>
				<input type="email" class="form-control" id="email" name="email" placeholder="Enter email">
			</div>

			<div class="form-group">
				<label for="phone_number">Phone Number</label>
				<input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Enter phone number">
			</div>

			<div class="form-group">
				<label for="type">Account Type</label>
				<select class="form-control" id="type" name="type">
					<?php
					for($i=TYPE_FIRST; $i <= TYPE_LAST; $i++)
					{
						echo "<option value=\"".$i."\">".Staff::GetTypeReal($i)."</option>";
					}
					?>
				</select>
			</div>

			<button type="submit" name="add" class="btn btn-default">Create Account</button>
		</form>
		<?php
	}
	elseif(isset($_GET["id"]))
	{
		$staff = Staff::Load($_GET["id"]);
		if($staff->IsValid() && $staff->IsActive() && $me->GetType() == TYPE_SUPERSTAFF)
		{
			if(isset($_POST["edit"]))
			{
				$password = $_POST["password"];
				$password_confirm = $_POST["password_confirm"];
				$name = $_POST["name"];
				$building = $_POST["building"];
				$email = CleanString($_POST["email"]);
				$phone_number = CleanString($_POST["phone_number"]);
				$type = $_POST["type"];

				if(empty($name) || empty($building) || empty($email) || empty($phone_number))
				{
					ShowError("One or more fields were empty!");
				}
				elseif($password != $password_confirm)
				{
					ShowError("Passwords did not match! Go back and try again.");
				}
				elseif(!Building::Exists($building) && $building !== "N/A")
				{
					ShowError("Invalid building.");
				}
				elseif($me->GetID() == $staff->GetID() && $me->GetType() != $type)
				{
					ShowError("You can't change your account access!");
				}
				else
				{
					if(strlen($password) > 0)
					{
						$staff->SetPassword(EncryptPassword($password));
					}

					$staff->Edit($name, $type, $building, $email, $phone_number);

					ShowInfo("Edited Staff Member Successfully");

					RedirectTimer("admin&amp;staff", 3);
				}
			}
			elseif(isset($_GET["edit"]))
			{
				?>
				<form class="form-horizontal" role="form" method="post">
					<div class="form-group">
						<label for="username">Username</label>
						<input type="text" class="form-control" id="username" name="username" value="<?php echo $staff->GetUsername(); ?>" disabled>
					</div>
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" class="form-control" id="password" name="password" placeholder="Password">
						<p class="help-block">Leave blank to keep the current password.</p>
					</div>
					<div class="form-group">
						<label for="password_confirm">Confirm Password</label>
						<input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Re-enter password">
						<p class="help-block">Leave blank to keep the current password.</p>
					</div>
					<div class="form-group">
						<label for="name">Full Name</label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo $staff->GetName(); ?>">
					</div>
					<div class="form-group">
						<label for="building">Building</label>
						<select class="form-control" id="building" name="building">
							<?php

							$selected = "";
							if($staff->GetBuilding() === "N/A")
							{
								$selected = " selected";
							}
							echo "<option".$selected.">N/A</option>";

							$parents = Building::GetUniqueParents();
							foreach($parents as $parent)
							{
								$children = Building::GetChildren($parent);
								echo "<optgroup label=\"".$parent."\">";
								for($i=0; $i < count($children); $i++)
								{
									$name = $children[$i]->GetChild();
									$selected = "";
									if($staff->GetBuilding() === $name)
									{
										$selected = " selected";
									}
									echo "<option".$selected.">".$name."</option>";
								}
								echo "</optgroup>";
							}

							?>
						</select>
					</div>
					<div class="form-group">
						<label for="email">Email</label>
						<input type="email" class="form-control" id="email" name="email" value="<?php echo $staff->GetEmail(); ?>">
					</div>
					<div class="form-group">
						<label for="phone_number">Phone Number</label>
						<input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo $staff->GetPhoneNumber(); ?>">
					</div>
					<div class="form-group">
						<label for="type">Account Type</label>
						<select class="form-control" id="type" name="type">
							<?php
							for($i=TYPE_FIRST; $i <= TYPE_LAST; $i++)
							{
								if($staff->GetType() == $i)
								{
									echo "<option value=\"".$i."\" selected>".Staff::GetTypeReal($i)."</option>";
								}
								else
								{
									echo "<option value=\"".$i."\">".Staff::GetTypeReal($i)."</option>";
								}
							}
							?>
						</select>
					</div>
					<div class="form-group">
						<button type="submit" name="edit" class="btn btn-default">Edit Account</button>
					</div>
				</form>
				<?php
			}
			elseif(isset($_GET["delete"]))
			{
				$staff->SetActive(0);
				ShowInfo("Successfully deleted account");
				RedirectTimer("admin&amp;accounts", 3);
			}
		}
		else
		{
			ShowError("Unknown staff member");
		}
	}
	else
	{
		?>

		<p>
			<a href="index.php?p=admin&amp;staff&amp;add"><button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Add Staff Member</button></a>
		</p>

		<table class="table table-bordered table-striped table-hover">
			<thead>
				<tr>
					<th>#</th>
					<th>Full Name</th>
					<th>Account Type</th>
					<th>Username</th>
					<th>Building</th>
					<th>Email</th>
					<th>Phone Number</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody class="searchable">
				<?php
				foreach(Staff::GetAll() as $staff)
				{
					if(!$staff->IsActive())
					{
						continue;
					}

					echo "<tr>";
					echo "<td>".$staff->GetID()."</td>";
					echo "<td>".$staff->GetName()."</td>";
					echo "<td>".Staff::GetTypeReal($staff->GetType())."</td>";
					echo "<td>".$staff->GetUsername()."</td>";
					echo "<td>".$staff->GetBuilding()."</td>";
					echo "<td><a href=\"mailto:".$staff->GetEmail()."\">".$staff->GetEmail()."</a></td>";
					echo "<td>".$staff->GetPhoneNumber()."</td>";

					echo "<td>";
					echo "<a class=\"btn btn-default btn-sm\" href=\"index.php?p=admin&amp;staff&amp;id=".$staff->GetID()."&amp;edit\" title=\"Edit Account\"><span class=\"glyphicon glyphicon-pencil\"></span></a> ";
					echo "<a class=\"btn btn-default btn-sm staff_delete\" href=\"#confirm_delete\" data-id=\"".$staff->GetID()."\" data-toggle=\"modal\" title=\"Delete Account\"><span class=\"glyphicon glyphicon-trash\"></span></a>";
					echo "</td>";

					echo "</tr>";
				}
				?>
			</tbody>
		</table>

		<div class="modal fade" id="confirm_delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Confirm Staff Member Account Deletion</h4>
					</div>
					<div class="modal-body">
						Are you sure you want to delete this staff members account?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<a class="btn btn-primary staff_delete_button" href="#" >Delete</a>
					</div>
				</div>
			</div>
		</div>

		<?php
	}
}
elseif(isset($_GET["clients"]))
{
	if(isset($_POST["add"]))
	{
		$name = $_POST["name"];
		$username = CleanString($_POST["username"]);
		$building = $_POST["building"];
		$location = $_POST["location"];
		$phone_number = CleanString($_POST["phone_number"]);

		if(empty($name) || empty($username) || empty($building) || empty($location) || empty($phone_number))
		{
			ShowError("One or more fields were empty!");
		}
		elseif(!Building::Exists($building) && $building !== "N/A")
		{
			ShowError("Invalid building.");
		}
		else
		{
			$client = Client::GetByUsername($username);

			if($client->IsValid())
			{
				if($client->IsActive())
				{
					ShowError("A client with that username already exists");
				}
				else
				{
					Client::EditByUsername($username, $name, $building, $location, $phone_number);

					ShowInfo("Successfully Added Client");

					RedirectTimer("admin&amp;clients", 3);
				}
			}
			else
			{
				Client::Add($name, $username, $building, $location, $phone_number);

				ShowInfo("Successfully Added Client");

				RedirectTimer("admin&amp;clients", 3);
			}
		}
	}
	elseif(isset($_POST["upload"]))
	{
		if(!isset($_FILES['file']['error']))
		{
			ShowError("Unable to upload file.");
		}
		else
		{
			$good = false;

			switch ($_FILES['file']['error']) {
				case UPLOAD_ERR_OK:
					$good = true;
					break;

				case UPLOAD_ERR_NO_FILE:
					ShowError("No file was selected.");
					break;

				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					ShowError("Exceeded filesize limit.");
					break;

				default:
					ShowError("Unknown upload error.");
					break;
			}

			if($good)
			{
				$filename = "data/clients_".time().".csv";

				if(!move_uploaded_file($_FILES['file']['tmp_name'], $filename))
				{
					ShowError("Unable to move uploaded file.");
				}
				else
				{
					$handle = @fopen($filename, "r");

					if($handle)
					{
						// we are working with a lot of data here
						// we rarely update the client list, this should be fine
						ini_set("memory_limit", "512M");

						// mark all current clients as inactive
						Client::SetAllInactive();

						$database->beginTransaction();

						// get the usernames of all the current clients
						$usernames = Client::GetUsernames();

						// for faster lookup
						$usernames_fast = array();
						foreach(array_values($usernames) as $v)
						{
							$usernames_fast[$v] = 1;
						}


						while(($line = fgets($handle)) !== false)
						{
							if(strpos($line, "#") === 0)
							{
								continue;
							}

							$data = explode(",", $line);

							$done = false;
							$username = CleanString($data[1]);

							if(isset($usernames_fast[$username]))
							{
								// update current client
								Client::EditByUsername($username, $data[0], $data[2], $data[3], $data[4]);
							}
							else
							{
								// add new client
								Client::Add($data[0], $data[1], $data[2], $data[3], $data[4]);
							}
						}

						fclose($handle);

						$database->commit();

						ShowInfo("Done");

						RedirectTimer("admin&amp;clients", 3);
					}
				}
			}
		}
	}
	elseif(isset($_GET["upload"]))
	{
		?>
		<form role="form" method="post" enctype="multipart/form-data">
			<div class="form-group">
				<label for="file">Client Database File</label>
				<input type="file" class="form-control" id="file" name="file">
				<p class="help-block">Select the client database file.</p>
				<h2>This will merge all existing clients with the ones from the file.</h2>
			</div>
			<div class="form-group">
				<button type="submit" name="upload" class="btn btn-default">Upload Client Database File</button>
			</div>
		</form>
		<?php
	}
	elseif(isset($_GET["add"]))
	{
		?>
		<form class="form-horizontal" role="form" method="post">

			<div class="form-group">
				<label for="name">Full Name</label>
				<input type="text" class="form-control" id="name" name="name" placeholder="Enter name">
			</div>

			<div class="form-group">
				<label for="username">Username</label>
				<input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
			</div>

			<div class="form-group">
				<label for="building">Building</label>
				<select class="form-control" id="building" name="building">
					<option value="N/A">N/A</option>
					<?php
					$parents = Building::GetUniqueParents();
					foreach($parents as $parent)
					{
						$children = Building::GetChildren($parent);
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

			<div class="form-group">
				<label for="location">Location (Room Number)</label>
				<input type="text" class="form-control" id="location" name="location" placeholder="Enter location">
			</div>

			<div class="form-group">
				<label for="phone_number">Phone Number</label>
				<input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Enter phone number">
			</div>

			<button type="submit" name="add" class="btn btn-default">Add Client</button>
		</form>
		<?php
	}
	else
	{
	?>
		<p>
			<a href="index.php?p=admin&amp;clients&amp;add"><button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Add Client</button></a>
			<a href="index.php?p=admin&amp;clients&amp;upload"><button type="button" class="btn btn-default"><span class="glyphicon glyphicon-refresh"></span> Upload Client Database</button></a>
		</p>

		<table class="table table-bordered table-striped table-hover dt-clients">
			<thead>
				<tr>
					<th>#</th>
					<th>Full Name</th>
					<th>Username</th>
					<th>Building</th>
					<th>Room</th>
					<th>Phone Number</th>
				</tr>
			</thead>
			<tbody class="searchable">
			</tbody>
		</table>
	<?php
	}
}
elseif(isset($_GET["tags"]))
{
	if(isset($_POST["add"]))
	{
		$name = $_POST["name"];
		$parent = $_POST["parent"];

		if(empty($name) || empty($parent))
		{
			ShowError("One or more fields were empty!");
		}
		elseif(Tag::Exists($name))
		{
			ShowError("A tag with that name already exists");
		}
		else
		{
			Tag::Add($name, $parent);

			ShowInfo("Created Tag Successfully");

			RedirectTimer("admin&amp;tags", 0);
		}
	}
	elseif(isset($_GET["add"]))
	{
		?>
		<form class="form-horizontal" role="form" method="post">
			<div class="form-group">
				<label for="username">Tag Name</label>
				<input type="text" class="form-control" id="name" name="name" placeholder="Enter tag name">
			</div>
			<div class="form-group">
				<label for="password">Tag Category</label>
				<input type="text" class="form-control" id="parent" name="parent" placeholder="Enter tag category">
			</div>
			<div class="form-group">
				<button type="submit" name="add" class="btn btn-default">Create Tag</button>
			</div>
		</form>
		<?php
	}
	elseif(isset($_GET["name"]) && isset($_GET["delete"]))
	{
		if(Tag::Exists($_GET["name"]))
		{
			Tag::Remove($_GET["name"]);

			ShowInfo("Removed Tag Successfully");

			RedirectTimer("admin&amp;tags", 0);
		}
		else
		{
			ShowError("Invalid tag name.");
		}
	}
	else
	{
		?>

		<p>
			<a href="index.php?p=admin&amp;tags&amp;add"><button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Create Tag</button></a>
		</p>
		
		<table class="table table-bordered table-striped table-hover">
			<thead>
				<tr>
					<th>Name</th>
					<th>Category</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody class="searchable">
				<?php
				foreach(Tag::GetAll() as $tag)
				{
					echo "<tr>";
					echo "<td>".$tag->GetChild()."</td>";
					echo "<td>".$tag->GetParent()."</td>";
					
					echo "<td>";
					echo "<a class=\"btn btn-default btn-sm tag_delete\" href=\"#confirm_delete\" data-name=\"".$tag->GetChild()."\" data-toggle=\"modal\" title=\"Delete Tag\"><span class=\"glyphicon glyphicon-trash\"></span></a>";
					echo "</td>";

					echo "</tr>";
				}
				?>
			</tbody>
		</table>

		<div class="modal fade" id="confirm_delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Confirm Tag Deletion</h4>
					</div>
					<div class="modal-body">
						Are you sure you want to delete this tag?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<a class="btn btn-primary tag_delete_button" href="#">Delete</a>
					</div>
				</div>
			</div>
		</div>
<?php
	}
}
elseif(isset($_GET["buildings"]))
{
	if(isset($_POST["add"]))
	{
		$name = $_POST["name"];
		$parent = $_POST["parent"];

		if(empty($name) || empty($parent))
		{
			ShowError("One or more fields were empty!");
		}
		elseif(Building::Exists($name))
		{
			ShowError("A building with that name already exists");
		}
		else
		{
			Building::Add($name, $parent);

			ShowInfo("Added Building Successfully");

			RedirectTimer("admin&amp;buildings", 0);
		}
	}
	elseif(isset($_GET["add"]))
	{
		?>
		<form class="form-horizontal" role="form" method="post">
			<div class="form-group">
				<label for="username">Building Name</label>
				<input type="text" class="form-control" id="name" name="name" placeholder="Enter building name">
			</div>
			<div class="form-group">
				<label for="password">Building Community</label>
				<input type="text" class="form-control" id="parent" name="parent" placeholder="Enter building community">
			</div>
			<div class="form-group">
				<button type="submit" name="add" class="btn btn-default">Add Building</button>
			</div>
		</form>
		<?php
	}
	elseif(isset($_GET["name"]) && isset($_GET["delete"]))
	{
		if(Building::Exists($_GET["name"]))
		{
			Building::Remove($_GET["name"]);

			ShowInfo("Removed Building Successfully");

			RedirectTimer("admin&amp;buildings", 0);
		}
		else
		{
			ShowError("Invalid building name.");
		}
	}
	else
	{
		?>

		<p>
			<a href="index.php?p=admin&amp;buildings&amp;add"><button type="button" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Add Building</button></a>
		</p>
		
		<table class="table table-bordered table-striped table-hover">
			<thead>
				<tr>
					<th>Name</th>
					<th>Community</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody class="searchable">
				<?php
				foreach(Building::GetAll() as $building)
				{
					echo "<tr>";
					echo "<td>".$building->GetChild()."</td>";
					echo "<td>".$building->GetParent()."</td>";
					
					echo "<td>";
					echo "<a class=\"btn btn-default btn-sm building_delete\" href=\"#confirm_delete\" data-name=\"".$building->GetChild()."\" data-toggle=\"modal\" title=\"Delete Building\"><span class=\"glyphicon glyphicon-trash\"></span></a>";
					echo "</td>";

					echo "</tr>";
				}
				?>
			</tbody>
		</table>

		<div class="modal fade" id="confirm_delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Confirm Building Deletion</h4>
					</div>
					<div class="modal-body">
						Are you sure you want to delete this building?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<a class="btn btn-primary building_delete_button" href="#">Delete</a>
					</div>
				</div>
			</div>
		</div>
<?php
	}
}

?>
