<?php
/**
 * add_book.php
 * 
 * Page for adding a new book to the library catalog.
 * Restrained to admin users. Uses prepared statements for SQL insertion.
 * 
 * Owner: Member A (Database & Core Backend Lead)
 */

require_once '../config/db.php';
require_once '../includes/validate.php';

// Verify user authentication
check_auth();

// Verify role access (Only administrators can add books)
if (!is_admin()) {
    die("Access Denied: You do not have permission to access this page.");
}

$success_msg = "";
$error_msg = "";

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
        // Insert query using a prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO books (title, author, category_id, quantity, added_by) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $added_by = $_SESSION['user_id'];
            $stmt->bind_param("ssiii", $title, $author, $category_id, $quantity, $added_by);
            
            if ($stmt->execute()) {
                $success_msg = "Book '" . htmlspecialchars($title) . "' has been added successfully!";
            } else {
                $error_msg = "Error executing query: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_msg = "Error preparing statement: " . $conn->error;
        }
    }
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
    <h2>Add New Book</h2>

    <!-- Display Feedback Messages -->
    <?php if (!empty($success_msg)): ?>
        <p class="success-message" style="color: green; font-weight: bold;"><?php echo $success_msg; ?></p>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <p class="error-message" style="color: red; font-weight: bold;"><?php echo $error_msg; ?></p>
    <?php endif; ?>

    <!-- Add Book Form (validated client-side via js/validation.js owned by Member D) -->
    <form action="add_book.php" method="POST" id="bookForm" class="styled-form">
        <div class="form-group">
            <label for="title">Book Title:</label>
            <input type="text" name="title" id="title" required placeholder="Enter book title">
        </div>

        <div class="form-group">
            <label for="author">Author:</label>
            <input type="text" name="author" id="author" required placeholder="Enter author name">
        </div>

        <div class="form-group">
            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" min="1" value="1" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Add Book</button>
            <a href="../books/list_books.php" class="btn-secondary">Back to List</a>
        </div>
    </form>
</div>

<?php
// Include page footer (styled by Member C)
require_once '../includes/footer.php';
?>
