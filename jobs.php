<?php
/**
 * Dynamic Jobs Page - Nexora IT Solutions
 * This page displays job listings from the database
 */

// Set page variables
$currentPage = 'jobs';
$pageTitle = 'Nexora Careers | Position Descriptions';
$additionalMeta = '<meta name="description" content="Open roles at Nexora IT Solutions with responsibilities and qualifications.">
    <meta name="robots" content="noindex,nofollow">';

require_once 'settings.php';

// Get database connection
function get_db_connection() {
    global $servername, $username, $db_password, $database;
    
    $conn = new mysqli($servername, $username, $db_password);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist
    $conn->query("CREATE DATABASE IF NOT EXISTS $database");
    $conn->select_db($database);
    
    // Create jobs table if it doesn't exist
    $create_jobs_table = "CREATE TABLE IF NOT EXISTS jobs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        reference VARCHAR(10) UNIQUE NOT NULL,
        title VARCHAR(100) NOT NULL,
        location VARCHAR(50) NOT NULL,
        contract_type VARCHAR(20) NOT NULL,
        description TEXT NOT NULL,
        salary_range VARCHAR(50) NOT NULL,
        reports_to VARCHAR(100) NOT NULL,
        key_responsibilities TEXT NOT NULL,
        essential_qualifications TEXT NOT NULL,
        preferable_qualifications TEXT,
        status ENUM('Open', 'Closed', 'Draft') DEFAULT 'Open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($create_jobs_table);
    
    // Insert sample data if table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM jobs");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $jobs_data = [
            [
                'NX7D2', 'Data Scientist', 'Ho Chi Minh City', 'Full-Time',
                'Architect machine learning solutions that sharpen decision-making for national logistics and smart-city clients.',
                '60,000,000 – 80,000,000 VND per month', 'Director of Data & Insight',
                'Design and deploy predictive pipelines for demand forecasting and anomaly detection.|Partner with solution architects to integrate models into cloud-native platforms.|Champion model governance, observability, and ethical AI reviews.',
                '5+ years in applied data science using Python or R.|Advanced SQL with experience in Snowflake or BigQuery.|Hands-on MLOps tooling (MLflow, Kubeflow, or Vertex AI).',
                'Experience in Vietnamese logistics, fintech, or public sector analytics.|Knowledge of Vietnamese data privacy regulations.'
            ],
            [
                'NX5C8', 'Cloud Infrastructure Architect', 'Hanoi', 'Hybrid',
                'Lead enterprise cloud migrations and design high-availability systems that scale across Vietnam\'s dynamic market.',
                '55,000,000 – 70,000,000 VND per month', 'Head of Cloud Engineering',
                'Develop cloud transformation roadmaps spanning assessment to steady-state operations.|Architect landing zones with security, compliance, and cost-optimisation baked in.|Coach engineering squads on infrastructure-as-code best practices.',
                '7+ years in infrastructure or DevOps engineering.|Certification in AWS, Azure, or Google Cloud (Architect level).|Proficiency with Terraform and CI/CD orchestration.',
                'Exposure to regional financial services or manufacturing environments.|Experience leading bilingual (English & Vietnamese) delivery teams.'
            ],
            [
                'NX4S3', 'Cybersecurity Analyst', 'Da Nang', 'Full-Time',
                'Monitor and defend mission-critical environments with intelligence-led detection and response.',
                '32,000,000 – 42,000,000 VND per month', 'SOC Manager',
                'Triaging security alerts, conducting threat hunting, and leading incident response.|Maintaining SIEM use cases and SOAR playbooks tuned to client environments.|Collaborating with compliance teams to meet ISO 27001 and local regulations.',
                '3+ years in SOC or cyber defence roles.|Hands-on with tools such as Splunk, Sentinel, or QRadar.|Familiarity with MITRE ATT&CK and incident response frameworks.',
                'Certifications: Security+, CCNA Security, or GIAC.|Vietnamese government or critical infrastructure experience.'
            ],
            [
                'NX6P1', 'Digital Delivery Lead', 'Ho Chi Minh City', 'Full-Time',
                'Guide cross-functional squads delivering cloud, data, and security programs with precision and empathy.',
                '45,000,000 – 58,000,000 VND per month', 'Director of Delivery & Transformation',
                'Facilitate discovery workshops and define delivery roadmaps with stakeholders.|Ensure agile rituals drive transparency, velocity, and measurable outcomes.|Mentor project managers and scrum masters across engagement portfolios.',
                '6+ years leading technology projects, ideally in consulting environments.|Certification in PMP, PRINCE2, or Scrum Mastery.|Strong communication in English and Vietnamese.',
                'Background in cloud migrations or cybersecurity engagements.|Experience working with government-funded innovation programs.'
            ]
        ];
        
        $stmt = $conn->prepare("INSERT INTO jobs (reference, title, location, contract_type, description, salary_range, reports_to, key_responsibilities, essential_qualifications, preferable_qualifications) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($jobs_data as $job) {
            $stmt->bind_param("ssssssssss", ...$job);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    return $conn;
}

