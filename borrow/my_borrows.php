<?php
/**
 * my_borrows.php
 * 
 * Displays a list of books borrowed by the current user.
 * Allows the user to return books that are currently borrowed.
 */

session_start();
require_once '../config/db.php';
require_once '../includes/validate.php';

// Check if user is logged in
check_auth();

$user_id = $_SESSION['user_id'];

// Fetch the user's borrow records
$sql = "SELECT br.record_id, br.borrow_date, br.return_date, br.status, b.book_id, b.title, b.author 
        FROM borrow_records br 
        JOIN books b ON br.book_id = b.book_id 
        WHERE br.user_id = ? 
        ORDER BY br.borrow_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

require_once '../includes/header.php';
?>

<div class="container">
    <h2>My Borrows</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <table class="styled-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                        <td><?php echo $row['return_date'] ? htmlspecialchars($row['return_date']) : '-'; ?></td>
                        <td>
                            <?php if ($row['status'] === 'borrowed'): ?>
                                <span class="status-badge status-borrowed">Borrowed</span>
                            <?php else: ?>
                                <span class="status-badge status-returned">Returned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'borrowed'): ?>
                                <form action="return_book.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to return this book?')">
                                    <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">Return</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">You have not borrowed any books yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
$stmt->close();
?>
