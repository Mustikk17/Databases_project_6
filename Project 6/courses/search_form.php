<?php
/**
 * Course Search Form
 * Query 1.2 - Course Popularity Analysis
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Search - Academic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Course Popularity Analysis</h1>
            <p class="subtitle">Search and Filter Courses</p>
        </header>

        <main>
            <div class="nav-links">
                <a href="../index.php">← Back to Home</a>
            </div>

            <div class="search-form">
                <form action="search_results.php" method="GET">
                    <h3 style="margin-bottom: 20px; color: #333;">Search Criteria</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="course_code">Course Code</label>
                            <input type="text" id="course_code" name="course_code" 
                                   placeholder="e.g., CS101, MATH201">
                        </div>

                        <div class="form-group">
                            <label for="course_name">Course Name</label>
                            <input type="text" id="course_name" name="course_name" 
                                   placeholder="e.g., Database Systems">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="semester">Semester</label>
                            <select id="semester" name="semester">
                                <option value="">-- All Semesters --</option>
                                <option value="WS25">WS25 (Winter 2025)</option>
                                <option value="SS25">SS25 (Summer 2025)</option>
                                <option value="WS24">WS24 (Winter 2024)</option>
                                <option value="SS24">SS24 (Summer 2024)</option>
                                <option value="WS23">WS23 (Winter 2023)</option>
                                <option value="SS23">SS23 (Summer 2023)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="min_students">Minimum Students</label>
                            <input type="number" id="min_students" name="min_students" 
                                   min="0" placeholder="e.g., 10">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="min_instructors">Minimum Instructors</label>
                            <input type="number" id="min_instructors" name="min_instructors" 
                                   min="0" placeholder="e.g., 1">
                        </div>

                        <div class="form-group">
                            <label for="has_tutors">Has Tutors</label>
                            <select id="has_tutors" name="has_tutors">
                                <option value="">-- Any --</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="sort_by">Sort By</label>
                            <select id="sort_by" name="sort_by">
                                <option value="student_count">Student Count (Default)</option>
                                <option value="instructor_count">Instructor Count</option>
                                <option value="course_code">Course Code</option>
                                <option value="semester">Semester</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="order">Order</label>
                            <select id="order" name="order">
                                <option value="DESC">Descending (High to Low)</option>
                                <option value="ASC">Ascending (Low to High)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">🔍 Search Courses</button>
                        <button type="reset" class="btn btn-secondary">Reset Form</button>
                    </div>
                </form>
            </div>

            <div class="message info">
                <strong>💡 Search Tips:</strong>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>Leave fields empty to search all courses</li>
                    <li>Use partial names/codes for broader searches</li>
                    <li>Combine multiple criteria for precise results</li>
                </ul>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Academic Management System | Course Search</p>
        </footer>
    </div>
</body>
</html>