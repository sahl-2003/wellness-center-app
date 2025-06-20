<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Manage Users';

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Get all users
$users = [];
$sql = "SELECT user_id, username, email, phone, role FROM users ORDER BY user_id DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
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
                <li><a class="admin-nav-link active" href="users.php"><i class="fas fa-users me-2"></i>Manage Users</a></li>
                <li><a class="admin-nav-link" href="therapists.php"><i class="fas fa-user-md me-2"></i>Therapists</a></li>
                <li><a class="admin-nav-link" href="services.php"><i class="fas fa-concierge-bell me-2"></i>Services</a></li>
                <li><a class="admin-nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="mt-3"><a class="admin-nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1>Manage Users</h1>
                <a href="add_user.php" class="admin-btn admin-btn-primary" style="max-width:200px;">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            </div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5>User List</h5>
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
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-primary">Admin</span>
                                        <?php elseif ($user['role'] === 'therapist'): ?>
                                            <span class="badge bg-success">Therapist</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Client</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="admin-btn-group" style="flex-direction: row; gap: 10px;">
                                            <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="admin-btn admin-btn-primary" style="max-width:90px;padding:6px 10px;font-size:0.98rem;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                <button type="submit" name="delete_user" class="admin-btn admin-btn-danger" style="max-width:90px;padding:6px 10px;font-size:0.98rem;"
                                                        onclick="return confirm('Are you sure you want to delete this user?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
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