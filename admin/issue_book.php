<?php

// issue_book.php

include '../database_connection.php';
include '../function.php';

if (!is_admin_login()) {
    header('location:../admin_login.php');
}

$error = '';

if (isset($_POST["issue_book_button"])) {
    $formdata = array();

    if (empty($_POST["book_name"])) {
        $error .= '<li>Book Name is required</li>';
    } else {
        $formdata['book_name'] = trim($_POST['book_name']);
    }

    if (empty($_POST["book_author"])) {
        $error .= '<li>Book Author is required</li>';
    } else {
        $formdata['book_author'] = trim($_POST['book_author']);
    }

    if (empty($_POST["student_id"])) {
        $error .= '<li>Student ID is required</li>';
    } else {
        $formdata['student_id'] = trim($_POST['student_id']);
    }

    if ($error == '') {
        // Check Book Availability
        $query = "
        SELECT * FROM lms_book
        WHERE book_name = :book_name
        AND book_author = :book_author
        ";
        $statement = $connect->prepare($query);
        $statement->execute([
            ':book_name' => $formdata['book_name'],
            ':book_author' => $formdata['book_author']
        ]);

        if ($statement->rowCount() > 0) {
            foreach ($statement->fetchAll() as $book_row) {
                if ($book_row['book_status'] == 'Enable' && $book_row['book_no_of_copy'] > 0) {
                    // Check Student Existence
                    $query = "
                    SELECT student_id, user_status FROM lms_user
                    WHERE student_id = :student_id
                    ";
                    $statement = $connect->prepare($query);
                    $statement->execute([':student_id' => $formdata['student_id']]);

                    if ($statement->rowCount() > 0) {
                        foreach ($statement->fetchAll() as $user_row) {
                            if ($user_row['user_status'] == 'Enable') {
                                $book_issue_limit = get_book_issue_limit_per_user($connect);
                                $total_book_issue = get_total_book_issue_per_user($connect, $formdata['student_id']);

                                if ($total_book_issue < $book_issue_limit) {
                                    $total_book_issue_day = get_total_book_issue_day($connect);
                                    $today_date = get_date_time($connect);
                                    $expected_return_date = date('Y-m-d H:i:s', strtotime($today_date . ' + ' . $total_book_issue_day . ' days'));
                                    $data = array(
                                        ':book_id' => $book_row['book_id'],
                                        ':user_id' => $formdata['student_id'],
                                        ':issue_date_time' => $today_date,
                                        ':expected_return_date' => $expected_return_date,
                                        ':return_date_time' => '',
                                        ':book_fines' => 0,
                                        ':book_issue_status' => 'Issue'
                                    );

                                    $query = "
                                    INSERT INTO lms_issue_book
                                    (book_id, student_id, issue_date_time, expected_return_date, return_date_time, book_fines, book_issue_status)
                                    VALUES (:book_id, :user_id, :issue_date_time, :expected_return_date, :return_date_time, :book_fines, :book_issue_status)
                                    ";
                                    $statement = $connect->prepare($query);
                                    $statement->execute($data);

                                    $query = "
                                    UPDATE lms_book
                                    SET book_no_of_copy = book_no_of_copy - 1,
                                    book_updated_on = :today_date
                                    WHERE book_id = :book_id
                                    ";
                                    $statement = $connect->prepare($query);
                                    $statement->execute([
                                        ':today_date' => $today_date,
                                        ':book_id' => $book_row['book_id']
                                    ]);

                                    header('location:issue_book.php?msg=add');
                                } else {
                                    $error .= 'User has already reached Book Issue Limit, First return pending book';
                                }
                            } else {
                                $error .= '<li>User Account is Disabled, Contact Admin</li>';
                            }
                        }
                    } else {
                        $error .= '<li>Student not Found</li>';
                    }
                } else {
                    $error .= '<li>Book not Available</li>';
                }
            }
        } else {
            $error .= '<li>Book not Found</li>';
        }
    }
}

if (isset($_POST["book_return_button"])) {
    if (isset($_POST["book_return_confirmation"])) {
        $data = array(
            ':return_date_time' => get_date_time($connect),
            ':book_issue_status' => 'Return',
            ':issue_book_id' => $_POST['issue_book_id']
        );

        $query = "
        UPDATE lms_issue_book
        SET return_date_time = :return_date_time,
        book_issue_status = :book_issue_status
        WHERE issue_book_id = :issue_book_id
        ";
        $statement = $connect->prepare($query);
        $statement->execute($data);

        $query = "
        UPDATE lms_book
        SET book_no_of_copy = book_no_of_copy + 1
        WHERE book_id = :book_id
        ";
        $statement = $connect->prepare($query);
        $statement->execute([':book_id' => $_POST["book_id"]]);

        header("location:issue_book.php?msg=return");
    } else {
        $error = 'Please first confirm return book received by clicking on checkbox';
    }
}

