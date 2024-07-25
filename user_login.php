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

	if (empty($_POST["student_id"])) {
		$message .= '<li>Student ID is required</li>';
	} else {
		$formdata['student_id'] = trim($_POST['student_id']);
	}

	if (empty($_POST['user_password'])) {
		$message .= '<li>Password is required</li>';
	} else {
		$formdata['user_password'] = trim($_POST['user_password']);
	}


	$message = '';

	if ($message == '') {
		// Prepare and execute the query to check for the user
		$data = [':student_id' => $formdata['student_id']];
		$query = "SELECT * FROM lms_user WHERE student_id = :student_id";
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
					header('location:issue_book_details.php');
					exit;
				} else {
					$message = '<li>Wrong password</li>';
				}
			}
		} else {
			$message = '<li>Invalid student ID or password</li>';
		}
	}
}


include 'header.php';
?>

<div class="d-flex align-items-center justify-content-center" style="height:700px;">
	<div class="col-md-6">
		<?php
		if ($message != '') {
			echo '<div class="alert alert-danger"><ul>' . $message . '</ul></div>';
		}
		?>
		<div class="card">
			<div class="card-header">User Login</div>
			<div class="card-body">
				<form method="POST">
					<div class="mb-3">
						<label class="form-label">Student ID</label>
						<input type="text" name="student_id" id="student_id" class="form-control" />
					</div>
					<div class="mb-3">
						<label class="form-label">Password</label>
						<input type="password" name="user_password" id="user_password" class="form-control" />
					</div>
					<div class="d-flex align-items-center justify-content-between mt-4 mb-0">
						<input type="submit" name="login_button" class="btn btn-primary" value="Login" />
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<?php include 'footer.php'; ?>