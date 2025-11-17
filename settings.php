<?php
/**
 * Database connection settings
 * This file contains the database connection variables for the Nexora IT Solutions project
 */

// Database connection parameters
$host = "localhost";        // Database host (XAMPP default)
$user = "root";            // Database username (XAMPP default)  
$password = "";            // Database password (XAMPP default is empty, but might be 'root' or set by user)
$dbname = "nexora_db";     // Database name for the project

// Create connection string for MySQLi
$servername = $host;
$username = $user;
$db_password = $password;
$database = $dbname;

// Alternative PDO connection variables if needed
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

?>