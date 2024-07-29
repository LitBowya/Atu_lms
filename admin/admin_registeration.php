<?php

include '../database_connection.php';
include '../function.php';

$message = '';
$success = '';
$hashed_password = '';

if (isset($_POST["register_button"])) {
    $formdata = [];

    if (empty($_POST["admin_email"])) {
        $message .= '<li>Email Address is required</li>';
    } elseif (!filter_var($_POST["admin_email"], FILTER_VALIDATE_EMAIL)) {
        $message .= '<li>Invalid Email Address</li>';
    } else {
        $formdata['admin_email'] = trim($_POST['admin_email']);
    }

    if (empty($_POST["admin_username"])) {
        $message .= '<li>Username is required</li>';
    } else {
        $formdata['admin_username'] = trim($_POST['admin_username']);
    }

    if (empty($_POST["admin_password"])) {
        $message .= '<li>Password is required</li>';
    } else {
        $hashed_password = password_hash(trim($_POST['admin_password']), PASSWORD_DEFAULT);
        $formdata['admin_password'] = $hashed_password;
    }

    if (empty($_POST['admin_contact_no'])) {
        $message .= '<li>Contact Number is required</li>';
    } else {
        $formdata['admin_contact_no'] = trim($_POST['admin_contact_no']);
    }

    // Image validation
    if (!empty($_FILES['admin_profile']['name'])) {
        $fileName = $_FILES['admin_profile']['name'];
        $fileTmpName = $_FILES['admin_profile']['tmp_name'];
        $fileSize = $_FILES['admin_profile']['size'];
        $fileError = $_FILES['admin_profile']['error'];
        $fileType = $_FILES['admin_profile']['type'];

        if ($fileError === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (in_array($fileExt, $allowed)) {
                if ($fileSize <= 2 * 1024 * 1024) {
                    $fileNewName = uniqid('', true) . '.' . $fileExt;
                    $fileDestination = 'upload/admin' . $fileNewName;

                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        $formdata['admin_profile'] = $fileNewName;
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

    $formdata['admin_createdon'] = date('Y-m-d H:i:s'); // Set the registration time

    if ($message == '') {
        $data = [':admin_email' => $formdata['admin_email']];
        $query = "SELECT * FROM lms_admin WHERE admin_email = :admin_email";
        $statement = $connect->prepare($query);
        $statement->execute($data);

        if ($statement->rowCount() > 0) {
            $message = '<li>Email Already Registered</li>';
        } else {
            $data = [
                ':admin_username' => $formdata['admin_username'],
                ':admin_contact_no' => $formdata['admin_contact_no'],
                ':admin_profile' => $formdata['admin_profile'],
                ':admin_email' => $formdata['admin_email'],
                ':admin_password' => $formdata['admin_password'],
                ':admin_createdon' => $formdata['admin_createdon']
            ];

            $query = "INSERT INTO lms_admin 
                      (admin_username, admin_contact_no, admin_profile, admin_email, 
                       admin_password, admin_createdon) 
                      VALUES (:admin_username, :admin_contact_no, :admin_profile, :admin_email, 
                              :admin_password, :admin_createdon)";
            $statement = $connect->prepare($query);
            $statement->execute($data);

            $success = 'Registration Successful! You can now log in.<br>Hashed Password: ' . htmlspecialchars($hashed_password);
            // Redirect to login page or display success message
            // header('Location: admin_login.php'); // Uncomment this line for actual redirection
        }
    }
}

include '../header.php';
?>

<!-- HTML code for form display -->
<div class="d-flex align-items-center justify-content-center" style="min-height:700px;">
    <div class="col-md-6">
        <?php
        if ($message != '') {
            echo '<div class="alert alert-danger"><ul>' . $message . '</ul></div>';
        }
        if ($success != '') {
            echo '<div class="alert alert-success">' . $success . '</div>';
        }
        ?>
        <div class="card">
            <div class="card-header">Admin Registration</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="admin_email" id="admin_email" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="admin_username" id="admin_username" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="admin_password" id="admin_password" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="admin_contact_no" id="admin_contact_no" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Photo</label><br />
                        <input type="file" name="admin_profile" id="admin_profile" />
                        <br />
                        <span class="text-muted">Only .jpg & .png images allowed. Image size should be appropriate.</span>
                    </div>
                    <div class="text-center mt-4 mb-2">
                        <input type="submit" name="register_button" class="btn btn-primary" value="Register" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>