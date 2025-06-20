<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Add New User';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $specialty = ($role === 'therapist') ? trim($_POST['specialty']) : null;
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = 'Please fill all required fields';
    } elseif ($role === 'therapist' && empty($specialty)) {
        $error = 'Specialty is required for therapists';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'Email already exists';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, phone, pwd, role, specialty) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $email, $phone, $hashed_password, $role, $specialty);
            
            if ($stmt->execute()) {
                $success = 'User added successfully!';
                // Clear form
                $username = $email = $phone = $role = $specialty = '';
            } else {
                $error = 'Error adding user: ' . $conn->error;
            }
        }
        $stmt->close();
    }
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
                <h1>Add New User</h1>
                <a href="users.php" class="admin-btn admin-btn-secondary" style="max-width:200px;">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>
            <div class="admin-card" style="max-width:600px;margin:0 auto;">
                <div class="admin-card-header">User Details</div>
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
                        <div style="margin-bottom:18px;">
                            <label for="username" style="display:block;font-weight:500;margin-bottom:6px;">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="email" style="display:block;font-weight:500;margin-bottom:6px;">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="phone" style="display:block;font-weight:500;margin-bottom:6px;">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="password" style="display:block;font-weight:500;margin-bottom:6px;">Password</label>
                            <input type="password" id="password" name="password" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="role" style="display:block;font-weight:500;margin-bottom:6px;">Role</label>
                            <select id="role" name="role" required onchange="toggleSpecialtyField()" style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                                <option value="">Select Role</option>
                                <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="therapist" <?php echo (isset($role) && $role === 'therapist') ? 'selected' : ''; ?>>Therapist</option>
                                <option value="client" <?php echo (isset($role) && $role === 'client') ? 'selected' : ''; ?>>Client</option>
                            </select>
                        </div>
                        <div style="margin-bottom:18px;display:none;" id="specialty-field">
                            <label for="specialty" style="display:block;font-weight:500;margin-bottom:6px;">Specialty</label>
                            <input type="text" id="specialty" name="specialty" value="<?php echo isset($specialty) ? htmlspecialchars($specialty) : ''; ?>" style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-top:24px;">
                            <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;max-width:220px;">
                                <i class="fas fa-save me-2"></i>Add User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Function to show/hide specialty field based on role selection
        function toggleSpecialtyField() {
            const roleSelect = document.getElementById('role');
            const specialtyField = document.getElementById('specialty-field');
            if (roleSelect.value === 'therapist') {
                specialtyField.style.display = 'block';
                document.getElementById('specialty').required = true;
            } else {
                specialtyField.style.display = 'none';
                document.getElementById('specialty').required = false;
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleSpecialtyField();
        });
    </script>
</body>
</html>