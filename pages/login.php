<div class="container">
	<div class="row">
		<div class="col-md-4 col-md-offset-4">
			<div class="login-panel panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Login</h3>
				</div>
				<div class="panel-body">
					<form role="form" method="post">
						<fieldset>
							<?php
							if(is_string($error))
							{
								ShowError($error, true);
							}
							?>
							<div class="form-group">
								<input type="text" class="form-control" name="username" placeholder="Enter username" autofocus>
							</div>
							<div class="form-group">
								<input type="password" class="form-control" name="password" placeholder="Enter password">
							</div>
							<button type="submit" name="login" class="btn btn-lg btn-success btn-block">Login</button>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
