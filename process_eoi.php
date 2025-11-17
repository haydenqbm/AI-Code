<?php
/**
 * Process EOI Form - Nexora IT Solutions
 * This script processes the Expression of Interest form submission
 * Validates data server-side and inserts into MySQL database
 */

// Prevent direct access to this page
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: apply.php");
    exit;
}

// Include database connection settings
require_once 'settings.php';

// Set page variables for header
$currentPage = 'apply';
$pageTitle = 'Application Submitted | Nexora IT Solutions';

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate postcode based on state
function validate_postcode($postcode, $state) {
    $valid_ranges = [
        'VIC' => ['3000-3999', '8000-8999'],
        'NSW' => ['1000-2999'],
        'QLD' => ['4000-4999', '9000-9999'], 
        'NT' => ['0800-0999'],
        'WA' => ['6000-6999'],
        'SA' => ['5000-5999'],
        'TAS' => ['7000-7999'],
        'ACT' => ['0200-0299', '2600-2699']
    ];
    
    if (!isset($valid_ranges[$state])) return false;
    
    $postcode_int = intval($postcode);
    foreach ($valid_ranges[$state] as $range) {
        $bounds = explode('-', $range);
        if ($postcode_int >= intval($bounds[0]) && $postcode_int <= intval($bounds[1])) {
            return true;
        }
    }
    return false;
}

// Initialize error array
$errors = [];

