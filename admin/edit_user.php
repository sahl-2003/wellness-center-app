<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Edit User';
$error = '';
$success = '';

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch user data
$user = null;
if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT user_id, username, email, phone, role FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
    } else {
        $error = 'Database error: ' . $conn->error;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $new_password = trim($_POST['new_password']);
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($role)) {
        $error = 'Please fill all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email already exists for another user
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        if ($check_stmt) {
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error = 'Email already exists for another user';
            } else {
                // Update user data
                if (!empty($new_password)) {
                    // Update with new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ?, pwd = ?, role = ? WHERE user_id = ?");
                    if ($update_stmt) {
                        $update_stmt->bind_param("sssssi", $username, $email, $phone, $hashed_password, $role, $user_id);
                    }
                } else {
                    // Update without changing password
                    $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ?, role = ? WHERE user_id = ?");
                    if ($update_stmt) {
                        $update_stmt->bind_param("ssssi", $username, $email, $phone, $role, $user_id);
                    }
                }
                
                if (isset($update_stmt)) {
                    if ($update_stmt->execute()) {
                        $success = 'User updated successfully!';
                        // Refresh user data
                        $refresh_stmt = $conn->prepare("SELECT user_id, username, email, phone, role FROM users WHERE user_id = ?");
                        if ($refresh_stmt) {
                            $refresh_stmt->bind_param("i", $user_id);
                            $refresh_stmt->execute();
                            $result = $refresh_stmt->get_result();
                            $user = $result->fetch_assoc();
                            $refresh_stmt->close();
                        }
                    } else {
                        $error = 'Error updating user: ' . $conn->error;
                    }
                    $update_stmt->close();
                } else {
                    $error = 'Database error: ' . $conn->error;
                }
            }
            $check_stmt->close();
        } else {
            $error = 'Database error: ' . $conn->error;
        }
    }
}
$conn->close();

// Redirect if no user found
if (!$user && $user_id > 0) {
    header("Location: users.php");
    exit();
}
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
                <h1>Edit User</h1>
                <a href="users.php" class="admin-btn admin-btn-secondary" style="max-width:200px;">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>
            <div class="admin-card" style="max-width:600px;margin:0 auto;">
                <div class="admin-card-header">Edit User Details</div>
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
                    <form method="post">
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                        <div style="margin-bottom:18px;">
                            <label for="username" style="display:block;font-weight:500;margin-bottom:6px;">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="email" style="display:block;font-weight:500;margin-bottom:6px;">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="phone" style="display:block;font-weight:500;margin-bottom:6px;">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="role" style="display:block;font-weight:500;margin-bottom:6px;">Role</label>
                            <select id="role" name="role" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="therapist" <?php echo $user['role'] === 'therapist' ? 'selected' : ''; ?>>Therapist</option>
                                <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                            </select>
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="new_password" style="display:block;font-weight:500;margin-bottom:6px;">New Password <span style="color:#888;font-weight:400;">(leave blank to keep current)</span></label>
                            <input type="password" id="new_password" name="new_password" style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-top:24px;">
                            <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;max-width:220px;">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>