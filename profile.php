<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data from database (example)
$userData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '123-456-7890'
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <style>  
    body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .sidebar {
            width: 200px;
            background-color: #f4f4f4;
            padding: 20px;
            float: left;
            height: 100vh;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            margin-bottom: 5px;
            background-color: #ddd;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
        }
        .sidebar a:hover {
            background-color: #ccc;
        }
        .content {
            margin-left: 240px;
            padding: 20px;
        }
        </style>
</head>
<body>
    <div class="sidebar">
       <div class="sidebar">
        <h2>Profile</h2>
        <a href="client_dashboard.html">Dashboard</a>
        <a href="appointment.php">MY appointments</a>
        <a href="documents.php">Documents</a>
        <a href="messages.php">Messages</a>
        <a href="settings.php">Settings</a>
        <a href="home.php">Back to Home</a>
    </div>

    </div>

    <div class="content">
        <h1>My Profile</h1>
        <form action="update_profile.php" method="post">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>"><br>
            
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>"><br>
            
            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($userData['phone']); ?>"><br>
            
            <input type="submit" value="Update Profile">
        </form>
    </div>
</body>
</html>