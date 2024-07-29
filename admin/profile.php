<?php

include '../database_connection.php';
include '../function.php';

if (!is_admin_login()) {
	header('location:../admin_login.php');
	exit;
}

$message = '';
$error = '';

if (isset($_POST['edit_admin'])) {
	$formdata = array();

	if (empty($_POST['admin_email'])) {
		$error .= '<li>Email Address is required</li>';
	} else {
		if (!filter_var($_POST["admin_email"], FILTER_VALIDATE_EMAIL)) {
			$error .= '<li>Invalid Email Address</li>';
		} else {
			$formdata['admin_email'] = $_POST['admin_email'];
		}
	}

	if ($error == '') {
		$admin_id = $_SESSION['admin_id'];

		$data = array(
			':admin_email' => $formdata['admin_email'],
			':admin_id' => $admin_id
		);

		$query = "
        UPDATE lms_admin 
        SET admin_email = :admin_email
        WHERE admin_id = :admin_id
        ";

		$statement = $connect->prepare($query);
		$statement->execute($data);

		$message = 'User Data Edited';
	}
}

$query = "
SELECT * FROM lms_admin 
WHERE admin_id = :admin_id
";

$data = array(':admin_id' => $_SESSION["admin_id"]);
$statement = $connect->prepare($query);
$statement->execute($data);
$result = $statement->fetchAll();

include '../header.php';
?>

<div class="container-fluid px-4">
	<h1 class="mt-4">Profile</h1>
	<ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
		<li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
		<li class="breadcrumb-item active">Profile</li>
	</ol>
	<div class="card">
		<div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="view-profile-tab" href="#view-profile" role="tab" aria-controls="view-profile" aria-selected="true">View Profile</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="edit-profile-tab" href="#edit-profile" role="tab" aria-controls="edit-profile" aria-selected="false">Edit Profile</a>
				</li>
			</ul>
		</div>
		<div class="card-body">
			<div class="tab-content" id="profileTabsContent">
				<div class="tab-pane fade show active" id="view-profile" role="tabpanel" aria-labelledby="view-profile-tab">
					<div class="row">
						<div class="col-md-6">
							<?php
							if ($error != '') {
								echo '<div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="list-unstyled">' . $error . '</ul> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
							}

							if ($message != '') {
								echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $message . ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
							}
							?>
							<div class="card mb-4">
								<div class="card-header">
									<i class="fas fa-user"></i> View Profile Details
								</div>
								<div class="card-body">
									<?php
									foreach ($result as $row) {
									?>
										<p><strong>Username:</strong> <?php echo htmlspecialchars($row['admin_username']); ?></p>
										<p><strong>Email Address:</strong> <?php echo htmlspecialchars($row['admin_email']); ?></p>
										<p><strong>Contact Number:</strong> <?php echo htmlspecialchars($row['admin_contact_no']); ?></p>
										<p><strong>Profile Created On:</strong> <?php echo htmlspecialchars($row['admin_createdon']); ?></p>
										<?php if ($row['admin_profile']) { ?>
											<img src="../upload/<?php echo htmlspecialchars($row['admin_profile']); ?>" class="img-fluid rounded-circle mb-3" alt="Admin Photo" style="width: 150px; height: 150px;">
										<?php } else { ?>
											<i class="fa-solid fa-user-tie" style="width: 150px; height: 150px;">
										<?php } ?>
									<?php
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="edit-profile" role="tabpanel" aria-labelledby="edit-profile-tab">
					<div class="row">
						<div class="col-md-6">
							<div class="card mb-4">
								<div class="card-header">
									<i class="fas fa-user-edit"></i> Edit Profile Details
								</div>
								<div class="card-body">
									<?php
									foreach ($result as $row) {
									?>
										<form method="post">
											<div class="mb-3">
												<label class="form-label">Email Address</label>
												<input type="text" name="admin_email" id="admin_email" class="form-control" value="<?php echo htmlspecialchars($row['admin_email']); ?>" />
											</div>
											<div class="mt-4 mb-0">
												<input type="submit" name="edit_admin" class="btn btn-primary" value="Edit" />
											</div>
										</form>
									<?php
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const tabs = document.querySelectorAll('#profileTabs a');
		const tabContents = document.querySelectorAll('.tab-pane');

		function showTab(event) {
			event.preventDefault();
			const targetId = this.getAttribute('href').substring(1);

			// Remove active class from all tabs and tab content
			tabs.forEach(tab => tab.classList.remove('active'));
			tabContents.forEach(content => content.classList.remove('show', 'active'));

			// Add active class to the clicked tab and corresponding content
			this.classList.add('active');
			document.getElementById(targetId).classList.add('show', 'active');
		}

		// Add click event listeners to all tabs
		tabs.forEach(tab => tab.addEventListener('click', showTab));
	});
</script>

<?php
include '../footer.php';
?>