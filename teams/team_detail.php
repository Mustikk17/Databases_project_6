<?php
/**
 * Team Detail Page
 * Displays complete information about a single team
 */

require_once '../config/db_config.php';

// Get team ID from URL
$team_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($team_id <= 0) {
    die("Invalid team ID");
}

// Fetch team details
$conn = getDBConnection();
$team = null;
$members = [];
$projects = [];
$error = null;

try {
    // Main team query
    $sql = "SELECT 
        t.team_id,
        t.name AS team_name,
        t.formed_on,
        COUNT(DISTINCT tm.student_id) AS member_count,
        SUM(CASE WHEN tm.role_in_team = 'lead' THEN 1 ELSE 0 END) AS lead_count,
        SUM(CASE WHEN tm.role_in_team = 'member' THEN 1 ELSE 0 END) AS regular_member_count,
        COUNT(DISTINCT tp.project_id) AS project_count,
        DATEDIFF(CURDATE(), t.formed_on) AS days_since_formation
    FROM Team t
    INNER JOIN TeamMember tm ON t.team_id = tm.team_id
    LEFT JOIN TeamProject tp ON t.team_id = tp.team_id
    WHERE t.team_id = ?
    GROUP BY t.team_id, t.name, t.formed_on";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $team_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $team = $result->fetch_assoc();
    $stmt->close();
    
    if (!$team) {
        throw new Exception("Team not found");
    }
    
    // Fetch team members
    $sql_members = "SELECT 
        s.student_id,
        s.matric_no,
        CONCAT(p.first_name, ' ', p.last_name) AS student_name,
        s.study_program,
        p.email,
        tm.role_in_team,
        GROUP_CONCAT(DISTINCT pp.phone_no SEPARATOR ', ') AS phone_numbers
    FROM TeamMember tm
    INNER JOIN Student s ON tm.student_id = s.student_id
    INNER JOIN Person p ON s.student_id = p.person_id
    LEFT JOIN PersonPhone pp ON p.person_id = pp.person_id
    WHERE tm.team_id = ?
    GROUP BY s.student_id, s.matric_no, p.first_name, p.last_name, s.study_program, p.email, tm.role_in_team
    ORDER BY tm.role_in_team DESC, p.last_name, p.first_name";
    
    $stmt = $conn->prepare($sql_members);
    $stmt->bind_param('i', $team_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Fetch team projects
    $sql_projects = "SELECT 
        proj.project_id,
        proj.title,
        proj.start_date,
        proj.end_date,
        DATEDIFF(COALESCE(proj.end_date, CURDATE()), proj.start_date) AS project_duration_days,
        CASE 
            WHEN rp.project_id IS NOT NULL THEN 'Research'
            WHEN cp.project_id IS NOT NULL THEN 'Course'
            ELSE 'Other'
        END AS project_type,
        rp.sponsor,
        rp.budget_eur,
        c.code AS course_code,
        c.name AS course_name
    FROM TeamProject tp
    INNER JOIN Project proj ON tp.project_id = proj.project_id
    LEFT JOIN ResearchProject rp ON proj.project_id = rp.project_id
    LEFT JOIN CourseProject cp ON proj.project_id = cp.project_id
    LEFT JOIN Course c ON cp.course_id = c.course_id
    WHERE tp.team_id = ?
    ORDER BY proj.start_date DESC";
    
    $stmt = $conn->prepare($sql_projects);
    $stmt->bind_param('i', $team_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $projects = $result->fetch_all(MYSQLI_ASSOC);
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
    <title><?php echo $team ? sanitizeOutput($team['team_name']) : 'Team'; ?> - Detail</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Team Details</h1>
            <p class="subtitle"><?php echo $team ? sanitizeOutput($team['team_name']) : ''; ?></p>
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
            <?php elseif ($team): ?>
                
                <!-- Team Overview -->
                <div class="detail-card">
                    <div class="detail-header">
                        <h2>👥 <?php echo sanitizeOutput($team['team_name']); ?></h2>
                    </div>
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Team ID</label>
                            <div class="value"><?php echo sanitizeOutput($team['team_id']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Formed On</label>
                            <div class="value"><?php echo sanitizeOutput($team['formed_on']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Days Active</label>
                            <div class="value"><?php echo sanitizeOutput($team['days_since_formation']); ?> days</div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Total Members</label>
                            <div class="value"><?php echo sanitizeOutput($team['member_count']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Team Leads</label>
                            <div class="value"><?php echo sanitizeOutput($team['lead_count']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Regular Members</label>
                            <div class="value"><?php echo sanitizeOutput($team['regular_member_count']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Active Projects</label>
                            <div class="value"><?php echo sanitizeOutput($team['project_count']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Team Members List -->
                <?php if (!empty($members)): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h2>👤 Team Members (<?php echo count($members); ?>)</h2>
                    </div>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Matric No</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Study Program</th>
                                <th>Email</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo sanitizeOutput($member['matric_no']); ?></td>
                                    <td><?php echo sanitizeOutput($member['student_name']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $member['role_in_team'] === 'lead' ? '#764ba2' : '#333'; ?>; font-weight: bold;">
                                            <?php echo strtoupper(sanitizeOutput($member['role_in_team'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo sanitizeOutput($member['study_program']); ?></td>
                                    <td><?php echo sanitizeOutput($member['email']); ?></td>
                                    <td><?php echo sanitizeOutput($member['phone_numbers'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Projects List -->
                <?php if (!empty($projects)): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h2>📊 Team Projects (<?php echo count($projects); ?>)</h2>
                    </div>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Duration (Days)</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><strong><?php echo sanitizeOutput($project['title']); ?></strong></td>
                                    <td>
                                        <span style="color: <?php echo $project['project_type'] === 'Research' ? '#667eea' : '#764ba2'; ?>;">
                                            <?php echo sanitizeOutput($project['project_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo sanitizeOutput($project['start_date']); ?></td>
                                    <td><?php echo sanitizeOutput($project['end_date'] ?? 'Ongoing'); ?></td>
                                    <td><?php echo sanitizeOutput($project['project_duration_days']); ?></td>
                                    <td>
                                        <?php if ($project['project_type'] === 'Research'): ?>
                                            Sponsor: <?php echo sanitizeOutput($project['sponsor'] ?? 'N/A'); ?><br>
                                            Budget: €<?php echo number_format($project['budget_eur'] ?? 0, 2); ?>
                                        <?php elseif ($project['project_type'] === 'Course'): ?>
                                            Course: <?php echo sanitizeOutput($project['course_code'] ?? 'N/A'); ?><br>
                                            <?php echo sanitizeOutput($project['course_name'] ?? ''); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2025 Academic Management System | Team Details</p>
        </footer>
    </div>
</body>
</html>