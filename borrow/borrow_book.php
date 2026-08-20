<?php
/**
 * borrow_book.php
 * 
 * Handles the logic for a user to borrow a book.
 * Checks availability and updates both borrow_records and books tables.
 * 
 * Owner: Member B (Auth & Secondary Backend) - Co-lead with Member A
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
    $user_id = $_SESSION['user_id']; // Usually the logged-in user borrows
    $borrow_date = date('Y-m-d');

    // Basic validation
    if (!is_positive_integer($book_id)) {
        $message = "Invalid book selection.";
        $status_type = "error";
    } else {
        // Start transaction to ensure both updates happen together
        $conn->begin_transaction();

        try {
            // 1. Check book availability
            $check_sql = "SELECT quantity FROM books WHERE book_id = ? FOR UPDATE";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $book_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $book = $check_result->fetch_assoc();

            if ($book && $book['quantity'] > 0) {
                // 2. Insert borrow record
                $insert_sql = "INSERT INTO borrow_records (book_id, user_id, borrow_date, status) VALUES (?, ?, ?, 'borrowed')";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iis", $book_id, $user_id, $borrow_date);
                $insert_stmt->execute();

                // 3. Decrement book quantity
                $update_sql = "UPDATE books SET quantity = quantity - 1 WHERE book_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $book_id);
                $update_stmt->execute();

                // Commit the transaction
                $conn->commit();
                $message = "Book borrowed successfully!";
                $status_type = "success";
            } else {
                $message = "Sorry, this book is currently unavailable.";
                $status_type = "error";
                $conn->rollback();
            }
            
            $check_stmt->close();
        } catch (Exception $e) {
            // Rollback on any error
            $conn->rollback();
            $message = "An error occurred: " . $e->getMessage();
            $status_type = "error";
        }
    }
}

// If accessed via GET, we might want to show a confirmation or redirect

require_once '../includes/header.php';
?>

<div class="container">
    <h2>Borrow Book</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $status_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <p><a href="../books/list_books.php" class="btn-primary">Back to Books</a></p>
    <?php else: ?>
        <p>Are you sure you want to borrow this book?</p>
        <!-- This would normally be reached via a form on list_books.php -->
        <form action="borrow_book.php" method="POST">
            <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($_GET['book_id'] ?? ''); ?>">
            <button type="submit" class="btn-primary">Confirm Borrow</button>
            <a href="../books/list_books.php" class="btn-secondary">Cancel</a>
        </form>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
