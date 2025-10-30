<?php
/**
 * Course Search Results
 * Query 1.2 - Course Popularity Analysis Results
 */

require_once '../config/db_config.php';

// Get search parameters
$course_code = isset($_GET['course_code']) ? trim($_GET['course_code']) : '';
$course_name = isset($_GET['course_name']) ? trim($_GET['course_name']) : '';
$semester = isset($_GET['semester']) ? trim($_GET['semester']) : '';
$min_students = isset($_GET['min_students']) ? intval($_GET['min_students']) : 0;
$min_instructors = isset($_GET['min_instructors']) ? intval($_GET['min_instructors']) : 0;
$has_tutors = isset($_GET['has_tutors']) ? trim($_GET['has_tutors']) : '';
$sort_by = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'student_count';
$order = isset($_GET['order']) ? trim($_GET['order']) : 'DESC';

// Build the SQL query
$sql = "SELECT 
    c.course_id,
    c.code,
    c.name AS course_name,
    c.semester,
    COUNT(DISTINCT t.instructor_id) AS instructor_count,
    COUNT(DISTINCT e.student_id) AS student_count,
    ROUND(COUNT(DISTINCT e.student_id) * 1.0 / NULLIF(COUNT(DISTINCT t.instructor_id), 0), 2) AS students_per_instructor,
    COUNT(DISTINCT CASE WHEN e.enrollment_role = 'tutor' THEN e.student_id END) AS tutor_count
FROM Course c
LEFT JOIN Teaches t ON c.course_id = t.course_id
LEFT JOIN Enrollment e ON c.course_id = e.course_id
WHERE 1=1";

// Add WHERE conditions dynamically
$params = [];
$types = '';

if (!empty($course_code)) {
    $sql .= " AND c.code LIKE ?";
    $params[] = '%' . $course_code . '%';
    $types .= 's';
}

if (!empty($course_name)) {
    $sql .= " AND c.name LIKE ?";
    $params[] = '%' . $course_name . '%';
    $types .= 's';
}

if (!empty($semester)) {
    $sql .= " AND c.semester = ?";
    $params[] = $semester;
    $types .= 's';
}

$sql .= " GROUP BY c.course_id, c.code, c.name, c.semester";

// Add HAVING conditions
$having_conditions = [];
if ($min_students > 0) {
    $having_conditions[] = "COUNT(DISTINCT e.student_id) >= $min_students";
}
if ($min_instructors > 0) {
    $having_conditions[] = "COUNT(DISTINCT t.instructor_id) >= $min_instructors";
}
if ($has_tutors === 'yes') {
    $having_conditions[] = "COUNT(DISTINCT CASE WHEN e.enrollment_role = 'tutor' THEN e.student_id END) > 0";
} elseif ($has_tutors === 'no') {
    $having_conditions[] = "COUNT(DISTINCT CASE WHEN e.enrollment_role = 'tutor' THEN e.student_id END) = 0";
}

if (!empty($having_conditions)) {
    $sql .= " HAVING " . implode(' AND ', $having_conditions);
}

// Add ORDER BY
$valid_sort_columns = ['student_count', 'instructor_count', 'course_code' => 'c.code', 'semester'];
$sort_column = isset($valid_sort_columns[$sort_by]) ? 
    (is_string($valid_sort_columns[$sort_by]) ? $valid_sort_columns[$sort_by] : $sort_by) : 
    'student_count';
$order = ($order === 'ASC') ? 'ASC' : 'DESC';
$sql .= " ORDER BY $sort_column $order, instructor_count DESC";

// Execute query
$conn = getDBConnection();
$results = [];
$error = null;

try {
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $results = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $result = $conn->query($sql);
        $results = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $error = "Search error: " . $e->getMessage();
}

closeDBConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Search Results - Academic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Course Search Results</h1>
            <p class="subtitle">Found <?php echo count($results); ?> course(s)</p>
        </header>

        <main>
            <div class="nav-links">
                <a href="search_form.php">← New Search</a>
                <a href="../index.php">🏠 Home</a>
            </div>

            <?php if ($error): ?>
                <div class="message error">
                    <strong>Error:</strong> <?php echo sanitizeOutput($error); ?>
                </div>
            <?php elseif (empty($results)): ?>
                <div class="message info">
                    <strong>No courses found.</strong> Try adjusting your search criteria.
                </div>
            <?php else: ?>
                <div class="results-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Semester</th>
                                <th>Students</th>
                                <th>Instructors</th>
                                <th>Tutors</th>
                                <th>Students/Instructor</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><strong><?php echo sanitizeOutput($row['code']); ?></strong></td>
                                    <td><?php echo sanitizeOutput($row['course_name']); ?></td>
                                    <td><?php echo sanitizeOutput($row['semester']); ?></td>
                                    <td><?php echo sanitizeOutput($row['student_count']); ?></td>
                                    <td><?php echo sanitizeOutput($row['instructor_count']); ?></td>
                                    <td><?php echo sanitizeOutput($row['tutor_count']); ?></td>
                                    <td><?php echo $row['students_per_instructor'] ?? 'N/A'; ?></td>
                                    <td>
                                        <a href="course_detail.php?id=<?php echo $row['course_id']; ?>">
                                            View Details →
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2025 Academic Management System | Course Search Results</p>
        </footer>
    </div>
</body>
</html>