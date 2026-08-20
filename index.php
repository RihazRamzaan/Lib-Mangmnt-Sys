<?php
// index.php - Landing and login page
// Owner: Member C (Frontend Structure & Styling)
session_start();

// If user is already logged in, redirect to the main application
if (isset($_SESSION['user_id'])) {
    header("Location: books/list_books.php");
    exit();
}

// Include shared header
require_once 'includes/header.php';
?>

<main class="container">
    <div class="auth-card">
        <h2>Welcome to the Library</h2>
        <p>Please log in to manage books and borrow records.</p>
        
        <!-- Login form POSTs to Member B's auth/login.php endpoint -->
        <form action="auth/login.php" method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Log In</button>
        </form>
        
        <p class="auth-link">
            Don't have an account? <a href="auth/register.php">Register here</a>
        </p>
    </div>
</main>

<?php
// Include shared footer
require_once 'includes/footer.php';
?>
