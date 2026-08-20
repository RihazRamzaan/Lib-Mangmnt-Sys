<?php
/**
 * borrow_book.php
 * 
 * Handles the transaction logic for a user borrowing a book.
 * Validates book availability, prevents duplicate active borrows,
 * updates book inventory quantity, and creates a borrow record.
 * Uses a database transaction for data integrity.
 * 
 * Owner: Member B (Auth & Secondary Backend) - Co-developed with Member A (Database Lead)
 */

require_once '../config/db.php';
require_once '../includes/validate.php';

// Verify user authentication
check_auth();

$success_msg = "";
$error_msg = "";

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = isset($_POST['book_id']) ? $_POST['book_id'] : '';
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

    // Validate inputs
    if (!is_positive_integer($book_id) || !is_positive_integer($user_id)) {
        $error_msg = "Invalid Book ID or User ID.";
    } else {
        // Start a database transaction to ensure both updates complete atomically
        $conn->begin_transaction();

        try {
            // 1. Check book existence and quantity (lock for update to handle race conditions)
            $stmt = $conn->prepare("SELECT quantity, title FROM books WHERE book_id = ? FOR UPDATE");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Book does not exist.");
            }
            
            $book = $result->fetch_assoc();
            $stmt->close();

            if ($book['quantity'] <= 0) {
                throw new Exception("Book '" . $book['title'] . "' is currently out of stock.");
            }

            // 2. Check if the user already has this book borrowed and not returned
            $stmt = $conn->prepare("SELECT record_id FROM borrow_records WHERE book_id = ? AND user_id = ? AND status = 'borrowed'");
            $stmt->bind_param("ii", $book_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception("You already have an active borrow record for this book.");
            }
            $stmt->close();

            // 3. Decrement the book quantity by 1
            $stmt = $conn->prepare("UPDATE books SET quantity = quantity - 1 WHERE book_id = ?");
            $stmt->bind_param("i", $book_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update book quantity.");
            }
            $stmt->close();

            // 4. Create the borrow record
            $borrow_date = date('Y-m-d');
            $status = 'borrowed';
            $stmt = $conn->prepare("INSERT INTO borrow_records (book_id, user_id, borrow_date, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $book_id, $user_id, $borrow_date, $status);
            if (!$stmt->execute()) {
                throw new Exception("Failed to create borrow record.");
            }
            $stmt->close();

            // Commit transaction if all queries succeeded
            $conn->commit();
            $success_msg = "Book '" . htmlspecialchars($book['title']) . "' successfully borrowed!";
        } catch (Exception $e) {
            // Rollback transaction if any error occurs to prevent partial updates
            $conn->rollback();
            $error_msg = $e->getMessage();
        }
    }
}

// Redirect back to book list page with success or error message
$redirect_url = "../books/list_books.php";
if (!empty($success_msg)) {
    header("Location: " . $redirect_url . "?success=" . urlencode($success_msg));
} else {
    header("Location: " . $redirect_url . "?error=" . urlencode($error_msg));
}
exit();
?>
