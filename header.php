<?php

//header.php

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="generator" content="">
    <title>Online Library Management</title>
    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url(); ?>asset/css/simple-datatables-style.css" rel="stylesheet" />
    <link href="<?php echo base_url(); ?>asset/css/styles.css" rel="stylesheet" />
    <script src="<?php echo base_url(); ?>" crossorigin="anonymous"></script>
    <!-- Favicons -->
    <link rel="apple-touch-icon" href="" sizes="180x180">
    <link rel="icon" href="" sizes="32x32" type="image/png">
    <link rel="icon" href="" sizes="16x16" type="image/png">
    <link rel="manifest" href="">
    <link rel="mask-icon" href="" color="#7952b3">
    <link rel="icon" href="">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <meta name="theme-color" content="#7952b3">

    <!-- Scripts -->
    <script src="<?php echo base_url(); ?>asset/js/bootstrap.bundle.min.js" crossorigin="anonymous" defer></script>
    <script src="<?php echo base_url(); ?>asset/js/scripts.js" defer></script>
    <script src="<?php echo base_url(); ?>asset/js/simple-datatables@latest.js" crossorigin="anonymous" defer></script>
    <script src="<?php echo base_url(); ?>asset/js/datatables-simple-demo.js" defer></script>

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        hr {
            background-color: white;
            width: 75vw;
            max-width: 100%;
            margin-inline: auto;
            opacity: 1;
            padding: 1px;
            border-radius: 8px;
        }

        header {
            background-color: white;
            margin-bottom: 75px;
        }

        .nav-link.active {
            background-color: #ffffff;
            color: #000000 !important;
        }

        .nav-link.active:hover {
            background-color: #e9ecef;
        }

        .fa-solid {
            font-size: 35px;
        }
    </style>
</head>

<body>

    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    // Check if the user is logged in as either an admin or a regular user
    if (is_admin_login() || is_user_login()) {
        $name = '';
        $profileImage = '';

        if (is_admin_login()) {
            $name = $_SESSION['admin_username'];
            $profileImage = $_SESSION['admin_profile'];
        } elseif (is_user_login()) {
            $name = $_SESSION['user_name'];
            $profileImage = $_SESSION['user_profile'];
        }


    ?>
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <div class="navbar-brand-container">
                <a class="navbar-brand ps-3" href="index.php">Library System</a>
                <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            </div>
            <div class="navbar-user-info ms-auto d-flex align-items-center">
                <span class="navbar-user-name text-white me-2"> <?php echo htmlspecialchars($name); ?></span>
            </div>
        </nav>

        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <?php if (is_admin_login()) { ?>
                                <a class="nav-link <?php echo $current_page == 'issue_book.php' ? 'active' : ''; ?>" href="issue_book.php">Issue Book</a>
                                <a class="nav-link <?php echo $current_page == 'category.php' ? 'active' : ''; ?>" href="category.php">Category</a>
                                <a class="nav-link <?php echo $current_page == 'author.php' ? 'active' : ''; ?>" href="author.php">Author</a>
                                <a class="nav-link <?php echo $current_page == 'location_rack.php' ? 'active' : ''; ?>" href="location_rack.php">Location Rack</a>
                                <a class="nav-link <?php echo $current_page == 'book.php' ? 'active' : ''; ?>" href="book.php">Book</a>
                                <a class="nav-link <?php echo $current_page == 'user.php' ? 'active' : ''; ?>" href="user.php">Students</a>
                                <a class="nav-link <?php echo $current_page == 'admin_registeration.php' ? 'active' : ''; ?>" href="admin_registeration.php">Register Admin</a>
                            <?php } ?>


                            <?php if (is_user_login()) { ?>
                                <a class="nav-link <?php echo $current_page == 'issue_book_details.php' ? 'active' : ''; ?>" href="issue_book_details.php">Issue Book</a>
                                <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" href="profile.php">Profile</a>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer d-flex justify-content-between">
                        <div>
                            <div class="small">Logged in as:</div>
                            <?php echo is_admin_login() ? 'Admin' : 'Student'; ?>
                        </div>
                        <a href="logout.php">
                            <i class="fa-solid fa-right-from-bracket text-white"></i>
                        </a>
                    </div>
                </nav>
            </div>

            <div id="layoutSidenav_content">
                <main>
                <?php
            } else {
                ?>
                    <!-- Display public or guest view -->
                    <header>
                        <div class="container pt-3">
                            <a href="index.php" class="d-flex align-items-center text-dark text-decoration-none">
                                <img src="./asset/images/Accra_tu.png" alt="" height="50" width="100">
                            </a>
                        </div>
                        <hr />
                    </header>

                    <main>
                    <?php
                }
                    ?>