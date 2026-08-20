<?php
/**
 * return_book.php
 * 
 * Handles the transaction logic for returning a borrowed book.
 * Validates active borrow record, updates the borrow record (status and return date),
 * increments book quantity inventory, and completes the action.
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
    $record_id = isset($_POST['record_id']) ? $_POST['record_id'] : '';
    $book_id = isset($_POST['book_id']) ? $_POST['book_id'] : '';
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

    // We need either a specific record_id OR both book_id and user_id to process return
    if (!is_positive_integer($record_id) && (!is_positive_integer($book_id) || !is_positive_integer($user_id))) {
        $error_msg = "Invalid request: missing identifiers.";
    } else {
        // Start database transaction
        $conn->begin_transaction();

        try {
            // 1. Find active borrow record
            if (is_positive_integer($record_id)) {
                $stmt = $conn->prepare("SELECT record_id, book_id, user_id FROM borrow_records WHERE record_id = ? AND status = 'borrowed' FOR UPDATE");
                $stmt->bind_param("i", $record_id);
            } else {
                $stmt = $conn->prepare("SELECT record_id, book_id, user_id FROM borrow_records WHERE book_id = ? AND user_id = ? AND status = 'borrowed' LIMIT 1 FOR UPDATE");
                $stmt->bind_param("ii", $book_id, $user_id);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("No active borrow record found to return.");
            }

            $record = $result->fetch_assoc();
            $stmt->close();

            $target_record_id = $record['record_id'];
            $target_book_id = $record['book_id'];

            // 2. Fetch the title of the book for user feedback
            $stmt = $conn->prepare("SELECT title FROM books WHERE book_id = ?");
            $stmt->bind_param("i", $target_book_id);
            $stmt->execute();
            $book_result = $stmt->get_result();
            $book_title = "Book";
            if ($book_row = $book_result->fetch_assoc()) {
                $book_title = $book_row['title'];
            }
            $stmt->close();

            // 3. Update the borrow record's return_date and status
            $return_date = date('Y-m-d');
            $status = 'returned';
            $stmt = $conn->prepare("UPDATE borrow_records SET return_date = ?, status = ? WHERE record_id = ?");
            $stmt->bind_param("ssi", $return_date, $status, $target_record_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update return status.");
            }
            $stmt->close();

            // 4. Increment the book quantity by 1
            $stmt = $conn->prepare("UPDATE books SET quantity = quantity + 1 WHERE book_id = ?");
            $stmt->bind_param("i", $target_book_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update book quantity inventory.");
            }
            $stmt->close();

            // Commit the transaction
            $conn->commit();
            $success_msg = "Book '" . htmlspecialchars($book_title) . "' has been returned successfully!";
        } catch (Exception $e) {
            // Rollback the transaction on failure
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
