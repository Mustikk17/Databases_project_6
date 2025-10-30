<?php
/**
 * Team Search Form
 * Query 2.1 - Team Composition and Project Involvement
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Search - Academic Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Team Composition & Projects</h1>
            <p class="subtitle">Search and Analyze Teams</p>
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
                            <label for="team_name">Team Name</label>
                            <input type="text" id="team_name" name="team_name" 
                                   placeholder="e.g., Alpha Team, Team Rocket">
                        </div>

                        <div class="form-group">
                            <label for="min_members">Minimum Members</label>
                            <input type="number" id="min_members" name="min_members" 
                                   min="1" placeholder="e.g., 3">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="max_members">Maximum Members</label>
                            <input type="number" id="max_members" name="max_members" 
                                   min="1" placeholder="e.g., 10">
                        </div>

                        <div class="form-group">
                            <label for="min_projects">Minimum Projects</label>
                            <input type="number" id="min_projects" name="min_projects" 
                                   min="0" placeholder="e.g., 1">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="has_lead">Has Team Lead</label>
                            <select id="has_lead" name="has_lead">
                                <option value="">-- Any --</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="formed_after">Formed After Date</label>
                            <input type="date" id="formed_after" name="formed_after">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="formed_before">Formed Before Date</label>
                            <input type="date" id="formed_before" name="formed_before">
                        </div>

                        <div class="form-group">
                            <label for="days_active">Minimum Days Active</label>
                            <input type="number" id="days_active" name="days_active" 
                                   min="0" placeholder="e.g., 30">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="sort_by">Sort By</label>
                            <select id="sort_by" name="sort_by">
                                <option value="project_count">Project Count (Default)</option>
                                <option value="member_count">Member Count</option>
                                <option value="formed_on">Formation Date</option>
                                <option value="team_name">Team Name</option>
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
                        <button type="submit" class="btn btn-primary">🔍 Search Teams</button>
                        <button type="reset" class="btn btn-secondary">Reset Form</button>
                    </div>
                </form>
            </div>

            <div class="message info">
                <strong>💡 Search Tips:</strong>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>Leave fields empty to view all teams</li>
                    <li>Use member count range to find teams of specific sizes</li>
                    <li>Filter by formation date to find new or established teams</li>
                    <li>Combine criteria for more specific results</li>
                </ul>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Academic Management System | Team Search</p>
        </footer>
    </div>
</body>
</html>