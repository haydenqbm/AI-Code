<?php
/**
 * HR Management System - Nexora IT Solutions
 * This page allows HR managers to view, filter, sort, update and delete EOI records
 * Includes authentication system with login attempts tracking
 */

session_start();
require_once 'settings.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is locked out
function check_lockout() {
    if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
        return true;
    }
    return false;
}

// Create database connection
function get_db_connection() {
    global $host, $db_user, $db_pass, $db_name;

    $conn = new mysqli($host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

    
    // Create database if it doesn't exist
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->select_db($dbname);
    
    // Create managers table if it doesn't exist
    $create_managers_table = "CREATE TABLE IF NOT EXISTS managers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($create_managers_table);
    
    // Create default admin user if no managers exist
    $result = $conn->query("SELECT COUNT(*) as count FROM managers");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $default_password = password_hash("admin123", PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO managers (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $default_username, $default_password);
        $default_username = "admin";
        $stmt->execute();
        $stmt->close();
    }
    
    return $conn;


// Handle login
if (isset($_POST['login'])) {
    if (check_lockout()) {
        $login_error = "Account temporarily locked. Please try again later.";
    } else {
        $username = sanitize_input($_POST['username']);
        $password = $_POST['password'];
        
        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT id, password FROM managers WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['manager_id'] = $user['id'];
                $_SESSION['username'] = $username;
                $_SESSION['login_attempts'] = 0;
                unset($_SESSION['lockout_time']);
                header("Location: manage.php");
                exit;
            }
        }
        
        // Failed login
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['lockout_time'] = time() + 300; // 5 minutes lockout
            $login_error = "Too many failed attempts. Account locked for 5 minutes.";
        } else {
            $login_error = "Invalid username or password.";
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: manage.php");
    exit;
}

// Handle manager registration
if (isset($_POST['register'])) {
    $username = sanitize_input($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $registration_errors = [];
    
    // Validation
    if (empty($username) || strlen($username) < 3) {
        $registration_errors[] = "Username must be at least 3 characters long.";
    }
    
    if (strlen($password) < 6) {
        $registration_errors[] = "Password must be at least 6 characters long.";
    }
    
    if ($password !== $confirm_password) {
        $registration_errors[] = "Passwords do not match.";
    }
    
    if (empty($registration_errors)) {
        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT id FROM managers WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $registration_errors[] = "Username already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO managers (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);
            
            if ($stmt->execute()) {
                $registration_success = "Manager account created successfully!";
            } else {
                $registration_errors[] = "Error creating account.";
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['manager_id']);

// Handle EOI operations (only if logged in)
if ($is_logged_in) {
    $conn = get_db_connection();
    
    // Handle delete operation
    if (isset($_POST['delete_job_ref'])) {
        $job_ref = sanitize_input($_POST['delete_job_ref']);
        $stmt = $conn->prepare("DELETE FROM eoi WHERE JobReference = ?");
        $stmt->bind_param("s", $job_ref);
        $stmt->execute();
        $delete_message = "Deleted " . $stmt->affected_rows . " EOI(s) for job reference: " . $job_ref;
        $stmt->close();
    }
    
    // Handle status update
    if (isset($_POST['update_status'])) {
        $eoi_number = intval($_POST['eoi_number']);
        $new_status = sanitize_input($_POST['new_status']);
        
        $stmt = $conn->prepare("UPDATE eoi SET Status = ? WHERE EOInumber = ?");
        $stmt->bind_param("si", $new_status, $eoi_number);
        $stmt->execute();
        $update_message = "Status updated for EOI #" . $eoi_number;
        $stmt->close();
    }
    
    // Build query based on filters
    $where_conditions = [];
    $params = [];
    $param_types = "";
    
    if (isset($_GET['job_ref']) && !empty($_GET['job_ref'])) {
        $where_conditions[] = "JobReference = ?";
        $params[] = sanitize_input($_GET['job_ref']);
        $param_types .= "s";
    }
    
    if (isset($_GET['first_name']) && !empty($_GET['first_name'])) {
        $where_conditions[] = "FirstName LIKE ?";
        $params[] = "%" . sanitize_input($_GET['first_name']) . "%";
        $param_types .= "s";
    }
    
    if (isset($_GET['last_name']) && !empty($_GET['last_name'])) {
        $where_conditions[] = "LastName LIKE ?";
        $params[] = "%" . sanitize_input($_GET['last_name']) . "%";
        $param_types .= "s";
    }
    
    // Build ORDER BY clause
    $valid_sort_fields = ['EOInumber', 'JobReference', 'FirstName', 'LastName', 'Status', 'CreatedAt'];
    $sort_field = (isset($_GET['sort']) && in_array($_GET['sort'], $valid_sort_fields)) ? $_GET['sort'] : 'EOInumber';
    $sort_order = (isset($_GET['order']) && $_GET['order'] === 'desc') ? 'DESC' : 'ASC';
    
    // Construct final query
    $sql = "SELECT * FROM eoi";
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    $sql .= " ORDER BY $sort_field $sort_order";
    
    // Execute query
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $eoi_records = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
}

// Set page variables
$currentPage = 'manage';
$pageTitle = 'HR Management System | Nexora IT Solutions';
?>

<?php include 'header.inc'; ?>

<main>
    <section class="section">
        <div class="wrapper">
            <h1>HR Management System</h1>
            
            <?php if (!$is_logged_in): ?>
                <!-- Login Form -->
                <div style="max-width: 500px; margin: 0 auto;">
                    <h2>Manager Login</h2>
                    
                    <?php if (isset($login_error)): ?>
                        <div style="background-color: #ffe6e6; border: 1px solid #ff9999; padding: 15px; margin: 15px 0; border-radius: 5px;">
                            <?= $login_error ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (check_lockout()): ?>
                        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px 0; border-radius: 5px;">
                            Account locked. Try again in <?= ceil(($_SESSION['lockout_time'] - time()) / 60) ?> minutes.
                        </div>
                    <?php else: ?>
                        <form method="post" style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
                            <div style="margin-bottom: 15px;">
                                <label for="username">Username:</label><br>
                                <input type="text" id="username" name="username" required style="width: 100%; padding: 8px; margin-top: 5px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label for="password">Password:</label><br>
                                <input type="password" id="password" name="password" required style="width: 100%; padding: 8px; margin-top: 5px;">
                            </div>
                            <button type="submit" name="login" style="background: #00C2FF; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;">Login</button>
                        </form>
                        
                        <p style="text-align: center; margin: 20px 0;"><em>Default login: admin / admin123</em></p>
                    <?php endif; ?>
                    
                    <!-- Manager Registration -->
                    <details style="margin-top: 30px;">
                        <summary style="cursor: pointer; font-weight: bold;">Register New Manager</summary>
                        
                        <?php if (!empty($registration_errors)): ?>
                            <div style="background-color: #ffe6e6; border: 1px solid #ff9999; padding: 15px; margin: 15px 0; border-radius: 5px;">
                                <ul>
                                    <?php foreach ($registration_errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($registration_success)): ?>
                            <div style="background-color: #e6ffe6; border: 1px solid #99cc99; padding: 15px; margin: 15px 0; border-radius: 5px;">
                                <?= $registration_success ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-top: 10px;">
                            <div style="margin-bottom: 15px;">
                                <label for="reg_username">Username:</label><br>
                                <input type="text" id="reg_username" name="reg_username" required style="width: 100%; padding: 8px; margin-top: 5px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label for="reg_password">Password:</label><br>
                                <input type="password" id="reg_password" name="reg_password" required style="width: 100%; padding: 8px; margin-top: 5px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label for="confirm_password">Confirm Password:</label><br>
                                <input type="password" id="confirm_password" name="confirm_password" required style="width: 100%; padding: 8px; margin-top: 5px;">
                            </div>
                            <button type="submit" name="register" style="background: #009688; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;">Register</button>
                        </form>
                    </details>
                </div>
            
            <?php else: ?>
                <!-- Management Interface -->
                <div style="text-align: right; margin-bottom: 20px;">
                    Welcome, <?= $_SESSION['username'] ?> | 
                    <a href="manage.php?logout=1" style="color: #ff4444;">Logout</a>
                </div>
                
                <?php if (isset($delete_message)): ?>
                    <div style="background-color: #e6ffe6; border: 1px solid #99cc99; padding: 15px; margin: 15px 0; border-radius: 5px;">
                        <?= $delete_message ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($update_message)): ?>
                    <div style="background-color: #e6ffe6; border: 1px solid #99cc99; padding: 15px; margin: 15px 0; border-radius: 5px;">
                        <?= $update_message ?>
                    </div>
                <?php endif; ?>
                
                <!-- Filter Form -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <h3>Filter EOIs</h3>
                    <form method="get" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                        <div>
                            <label for="job_ref">Job Reference:</label><br>
                            <input type="text" id="job_ref" name="job_ref" value="<?= $_GET['job_ref'] ?? '' ?>" placeholder="e.g., NX7D2">
                        </div>
                        <div>
                            <label for="first_name">First Name:</label><br>
                            <input type="text" id="first_name" name="first_name" value="<?= $_GET['first_name'] ?? '' ?>">
                        </div>
                        <div>
                            <label for="last_name">Last Name:</label><br>
                            <input type="text" id="last_name" name="last_name" value="<?= $_GET['last_name'] ?? '' ?>">
                        </div>
                        <div>
                            <label for="sort">Sort by:</label><br>
                            <select name="sort">
                                <option value="EOInumber" <?= ($_GET['sort'] ?? '') === 'EOInumber' ? 'selected' : '' ?>>EOI Number</option>
                                <option value="JobReference" <?= ($_GET['sort'] ?? '') === 'JobReference' ? 'selected' : '' ?>>Job Reference</option>
                                <option value="FirstName" <?= ($_GET['sort'] ?? '') === 'FirstName' ? 'selected' : '' ?>>First Name</option>
                                <option value="LastName" <?= ($_GET['sort'] ?? '') === 'LastName' ? 'selected' : '' ?>>Last Name</option>
                                <option value="Status" <?= ($_GET['sort'] ?? '') === 'Status' ? 'selected' : '' ?>>Status</option>
                                <option value="CreatedAt" <?= ($_GET['sort'] ?? '') === 'CreatedAt' ? 'selected' : '' ?>>Created Date</option>
                            </select>
                        </div>
                        <div>
                            <select name="order">
                                <option value="asc" <?= ($_GET['order'] ?? '') === 'asc' ? 'selected' : '' ?>>Ascending</option>
                                <option value="desc" <?= ($_GET['order'] ?? '') === 'desc' ? 'selected' : '' ?>>Descending</option>
                            </select>
                        </div>
                        <button type="submit" style="background: #00C2FF; color: white; padding: 8px 15px; border: none; border-radius: 3px; cursor: pointer;">Filter</button>
                        <a href="manage.php" style="background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">Clear</a>
                    </form>
                </div>
                
                <!-- Delete by Job Reference -->
                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <h3>Delete All EOIs by Job Reference</h3>
                    <form method="post" style="display: inline-flex; gap: 10px; align-items: center;" onsubmit="return confirm('Are you sure you want to delete all EOIs for this job reference?');">
                        <label for="delete_job_ref">Job Reference:</label>
                        <input type="text" id="delete_job_ref" name="delete_job_ref" required placeholder="e.g., NX7D2">
                        <button type="submit" style="background: #dc3545; color: white; padding: 8px 15px; border: none; border-radius: 3px; cursor: pointer;">Delete All</button>
                    </form>
                </div>
                
                <!-- EOI Records Table -->
                <h3>EOI Records (<?= count($eoi_records) ?> found)</h3>
                
                <?php if (empty($eoi_records)): ?>
                    <p>No EOI records found matching your criteria.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                            <thead>
                                <tr style="background-color: #f8f9fa;">
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">EOI#</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Job Ref</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Name</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Email</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Phone</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">State</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Skills</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Status</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eoi_records as $record): ?>
                                    <tr>
                                        <td style="border: 1px solid #dee2e6; padding: 8px;"><?= $record['EOInumber'] ?></td>
                                        <td style="border: 1px solid #dee2e6; padding: 8px;"><?= htmlspecialchars($record['JobReference']) ?></td>
                                        <td style="border: 1px solid #dee2e6; padding: 8px;"><?= htmlspecialchars($record['FirstName'] . ' ' . $record['LastName']) ?></td>
                                        <td style="border: 1px solid #dee2e6; padding: 8px;"><?= htmlspecialchars($record['EmailAddress']) ?></td>
                                        <td style="border: 1px solid #dee2e6; padding: 8px;"><?= htmlspecialchars($record['PhoneNumber']) ?></td>
                                        <td style="border: 1px solid #dee2e6; padding: 8px;"><?= $record['State'] ?></td>
                                        <td style="border: 1px solid #dee2e6; padding: 8px;">
                                            <?php
                                            $skills = [];
                                            if ($record['Skill1']) $skills[] = 'Cloud';
                                            if ($record['Skill2']) $skills[] = 'Security';
                                            if ($record['Skill3']) $skills[] = 'Data';
                                            if ($record['Skill4']) $skills[] = 'Agile';
                                            if ($record['Skill5']) $skills[] = 'Other';
                                            echo implode(', ', $skills);
                                            ?>
                                        </td>
                                        <td style="border: 1px solid #dee2e6; padding: 8px;"><?= $record['Status'] ?></td>
                                        <td style="border: 1px solid #dee2e6; padding: 8px;">
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="eoi_number" value="<?= $record['EOInumber'] ?>">
                                                <select name="new_status" style="font-size: 12px;">
                                                    <option value="New" <?= $record['Status'] === 'New' ? 'selected' : '' ?>>New</option>
                                                    <option value="Current" <?= $record['Status'] === 'Current' ? 'selected' : '' ?>>Current</option>
                                                    <option value="Final" <?= $record['Status'] === 'Final' ? 'selected' : '' ?>>Final</option>
                                                </select>
                                                <button type="submit" name="update_status" style="background: #28a745; color: white; padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;">Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'footer.inc'; ?>