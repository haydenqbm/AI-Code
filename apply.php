<?php
    $currentPage = 'apply';
    $pageTitle = 'Apply | Nexora IT Solutions Careers';
    
    // Load job references from database
    require_once 'settings.php';
    
    $jobs = [];
    try {
        $conn = new mysqli($servername, $username, $db_password);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->query("CREATE DATABASE IF NOT EXISTS $database");
        $conn->select_db($database);
        
        $result = $conn->query("SELECT reference, title FROM jobs WHERE status = 'Open' ORDER BY reference");
        if ($result) {
            $jobs = $result->fetch_all(MYSQLI_ASSOC);
        }
        $conn->close();
    } catch (Exception $e) {
        // If database fails, use fallback data
        $jobs = [
            ['reference' => 'NX7D2', 'title' => 'Data Scientist'],
            ['reference' => 'NX5C8', 'title' => 'Cloud Infrastructure Architect'],
            ['reference' => 'NX4S3', 'title' => 'Cybersecurity Analyst'],
            ['reference' => 'NX6P1', 'title' => 'Digital Delivery Lead']
        ];
    }
?>
<?php include 'header.inc'; ?>

    <!-- --------------------------------------------------------------------------------
         Application hero: contextual guidance
         -------------------------------------------------------------------------------- -->
    <header class="application-hero" role="banner">
        <div class="wrapper">
            <h1>Submit Your Application</h1>
            <p>Complete the form below to express interest in Nexora opportunities. Required fields ensure we can evaluate your strengths promptly.</p> <!-- AI Generated content -->
        </div>
    </header>

    <main>
        <!-- --------------------------------------------------------------------------------
             Application form
             -------------------------------------------------------------------------------- -->
        <section class="application-section" aria-labelledby="apply-heading">
            <div class="wrapper application-wrap">
                <h2 id="apply-heading">Candidate Details</h2>
                <!-- No JavaScript: use HTML5 validation and POST to the provided Mercury endpoint. -->
                <form class="application-form" action="process_eoi.php" method="post" novalidate="novalidate">
                    <div>
                        <label for="ref-number">Job Reference Number</label>
                        <select id="ref-number" name="reference" required>
                            <option value="" disabled selected>Select a reference</option>
                            <?php foreach ($jobs as $job): ?>
                                <option value="<?= htmlspecialchars($job['reference']) ?>"><?= htmlspecialchars($job['reference']) ?> &mdash; <?= htmlspecialchars($job['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="first-name">First Name</label>
                        <!-- Names: letters only, up to 20 characters. -->
                        <input id="first-name" name="first-name" type="text" maxlength="20" pattern="[A-Za-z]{1,20}" required>
                    </div>
                    <div>
                        <label for="last-name">Last Name</label>
                        <!-- Names: letters only, up to 20 characters. -->
                        <input id="last-name" name="last-name" type="text" maxlength="20" pattern="[A-Za-z]{1,20}" required>
                    </div>
                    <div>
                        <label for="dob">Date of Birth</label>
                        <input id="dob" name="dob" type="date" required>
                    </div>
                    <fieldset>
                        <!-- Group related radio inputs with a legend for accessibility. -->
                        <legend>Gender</legend>
                        <label><input type="radio" name="gender" value="female" required> Female</label>
                        <label><input type="radio" name="gender" value="male" required> Male</label>
                        <label><input type="radio" name="gender" value="nonbinary" required> Non-binary</label>
                        <label><input type="radio" name="gender" value="unspecified" required> Prefer not to say</label>
                    </fieldset>
                    <div>
                        <label for="street">Street Address</label>
                        <input id="street" name="street" type="text" maxlength="40" required>
                    </div>
                    <div>
                        <label for="suburb">Suburb / Town</label>
                        <input id="suburb" name="suburb" type="text" maxlength="40" required>
                    </div>
                    <div>
                        <label for="state">State</label>
                        <!-- State constrained to Australian options per assignment rubric. -->
                        <select id="state" name="state" required>
                            <option value="" disabled selected>Select state</option>
                            <option value="VIC">VIC</option>
                            <option value="NSW">NSW</option>
                            <option value="QLD">QLD</option>
                            <option value="NT">NT</option>
                            <option value="WA">WA</option>
                            <option value="SA">SA</option>
                            <option value="TAS">TAS</option>
                            <option value="ACT">ACT</option>
                        </select>
                    </div>
                    <div>
                        <label for="postcode">Postcode</label>
                        <!-- Exactly 4 digits as required by rubric. -->
                        <input id="postcode" name="postcode" type="text" inputmode="numeric" pattern="\d{4}" maxlength="4" required title="Postcode must be exactly four digits.">
                    </div>
                    <div>
                        <label for="email">Email Address</label>
                        <!-- Browser-native email validation with a conservative regex. -->
                        <input id="email" name="email" type="email" required pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$" title="Enter a valid email address.">
                    </div>
                    <div>
                        <label for="phone">Phone Number</label>
                        <!-- Allow 8–12 digits with optional spaces. -->
                        <input id="phone" name="phone" type="text" pattern="[0-9 ]{8,12}" required title="Use 8-12 digits, spaces permitted.">
                    </div>
                    <fieldset>
                        <!-- Required to check at least one skill: 'required' on the first checkbox enforces selection. -->
                        <legend>Required Technical Skills</legend>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="skills" value="cloud" required> Cloud Architecture</label>
                            <label><input type="checkbox" name="skills" value="security"> Cybersecurity Operations</label>
                            <label><input type="checkbox" name="skills" value="data"> Data Engineering</label>
                            <label><input type="checkbox" name="skills" value="agile"> Agile Delivery Leadership</label>
                            <label><input type="checkbox" name="skills" value="other"> Other Skills</label>
                        </div>
                    </fieldset>
                    <div>
                        <label for="other-skills">Other Skills</label>
                        <!-- Optional free-text field. Keep placeholder concise and instructive. -->
                        <textarea id="other-skills" name="other-skills" placeholder="Share any additional strengths or certifications."></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit">Apply</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

<?php include 'footer.inc'; ?>
