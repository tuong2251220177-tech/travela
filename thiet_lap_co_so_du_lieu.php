<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'travela';

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS $db");
$conn->select_db($db);

// Create tbl_users table
$sql = "CREATE TABLE IF NOT EXISTS tbl_users (
    userId int NOT NULL AUTO_INCREMENT,
    username varchar(255) NOT NULL UNIQUE,
    email varchar(255) NOT NULL UNIQUE,
    password varchar(255) NOT NULL,
    avatar varchar(255),
    activation_token varchar(255),
    isActive char(1) DEFAULT 'n',
    google_id varchar(255),
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table tbl_users created successfully!\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>
