<?php
/**
 * validate.php
 * 
 * Contains shared server-side validation functions to ensure data integrity.
 * These functions are used across various forms (auth, books, etc.).
 * 
 * Owner: Member B (Auth & Secondary Backend)
 */

/**
 * Checks if a string is empty after trimming.
 * 
 * @param string $data The input string to check.
 * @return bool True if empty, false otherwise.
 */
function is_empty($data) {
    return empty(trim($data));
}

/**
 * Validates an email address format.
 * 
 * @param string $email The email to validate.
 * @return bool True if valid, false otherwise.
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validates that a value is a positive integer.
 * Useful for quantity or ID validation.
 * 
 * @param mixed $value The value to check.
 * @return bool True if it's a positive integer, false otherwise.
 */
function is_positive_integer($value) {
    return filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) !== false;
}

/**
 * Sanitizes input data to prevent XSS.
 * 
 * @param string $data The input string.
 * @return string The sanitized string.
 */
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Checks if a user is logged in. Redirects to login page if not.
 */
function check_auth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

/**
 * Checks if the logged-in user has an admin role.
 * 
 * @return bool True if admin, false otherwise.
 */
function is_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>
