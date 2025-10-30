<?php
/**
 * Team Search Results
 * Query 2.1 - Team Composition and Project Involvement Results
 */

require_once '../config/db_config.php';

// Get search parameters
$team_name = isset($_GET['team_name']) ? trim($_GET['team_name']) : '';
$min_members = isset($_GET['min_members']) ? intval($_GET['min_members']) : 0;
$max_members = isset($_GET['max_members']) ? intval($_GET['max_members']) : 0;
$min_projects = isset($_GET['min_projects']) ? intval($_GET['min_projects']) : 0;
$has_lead = isset($_GET['has_lead']) ? trim($_GET['has_lead']) : '';
$formed_after = isset($_GET['formed_after']) ? trim($_GET['formed_after']) : '';
$formed_before = isset($_GET['formed_before']) ? trim($_GET['formed_before']) : '';
$days_active = isset($_GET['days_active']) ? intval($_GET['days_active']) : 0;
$sort_by = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'project_count';
$order = isset($_GET['order']) ? trim($_GET['order']) : 'DESC';

// Build the SQL query
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
WHERE 1=1";

// Add WHERE conditions dynamically
$params = [];
$types = '';

if (!empty($team_name)) {
    $sql .= " AND t.name LIKE ?";
    $params[] = '%' . $team_name . '%';
    $types .= 's';
}

if (!empty($formed_after)) {
    $sql .= " AND t.formed_on >= ?";
    $params[] = $formed_after;
    $types .= 's';
}

if (!empty($formed_before)) {
    $sql .= " AND t.formed_on <= ?";
    $params[] = $formed_before;
    $types .= 's';
}

$sql .= " GROUP BY t.team_id, t.name, t.formed_on";

// Add HAVING conditions
$having_conditions = [];
if ($min_members > 0) {
    $having_conditions[] = "COUNT(DISTINCT tm.student_id) >= $min_members";
}
if ($max_members > 0) {
    $having_conditions[] = "COUNT(DISTINCT tm.student_id) <= $max_members";
}
if ($min_projects > 0) {
    $having_conditions[] = "COUNT(DISTINCT tp.project_id) >= $min_projects";
}
if ($has_lead === 'yes') {
    $having_conditions[] = "SUM(CASE WHEN tm.role_in_team = 'lead' THEN 1 ELSE 0 END) > 0";
} elseif ($has_lead === 'no') {
    $having_conditions[] = "SUM(CASE WHEN tm.role_in_team = 'lead' THEN 1 ELSE 0 END) = 0";
}
if ($days_active > 0) {
    $having_conditions[] = "DATEDIFF(CURDATE(), t.formed_on) >= $days_active";
}

if (!empty($having_conditions)) {
    $sql .= " HAVING " . implode(' AND ', $having_conditions);
}

// Add ORDER BY
$valid_sort_columns = [
    'project_count', 
    'member_count', 
    'formed_on' => 't.formed_on', 
    'team_name' => 't.name'
];
$sort_column = isset($valid_sort_columns[$sort_by]) ? 
    (is_string($valid_sort_columns[$sort_by]) ? $valid_sort_columns[$sort_by] : $sort_by) : 
    'project_count';
$order = ($order === 'ASC') ? 'ASC' : 'DESC';
$sql .= " ORDER BY $sort_column $order, member_count DESC";

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
    <title>Team Search Results - Academic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Team Search Results</h1>
            <p class="subtitle">Found <?php echo count($results); ?> team(s)</p>
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
                    <strong>No teams found.</strong> Try adjusting your search criteria.
                </div>
            <?php else: ?>
                <div class="results-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Team Name</th>
                                <th>Formed On</th>
                                <th>Total Members</th>
                                <th>Leads</th>
                                <th>Regular Members</th>
                                <th>Projects</th>
                                <th>Days Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><strong><?php echo sanitizeOutput($row['team_name']); ?></strong></td>
                                    <td><?php echo sanitizeOutput($row['formed_on']); ?></td>
                                    <td><?php echo sanitizeOutput($row['member_count']); ?></td>
                                    <td><?php echo sanitizeOutput($row['lead_count']); ?></td>
                                    <td><?php echo sanitizeOutput($row['regular_member_count']); ?></td>
                                    <td><?php echo sanitizeOutput($row['project_count']); ?></td>
                                    <td><?php echo sanitizeOutput($row['days_since_formation']); ?></td>
                                    <td>
                                        <a href="team_detail.php?id=<?php echo $row['team_id']; ?>">
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
            <p>&copy; 2025 Academic Management System | Team Search Results</p>
        </footer>
    </div>
</body>
</html>