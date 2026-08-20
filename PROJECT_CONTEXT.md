# Sample Practical Exam Template — Library Management System
*(Adapt entity names for whatever theme you're given: books→patients/products/events, members→students/customers, etc.)*

> **Usage note:** Once your team adapts this to your real exam theme, the Repo Setup Lead should commit this whole file into the repo root as `PROJECT_CONTEXT.md`. Every member then points their AI coding assistant at this file before generating code, so all 4 machines produce consistent, framework-free, similarly-styled output — see the task breakdown doc for the exact repo setup steps.

> **Tech stack constraint:** Pure **HTML, CSS, JavaScript, and PHP** only (with MySQL as the database, accessed via raw `mysqli`/`PDO` — never an ORM). No CSS frameworks (Bootstrap/Tailwind), no JS frameworks/libraries (React/Vue/jQuery), no PHP frameworks (Laravel/CodeIgniter). Everything should be hand-written so every member can explain every line.

---

## For AI Coding Assistants (Copilot, Cursor, Claude Code, etc.)

**Read this block before generating any code in this repository.** This project is a timed team exam with 4 collaborators working in separate IDEs. Follow these rules exactly so code generated on different machines stays compatible:

- **Stack:** HTML, CSS, JavaScript, PHP, and MySQL only. Never suggest, install, or import a framework, UI library, JS library, PHP framework, or ORM — even if it would be "easier" or "more standard." Vanilla code only.
- **Database access:** `mysqli` or `PDO` with **prepared statements only**. Never build SQL with string concatenation.
- **Naming conventions:** `snake_case` for database tables/columns and PHP variables/functions; `camelCase` for JavaScript variables/functions; file names in `snake_case.php` / `snake_case.js` matching the endpoint contract in Section 3 below.
- **File ownership:** Do not create, rename, or restructure files/folders outside the structure defined in Section 2. If a new file seems needed, flag it to the person rather than creating it unprompted.
- **Shared files** (`header.php`, `footer.php`, `style.css`) belong to one owner (see the team's `README.md`) — don't modify these unless you are that owner.
- **UI style:** Follow Section 5 (clean, minimalistic — neutral palette, one accent color, one font family, generous whitespace, consistent buttons/tables/forms). Don't add decorative flourishes, gradients, animations, or icon libraries.
- **Security basics to always include:** `password_hash()`/`password_verify()` for passwords, `session_start()` + `$_SESSION` for login state, server-side validation on every form even when client-side validation exists.
- **Code comments are mandatory.** Every AI-generated function, query, and non-trivial block must include a clear explanatory comment describing *what it does and why* (not just restating the code) — e.g. `// Using a prepared statement here to prevent SQL injection; ? placeholders are bound below`. Every member must be able to read their own file and explain it without help, since this is presented live.
- **Strictly do not edit files outside your own responsibility.** Each file in Section 2 has one owner (see the team's `README.md`). If your AI assistant suggests, generates, or auto-completes changes to a file owned by someone else — including "helpful" refactors of shared code — reject it. This applies even to small fixes; flag the issue to the file's owner instead of editing it yourself. This is the primary way 4 people avoid merge conflicts under time pressure.
- **When unsure, match existing code in the file/folder** rather than introducing a new pattern — consistency across 4 contributors matters more than any individual stylistic preference.

---

## 1. Database Schema (MySQL)

```sql
CREATE DATABASE library_db;
USE library_db;

-- Users table (handles both admin and member roles)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,   -- store hashed with password_hash()
    role ENUM('admin','member') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories (for relationship/join demo)
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL
);

-- Books (main entity)
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    author VARCHAR(100) NOT NULL,
    category_id INT,
    quantity INT DEFAULT 1,
    added_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (added_by) REFERENCES users(user_id)
);

-- Borrow records (transaction table — great for aggregate/report queries)
CREATE TABLE borrow_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    user_id INT,
    borrow_date DATE,
    return_date DATE,
    status ENUM('borrowed','returned') DEFAULT 'borrowed',
    FOREIGN KEY (book_id) REFERENCES books(book_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
```

**Why this schema is a good exam template:**
- 4 tables → enough to demonstrate normalization and at least 2 foreign key relationships
- `borrow_records` gives you a natural reason to write JOIN queries and aggregate functions (`COUNT`, `SUM`, `GROUP BY`) for a "dashboard/report" feature
- `role` column gives you a simple way to demo access control without needing a separate framework

---

## 2. Folder / File Structure

```
project/
│
├── config/
│   └── db.php                 # DB connection (mysqli or PDO)
│
├── auth/
│   ├── register.php
│   ├── login.php
│   └── logout.php
│
├── books/
│   ├── list_books.php         # Read (with search/filter)
│   ├── add_book.php           # Create
│   ├── edit_book.php          # Update
│   └── delete_book.php        # Delete
│
├── borrow/
│   ├── borrow_book.php
│   └── return_book.php
│
├── dashboard/
│   └── report.php             # aggregate queries (most borrowed, overdue, etc.)
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── validate.php           # shared server-side validation functions
│
├── css/
│   └── style.css
│
├── js/
│   ├── validation.js          # client-side form validation
│   └── main.js                # dynamic UI behavior, fetch() calls
│
└── index.php                  # landing/login page
```

---

## 3. Suggested Endpoint "Contract" (agree on this FIRST, as a team)

Decide this in your first 15 minutes so members can work in parallel without waiting on each other:

| File | Method | Expects (POST/GET) | Returns |
|---|---|---|---|
| `auth/login.php` | POST | `email`, `password` | redirect + session, or error message |
| `books/add_book.php` | POST | `title`, `author`, `category_id`, `quantity` | success/fail message |
| `books/list_books.php` | GET | optional `search`, `category_id` | HTML table or JSON rows |
| `books/delete_book.php` | POST | `book_id` | success/fail message |
| `borrow/borrow_book.php` | POST | `book_id`, `user_id` | success/fail message |
| `dashboard/report.php` | GET | — | counts/summary via aggregate SQL |

Agreeing on **exact field names** up front is the single biggest time-saver — it's what lets your frontend person (JS/HTML) and backend people (PHP/SQL) build simultaneously instead of blocking each other.

---

## 4. Core Code Patterns You'll Be Expected to Explain

**DB connection (config/db.php) — using mysqli with prepared statements:**
```php
<?php
$conn = new mysqli("localhost", "root", "", "library_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

**Prepared statement example (prevents SQL injection — you WILL be asked about this):**
```php
$stmt = $conn->prepare("INSERT INTO books (title, author, category_id, quantity) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssii", $title, $author, $category_id, $quantity);
$stmt->execute();
```

**Password hashing (also commonly asked):**
```php
$hashed = password_hash($password, PASSWORD_DEFAULT);
// verify at login:
if (password_verify($input_password, $stored_hash)) { /* success */ }
```

**Client-side validation (js/validation.js) — pair this with server-side checks:**
```javascript
document.getElementById("bookForm").addEventListener("submit", function(e) {
    const title = document.getElementById("title").value.trim();
    if (title === "") {
        alert("Title is required");
        e.preventDefault();
    }
});
```
Be ready to explain: **client-side validation improves UX and reduces server load, but server-side validation is mandatory because client-side checks can be bypassed.**

---

## 5. UI Design Guidelines — Keep It Clean, Minimalistic, Simple

Since there's no CSS framework to lean on, the temptation is either to over-decorate or to leave pages looking like unstyled default HTML. Aim for the middle: simple, intentional, and consistent. This is also easier to finish in 4 hours than a "fancy" design, and examiners generally reward clarity over decoration.

**Layout**
- Generous whitespace — don't cram forms/tables edge to edge
- Max content width (e.g. `max-width: 900px; margin: 0 auto;`) instead of stretching full browser width
- One consistent layout shell (header + content + footer) reused via `header.php`/`footer.php` on every page
- Align form labels and inputs consistently (stacked labels above inputs is simplest and reads clean)

**Color**
- Pick 1 neutral base (white/light gray background) + 1 accent color (used sparingly, e.g. buttons and links only) + 1 dark text color
- Avoid more than 2–3 colors total; avoid gradients or shadows unless very subtle
- Use color with purpose: red-ish tone for delete/error, green-ish tone for success — not decoration

**Typography**
- One font family throughout (a clean system font is enough: `font-family: 'Segoe UI', Arial, sans-serif;`)
- Clear size hierarchy: larger/bold for page titles, normal weight for body text, no more than 2–3 font sizes total
- Comfortable line-height (`1.4`–`1.6`) so text doesn't feel cramped

**Components**
- Buttons: consistent padding, rounded corners (small radius, e.g. `4px`–`6px`), one style per action type (primary vs delete/danger)
- Tables: light borders or zebra-striping (alternating row background) instead of heavy grid lines, adequate cell padding
- Forms: consistent spacing between fields, clear focus states on inputs (`:focus` outline or border color change)
- Avoid: drop shadows stacked everywhere, multiple border styles, inconsistent button sizes, walls of unstyled default `<table>`/`<input>` elements

**Practical tip for the 4-hour constraint**
Write a small set of reusable CSS utility rules early (spacing, button style, table style, form style) in `style.css`, and reuse them across every page rather than styling each page from scratch. This keeps the UI consistent automatically and saves time — consistency reads as "clean and simple" even with very few CSS rules.

---

## 6. Quick Presentation-Readiness Checklist

- [ ] Each member can explain their own SQL queries line by line
- [ ] Everyone knows why prepared statements are used (not just that they are)
- [ ] Everyone can explain client vs server-side validation
- [ ] At least one join query and one aggregate query (COUNT/SUM/GROUP BY) in the report page
- [ ] Passwords are hashed, not stored in plain text
- [ ] Sessions used correctly for login state (`session_start()`, `$_SESSION`)
- [ ] Basic responsive CSS (no framework, but should still look intentional, not default browser styling)
- [ ] UI is clean, minimalistic, and visually consistent across all pages (same header/footer, same button/table/form styling everywhere)
- [ ] Every function/query has an explanatory comment — you should be able to read your own file cold and explain it without notes
- [ ] No member edited another member's assigned files during development
