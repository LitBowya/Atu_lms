<?php
// user_login.php
include 'database_connection.php';
include 'function.php';

if (is_user_login()) {
	header('location:issue_book_details.php');
	exit;
}

$message = '';

if (isset($_POST["login_button"])) {
	$formdata = [];

	if (empty($_POST["user_name"])) {
		$message .= '<li>Username is required</li>';
	} else {
		$formdata['user_name'] = trim($_POST['user_name']);
	}

	if (empty($_POST['user_password'])) {
		$message .= '<li>Password is required</li>';
	} else {
		$formdata['user_password'] = trim($_POST['user_password']);
	}

	if ($message == '') {
		// Prepare and execute the query to check for the user
		$data = [':user_name' => $formdata['user_name']];
		$query = "SELECT * FROM lms_user WHERE user_name = :user_name";
		$statement = $connect->prepare($query);
		$statement->execute($data);

		if ($statement->rowCount() > 0) {
			$row = $statement->fetch(PDO::FETCH_ASSOC);

			// Check if user status is 'Disable'
			if ($row['user_status'] == 'Disable') {
				$message = '<li>User is not active</li>';
			} else {
				// User is active, check the password
				if (password_verify($formdata['user_password'], $row['user_password'])) {
					// Password is correct, start session and redirect
					session_start();
					$_SESSION['user_id'] = $row['student_id'];
					$_SESSION['user_name'] = $row['user_name'];
					$_SESSION['user_profile'] = $row['user_profile'];

					header('location:issue_book_details.php');
					exit;
				} else {
					$message = '<li>Wrong password</li>';
				}
			}
		} else {
			$message = '<li>Invalid username or password</li>';
		}
	}
}
include 'header.php';
?>

<head>
	<style>
		body {
			background: linear-gradient(to left, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url("./asset/images/library-488690_1920.jpg");
			background-size: cover !important;
			background-position: center !important;
			background-attachment: fixed !important;
			width: 100% !important;
			height: 100%;
		}

		.student_input_form {
			width: 550px;
			max-width: 100%;
		}

		.card {
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
		}

		.card-header {
			color: #fff !important;
		}

		.form-label {
			font-weight: bold;
		}

		.school-logo {
			width: 150px;
			max-width: 100%;
		}
	</style>

</head>

<div class="student_input_container">
	<div class="container student_input_form">
		<div class="d-flex justify-content-center">
			<div class="col-md-8">
				<div class="card">
					<div class="card-header text-center">
						<img src="./asset/images/Accra_tu.png" alt="School Logo" class="school-logo">
					</div>
					<div class="card-body">
						<?php
						if ($message != '') {
							echo '<div class="alert alert-danger"><ul>' . $message . '</ul></div>';
						}
						?>
						<form method="POST">
							<div class="mb-3">
								<label class="form-label">Username</label>
								<input type="text" name="user_name" id="user_name" class="form-control" />
							</div>
							<div class="mb-3">
								<label class="form-label">Password</label>
								<input type="password" name="user_password" id="user_password" class="form-control" />
							</div>
							<div class="text-center mt-4 mb-2">
								<input type="submit" name="login_button" class="btn btn-primary" value="Login" />
							</div>

							<p class="text-center">
								<a href="./user_registration.php">Do not own an account?</a>
							</p>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>