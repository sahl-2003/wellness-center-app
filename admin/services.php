<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Manage Services';
$error = '';
$success = '';

// Handle service deletion and activation/deactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_service'])) {
    $service_id = $_POST['service_id'];
    $stmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    if ($stmt->execute()) {
        $success = 'Service deleted successfully!';
    } else {
        $error = 'Error deleting service: ' . $conn->error;
    }
    $stmt->close();
    } elseif (isset($_POST['toggle_active'])) {
        $service_id = $_POST['service_id'];
        $stmt = $conn->prepare("UPDATE services SET is_active = NOT is_active WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);
        if ($stmt->execute()) {
            $success = 'Service status updated successfully!';
        } else {
            $error = 'Error updating service status: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Handle filters
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$where = '';
if ($filter === 'active') {
    $where = "WHERE is_active = 1";
} elseif ($filter === 'inactive') {
    $where = "WHERE is_active = 0";
}

// Get all services
$services = [];
$sql = "SELECT service_id, name, description, price, duration, category, is_active FROM services ORDER BY service_id DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
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
                <li><a class="admin-nav-link" href="therapists.php"><i class="fas fa-user-md"></i>Therapists</a></li>
                <li><a class="admin-nav-link active" href="services.php"><i class="fas fa-concierge-bell"></i>Services</a></li>
                <li><a class="admin-nav-link" href="appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a></li>
                <li><a class="admin-nav-link" href="messages.php"><i class="fas fa-envelope"></i>Messages<?php if ($unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="mt-3"><a class="admin-nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1>Manage Services</h1>
                <a href="add_service.php" class="admin-btn admin-btn-primary" style="max-width:220px;">
                    <i class="fas fa-plus"></i> Add New Service
                </a>
            </div>
            <div class="admin-card">
                <div class="admin-card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div>Service List</div>
                    <div class="filter-bar" style="display:flex;gap:10px;">
                        <a href="services.php" class="admin-btn<?php echo empty($filter) ? ' admin-btn-success' : ' admin-btn-secondary'; ?>" style="padding:6px 16px;font-size:0.98rem;">All</a>
                        <a href="services.php?filter=active" class="admin-btn<?php echo $filter === 'active' ? ' admin-btn-success' : ' admin-btn-secondary'; ?>" style="padding:6px 16px;font-size:0.98rem;">Active</a>
                        <a href="services.php?filter=inactive" class="admin-btn<?php echo $filter === 'inactive' ? ' admin-btn-success' : ' admin-btn-secondary'; ?>" style="padding:6px 16px;font-size:0.98rem;">Inactive</a>
                    </div>
                </div>
                <div class="admin-card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="margin-bottom:18px;background:#ffeaea;color:#c0392b;padding:12px 18px;border-radius:8px;">
                            <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                        <div class="alert alert-success" style="margin-bottom:18px;background:#eaffea;color:#218838;padding:12px 18px;border-radius:8px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($service['description']); ?></td>
                                    <td><?php echo htmlspecialchars($service['duration']); ?> min</td>
                                    <td>$<?php echo number_format($service['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars(isset($service['category']) ? $service['category'] : 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $service['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                                    </td>
                                    <td>
                                        <div class="admin-btn-group" style="flex-direction: row; gap: 8px;">
                                            <a href="edit_service.php?id=<?= $service['service_id'] ?>" class="admin-btn admin-btn-primary" style="padding:6px 10px;font-size:0.98rem;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                                                <button type="submit" name="delete_service" class="admin-btn admin-btn-danger" style="padding:6px 10px;font-size:0.98rem;" onclick="return confirm('Are you sure you want to delete this service?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                                                <button type="submit" name="toggle_active" class="admin-btn <?php echo $service['is_active'] ? 'admin-btn-secondary' : 'admin-btn-success'; ?>" style="padding:6px 10px;font-size:0.98rem;">
                                                    <i class="fas fa-toggle-on"></i> <?php echo $service['is_active'] ? 'Deactivate' : 'Activate'; ?>
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