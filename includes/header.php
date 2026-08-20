<?php
// includes/header.php
// Owner: Member C (Frontend Structure & Styling)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate base URL dynamically based on project location in web root
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$proj_root = str_replace('\\', '/', dirname(__DIR__));
$base_url = str_replace($doc_root, '', $proj_root) . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/style.css">
</head>
<body>
    <header class="app-header">
        <div class="container header-container">
            <h1><a href="<?php echo $base_url; ?>index.php" style="color: inherit; text-decoration: none;">Library System</a></h1>
            <nav>
                <ul class="nav-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo $base_url; ?>books/list_books.php">Catalog</a></li>
                        <li><a href="<?php echo $base_url; ?>borrow/return_book.php">My Borrows</a></li>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li><a href="<?php echo $base_url; ?>dashboard/report.php">Dashboard</a></li>
                            <li><a href="<?php echo $base_url; ?>books/add_book.php">Add Book</a></li>
                        <?php endif; ?>
                        
                        <li><a href="<?php echo $base_url; ?>auth/logout.php" class="btn btn-sm btn-secondary">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $base_url; ?>index.php">Login</a></li>
                        <li><a href="<?php echo $base_url; ?>auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
