# Assignment 6 - Search Component

## Project Overview
Academic Management System with 2 search queries for team size N=2.

  Implemented Queries:
- Query 1.2: Course Popularity Analysis
- Query 2.1: Team Composition and Project Involvement

## File Structure
```
assignment6/
├── index.php              # Main page
├── css/style.css          # Styling
├── config/
│   └── db_config.php     # Database config
├── courses/              # Course search (Query 1.2)
│   ├── search_form.php
│   ├── search_results.php
│   └── course_detail.php
└── teams/                # Team search (Query 2.1)
    ├── search_form.php
    ├── search_results.php
    └── team_detail.php
```

## Quick Setup

   1. XAMPP Setup
- Copy project to `C:\xampp\htdocs\project 6\`
- Start Apache and MySQL

   2. Database
```sql
CREATE DATABASE project_a2;
USE project_a2;
-- Import Schema.sql
```

   3. Configure
Edit `config/db_config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');              // Empty for XAMPP
define('DB_NAME', 'project_a2');
```

   4. Access
Open: `http://localhost/project 6/`

## Features
- 2 complete search modules
- Advanced filtering and sorting
- Detail pages for each result
- Responsive design
- SQL injection protection

## Requirements Met
- N queries (N=2)
- Search forms
- Results lists with links
- Detail pages
- Database integration
- Error handling

## Team
Team Size: 2 members