<?php
/**
 * edit_book.php
 * 
 * Page for editing an existing book's details in the library catalog.
 * Restricted to admins. Uses prepared statements to select and update the book.
 * 
 * Owner: Member A (Database & Core Backend Lead)
 */

require_once '../config/db.php';
require_once '../includes/validate.php';

// Verify user authentication
check_auth();

// Verify role access (Only administrators can edit books)
if (!is_admin()) {
    die("Access Denied: You do not have permission to access this page.");
}

$success_msg = "";
$error_msg = "";
$book = null;

// Determine book ID (required for both GET and POST)
$book_id = isset($_REQUEST['book_id']) ? $_REQUEST['book_id'] : '';

if (!is_positive_integer($book_id)) {
    die("Error: Invalid or missing Book ID.");
}

// Handle form submission via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $title = isset($_POST['title']) ? sanitize_input($_POST['title']) : '';
    $author = isset($_POST['author']) ? sanitize_input($_POST['author']) : '';
    $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : '';

    // Server-side validation
    if (is_empty($title) || is_empty($author) || is_empty($category_id) || is_empty($quantity)) {
        $error_msg = "All fields are required.";
    } elseif (!is_positive_integer($category_id)) {
        $error_msg = "Invalid category selected.";
    } elseif (!is_positive_integer($quantity)) {
        $error_msg = "Quantity must be a positive integer.";
    } else {
        // Update query using a prepared statement to prevent SQL injection
        $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, category_id = ?, quantity = ? WHERE book_id = ?");
        if ($stmt) {
            $stmt->bind_param("ssiii", $title, $author, $category_id, $quantity, $book_id);
            if ($stmt->execute()) {
                $success_msg = "Book has been updated successfully!";
            } else {
                $error_msg = "Error executing update: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_msg = "Error preparing update statement: " . $conn->error;
        }
    }
}

// Fetch the current book details from the database
$stmt = $conn->prepare("SELECT book_id, title, author, category_id, quantity FROM books WHERE book_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("Error: Book not found.");
    }
    $book = $result->fetch_assoc();
    $stmt->close();
} else {
    die("Database error preparing statement: " . $conn->error);
}

// Fetch available categories for the dropdown menu
$categories = [];
$cat_result = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Include page header (styled by Member C)
require_once '../includes/header.php';
?>

<div class="content-container">
    <h2>Edit Book Details</h2>

    <!-- Display Feedback Messages -->
    <?php if (!empty($success_msg)): ?>
        <p class="success-message" style="color: green; font-weight: bold;"><?php echo $success_msg; ?></p>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <p class="error-message" style="color: red; font-weight: bold;"><?php echo $error_msg; ?></p>
    <?php endif; ?>

    <!-- Edit Book Form -->
    <form action="edit_book.php" method="POST" id="bookForm" class="styled-form">
        <!-- Keep track of the book ID -->
        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">

        <div class="form-group">
            <label for="title">Book Title:</label>
            <input type="text" name="title" id="title" required value="<?php echo htmlspecialchars($book['title']); ?>">
        </div>

        <div class="form-group">
            <label for="author">Author:</label>
            <input type="text" name="author" id="author" required value="<?php echo htmlspecialchars($book['author']); ?>">
        </div>

        <div class="form-group">
            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>" <?php echo ($category['category_id'] == $book['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" min="1" value="<?php echo htmlspecialchars($book['quantity']); ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Update Book</button>
            <a href="../books/list_books.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
// Include page footer (styled by Member C)
require_once '../includes/footer.php';
?>
