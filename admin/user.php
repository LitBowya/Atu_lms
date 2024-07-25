<?php

//user.php

include '../database_connection.php';
include '../function.php';

if (!is_admin_login()) {
	header('location:../admin_login.php');
	exit;
}

if (isset($_GET["action"], $_GET['code'])) {
	$student_id = $_GET["code"];
	$action = $_GET["action"];

	if ($action == 'disable') {
		$data = array(
			':user_updated_on' => get_date_time($connect),
			':student_id' => $student_id
		);

		$query = "
        UPDATE lms_user 
        SET user_status = 'Disable', 
            user_updated_on = :user_updated_on 
        WHERE student_id = :student_id
        ";

		$statement = $connect->prepare($query);
		$statement->execute($data);

		header('location:user.php?msg=disable');
		exit;
	} elseif ($action == 'enable') {
		$data = array(
			':user_updated_on' => get_date_time($connect),
			':student_id' => $student_id
		);

		$query = "
        UPDATE lms_user 
        SET user_status = 'Enable', 
            user_updated_on = :user_updated_on 
        WHERE student_id = :student_id
        ";

		$statement = $connect->prepare($query);
		$statement->execute($data);

		header('location:user.php?msg=enable');
		exit;
	}
}


$query = "
    SELECT * FROM lms_user 
    ORDER BY student_id DESC
";

$statement = $connect->prepare($query);
$statement->execute();

include '../header.php';

?>

<div class="container-fluid py-4" style="min-height: 700px;">
	<h1>User Management</h1>
	<ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
		<li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
		<li class="breadcrumb-item active">User Management</li>
	</ol>
	<?php
	if (isset($_GET["msg"])) {
		if ($_GET["msg"] == 'disable') {
			echo '<div class="alert alert-success alert-dismissible fade show" role="alert">User Status Changed to Disable <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
		}

		if ($_GET["msg"] == 'enable') {
			echo '<div class="alert alert-success alert-dismissible fade show" role="alert">User Status Changed to Enable <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
		}
	}
	?>
	<div class="card mb-4">
		<div class="card-header">
			<div class="row">
				<div class="col col-md-6">
					<i class="fas fa-table me-1"></i> User Management
				</div>
				<div class="col col-md-6" align="right">
				</div>
			</div>
		</div>
		<div class="card-body">
			<table id="datatablesSimple">
				<thead>
					<tr>
						<th>Image</th>
						<th>Student ID</th>
						<th>User Name</th>
						<th>Email Address</th>
						<th>Contact No.</th>
						<th>Address</th>
						<th>User Status</th>
						<th>Created On</th>
						<th>Updated On</th>
						<th>Action</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>Image</th>
						<th>Student ID</th>
						<th>User Name</th>
						<th>Email Address</th>
						<th>Contact No.</th>
						<th>Address</th>
						<th>User Status</th>
						<th>Created On</th>
						<th>Updated On</th>
						<th>Action</th>
					</tr>
				</tfoot>
				<tbody>
					<?php
					if ($statement->rowCount() > 0) {
						foreach ($statement->fetchAll() as $row) {
							$status = htmlspecialchars($row["user_status"]);
							$statusBadge = ($status == 'Enable') ? 'bg-success' : 'bg-danger';
							$statusText = ($status == 'Enable') ? 'Enabled' : 'Disabled';

							// Set button action based on user status
							$buttonAction = ($status == 'Enable') ? 'disable' : 'enable';
							$buttonText = ($status == 'Enable') ? 'Disable' : 'Enable';
							$buttonClass = ($status == 'Enable') ? 'btn-danger' : 'btn-success';

							echo '
            <tr>
                <td><img src="../upload/' . htmlspecialchars($row["user_profile"]) . '" class="img-thumbnail" width="75" /></td>
                <td>' . htmlspecialchars($row["student_id"]) . '</td>
                <td>' . htmlspecialchars($row["user_name"]) . '</td>
                <td>' . htmlspecialchars($row["user_email_address"]) . '</td>
                <td>' . htmlspecialchars($row["user_contact_no"]) . '</td>
                <td>' . htmlspecialchars($row["user_address"]) . '</td>
                <td><span class="badge ' . $statusBadge . '">' . $statusText . '</span></td>
                <td>' . htmlspecialchars($row["user_created_on"]) . '</td>
                <td>' . htmlspecialchars($row["user_updated_on"]) . '</td>
                <td>
                    <a href="user.php?action=' . $buttonAction . '&code=' . htmlspecialchars($row["student_id"]) . '" class="btn ' . $buttonClass . ' btn-sm">' . $buttonText . '</a>
                </td>
            </tr>
            ';
						}
					} else {
						echo '
        <tr>
            <td colspan="10" class="text-center">No Data Found</td>
        </tr>
        ';
					}
					?>
				</tbody>


			</table>
		</div>
	</div>
</div>

<?php

include '../footer.php';

?>