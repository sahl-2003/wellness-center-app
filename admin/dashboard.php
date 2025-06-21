<?php
// admin/dashboard.php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

// Get admin details
$admin_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Get stats for dashboard
$stats = [];
$queries = [
    'total_users' => "SELECT COUNT(*) as count FROM users",
    'total_therapists' => "SELECT COUNT(*) as count FROM users WHERE role = 'therapist'",
    'total_clients' => "SELECT COUNT(*) as count FROM users WHERE role = 'client'",
    'total_services' => "SELECT COUNT(*) as count FROM services" // Added services count
];

foreach ($queries as $key => $query) {
    $result = $conn->query($query);
    $stats[$key] = $result->fetch_assoc()['count'];
}

// Get recent users (using user_id instead of created_at)
$recent_users = [];
$sql = "SELECT username, email, user_id FROM users ORDER BY user_id DESC LIMIT 5";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $recent_users[] = $row;
}

// Get recent services
$recent_services = [];
$sql = "SELECT name, price, is_active FROM services ORDER BY service_id DESC LIMIT 3";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $recent_services[] = $row;
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
    <title>Admin Dashboard</title>
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
                <h5><?php echo htmlspecialchars($admin['username']); ?></h5>
                <small><?php echo htmlspecialchars($admin['email']); ?></small>
                <div class="mt-2 admin-badge">Administrator</div>
            </div>
            <ul class="admin-nav">
                <li><a class="admin-nav-link active" href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li><a class="admin-nav-link" href="users.php"><i class="fas fa-users"></i>Manage Users</a></li>
                <li><a class="admin-nav-link" href="therapists.php"><i class="fas fa-user-md"></i>Therapists</a></li>
                <li><a class="admin-nav-link" href="services.php"><i class="fas fa-concierge-bell"></i>Services</a></li>
                <li><a class="admin-nav-link" href="appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a></li>
                <li><a class="admin-nav-link" href="messages.php"><i class="fas fa-envelope"></i>Messages<?php if ($unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="mt-3"><a class="admin-nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-toolbar">
                    <button type="button" class="admin-btn admin-btn-secondary">Export</button>
                </div>
            </div>
            <!-- Stats Cards -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card admin-card-primary">
                        <h5>Total Users</h5>
                        <h2><?php echo $stats['total_users']; ?></h2>
                    <i class="fas fa-users"></i>
                </div>
                <div class="admin-stat-card admin-card-success">
                        <h5>Therapists</h5>
                        <h2><?php echo $stats['total_therapists']; ?></h2>
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="admin-stat-card admin-card-info">
                        <h5>Clients</h5>
                        <h2><?php echo $stats['total_clients']; ?></h2>
                    <i class="fas fa-user"></i>
                </div>
                <div class="admin-stat-card admin-card-warning">
                        <h5>Services</h5>
                        <h2><?php echo $stats['total_services']; ?></h2>
                    <i class="fas fa-concierge-bell"></i>
                </div>
            </div>
            <!-- Quick Actions -->
            <div class="admin-row">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="admin-card-body">
                        <div class="admin-btn-group">
                            <a href="users.php?action=add" class="admin-btn admin-btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Add New User
                            </a>
                            <a href="therapists.php" class="admin-btn admin-btn-success">
                                <i class="fas fa-user-md me-2"></i>Manage Therapists
                            </a>
                            <a href="services.php" class="admin-btn admin-btn-info">
                                <i class="fas fa-concierge-bell me-2"></i>Manage Services
                            </a>
                            <a href="add_service.php" class="admin-btn admin-btn-warning">
                                <i class="fas fa-plus-circle me-2"></i>Add New Service
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Recent Services and Recent Users Section in Flex Row -->
            <div class="admin-row">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5>Recent Services</h5>
                    </div>
                    <div class="admin-card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_services as $service): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($service['name']); ?></td>
                                        <td>$<?php echo number_format($service['price'], 2); ?></td>
                                        <td>
                                            <span class="badge <?php echo $service['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5>Recent Users</h5>
                    </div>
                    <div class="admin-card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>User ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>