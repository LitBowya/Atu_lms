<?php

include '../database_connection.php';

if (isset($_POST["action"])) {
	if ($_POST["action"] == 'search_book_name_author') {
		$book_name = $_POST['book_name'];
		$author_name = $_POST['author_name'];

		$query = "
    SELECT book_name, book_author FROM lms_book
    WHERE book_name LIKE '%$book_name%'
    AND book_author LIKE '%$author_name%'
    AND book_status = 'Enable'
    ";

		$result = $connect->query($query);

		$data = array();
		foreach ($result as $row) {
			$data[] = array(
				'book_name' => str_replace($book_name, '<b>' . $book_name . '</b>', $row["book_name"]),
				'author_name' => str_replace($author_name, '<b>' . $author_name . '</b>', $row["author_name"])
			);
		}
		echo json_encode($data);
	}

	if ($_POST["action"] == 'search_user_id') {
		$query = "
    SELECT student_id, user_name FROM lms_user
    WHERE student_id LIKE '%" . $_POST["request"] . "%'
    AND user_status = 'Enable'
    ";

		$result = $connect->query($query);

		$data = array();
		foreach ($result as $row) {
			$data[] = array(
				'student_id' => str_replace($_POST["request"], '<b>' . $_POST["request"] . '</b>', $row["student_id"]),
				'user_name' => $row["user_name"]
			);
		}

		echo json_encode($data);
	}
}