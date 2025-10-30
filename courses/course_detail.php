<?php
/**
 * Course Detail Page
 * Displays complete information about a single course
 */

require_once '../config/db_config.php';

// Get course ID from URL
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    die("Invalid course ID");
}

// Fetch course details
$conn = getDBConnection();
$course = null;
$instructors = [];
$students = [];
$error = null;

try {
    // Main course query
    $sql = "SELECT 
        c.course_id,
        c.code,
        c.name AS course_name,
        c.semester,
        COUNT(DISTINCT t.instructor_id) AS instructor_count,
        COUNT(DISTINCT e.student_id) AS student_count,
        COUNT(DISTINCT CASE WHEN e.enrollment_role = 'tutor' THEN e.student_id END) AS tutor_count,
        COUNT(DISTINCT CASE WHEN e.enrollment_role = 'student' THEN e.student_id END) AS regular_student_count
    FROM Course c
    LEFT JOIN Teaches t ON c.course_id = t.course_id
    LEFT JOIN Enrollment e ON c.course_id = e.course_id
    WHERE c.course_id = ?
    GROUP BY c.course_id, c.code, c.name, c.semester";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();
    
    if (!$course) {
        throw new Exception("Course not found");
    }
    
    // Fetch instructors
    $sql_instructors = "SELECT 
        i.instructor_id,
        CONCAT(p.first_name, ' ', p.last_name) AS instructor_name,
        i.title,
        i.office,
        p.email
    FROM Teaches t
    INNER JOIN Instructor i ON t.instructor_id = i.instructor_id
    INNER JOIN Person p ON i.instructor_id = p.person_id
    WHERE t.course_id = ?
    ORDER BY p.last_name, p.first_name";
    
    $stmt = $conn->prepare($sql_instructors);
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $instructors = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Fetch enrolled students (sample - limit to 20 for display)
    $sql_students = "SELECT 
        s.student_id,
        s.matric_no,
        CONCAT(p.first_name, ' ', p.last_name) AS student_name,
        s.study_program,
        e.enrollment_role,
        e.enrolled_on
    FROM Enrollment e
    INNER JOIN Student s ON e.student_id = s.student_id
    INNER JOIN Person p ON s.student_id = p.person_id
    WHERE e.course_id = ?
    ORDER BY e.enrollment_role, p.last_name, p.first_name
    LIMIT 20";
    
    $stmt = $conn->prepare($sql_students);
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $course ? sanitizeOutput($course['code']) : 'Course'; ?> - Detail</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Course Details</h1>
            <p class="subtitle"><?php echo $course ? sanitizeOutput($course['code']) : ''; ?></p>
        </header>

        <main>
            <div class="nav-links">
                <a href="search_form.php">← New Search</a>
                <a href="javascript:history.back()">← Back to Results</a>
                <a href="../index.php">🏠 Home</a>
            </div>

            <?php if ($error): ?>
                <div class="message error">
                    <strong>Error:</strong> <?php echo sanitizeOutput($error); ?>
                </div>
            <?php elseif ($course): ?>
                
                <!-- Course Overview -->
                <div class="detail-card">
                    <div class="detail-header">
                        <h2>📚 <?php echo sanitizeOutput($course['course_name']); ?></h2>
                    </div>
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Course Code</label>
                            <div class="value"><?php echo sanitizeOutput($course['code']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Semester</label>
                            <div class="value"><?php echo sanitizeOutput($course['semester']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Total Students</label>
                            <div class="value"><?php echo sanitizeOutput($course['student_count']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Regular Students</label>
                            <div class="value"><?php echo sanitizeOutput($course['regular_student_count']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Tutors</label>
                            <div class="value"><?php echo sanitizeOutput($course['tutor_count']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Instructors</label>
                            <div class="value"><?php echo sanitizeOutput($course['instructor_count']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Instructors List -->
                <?php if (!empty($instructors)): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h2>👨‍🏫 Instructors (<?php echo count($instructors); ?>)</h2>
                    </div>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Title</th>
                                <th>Office</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($instructors as $instructor): ?>
                                <tr>
                                    <td><?php echo sanitizeOutput($instructor['instructor_name']); ?></td>
                                    <td><?php echo sanitizeOutput($instructor['title'] ?? 'N/A'); ?></td>
                                    <td><?php echo sanitizeOutput($instructor['office'] ?? 'N/A'); ?></td>
                                    <td><?php echo sanitizeOutput($instructor['email']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Students List -->
                <?php if (!empty($students)): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h2>👥 Enrolled Students (Showing <?php echo count($students); ?>)</h2>
                    </div>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Matric No</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Role</th>
                                <th>Enrolled On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo sanitizeOutput($student['matric_no']); ?></td>
                                    <td><?php echo sanitizeOutput($student['student_name']); ?></td>
                                    <td><?php echo sanitizeOutput($student['study_program']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $student['enrollment_role'] === 'tutor' ? '#764ba2' : '#333'; ?>; font-weight: bold;">
                                            <?php echo strtoupper(sanitizeOutput($student['enrollment_role'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo sanitizeOutput($student['enrolled_on']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2025 Academic Management System | Course Details</p>
        </footer>
    </div>
</body>
</html>