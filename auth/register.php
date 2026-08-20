<?php
/**
 * register.php
 * 
 * Handles user registration by collecting full name, email, and password.
 * Passwords are encrypted using password_hash() before being stored.
 * 
 * Owner: Member B (Auth & Secondary Backend)
 */

// Include database connection and validation functions
require_once '../config/db.php';
require_once '../includes/validate.php';
// require_once '../includes/header.php'; // Will be available once Member C completes it

$message = "";

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password']; // Don't sanitize password as it will be hashed

    // Use shared validation functions
    if (is_empty($full_name) || is_empty($email) || is_empty($password)) {
        $message = "All fields are required.";
    } elseif (!is_valid_email($email)) {
        $message = "Invalid email format.";
    } else {
        // Check if email already exists
        $check_sql = "SELECT user_id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Email is already registered.";
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'member'; // Default role for new registrations

            // Prepare SQL statement to prevent SQL injection
            $insert_sql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            
            // "ssss" means 4 strings
            $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $message = "Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!-- HTML Form for Registration (Structure by Member B, Styling by Member C) -->
<div class="auth-container">
    <h2>Register</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="register.php" method="POST" id="registerForm">
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" id="full_name" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="btn-primary">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php 
// require_once '../includes/footer.php'; // Will be available once Member C completes it
?>
