<?php
session_start();

// Connect to database FIRST
require_once 'dbconnect.php';
global $pdo; 
// Ensure $pdo is available
if (!isset($pdo)) {
    die("Database connection not established.");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch current user from database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// If user not found, destroy session and redirect to login
if (!$current_user) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
