<?php
/**
 * report.php
 * 
 * Generates aggregate queries/reports (e.g., total books, currently borrowed, most borrowed).
 * 
 * Owner: Member B (Auth & Secondary Backend) - SQL queries written by Member A
 */

session_start();
require_once '../config/db.php';
require_once '../includes/validate.php';

// Ensure user is logged in
check_auth();

// Array to hold our report data
$report_data = [
    'total_unique_books' => 0,
    'total_inventory' => 0,
    'currently_borrowed' => 0,
    'most_borrowed' => []
];

// 1. Get total unique books and total physical inventory
$inventory_sql = "SELECT COUNT(book_id) as unique_books, SUM(quantity) as total_inventory FROM books";
$inventory_result = $conn->query($inventory_sql);
if ($inventory_row = $inventory_result->fetch_assoc()) {
    $report_data['total_unique_books'] = $inventory_row['unique_books'] ?? 0;
    $report_data['total_inventory'] = $inventory_row['total_inventory'] ?? 0;
}

// 2. Get currently borrowed count
$borrowed_sql = "SELECT COUNT(record_id) as currently_borrowed FROM borrow_records WHERE status = 'borrowed'";
$borrowed_result = $conn->query($borrowed_sql);
if ($borrowed_row = $borrowed_result->fetch_assoc()) {
    $report_data['currently_borrowed'] = $borrowed_row['currently_borrowed'] ?? 0;
}

// 3. Get Top 5 Most Borrowed Books (Aggregate JOIN Query)
$popular_sql = "SELECT b.title, c.category_name, COUNT(br.record_id) as borrow_count 
                FROM books b
                LEFT JOIN categories c ON b.category_id = c.category_id
                JOIN borrow_records br ON b.book_id = br.book_id 
                GROUP BY b.book_id 
                ORDER BY borrow_count DESC 
                LIMIT 5";
$popular_result = $conn->query($popular_sql);
while ($row = $popular_result->fetch_assoc()) {
    $report_data['most_borrowed'][] = $row;
}
?>

<div class="container">
    <h2>Library Reports Dashboard</h2>
    
    <div class="report-cards" style="display: flex; gap: 20px; margin-bottom: 30px;">
        <div class="card" style="border: 1px solid #ccc; padding: 20px; border-radius: 8px; flex: 1;">
            <h3>Total Unique Titles</h3>
            <p style="font-size: 24px; font-weight: bold;"><?php echo $report_data['total_unique_books']; ?></p>
        </div>
        <div class="card" style="border: 1px solid #ccc; padding: 20px; border-radius: 8px; flex: 1;">
            <h3>Total Inventory (Available)</h3>
            <p style="font-size: 24px; font-weight: bold;"><?php echo $report_data['total_inventory']; ?></p>
        </div>
        <div class="card" style="border: 1px solid #ccc; padding: 20px; border-radius: 8px; flex: 1;">
            <h3>Currently Borrowed</h3>
            <p style="font-size: 24px; font-weight: bold;"><?php echo $report_data['currently_borrowed']; ?></p>
        </div>
    </div>

    <h3>Top 5 Most Borrowed Books</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Total Times Borrowed</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($report_data['most_borrowed'])): ?>
                <?php foreach ($report_data['most_borrowed'] as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></td>
                        <td><?php echo $book['borrow_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No borrow records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
