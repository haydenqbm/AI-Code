<?php
    $currentPage = 'about';
    $pageTitle = 'About Our Team | Nexora IT Solutions';
?>
<?php include 'header.inc'; ?>

    <main>
        <!-- --------------------------------------------------------------------------------
             Team overview and contributions
             -------------------------------------------------------------------------------- -->
        <section class="section" aria-labelledby="team-heading">
            <div class="wrapper">
                <!-- Team information -->
                <h1 id="team-heading">COS10026 Group 8</h1>
                <p class="group-ids">Student ID: 105975035 - SWH03439</p>
                <!-- Team photo -->
                <figure class="team-figure">
                    <img src="images/portrait.jpg" alt="Group 8 members photo">
                    <figcaption>Team Nexora Pioneers</figcaption>
                </figure>
                <h2>Group Overview</h2>
                <ul>
                    <li>Group Name: Nexora Pioneers
                        <ul>
                            <li>Class: COS10026 Web Technology Project</li>
                            <li>Class time: Wednesday 14:00 - 17:00</li>
                        </ul>
                    </li>
                    <li>Tutor: Thuy Linh Nguyen, Thomas Harrison</li>
                </ul>

                <!-- Contributions and interest summary -->
                <h2>Member Contributions</h2>
                <dl>
                    <dt>Quang Bui (Project Part 1)</dt>
                    <dd>Responsible for all aspects of Project Part 1 including UX design, brand concept, layout design, styling implementation, and website development.</dd>
                    
                    <dt>Quang Bui (Project Part 2)</dt>
                    <dd>
                        <strong>Server-Side Development:</strong>
                        <ul>
                            <li>Created PHP include files (header.inc, footer.inc) for code reusability</li>
                            <li>Developed database connection configuration (settings.php)</li>
                            <li>Implemented EOI form processing with comprehensive server-side validation (process_eoi.php)</li>
                            <li>Built HR management system with authentication, filtering, and CRUD operations (manage.php)</li>
                            <li>Converted static jobs page to dynamic database-driven content (jobs.php)</li>
                            <li>Designed and implemented MySQL database schema for EOI and jobs tables</li>
                            <li>Added security features including login attempt tracking and account lockout</li>
                            <li>Implemented data sanitization and validation for all user inputs</li>
                        </ul>
                        
                        <strong>Database Design:</strong>
                        <ul>
                            <li>EOI table with auto-incrementing primary key and status tracking</li>
                            <li>Jobs table for dynamic job listing management</li>
                            <li>Managers table with secure password hashing for authentication</li>
                            <li>Proper data types and constraints for data integrity</li>
                        </ul>
                        
                        <strong>Features Implemented:</strong>
                        <ul>
                            <li>Complete EOI application workflow with confirmation system</li>
                            <li>Advanced HR management interface with sorting and filtering</li>
                            <li>Manager registration and authentication system</li>
                            <li>Dynamic job reference loading in application form</li>
                            <li>Comprehensive error handling and user feedback</li>
                        </ul>
                    </dd>
                </dl>

                <h2>Team Interests</h2>
                <!-- AI Generated content -->
                <table class="team-table">
                    <caption>Shared Interests and Expertise</caption>
                    <thead>
                        <tr>
                            <th scope="col">Member</th>
                            <th scope="col">Primary Interest</th>
                            <th scope="col">Secondary Focus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">Quang Bui</th>
                            <td>Pickleball</td>
                            <td>Music</td>
                        </tr>
                        <tr>
                            <th scope="row"></th>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th scope="row"></th>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th scope="row">Collective Goal</th>
                            <td colspan="2">Build resilient, human-centric technology that empowers Vietnamese enterprises.</td> 
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

<?php include 'footer.inc'; ?>
