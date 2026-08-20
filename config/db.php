<?php
/**
 * db.php
 * 
 * Establishes a database connection to MySQL/MariaDB database 'library_db' using mysqli.
 * 
 * Owner: Member A (Database & Core Backend Lead)
 */

$host = "localhost";
$username = "root";
$password = "";
$database = "library_db";


// Establish a new connection using the mysqli object-oriented API
$conn = new mysqli($host, $username, $password, $database,3308);

// Check if the connection succeeded; exit and show an error if it failed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for secure handling of Unicode characters and to prevent SQL injection edge cases
$conn->set_charset("utf8mb4");
?>