// Collect and sanitize form data
$reference = sanitize_input($_POST['reference'] ?? '');
$first_name = sanitize_input($_POST['first-name'] ?? '');
$last_name = sanitize_input($_POST['last-name'] ?? '');
$dob = sanitize_input($_POST['dob'] ?? '');
$gender = sanitize_input($_POST['gender'] ?? '');
$street = sanitize_input($_POST['street'] ?? '');
$suburb = sanitize_input($_POST['suburb'] ?? '');
$state = sanitize_input($_POST['state'] ?? '');
$postcode = sanitize_input($_POST['postcode'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$phone = sanitize_input($_POST['phone'] ?? '');
$skills = $_POST['skills'] ?? [];
$other_skills = sanitize_input($_POST['other-skills'] ?? '');

// Server-side validation
if (empty($reference)) {
    $errors[] = "Job reference number is required.";
}

if (empty($first_name) || !preg_match("/^[A-Za-z]{1,20}$/", $first_name)) {
    $errors[] = "First name must contain only letters and be maximum 20 characters.";
}

if (empty($last_name) || !preg_match("/^[A-Za-z]{1,20}$/", $last_name)) {
    $errors[] = "Last name must contain only letters and be maximum 20 characters.";
}

if (empty($dob)) {
    $errors[] = "Date of birth is required.";
} else {
    $date_check = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$date_check || $date_check->format('Y-m-d') !== $dob) {
        $errors[] = "Invalid date of birth format.";
    }
}

if (empty($gender)) {
    $errors[] = "Gender selection is required.";
}

if (empty($street) || strlen($street) > 40) {
    $errors[] = "Street address is required and must be maximum 40 characters.";
}

if (empty($suburb) || strlen($suburb) > 40) {
    $errors[] = "Suburb/town is required and must be maximum 40 characters.";
}

$valid_states = ['VIC', 'NSW', 'QLD', 'NT', 'WA', 'SA', 'TAS', 'ACT'];
if (empty($state) || !in_array($state, $valid_states)) {
    $errors[] = "Valid state selection is required.";
}

if (empty($postcode) || !preg_match("/^\d{4}$/", $postcode)) {
    $errors[] = "Postcode must be exactly 4 digits.";
} elseif (!validate_postcode($postcode, $state)) {
    $errors[] = "Postcode does not match the selected state.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email address is required.";
}

if (empty($phone) || !preg_match("/^[0-9 ]{8,12}$/", $phone)) {
    $errors[] = "Phone number must be 8-12 digits, spaces permitted.";
}

if (empty($skills)) {
    $errors[] = "At least one technical skill must be selected.";
}

// Enhancement: Other skills not empty if "Other" checkbox selected
if (in_array('other', $skills) && empty($other_skills)) {
    $errors[] = "Please describe your other skills when 'Other Skills' is selected.";
}

// If there are validation errors, display error page
if (!empty($errors)) {
    include 'header.inc';
    ?>
    <main>
        <section class="section">
            <div class="wrapper">
                <h1>Application Error</h1>
                <div style="background-color: #ffe6e6; border: 1px solid #ff9999; padding: 20px; margin: 20px 0; border-radius: 5px;">
                    <h2>Please correct the following errors:</h2>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><a href="apply.php">← Return to Application Form</a></p>
                </div>
            </div>
        </section>
    </main>
    <?php
    include 'footer.inc';
    exit;
}

// If validation passes, proceed with database operations
try {
    // Create database connection
    $conn = new mysqli($servername, $username, $db_password);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $database";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db($database);

    // Create EOI table if it doesn't exist
    $create_table_sql = "CREATE TABLE IF NOT EXISTS eoi (
        EOInumber INT PRIMARY KEY AUTO_INCREMENT,
        JobReference VARCHAR(10) NOT NULL,
        FirstName VARCHAR(20) NOT NULL,
        LastName VARCHAR(20) NOT NULL,
        DateOfBirth DATE NOT NULL,
        Gender ENUM('male', 'female', 'nonbinary', 'unspecified') NOT NULL,
        StreetAddress VARCHAR(40) NOT NULL,
        Suburb VARCHAR(40) NOT NULL,
        State ENUM('VIC', 'NSW', 'QLD', 'NT', 'WA', 'SA', 'TAS', 'ACT') NOT NULL,
        Postcode CHAR(4) NOT NULL,
        EmailAddress VARCHAR(255) NOT NULL,
        PhoneNumber VARCHAR(12) NOT NULL,
        Skill1 BOOLEAN DEFAULT FALSE,
        Skill2 BOOLEAN DEFAULT FALSE,
        Skill3 BOOLEAN DEFAULT FALSE,
        Skill4 BOOLEAN DEFAULT FALSE,
        Skill5 BOOLEAN DEFAULT FALSE,
        OtherSkills TEXT,
        Status ENUM('New', 'Current', 'Final') DEFAULT 'New',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (!$conn->query($create_table_sql)) {
        throw new Exception("Error creating table: " . $conn->error);
    }

    // Process skills
    $skill1 = in_array('cloud', $skills) ? 1 : 0;
    $skill2 = in_array('security', $skills) ? 1 : 0;
    $skill3 = in_array('data', $skills) ? 1 : 0;
    $skill4 = in_array('agile', $skills) ? 1 : 0;
    $skill5 = in_array('other', $skills) ? 1 : 0;

    // Prepare and execute insert statement
    $stmt = $conn->prepare("INSERT INTO eoi (JobReference, FirstName, LastName, DateOfBirth, Gender, StreetAddress, Suburb, State, Postcode, EmailAddress, PhoneNumber, Skill1, Skill2, Skill3, Skill4, Skill5, OtherSkills) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssssssiiiiis", 
        $reference, $first_name, $last_name, $dob, $gender, 
        $street, $suburb, $state, $postcode, $email, $phone,
        $skill1, $skill2, $skill3, $skill4, $skill5, $other_skills
    );

    if (!$stmt->execute()) {
        throw new Exception("Error inserting record: " . $stmt->error);
    }

    // Get the generated EOI number
    $eoi_number = $conn->insert_id;

    $stmt->close();
    $conn->close();

    // Display success page
    include 'header.inc';
    ?>
    <main>
        <section class="section">
            <div class="wrapper">
                <h1>Application Submitted Successfully</h1>
                <div style="background-color: #e6ffe6; border: 1px solid #99cc99; padding: 20px; margin: 20px 0; border-radius: 5px;">
                    <h2>Thank you for your application!</h2>
                    <p><strong>Your EOI Number is: <?= $eoi_number ?></strong></p>
                    <p>Please keep this number for your records. We will review your application and contact you soon.</p>
                    <p><a href="index.php">← Return to Home</a> | <a href="jobs.php">View Open Positions</a></p>
                </div>
            </div>
        </section>
    </main>
    <?php
    include 'footer.inc';

} catch (Exception $e) {
    // Database error handling
    include 'header.inc';
    ?>
    <main>
        <section class="section">
            <div class="wrapper">
                <h1>System Error</h1>
                <div style="background-color: #ffe6e6; border: 1px solid #ff9999; padding: 20px; margin: 20px 0; border-radius: 5px;">
                    <h2>We're sorry, but there was a system error.</h2>
                    <p>Please try again later. If the problem persists, please contact our support team.</p>
                    <p><a href="apply.php">← Return to Application Form</a></p>
                </div>
            </div>
        </section>
    </main>
    <?php
    include 'footer.inc';
}
?>