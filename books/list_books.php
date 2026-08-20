<?php
/**
 * list_books.php
 * 
 * Displays a list of books available in the library.
 * Includes search functionality (by title or author) and filtering by category.
 * 
 * Owner: Member B (Auth & Secondary Backend)
 */

// Start session and include required files
session_start();
require_once '../config/db.php';
require_once '../includes/validate.php';

// Check if user is logged in
check_auth();

// Initialize variables for search and filter
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category_filter = isset($_GET['category_id']) ? $_GET['category_id'] : '';

// Fetch all categories for the filter dropdown
$cat_sql = "SELECT * FROM categories ORDER BY category_name ASC";
$cat_result = $conn->query($cat_sql);

// Base SQL query with JOIN to get category names
$sql = "SELECT b.*, c.category_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.category_id 
        WHERE 1=1";

$params = [];
$types = "";

// Apply search filter (Title or Author)
if (!empty($search)) {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

// Apply category filter
if (!empty($category_filter) && is_numeric($category_filter)) {
    $sql .= " AND b.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

$sql .= " ORDER BY b.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

require_once '../includes/header.php';
?>

<!-- HTML for Book Listing (Structure by Member B, Styling by Member C) -->
<div class="container">
    <h2>Library Books</h2>
    
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

    <!-- Search and Filter Form -->
    <form action="list_books.php" method="GET" class="filter-form">
        <div class="form-group-inline">
            <label for="search">Search</label>
            <input type="text" id="search" name="search" placeholder="Search by title or author..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="form-group-inline">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="">All Categories</option>
                <?php while ($cat = $cat_result->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo ($category_filter == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="list_books.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>

    <!-- Books Table -->
    <table class="styled-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($book = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></td>
                        <td><?php echo $book['quantity']; ?></td>
                        <td>
                            <?php if (is_admin()): ?>
                                <a href="edit_book.php?book_id=<?php echo $book['book_id']; ?>">Edit</a> | 
                                <form action="delete_book.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                    <button type="submit" style="background:none; border:none; color:#d9534f; text-decoration:underline; cursor:pointer; padding:0; font:inherit;">Delete</button>
                                </form> | 
                            <?php endif; ?>
                            <?php if ($book['quantity'] > 0): ?>
                                <a href="../borrow/borrow_book.php?book_id=<?php echo $book['book_id']; ?>" class="btn-sm btn-primary">Borrow</a>
                            <?php else: ?>
                                <span class="text-muted">Out of stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No books found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
$stmt->close();
?>
