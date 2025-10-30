# Setup Guide

## XAMPP Local Setup

### Step 1: Install XAMPP
Download from https://www.apachefriends.org/ and install.

### Step 2: Copy Project
Copy project 6/ folder to C:\xampp\htdocs\


### Step 3: Start Services
Open XAMPP Control Panel and start:
- Apache
- MySQL

### Step 4: Create Database
Open phpMyAdmin: `http://localhost/phpmyadmin`
- Create database: `project_a2`
- Import `Schema.sql`

### Step 5: Configure Database
Edit `config/db_config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');              // Empty
define('DB_NAME', 'project_a2');
```

### Step 6: Test
Open: `http://localhost/project 6/`



## InfinityFree Production (Optional)

### Step 1: Sign Up
Create account at https://infinityfree.net/

### Step 2: Create Database
- Go to MySQL Databases
- Create database and note credentials

### Step 3: Upload Files
- Use File Manager or FTP
- Upload to `htdocs/` directory

### Step 4: Import Database
- Open phpMyAdmin from control panel
- Import `Schema.sql`

### Step 5: Update Config
Edit `config/db_config.php` with InfinityFree credentials:
```php
define('DB_HOST', 'sql123.infinityfreeapp.com');
define('DB_USER', 'epiz_12345678');
define('DB_PASS', 'your_password');
define('DB_NAME', 'epiz_12345678_project_a2');
```



## Troubleshooting

**Database connection failed:**
- Check credentials in `config/db_config.php`
- Verify MySQL is running

**Blank page:**
Add to top of `index.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**CSS not loading:**
- Clear browser cache
- Check file path