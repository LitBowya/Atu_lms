<?php
// user_registration.php
include 'database_connection.php';
include 'function.php';

if (is_user_login()) {
	header('location:issue_book_details.php');
	exit;
}

$message = '';
$success = '';

if (isset($_POST["register_button"])) {
	$formdata = [];

	if (empty($_POST["user_email_address"])) {
		$message .= '<li>Email Address is required</li>';
	} elseif (!filter_var($_POST["user_email_address"], FILTER_VALIDATE_EMAIL)) {
		$message .= '<li>Invalid Email Address</li>';
	} else {
		$formdata['user_email_address'] = trim($_POST['user_email_address']);
	}

	if (empty($_POST["student_id"])) {
		$message .= '<li>Student Id is required</li>';
	} else {
		$formdata['student_id'] = trim($_POST['student_id']);
	}

	if (empty($_POST["user_password"])) {
		$message .= '<li>Password is required</li>';
	} else {
		$formdata['user_password'] = password_hash(trim($_POST['user_password']), PASSWORD_DEFAULT);
	}

	if (empty($_POST['user_name'])) {
		$message .= '<li>User Name is required</li>';
	} else {
		$formdata['user_name'] = trim($_POST['user_name']);
	}

	if (empty($_POST['user_address'])) {
		$message .= '<li>User Address Detail is required</li>';
	} else {
		$formdata['user_address'] = trim($_POST['user_address']);
	}

	if (empty($_POST['user_contact_no'])) {
		$message .= '<li>User Contact Number Detail is required</li>';
	} else {
		$formdata['user_contact_no'] = trim($_POST['user_contact_no']);
	}

	// New fields
	if (empty($_POST['student_faculty'])) {
		$message .= '<li>Faculty is required</li>';
	} else {
		$formdata['student_faculty'] = trim($_POST['student_faculty']);
	}

	if (empty($_POST['student_department'])) {
		$message .= '<li>Department is required</li>';
	} else {
		$formdata['student_department'] = trim($_POST['student_department']);
	}

	if (empty($_POST['student_program'])) {
		$message .= '<li>Program is required</li>';
	} else {
		$formdata['student_program'] = trim($_POST['student_program']);
	}

	// Image validation
	if (!empty($_FILES['user_profile']['name'])) {
		$fileName = $_FILES['user_profile']['name'];
		$fileTmpName = $_FILES['user_profile']['tmp_name'];
		$fileSize = $_FILES['user_profile']['size'];
		$fileError = $_FILES['user_profile']['error'];
		$fileType = $_FILES['user_profile']['type'];

		if ($fileError === 0) {
			$allowed = ['jpg', 'jpeg', 'png'];
			$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

			if (in_array($fileExt, $allowed)) {
				if ($fileSize <= 2 * 1024 * 1024) {
					$fileNewName = uniqid('', true) . '.' . $fileExt;
					$fileDestination = 'upload/' . $fileNewName;

					if (move_uploaded_file($fileTmpName, $fileDestination)) {
						$formdata['user_profile'] = $fileNewName;
					} else {
						$message .= '<li>Failed to move uploaded file</li>';
					}
				} else {
					$message .= '<li>File size must be less than 2MB</li>';
				}
			} else {
				$message .= '<li>Invalid file type. Only .jpg & .png images allowed</li>';
			}
		} else {
			$message .= '<li>Error uploading file</li>';
		}
	} else {
		$message .= '<li>Please select a profile image</li>';
	}

	if ($message == '') {
		$data = [':user_email_address' => $formdata['user_email_address']];
		$query = "SELECT * FROM lms_user WHERE user_email_address = :user_email_address";
		$statement = $connect->prepare($query);
		$statement->execute($data);

		if ($statement->rowCount() > 0) {
			$message = '<li>Email Already Registered</li>';
		} else {
			$data = [
				':user_name' => $formdata['user_name'],
				':user_address' => $formdata['user_address'],
				':user_contact_no' => $formdata['user_contact_no'],
				':user_profile' => $formdata['user_profile'],
				':user_email_address' => $formdata['user_email_address'],
				':student_id' => $formdata['student_id'],
				':user_password' => $formdata['user_password'],
				':user_created_on' => get_date_time($connect),
				// Adding new fields
				':student_faculty' => $formdata['student_faculty'],
				':student_department' => $formdata['student_department'],
				':student_program' => $formdata['student_program']
			];

			$query = "INSERT INTO lms_user 
                      (user_name, user_address, user_contact_no, user_profile, user_email_address, 
                       user_password, student_id, user_created_on, student_faculty, student_department, student_program) 
                      VALUES (:user_name, :user_address, :user_contact_no, :user_profile, :user_email_address, 
                              :user_password, :student_id, :user_created_on, :student_faculty, :student_department, :student_program)";
			$statement = $connect->prepare($query);
			$statement->execute($data);

			$success = 'Registration Successful! You can now log in.';
			header('Location: user_login.php');
			exit;
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

<!-- HTML code for form display -->
<div class="container">
	<div class="d-flex justify-content-center">
		<div class="col-md-8">
			<div class="card mb-5">
				<div class="card-header text-center">
					<img src="./asset/images/Accra_tu.png" alt="School Logo" class="school-logo">
				</div>
				<div class="card-body">
					<?php
					if ($message != '') {
						echo '<div class="alert alert-danger"><ul>' . $message . '</ul></div>';
					}
					if ($success != '') {
						echo '<div class="alert alert-success">' . $success . '</div>';
					}
					?>
					<form method="POST" enctype="multipart/form-data">
						<div class="row">
							<div class="mb-3 col-md-6">
								<label class="form-label">Student Name</label>
								<input type="text" name="user_name" class="form-control" id="user_name" />
							</div>
							<div class="mb-3 col-md-6">
								<label class="form-label">Student ID</label>
								<input type="text" name="student_id" class="form-control" id="student_id" />
							</div>
						</div>
						<div class="row">
							<div class="mb-3 col-md-6">
								<label class="form-label">Email address</label>
								<input type="text" name="user_email_address" class="form-control" id="user_email_address" />
							</div>
							<div class="mb-3 col-md-6">
								<label class="form-label">Password</label>
								<input type="password" name="user_password" class="form-control" id="user_password" />
							</div>
						</div>
						<div class="row">
							<div class="mb-3 col-md-6">
								<label class="form-label">Student Contact No.</label>
								<input type="text" name="user_contact_no" class="form-control" id="user_contact_no" />
							</div>
							<div class="mb-3 col-md-6">
								<label class="form-label">Student Address</label>
								<textarea name="user_address" class="form-control" id="user_address"></textarea>
							</div>
						</div>
						<div class="row">
							<div class="mb-3 col-md-6">
								<label class="form-label">Student Faculty</label>
								<input type="text" name="student_faculty" class="form-control" id="student_faculty" />
							</div>
							<div class="mb-3 col-md-6">
								<label class="form-label">Student Department</label>
								<input type="text" name="student_department" class="form-control" id="student_department" />
							</div>
						</div>
						<div class="row">
							<div class="mb-3 col-md-6">
								<label class="form-label">Student Program</label>
								<input type="text" name="student_program" class="form-control" id="student_program" />
							</div>
							<div class="mb-3 col-md-6">
								<label class="form-label">Student Photo</label><br />
								<input type="file" name="user_profile" id="user_profile" />
								<br />
								<span class="text-muted">Only .jpg & .png images allowed. Image size must be 225 x 225</span>
							</div>
						</div>
						<div class="text-center mt-4 mb-2">
							<input type="submit" name="register_button" class="btn btn-primary" value="Register" />
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>