<?php
/**
 * login.php
 * 
 * Handles user authentication. Verifies email and password,
 * and initializes a secure session for the user.
 * 
 * Owner: Member B (Auth & Secondary Backend)
 */

// Start session at the very beginning
session_start();

// Include database connection and validation functions
require_once '../config/db.php';
require_once '../includes/validate.php';
require_once '../includes/header.php';

$error = "";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password']; // Password shouldn't be sanitized before verification

    if (is_empty($email) || is_empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Prepare statement to fetch user details by email
        $sql = "SELECT user_id, full_name, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Verify password using password_verify()
            if (password_verify($password, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                // Redirect to landing page/dashboard
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>

<!-- HTML Form for Login (Structure by Member B, Styling by Member C) -->
<div class="auth-container">
    <h2>Login</h2>
    <?php if (!empty($error)): ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST" id="loginForm">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="btn-primary">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

<?php 
require_once '../includes/footer.php';
?>
