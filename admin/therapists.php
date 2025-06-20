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
$sql = "SELECT user_id, username, email, phone, role FROM users WHERE role = 'therapist'";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $therapists[] = $row;
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
            <div class="admin-sidebar-header">
                <h4>Admin Panel</h4>
                <hr>
                <div class="admin-profile">
                    <i class="fas fa-user-circle fa-3x"></i>
                    <div>
                        <h6><?php echo htmlspecialchars($_SESSION['username']); ?></h6>
                        <small><?php echo htmlspecialchars($_SESSION['email']); ?></small>
                    </div>
                </div>
            </div>
            <ul class="admin-nav">
                <li><a class="admin-nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                <li><a class="admin-nav-link" href="users.php"><i class="fas fa-users me-2"></i>Manage Users</a></li>
                <li><a class="admin-nav-link active" href="therapists.php"><i class="fas fa-user-md me-2"></i>Therapists</a></li>
                <li><a class="admin-nav-link" href="services.php"><i class="fas fa-concierge-bell me-2"></i>Services</a></li>
                <li><a class="admin-nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="mt-3"><a class="admin-nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
                                            <?php echo htmlspecialchars($therapist['role']); ?>
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