$query = "
SELECT ib.*, b.book_name, b.book_author, b.book_code
FROM lms_issue_book ib
JOIN lms_book b ON ib.book_id = b.book_id
ORDER BY ib.issue_book_id DESC
";

$statement = $connect->prepare($query);
$statement->execute();

include '../header.php';

?>
<div class="container-fluid py-4" style="min-height: 700px;">
    <h1>Issue Book Management</h1>
    <?php
    if (isset($_GET["action"])) {
        if ($_GET["action"] == 'add') {
    ?>
            <ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="issue_book.php">Issue Book Management</a></li>
                <li class="breadcrumb-item active">Issue New Book</li>
            </ol>
            <div class="row">
                <div class="col-md-6">
                    <?php
                    if ($error != '') {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="list-unstyled">' . $error . '</ul> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    }
                    ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-user-plus"></i> Issue New Book
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Book Name</label>
                                    <input type="text" name="book_name" id="book_name" class="form-control" />
                                    <span id="book_name_result"></span>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Book Author</label>
                                    <input type="text" name="book_author" id="book_author" class="form-control" />
                                    <span id="book_author_result"></span>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Student ID</label>
                                    <input type="text" name="student_id" id="student_id" class="form-control" />
                                    <span id="student_id_result"></span>
                                </div>
                                <div class="mt-4 mb-0">
                                    <input type="submit" name="issue_book_button" class="btn btn-success" value="Issue" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        } else if ($_GET["action"] == 'view') {
            $issue_book_id = convert_data($_GET["code"], 'decrypt');
            if ($issue_book_id > 0) {
                $query = "
                SELECT * FROM lms_issue_book 
                WHERE issue_book_id = :issue_book_id
                ";
                $statement = $connect->prepare($query);
                $statement->execute([':issue_book_id' => $issue_book_id]);

                foreach ($statement as $row) {
                    $query = "
                    SELECT * FROM lms_book 
                    WHERE book_id = :book_id
                    ";
                    $book_statement = $connect->prepare($query);
                    $book_statement->execute([':book_id' => $row["book_id"]]);

                    $query = "
                    SELECT * FROM lms_user 
                    WHERE student_id = :student_id
                    ";
                    $user_statement = $connect->prepare($query);
                    $user_statement->execute([':student_id' => $row["student_id"]]);

                    echo '
                    <ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="issue_book.php">Issue Book Management</a></li>
                        <li class="breadcrumb-item active">View Issue Book Details</li>
                    </ol>
                    ';

                    if ($error != '') {
                        echo '<div class="alert alert-danger">' . $error . '</div>';
                    }

                    foreach ($book_statement as $book_data) {
                        echo '
                        <h2>Book Details</h2>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Book Code</th>
                                <td width="70%">' . $book_data["book_code"] . '</td>
                            </tr>
                            <tr>
                                <th width="30%">Book Name</th>
                                <td width="70%">' . $book_data["book_name"] . '</td>
                            </tr>
                            <tr>
                                <th width="30%">Book Author</th>
                                <td width="70%">' . $book_data["book_author"] . '</td>
                            </tr>
                        </table>
                        <br />
                        ';
                    }

                    foreach ($user_statement as $user_data) {
                        echo '
                        <h2>User Details</h2>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Student ID</th>
                                <td width="70%">' . $user_data["student_id"] . '</td>
                            </tr>
                            <tr>
                                <th width="30%">Student Name</th>
                                <td width="70%">' . $user_data["user_name"] . '</td>
                            </tr>
                            <tr>
                                <th width="30%">Student Address</th>
                                <td width="70%">' . $user_data["user_address"] . '</td>
                            </tr>
                            <tr>
                                <th width="30%">Student Contact No.</th>
                                <td width="70%">' . $user_data["user_contact_no"] . '</td>
                            </tr>
                            <tr>
                                <th width="30%">Student Email Address</th>
                                <td width="70%">' . $user_data["user_email_address"] . '</td>
                            </tr>
                            <tr>
                                <th width="30%">Student Faculty</th>
                                <td width="70%">' . $user_data["student_faculty"] . '</td>
                            </tr>
                            <tr>
                                <th width="30%">Student Department</th>
                                <td width="70%">' . $user_data["student_department"] . '</td>
                            </tr>
                            <tr>
                                <th width="30%">Student Program</th>
                                <td width="70%">' . $user_data["student_program"] . '</td>
                            </tr>
                            <tr>
                                <th width="30%">Student Image</th>
                                <td width="70%"><img src="' . base_url() . 'upload/' . $user_data["user_profile"] . '" class="img-thumbnail" width="100" /></td>
                            </tr>
                        </table>
                        <br />
                        ';
                    }

                    $status = $row["book_issue_status"];
                    $form_item = '';

                    if ($status == "Issue" || $status == 'Not Return') {
                        $status_badge = ($status == "Issue") ? 'bg-warning' : 'bg-danger';
                        $form_item = '
                        <label><input type="checkbox" name="book_return_confirmation" value="Yes" /> I acknowledge that I have received Issued Book</label>
                        <br />
                        <div class="mt-4 mb-4">
                            <input type="submit" name="book_return_button" value="Book Return" class="btn btn-primary" />
                        </div>
                        ';
                    } else {
                        $status_badge = 'bg-primary';
                    }

                    echo '
                    <h2>Issue Book Details</h2>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Book Issue Date</th>
                            <td width="70%">' . $row["issue_date_time"] . '</td>
                        </tr>
                        <tr>
                            <th width="30%">Book Return Date</th>
                            <td width="70%">' . $row["return_date_time"] . '</td>
                        </tr>
                        <tr>
                            <th width="30%">Book Issue Status</th>
                            <td width="70%"><span class="badge ' . $status_badge . '">' . $status . '</span></td>
                        </tr>
                        <tr>
                            <th width="30%">Total Fines</th>
                            <td width="70%">' . get_currency_symbol($connect) . ' ' . $row["book_fines"] . '</td>
                        </tr>
                    </table>
                    <form method="POST">
                        <input type="hidden" name="issue_book_id" value="' . $issue_book_id . '" />
                        <input type="hidden" name="book_id" value="' . $row["book_id"] . '" />
                        ' . $form_item . '
                    </form>
                    <br />
                    ';
                }
            }
        }
    } else {
        ?>
        <ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Issue Book Management</li>
        </ol>

        <?php
        if (isset($_GET['msg'])) {
            if ($_GET['msg'] == 'add') {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">New Book Issued Successfully<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }

            if ($_GET["msg"] == 'return') {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Issued Book Successfully Returned to Library<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            }
        }
        ?>

        <div class="card mb-4">
            <div class="card-header">
                <div class="row">
                    <div class="col col-md-6">
                        <i class="fas fa-table me-1"></i> Issue Book Management
                    </div>
                    <div class="col col-md-6" align="right">
                        <a href="issue_book.php?action=add" class="btn btn-success btn-sm">Add</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>Book Code</th>
                            <th>Book Name</th>
                            <th>Book Author</th>
                            <th>Student ID</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th>Late Return Fines</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Book Code</th>
                            <th>Book Name</th>
                            <th>Book Author</th>
                            <th>Student ID</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th>Late Return Fines</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php
                        if ($statement->rowCount() > 0) {
                            $one_day_fine = get_one_day_fines($connect);
                            $currency_symbol = get_currency_symbol($connect);
                            set_timezone($connect);

                            foreach ($statement->fetchAll() as $row) {
                                $status = $row["book_issue_status"];
                                $book_fines = $row["book_fines"];

                                if ($status == "Issue") {
                                    $current_date_time = new DateTime(get_date_time($connect));
                                    $expected_return_date = new DateTime($row["expected_return_date"]);

                                    if ($current_date_time > $expected_return_date) {
                                        $interval = $current_date_time->diff($expected_return_date);
                                        $total_day = $interval->d;
                                        $book_fines = $total_day * $one_day_fine;
                                        $status = 'Not Return';

                                        $query = "
                                        UPDATE lms_issue_book 
                                        SET book_fines = :book_fines, 
                                        book_issue_status = :book_issue_status 
                                        WHERE issue_book_id = :issue_book_id
                                        ";
                                        $statement = $connect->prepare($query);
                                        $statement->execute([
                                            ':book_fines' => $book_fines,
                                            ':book_issue_status' => $status,
                                            ':issue_book_id' => $row["issue_book_id"]
                                        ]);
                                    }
                                }

                                if ($status == 'Issue') {
                                    $status = '<span class="badge bg-warning">Issue</span>';
                                } elseif ($status == 'Not Return') {
                                    $status = '<span class="badge bg-danger">Not Return</span>';
                                } elseif ($status == 'Return') {
                                    $status = '<span class="badge bg-primary">Return</span>';
                                }

                                echo '
                                <tr>
                                    <td>' . $row["book_code"] . '</td>
                                    <td>' . $row["book_name"] . '</td>
                                    <td>' . $row["book_author"] . '</td>
                                    <td>' . $row["student_id"] . '</td>
                                    <td>' . $row["issue_date_time"] . '</td>
                                    <td>' . $row["return_date_time"] . '</td>
                                    <td>' . $currency_symbol . $book_fines . '</td>
                                    <td>' . $status . '</td>
                                    <td>
                                        <a href="issue_book.php?action=view&code=' . convert_data($row["issue_book_id"]) . '" class="btn btn-info btn-sm">View</a>
                                    </td>
                                </tr>
                                ';
                            }
                        } else {
                            echo '
                            <tr>
                                <td colspan="9" class="text-center">No Data Found</td>
                            </tr>
                            ';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
    }
    ?>
</div>

<?php include '../footer.php'; ?>