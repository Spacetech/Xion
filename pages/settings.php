<?php

if(isset($_POST["save"]))
{
	$password = $_POST["password"];
	$password_confirm = $_POST["password_confirm"];
	$name = $_POST["name"];
	$email = CleanString($_POST["email"]);
	$phone_number = CleanString($_POST["phone_number"]);

	if(empty($name) || empty($email) || empty($phone_number))
	{
		ShowError("One or more fields were empty!");
	}
	elseif($password != $password_confirm)
	{
		ShowError("Passwords did not match! Go back and try again.");
	}
	else
	{
		if(strlen($password) > 0)
		{
			$me->SetPassword(EncryptPassword($password));
		}
		
		$me->Edit($name, $me->GetType(), $me->GetBuilding(), $email, $phone_number);

		ShowInfo("Saved Settings");
	}
}

?>

<div class="container">
	<div class="row">
		<div class="col-sm-4">
			<form class="form-horizontal" role="form" method="post">
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
					<input type="text" class="form-control" id="name" name="name" value="<?php echo $me->GetName(); ?>">
				</div>
				<div class="form-group">
					<label for="email">Email</label>
					<input type="email" class="form-control" id="email" name="email" value="<?php echo $me->GetEmail(); ?>">
				</div>
				<div class="form-group">
					<label for="phone_number">Phone Number</label>
					<input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo $me->GetPhoneNumber(); ?>">
				</div>
				<div class="form-group">
					<button type="submit" name="save" class="btn btn-default">Save Settings</button>
				</div>
			</form>
		</div>
	</div>

</div>
