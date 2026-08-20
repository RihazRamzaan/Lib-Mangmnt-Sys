# Library Management System

This project is a framework-free, vanilla HTML, CSS, JavaScript, and PHP web application for a Library Management System utilizing MySQL as the database.

For complete background information, guidelines, database schemas, and styling requirements, please see [PROJECT_CONTEXT.md](file:///c:/xampp/htdocs/Lib-Mangmnt-Sys/PROJECT_CONTEXT.md).

## Member File Ownership

As per [exam-task-breakdown.md](file:///c:/xampp/htdocs/Lib-Mangmnt-Sys/exam-task-breakdown.md), files are owned as follows:

### Member A: Database & Core Backend Lead
- `config/db.php`
- `books/add_book.php`
- `books/edit_book.php`
- `books/delete_book.php`
- `borrow/borrow_book.php` (Co-lead with Member B)
- SQL queries for `dashboard/report.php`

### Member B: Auth & Secondary Backend
- `auth/register.php`
- `auth/login.php`
- `auth/logout.php`
- `includes/validate.php`
- `books/list_books.php`
- `borrow/borrow_book.php` (Co-lead with Member A)
- `borrow/return_book.php`
- `dashboard/report.php`

### Member C: Frontend Structure & Styling
- `includes/header.php`
- `includes/footer.php`
- `css/style.css`
- `index.php`

### Member D: Frontend Logic / JavaScript
- `js/validation.js`
- `js/main.js`
