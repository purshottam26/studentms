# Student Management System — Setup Guide

## Database Setup (MySQL)

Run this SQL to create the required tables:

```sql
-- Create database
CREATE DATABASE IF NOT EXISTS mydb;
USE mydb;

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student table
CREATE TABLE IF NOT EXISTS student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    course VARCHAR(100),
    aadhaar VARCHAR(12),
    mobile VARCHAR(10),
    pincode VARCHAR(6),
    photo VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admin (username, password) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
```

## Default Login
- Username: **admin**
- Password: **admin123**

## Change Password
Use hash.php to generate a new password hash:
```
http://localhost/studentms/hash.php
```
Then update the admin table.

## Files Structure
- `login.php` — Secure login with CAPTCHA + brute-force protection
- `index.php` — Dashboard with stats and charts
- `students.php` — Add/search/manage students
- `students_list.php` — Full student list
- `profile.php` — Individual student profile
- `edit.php` + `update.php` — Edit student details
- `delete.php` — Delete student
- `documents.php` — Upload/manage monthly documents per student
- `export.php` — Export all students to Excel
- `logout.php` — Secure logout
