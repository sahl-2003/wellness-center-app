<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Manage Therapists';

// Get all therapists
$therapists = [];
$sql = "SELECT u.user_id, u.username, u.email, u.phone, t.specialization, t.qualifications, t.bio, t.profile_picture 
        FROM users u 
        LEFT JOIN therapists t ON u.user_id = t.user_id 
        WHERE u.role = 'therapist' 
        ORDER BY u.user_id DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $therapists[] = $row;
}

// Get unread messages count (contact messages from public contact form)
$unread_count = 0;
$sql = "SELECT COUNT(*) as count FROM contact_messages WHERE is_read = FALSE";
$result = $conn->query($sql);
if ($result) {
    $unread_count = $result->fetch_assoc()['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="admin_dashboard_custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-flex">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <img src="../image/c2.jpg" alt="Admin Profile" class="admin-profile-pic">
                </div>
                <h5><?php echo htmlspecialchars($_SESSION['username']); ?></h5>
                <small><?php echo htmlspecialchars($_SESSION['email']); ?></small>
                <div class="mt-2 admin-badge">Administrator</div>
            </div>
            <ul class="admin-nav">
                <li><a class="admin-nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li><a class="admin-nav-link" href="users.php"><i class="fas fa-users"></i>Manage Users</a></li>
                <li><a class="admin-nav-link active" href="therapists.php"><i class="fas fa-user-md"></i>Therapists</a></li>
                <li><a class="admin-nav-link" href="services.php"><i class="fas fa-concierge-bell"></i>Services</a></li>
                <li><a class="admin-nav-link" href="appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a></li>
                <li><a class="admin-nav-link" href="messages.php"><i class="fas fa-envelope"></i>Messages<?php if ($unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="mt-3"><a class="admin-nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1>Manage Therapists</h1>
                <a href="add_therapist.php" class="admin-btn admin-btn-primary" style="max-width:220px;">
                    <i class="fas fa-plus"></i> Add New Therapist
                </a>
            </div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5>Therapist List</h5>
                </div>
                <div class="admin-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($therapists as $therapist): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($therapist['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($therapist['username']); ?></td>
                                    <td><?php echo htmlspecialchars($therapist['email']); ?></td>
                                    <td><?php echo htmlspecialchars($therapist['phone']); ?></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo htmlspecialchars(isset($therapist['role']) ? $therapist['role'] : 'Therapist'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-btn-group" style="flex-direction: row; gap: 10px;">
                                            <a href="edit_therapist.php?id=<?= $therapist['user_id'] ?>" class="admin-btn admin-btn-primary" style="max-width:90px;padding:6px 10px;font-size:0.98rem;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>