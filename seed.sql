-- Seed script for Library Management System
USE library_db;

-- Clear existing data (in correct order of foreign key dependency)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE borrow_records;
TRUNCATE TABLE books;
TRUNCATE TABLE categories;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert Users
-- Roles: admin (password: admin123), member (password: member123)
INSERT INTO users (user_id, full_name, email, password, role) VALUES
(1, 'Alice Smith', 'alice.admin@library.com', '$2y$10$XeJUGq.Lge7nMbTvwgVa/O4I5Ae.DD.HWFIkPonFmrTPd5.NKFwfa', 'admin'),
(2, 'Bob Jones', 'bob.admin@library.com', '$2y$10$XeJUGq.Lge7nMbTvwgVa/O4I5Ae.DD.HWFIkPonFmrTPd5.NKFwfa', 'admin'),
(3, 'Charlie Brown', 'charlie@member.com', '$2y$10$RZoKdWs7kZGZykAs4Jk.m.tzD1p1U3lGTD4BXC4jclrlkgUDx16zK', 'member'),
(4, 'Diana Prince', 'diana@member.com', '$2y$10$RZoKdWs7kZGZykAs4Jk.m.tzD1p1U3lGTD4BXC4jclrlkgUDx16zK', 'member'),
(5, 'Evan Wright', 'evan@member.com', '$2y$10$RZoKdWs7kZGZykAs4Jk.m.tzD1p1U3lGTD4BXC4jclrlkgUDx16zK', 'member');

-- Insert Categories
INSERT INTO categories (category_id, category_name) VALUES
(1, 'Science Fiction'),
(2, 'Technology & Programming'),
(3, 'History'),
(4, 'Biographies'),
(5, 'Mathematics & Science');

-- Insert Books
INSERT INTO books (book_id, title, author, category_id, quantity, added_by) VALUES
(1, 'Dune', 'Frank Herbert', 1, 3, 1),
(2, 'Clean Code', 'Robert C. Martin', 2, 5, 1),
(3, 'A Brief History of Time', 'Stephen Hawking', 5, 2, 1),
(4, 'Steve Jobs', 'Walter Isaacson', 4, 2, 2),
(5, 'The Guns of August', 'Barbara W. Tuchman', 3, 1, 2),
(6, 'Introduction to Algorithms', 'Thomas H. Cormen', 2, 4, 1),
(7, 'The Hobbit', 'J.R.R. Tolkien', 1, 6, 2),
(8, 'Calculus', 'James Stewart', 5, 3, 2);

-- Insert Borrow Records
INSERT INTO borrow_records (record_id, book_id, user_id, borrow_date, return_date, status) VALUES
(1, 1, 3, '2026-08-01', '2026-08-15', 'returned'),
(2, 2, 3, '2026-08-10', NULL, 'borrowed'),
(3, 3, 4, '2026-08-12', '2026-08-18', 'returned'),
(4, 4, 4, '2026-08-15', NULL, 'borrowed'),
(5, 6, 5, '2026-08-18', NULL, 'borrowed'),
(6, 7, 3, '2026-08-19', NULL, 'borrowed');