// Get jobs from database
try {
    $conn = get_db_connection();
    $result = $conn->query("SELECT * FROM jobs WHERE status = 'Open' ORDER BY created_at DESC");
    $jobs = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
} catch (Exception $e) {
    $jobs = [];
    $error_message = "Unable to load jobs at this time. Please try again later.";
}

include 'header.inc';
?>

    <!-- --------------------------------------------------------------------------------
         Intro
         -------------------------------------------------------------------------------- -->
    <header role="banner">
        <div class="wrapper">
            <h1>Open Roles</h1>
            <p>Explore current opportunities at Nexora. Required fields on the application page help us evaluate your strengths promptly. Join an innovation-driven team delivering secure, scalable solutions for Vietnam's most ambitious enterprises.</p>
        </div>
    </header>

    <main>
        <!-- --------------------------------------------------------------------------------
             Section: Position descriptions + hiring process aside
             -------------------------------------------------------------------------------- -->
        <section class="section" aria-labelledby="roles-heading">
            <div class="wrapper">
                <h2 id="roles-heading">Current Opportunities</h2>

                <aside class="jobs-aside" aria-label="Application process summary">
                    <h3>How We Hire</h3>
                    <ol>
                        <li>Submit your application via the Nexora portal.</li>
                        <li>Meet the hiring manager for a capability conversation.</li>
                        <li>Complete a collaborative challenge with the delivery squad.</li>
                    </ol>
                    <ul>
                        <li>We respond to every candidate.</li>
                        <li>Interviews accommodate accessibility needs.</li>
                    </ul>
                    <p><strong>Tip:</strong> Use reference numbers from each role when you apply.</p>
                </aside>

                <?php if (isset($error_message)): ?>
                    <div style="background-color: #ffe6e6; border: 1px solid #ff9999; padding: 20px; margin: 20px 0; border-radius: 5px;">
                        <p><?= $error_message ?></p>
                    </div>
                <?php endif; ?>

                <!-- Role descriptions -->
                <div class="jobs-grid">
                    <?php if (empty($jobs)): ?>
                        <p>No open positions available at this time. Please check back later.</p>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <article class="job-card" aria-labelledby="role-<?= strtolower($job['reference']) ?>">
                                <h3 id="role-<?= strtolower($job['reference']) ?>"><?= htmlspecialchars($job['title']) ?></h3>
                                <div class="job-meta">
                                    <span>Ref: <?= htmlspecialchars($job['reference']) ?></span>
                                    <span>Location: <?= htmlspecialchars($job['location']) ?></span>
                                    <span><?= htmlspecialchars($job['contract_type']) ?></span>
                                </div>
                                <p class="job-description"><?= htmlspecialchars($job['description']) ?></p>
                                <p><strong>Salary Range:</strong> <?= htmlspecialchars($job['salary_range']) ?></p>
                                <p><strong>Reports To:</strong> <?= htmlspecialchars($job['reports_to']) ?></p>
                                
                                <section>
                                    <h4>Key Responsibilities</h4>
                                    <ul>
                                        <?php 
                                        $responsibilities = explode('|', $job['key_responsibilities']);
                                        foreach ($responsibilities as $responsibility): 
                                        ?>
                                            <li><?= htmlspecialchars(trim($responsibility)) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </section>
                                
                                <section>
                                    <h4>Essential Qualifications</h4>
                                    <ul>
                                        <?php 
                                        $qualifications = explode('|', $job['essential_qualifications']);
                                        foreach ($qualifications as $qualification): 
                                        ?>
                                            <li><?= htmlspecialchars(trim($qualification)) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </section>
                                
                                <?php if (!empty($job['preferable_qualifications'])): ?>
                                    <section>
                                        <h4>Preferable</h4>
                                        <ul>
                                            <?php 
                                            $preferable = explode('|', $job['preferable_qualifications']);
                                            foreach ($preferable as $pref): 
                                            ?>
                                                <li><?= htmlspecialchars(trim($pref)) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </section>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

<?php include 'footer.inc'; ?>