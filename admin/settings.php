<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'System Settings';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would update settings in database or config file
    if (isset($_POST['update_settings'])) {
        $site_name = $_POST['site_name'];
        $site_email = $_POST['site_email'];
        $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
        
        // Here you would typically save these to a database or config file
        $_SESSION['settings_updated'] = true;
    }
}

// Get current settings (in a real app, these would come from database)
$current_settings = [
    'site_name' => 'Mental Health App',
    'site_email' => 'admin@mentalhealthapp.com',
    'maintenance_mode' => false
];

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
                <li><a class="admin-nav-link" href="therapists.php"><i class="fas fa-user-md me-2"></i>Therapists</a></li>
                <li><a class="admin-nav-link" href="services.php"><i class="fas fa-concierge-bell me-2"></i>Services</a></li>
                <li><a class="admin-nav-link active" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="mt-3"><a class="admin-nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1>System Settings</h1>
            </div>
            <div class="admin-card" style="max-width:600px;margin:0 auto;">
                <div class="admin-card-header">Edit Settings</div>
                <div class="admin-card-body">
                    <?php if (isset($_SESSION['settings_updated'])): ?>
                        <div class="alert alert-success" style="margin-bottom:18px;background:#eaffea;color:#218838;padding:12px 18px;border-radius:8px;">
                            Settings updated successfully!
                        </div>
                        <?php unset($_SESSION['settings_updated']); ?>
                    <?php endif; ?>
                    <form method="post">
                        <div style="margin-bottom:18px;">
                            <label for="site_name" style="display:block;font-weight:500;margin-bottom:6px;">Site Name</label>
                            <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($current_settings['site_name']); ?>" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="site_email" style="display:block;font-weight:500;margin-bottom:6px;">Site Email</label>
                            <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($current_settings['site_email']); ?>" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;display:flex;align-items:center;gap:10px;">
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" <?php echo $current_settings['maintenance_mode'] ? 'checked' : ''; ?> style="width:18px;height:18px;">
                            <label for="maintenance_mode" style="font-weight:500;">Maintenance Mode</label>
                        </div>
                        <div style="margin-top:24px;">
                            <button type="submit" name="update_settings" class="admin-btn admin-btn-primary" style="width:100%;max-width:220px;">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>