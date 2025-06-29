<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../dbconnect.php');

$page_title = 'Manage Users';
$error = '';
$success = '';

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $error = 'You cannot delete your own account!';
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete related records from all tables that reference this user
            
            // Delete messages where user is sender or receiver
            $stmt = $conn->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
            $stmt->bind_param("ii", $user_id, $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Delete appointments where user is client
            $stmt = $conn->prepare("DELETE FROM appointments WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Delete therapist records if user is a therapist
            $stmt = $conn->prepare("DELETE FROM therapists WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Delete therapist availability records
            $stmt = $conn->prepare("DELETE FROM therapist_availability WHERE therapist_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Delete contact messages if any (assuming there's a user_id field)
            // Uncomment if contact_messages table has user_id field
            // $stmt = $conn->prepare("DELETE FROM contact_messages WHERE user_id = ?");
            // $stmt->bind_param("i", $user_id);
            // $stmt->execute();
            // $stmt->close();
            
            // Finally, delete the user
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            $success = 'User deleted successfully!';
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = 'Error deleting user: ' . $e->getMessage();
        }
    }
}

// Get all users
$users = [];
$sql = "SELECT user_id, username, email, phone, role FROM users ORDER BY user_id DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
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
                <li><a class="admin-nav-link active" href="users.php"><i class="fas fa-users"></i>Manage Users</a></li>
                <li><a class="admin-nav-link" href="therapists.php"><i class="fas fa-user-md"></i>Therapists</a></li>
                <li><a class="admin-nav-link" href="services.php"><i class="fas fa-concierge-bell"></i>Services</a></li>
                <li><a class="admin-nav-link" href="appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a></li>
                <li><a class="admin-nav-link" href="messages.php"><i class="fas fa-envelope"></i>Messages<?php if ($unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="spacing-top"><a class="admin-nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
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
            
            <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-bottom:18px;background:#ffeaea;color:#c0392b;padding:12px 18px;border-radius:8px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom:18px;background:#eaffea;color:#218838;padding:12px 18px;border-radius:8px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
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
                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                <button type="submit" name="delete_user" class="admin-btn admin-btn-danger" style="max-width:90px;padding:6px 10px;font-size:0.98rem;"
                                                        onclick="return confirm('Are you sure you want to delete this user? This will also delete all their messages, appointments, and related data.')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <span class="admin-btn admin-btn-secondary" style="max-width:90px;padding:6px 10px;font-size:0.98rem;opacity:0.6;cursor:not-allowed;">
                                                <i class="fas fa-user"></i> Current
                                            </span>
                                            <?php endif; ?>
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