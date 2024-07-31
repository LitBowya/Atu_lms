<?php

include 'database_connection.php';
include 'function.php';

if (is_user_login()) {
	header('location:issue_book_details.php');
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

		.header-text {
			background: linear-gradient(90deg, rgb(212, 175, 53), rgb(28, 74, 126));
			background-clip: text;
			color: transparent;
			-webkit-text-stroke: 0.5px white;
		}

		.fa-solid {
			font-size: 30px;
		}
	</style>
</head>

<div class="container">
	<header class="text-center header-text">
		<h1 class="text-center">Accra Technical University <br> Library Management System</h1>
	</header>
	<div class=" row align-items-md-stretch d-flex align-center mb-md-5 pb-md-5">

		<div class="col-md-6 my-md-5">

			<div class="h-100 p-5 text-white bg-dark rounded-3 text-center">

				<h2>Admin</h2>
				<p></p>
				<a href="./admin_login.php" class="btn btn-outline-light">
					<i class="fa-solid fa-user-tie"></i>
				</a>

			</div>

		</div>

		<div class="col-md-6 my-md-5">

			<div class="h-100 p-5 bg-light border rounded-3 text-center">

				<h2>Student</h2>

				<p></p>

				<a href="user_login.php" class="btn btn-outline-secondary">
					<i class="fa-solid fa-user"></i>
				</a>

			</div>

		</div>

	</div>
</div>