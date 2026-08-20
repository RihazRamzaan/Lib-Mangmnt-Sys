<?php
/**
 * return_book.php
 * 
 * Handles the logic for a user to return a borrowed book.
 * Updates the borrow_records status and increments the book quantity.
 * 
 * Owner: Member B (Auth & Secondary Backend) - Co-developed with Member A
 */

session_start();
require_once '../config/db.php';
require_once '../includes/validate.php';

// Ensure user is logged in
check_auth();

$message = "";
$status_type = ""; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $user_id = $_SESSION['user_id'];

    if (!is_positive_integer($book_id)) {
        $message = "Invalid book selection.";
        $status_type = "error";
    } else {
        // Start transaction to ensure both updates happen together
        $conn->begin_transaction();

        try {
            // 1. Find the active borrow record for this user and book
            // Using FOR UPDATE to lock the row during the transaction
            $find_sql = "SELECT record_id FROM borrow_records WHERE book_id = ? AND user_id = ? AND status = 'borrowed' LIMIT 1 FOR UPDATE";
            $find_stmt = $conn->prepare($find_sql);
            $find_stmt->bind_param("ii", $book_id, $user_id);
            $find_stmt->execute();
            $find_result = $find_stmt->get_result();
            $record = $find_result->fetch_assoc();

            if ($record) {
                $record_id = $record['record_id'];
                $return_date = date('Y-m-d');

                // 2. Update the borrow record status
                $update_record_sql = "UPDATE borrow_records SET status = 'returned', return_date = ? WHERE record_id = ?";
                $update_record_stmt = $conn->prepare($update_record_sql);
                $update_record_stmt->bind_param("si", $return_date, $record_id);
                $update_record_stmt->execute();

                // 3. Increment book quantity
                $update_book_sql = "UPDATE books SET quantity = quantity + 1 WHERE book_id = ?";
                $update_book_stmt = $conn->prepare($update_book_sql);
                $update_book_stmt->bind_param("i", $book_id);
                $update_book_stmt->execute();

                // Commit the transaction
                $conn->commit();
                $message = "Book returned successfully!";
                $status_type = "success";
            } else {
                $message = "No active borrow record found for this book.";
                $status_type = "error";
                $conn->rollback();
            }
            
            $find_stmt->close();
        } catch (Exception $e) {
            // Rollback on any error
            $conn->rollback();
            $message = "An error occurred: " . $e->getMessage();
            $status_type = "error";
        }
    }
}
?>

<div class="container">
    <h2>Return Book</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $status_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <p><a href="../books/list_books.php" class="btn-primary">Back to Books</a></p>
    <?php else: ?>
        <p>Are you sure you want to return this book?</p>
        <form action="return_book.php" method="POST">
            <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($_GET['book_id'] ?? ''); ?>">
            <button type="submit" class="btn-primary">Confirm Return</button>
            <a href="../books/list_books.php" class="btn-secondary">Cancel</a>
        </form>
    <?php endif; ?>
</div>
