<?php
/**
 * delete_book.php
 * 
 * Handles deletion of a book from the library catalog.
 * Restrained to admin users. Must be requested via POST to prevent accidental deletion/CSRF.
 * 
 * Owner: Member A (Database & Core Backend Lead)
 */

require_once '../config/db.php';
require_once '../includes/validate.php';

// Verify user authentication
check_auth();

// Verify role access (Only administrators can delete books)
if (!is_admin()) {
    die("Access Denied: You do not have permission to perform this action.");
}

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = isset($_POST['book_id']) ? $_POST['book_id'] : '';

    // Validate book ID
    if (!is_positive_integer($book_id)) {
        header("Location: ../books/list_books.php?error=Invalid+Book+ID");
        exit();
    }

    // Delete query using a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $book_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                header("Location: ../books/list_books.php?success=Book+deleted+successfully");
            } else {
                header("Location: ../books/list_books.php?error=Book+not+found+or+already+deleted");
            }
        } else {
            header("Location: ../books/list_books.php?error=Error+executing+delete+query");
        }
        $stmt->close();
    } else {
        header("Location: ../books/list_books.php?error=Error+preparing+delete+statement");
    }
} else {
    // If accessed via GET, redirect back to books list
    header("Location: ../books/list_books.php");
}
exit();
?>
