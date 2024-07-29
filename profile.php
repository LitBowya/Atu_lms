<?php
// profile.php
include 'database_connection.php';
include 'function.php';

if (!is_user_login()) {
	header('location:user_login.php');
	exit;
}

$message = '';
$success = '';

if (isset($_POST['save_button'])) {
	$formdata = [];

	if (empty($_POST['user_email_address'])) {
		$message .= '<li>Email Address is required</li>';
	} else {
		if (!filter_var($_POST["user_email_address"], FILTER_VALIDATE_EMAIL)) {
			$message .= '<li>Invalid Email Address</li>';
		} else {
			$formdata['user_email_address'] = trim($_POST['user_email_address']);
		}
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
		$message .= '<li>User Contact No is required</li>';
	} else {
		$formdata['user_contact_no'] = $_POST['user_contact_no'];
	}

	$formdata['user_profile'] = $_POST['hidden_user_profile'];

	if (!empty($_FILES['user_profile']['name'])) {
		$img_name = $_FILES['user_profile']['name'];
		$img_type = $_FILES['user_profile']['type'];
		$tmp_name = $_FILES['user_profile']['tmp_name'];
		$fileinfo = @getimagesize($tmp_name);
		$width = $fileinfo[0];
		$height = $fileinfo[1];
		$image_size = $_FILES['user_profile']['size'];
		$img_explode = explode(".", $img_name);
		$img_ext = strtolower(end($img_explode));
		$extensions = ["jpeg", "png", "jpg"];
		if (in_array($img_ext, $extensions)) {
			if ($image_size <= 2000000) {
				if ($width == 225 && $height == 225) {
					$new_img_name = time() . '-' . rand() . '.'  . $img_ext;
					if (move_uploaded_file($tmp_name, "upload/" . $new_img_name)) {
						$formdata['user_profile'] = $new_img_name;
					}
				} else {
					$message .= '<li>Image dimension should be within 225 X 225</li>';
				}
			} else {
				$message .= '<li>Image size exceeds 2MB</li>';
			}
		} else {
			$message .= '<li>Invalid Image File</li>';
		}
	}

	if ($message == '') {
		$data = array(
			':user_name' => $formdata['user_name'],
			':user_address' => $formdata['user_address'],
			':user_contact_no' => $formdata['user_contact_no'],
			':user_profile' => $formdata['user_profile'],
			':user_email_address' => $formdata['user_email_address'],
			':user_updated_on' => get_date_time($connect),
			':student_id' => $_SESSION['user_id']
		);

		$query = "
            UPDATE lms_user 
            SET user_name = :user_name, 
                user_address = :user_address, 
                user_contact_no = :user_contact_no, 
                user_profile = :user_profile, 
                user_email_address = :user_email_address, 
                user_updated_on = :user_updated_on 
            WHERE student_id = :student_id
        ";

		$statement = $connect->prepare($query);
		$statement->execute($data);

		$success = 'Data Changed Successfully';
	}
}

$query = "
    SELECT * FROM lms_user 
    WHERE student_id = :student_id
";

$statement = $connect->prepare($query);
$statement->execute([':student_id' => $_SESSION['user_id']]);
$result = $statement->fetch(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="d-flex align-items-center justify-content-center mt-5 mb-5">
	<div class="col-md-8">
		<?php if ($message != '') { ?>
			<div class="alert alert-danger">
				<ul><?php echo $message; ?></ul>
			</div>
		<?php } ?>
		<?php if ($success != '') { ?>
			<div class="alert alert-success"><?php echo $success; ?></div>
		<?php } ?>

		<div class="card">
			<div class="card-header">
				<ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="view-profile-tab" data-toggle="tab" href="#view-profile" role="tab" aria-controls="view-profile" aria-selected="true">View Profile</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="edit-profile-tab" data-toggle="tab" href="#edit-profile" role="tab" aria-controls="edit-profile" aria-selected="false">Edit Profile</a>
					</li>
				</ul>
			</div>
			<div class="card-body">
				<div class="tab-content" id="profileTabsContent">
					<div class="tab-pane fade show active" id="view-profile" role="tabpanel" aria-labelledby="view-profile-tab">
						<div class="row">
							<div class="col-md-4 text-center">
								<?php if ($result['user_profile']) { ?>
									<img src="upload/<?php echo htmlspecialchars($result['user_profile']); ?>" class="img-fluid rounded-circle mb-3" alt="User Photo" style="width: 150px; height: 150px;">
								<?php } else { ?>
									<img src="path/to/default/image.png" class="img-fluid rounded-circle mb-3" alt="User Photo" style="width: 150px; height: 150px;">
								<?php } ?>
							</div>
							<div class="col-md-8">
								<h4><?php echo htmlspecialchars($result['user_name']); ?></h4>
								<p>Email: <?php echo htmlspecialchars($result['user_email_address']); ?></p>
								<p>Contact No: <?php echo htmlspecialchars($result['user_contact_no']); ?></p>
								<p>Address: <?php echo nl2br(htmlspecialchars($result['user_address'])); ?></p>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="edit-profile" role="tabpanel" aria-labelledby="edit-profile-tab">
						<form method="POST" enctype="multipart/form-data">
							<div class="mb-3">
								<label class="form-label">Email Address</label>
								<input type="text" name="user_email_address" id="user_email_address" class="form-control" value="<?php echo htmlspecialchars($result['user_email_address']); ?>" />
							</div>
							<div class="mb-3">
								<label class="form-label">User Name</label>
								<input type="text" name="user_name" id="user_name" class="form-control" value="<?php echo htmlspecialchars($result['user_name']); ?>" />
							</div>
							<div class="mb-3">
								<label class="form-label">User Contact No.</label>
								<input type="text" name="user_contact_no" id="user_contact_no" class="form-control" value="<?php echo htmlspecialchars($result['user_contact_no']); ?>" />
							</div>
							<div class="mb-3">
								<label class="form-label">User Address</label>
								<textarea name="user_address" id="user_address" class="form-control"><?php echo htmlspecialchars($result['user_address']); ?></textarea>
							</div>
							<div class="mb-3">
								<label class="form-label">User Photo</label><br />
								<input type="file" name="user_profile" id="user_profile" />
								<br />
								<span class="text-muted">Only .jpg & .png image allowed. Image size must be 225 x 225</span>
								<br />
								<input type="hidden" name="hidden_user_profile" value="<?php echo htmlspecialchars($result['user_profile']); ?>" />
								<?php if ($result['user_profile']) { ?>
									<img src="upload/<?php echo htmlspecialchars($result['user_profile']); ?>" width="100" class="img-thumbnail" />
								<?php } ?>
							</div>
							<div class="text-center mt-4 mb-2">
								<input type="submit" name="save_button" class="btn btn-primary" value="Save" />
							</div>
						</form>
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