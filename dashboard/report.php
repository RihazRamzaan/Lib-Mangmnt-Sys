<?php
/**
 * report.php
 * 
 * Dashboard report showcasing aggregate stats and records.
 * 
 * Owner: Member B (Auth & Secondary Backend)
 * SQL aggregate and JOIN queries designed/implemented by: Member A (Database Lead)
 */

require_once '../config/db.php';
require_once '../includes/validate.php';

// Verify user authentication
check_auth();

// Fetch summary metrics (total counts)
$stats = [
    'total_books' => 0,
    'total_members' => 0,
    'active_borrows' => 0,
    'returned_books' => 0
];

// Query 1: Total Books count
$res = $conn->query("SELECT SUM(quantity) AS total FROM books");
if ($row = $res->fetch_assoc()) {
    $stats['total_books'] = $row['total'] ?? 0;
}

// Query 2: Total Users (Members) count
$res = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'member'");
if ($row = $res->fetch_assoc()) {
    $stats['total_members'] = $row['total'] ?? 0;
}

// Query 3: Active Borrows count
$res = $conn->query("SELECT COUNT(*) AS total FROM borrow_records WHERE status = 'borrowed'");
if ($row = $res->fetch_assoc()) {
    $stats['active_borrows'] = $row['total'] ?? 0;
}

// Query 4: Total Returns count
$res = $conn->query("SELECT COUNT(*) AS total FROM borrow_records WHERE status = 'returned'");
if ($row = $res->fetch_assoc()) {
    $stats['returned_books'] = $row['total'] ?? 0;
}

// Fetch Query: Most Borrowed Books (JOIN + COUNT + GROUP BY)
$most_borrowed = [];
$sql_most_borrowed = "
    SELECT b.book_id, b.title, b.author, COUNT(br.record_id) AS borrow_count 
    FROM books b 
    LEFT JOIN borrow_records br ON b.book_id = br.book_id 
    GROUP BY b.book_id, b.title, b.author 
    ORDER BY borrow_count DESC 
    LIMIT 5
";
$res_mb = $conn->query($sql_most_borrowed);
if ($res_mb) {
    while ($row = $res_mb->fetch_assoc()) {
        $most_borrowed[] = $row;
    }
}

// Fetch Query: Borrows by Category (JOIN + COUNT + GROUP BY)
$borrows_by_category = [];
$sql_category_stats = "
    SELECT c.category_name, COUNT(br.record_id) AS borrow_count 
    FROM categories c 
    LEFT JOIN books b ON c.category_id = b.category_id 
    LEFT JOIN borrow_records br ON b.book_id = br.book_id 
    GROUP BY c.category_id, c.category_name 
    ORDER BY borrow_count DESC
";
$res_cat = $conn->query($sql_category_stats);
if ($res_cat) {
    while ($row = $res_cat->fetch_assoc()) {
        $borrows_by_category[] = $row;
    }
}

// Fetch Query: Currently Borrowed Books Log (JOIN)
$active_borrows_log = [];
$sql_active_log = "
    SELECT br.record_id, b.title, u.full_name, br.borrow_date 
    FROM borrow_records br 
    JOIN books b ON br.book_id = b.book_id 
    JOIN users u ON br.user_id = u.user_id 
    WHERE br.status = 'borrowed' 
    ORDER BY br.borrow_date ASC
";
$res_log = $conn->query($sql_active_log);
if ($res_log) {
    while ($row = $res_log->fetch_assoc()) {
        $active_borrows_log[] = $row;
    }
}

// Include page header (styled by Member C)
require_once '../includes/header.php';
?>

<!-- HTML Layout to display the reports (Member B / Member C can adjust the UI styling classes) -->
<div class="content-container">
    <h2>Library System Report & Analytics</h2>

    <!-- Summary Statistics Grid -->
    <div class="stats-grid" style="display: flex; gap: 20px; margin-bottom: 30px;">
        <div class="stat-card" style="border: 1px solid #ccc; padding: 15px; border-radius: 5px; flex: 1;">
            <h3>Total Book Inventory</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 5px 0;"><?php echo $stats['total_books']; ?></p>
        </div>
        <div class="stat-card" style="border: 1px solid #ccc; padding: 15px; border-radius: 5px; flex: 1;">
            <h3>Registered Members</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 5px 0;"><?php echo $stats['total_members']; ?></p>
        </div>
        <div class="stat-card" style="border: 1px solid #ccc; padding: 15px; border-radius: 5px; flex: 1;">
            <h3>Active Borrows</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 5px 0;"><?php echo $stats['active_borrows']; ?></p>
        </div>
        <div class="stat-card" style="border: 1px solid #ccc; padding: 15px; border-radius: 5px; flex: 1;">
            <h3>Total Books Returned</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 5px 0;"><?php echo $stats['returned_books']; ?></p>
        </div>
    </div>

    <!-- Details Tables Grid -->
    <div class="reports-tables" style="display: flex; flex-direction: column; gap: 30px;">
        
        <!-- Most Borrowed Books -->
        <div class="report-section">
            <h3>Top 5 Most Borrowed Books</h3>
            <table class="report-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #f2f2f2; text-align: left;">
                        <th style="padding: 8px; border: 1px solid #ddd;">Title</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Author</th>
                        <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Times Borrowed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($most_borrowed)): ?>
                        <tr><td colspan="3" style="padding: 8px; text-align: center;">No borrow data available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($most_borrowed as $row): ?>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['author']); ?></td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo $row['borrow_count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Borrows by Category -->
        <div class="report-section">
            <h3>Borrow Frequency by Category</h3>
            <table class="report-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #f2f2f2; text-align: left;">
                        <th style="padding: 8px; border: 1px solid #ddd;">Category</th>
                        <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Total Borrows</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($borrows_by_category)): ?>
                        <tr><td colspan="2" style="padding: 8px; text-align: center;">No category data available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($borrows_by_category as $row): ?>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo $row['borrow_count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Active Borrows Log -->
        <div class="report-section">
            <h3>Currently Borrowed Books (Active Log)</h3>
            <table class="report-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #f2f2f2; text-align: left;">
                        <th style="padding: 8px; border: 1px solid #ddd;">Book Title</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Borrowed By</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Borrow Date</th>
                        <?php if (is_admin()): ?>
                            <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($active_borrows_log)): ?>
                        <tr><td colspan="<?php echo is_admin() ? 4 : 3; ?>" style="padding: 8px; text-align: center;">No active borrows at this moment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($active_borrows_log as $row): ?>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                                <?php if (is_admin()): ?>
                                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                                        <!-- Secure Return Button using return_book.php POST flow -->
                                        <form action="../borrow/return_book.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="record_id" value="<?php echo $row['record_id']; ?>">
                                            <button type="submit" class="btn-danger" onclick="return confirm('Confirm book return?');" style="cursor: pointer; background: #e74c3c; color: white; border: none; padding: 4px 8px; border-radius: 3px;">Return Book</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php
// Include page footer (styled by Member C)
require_once '../includes/footer.php';
?>
