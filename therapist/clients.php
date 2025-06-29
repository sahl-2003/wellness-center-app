<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: ../login.php");
    exit();
}

include('../dbconnect.php');

$page_title = 'My Clients';
$error = '';
$success = '';

// Get therapist profile data
$therapist = null;
$stmt = $conn->prepare("SELECT u.*, t.specialization, t.qualifications, t.bio, t.profile_picture 
                       FROM users u 
                       LEFT JOIN therapists t ON u.user_id = t.user_id
                       WHERE u.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$therapist = $result->fetch_assoc();
$stmt->close();

// Get therapist's clients (updated to use user_id instead of client_id)
$clients = [];
$stmt = $conn->prepare("SELECT u.user_id, u.username, u.email, cp.profile_picture FROM users u INNER JOIN appointments a ON u.user_id = a.user_id LEFT JOIN client_profiles cp ON u.user_id = cp.user_id WHERE a.therapist_id = ? AND a.status != 'cancelled' GROUP BY u.user_id");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $clients[] = $row;
}
$stmt->close();

// Get unread messages count
$unread_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM messages WHERE receiver_id = ? AND is_read = FALSE");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$unread_count = $result->fetch_assoc()['count'];
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="therapist_dashboard_custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="therapist-flex">
        <!-- Sidebar -->
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <div class="therapist-sidebar-custom">
            <div class="text-center mb-4">
                <div class="mb-3">
                <?php if (!empty($therapist['profile_picture'])): ?>
                        <img src="../<?php echo htmlspecialchars($therapist['profile_picture']); ?>" class="profile-picture" alt="Profile Picture">
                <?php else: ?>
                        <img src="../image/t1.jpg" class="profile-picture" alt="Profile Picture">
                    <?php endif; ?>
                    </div>
                <h5><?php echo htmlspecialchars($therapist['username'] ?? $_SESSION['username']); ?></h5>
                <small><?php echo htmlspecialchars($therapist['email'] ?? $_SESSION['email']); ?></small>
                <div class="mt-2 therapist-badge">Therapist</div>
            </div>
            <ul class="sidebar-nav">
                <li><a class="sidebar-link <?php if($current_page == 'dashboard.php') echo 'active'; ?>" href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li><a class="sidebar-link <?php if($current_page == 'profile.php') echo 'active'; ?>" href="profile.php"><i class="fas fa-user"></i>My Profile</a></li>
                <li><a class="sidebar-link <?php if($current_page == 'clients.php') echo 'active'; ?>" href="clients.php"><i class="fas fa-users"></i>My Clients</a></li>
                <li><a class="sidebar-link <?php if($current_page == 'appointments.php') echo 'active'; ?>" href="appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a></li>
                <li><a class="sidebar-link <?php if($current_page == 'schedule.php') echo 'active'; ?>" href="schedule.php"><i class="fas fa-calendar-alt"></i>Schedule</a></li>
                <li><a class="sidebar-link <?php if($current_page == 'messages.php') echo 'active'; ?>" href="messages.php"><i class="fas fa-envelope"></i>Messages<?php if (isset($unread_count) && $unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="spacing-top"><a class="sidebar-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="therapist-main">
            <div class="therapist-header" style="margin-bottom: 24px;">
                <h1 style="font-size: 2rem; color: #333;">My Clients</h1>
            </div>
            <?php if ($error): ?>
                <div class="alert-therapist alert-danger-therapist"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-therapist alert-info-therapist"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <div class="dashboard-stats-grid" style="flex-wrap: wrap; gap: 24px;">
                <?php if (empty($clients)): ?>
                    <div class="card-therapist" style="width:100%;"><div class="card-body-therapist text-center">You don't have any clients yet.</div></div>
                <?php else: ?>
                    <?php foreach ($clients as $client): ?>
                        <div class="card-therapist" style="min-width:260px;max-width:340px;flex:1 1 260px;">
                            <div class="card-body-therapist" style="display:flex;align-items:center;gap:18px;">
                                <div class="profile-image-container" style="width:60px;height:60px;min-width:60px;">
                                    <img src="<?php 
                                        $pic = $client['profile_picture'];
                                        if (!empty($pic)) {
                                            $pic = ltrim($pic, '/');
                                            if (strpos($pic, 'uploads/profiles/') === 0) {
                                                $pic = '../' . $pic;
                                            } else if (strpos($pic, '../uploads/profiles/') !== 0) {
                                                $pic = '../uploads/profiles/' . basename($pic);
                                            }
                                            echo htmlspecialchars($pic);
                                        } else {
                                            echo '../assets/images/default-avatar.jpg';
                                        }
                                    ?>" class="profile-picture" alt="Client Avatar" style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
                                        </div>
                                <div style="flex:1;">
                                    <h4 style="margin:0 0 4px 0;font-size:1.1rem;font-weight:600;"><?php echo htmlspecialchars($client['username']); ?></h4>
                                    <div style="color:#888;font-size:0.98rem;margin-bottom:4px;"><?php echo htmlspecialchars($client['email']); ?></div>
                                    <button class="btn-therapist btn-sm-therapist toggle-details-therapist" data-target="details-<?php echo $client['user_id']; ?>" style="margin-top:6px;">
                                        <i class="fas fa-eye me-1"></i> Details
                                    </button>
                                    <div class="client-details mt-2" id="details-<?php echo $client['user_id']; ?>" style="display:none;">
                                        <div style="margin-top:8px;"><strong>Name:</strong> <?php echo htmlspecialchars($client['username']); ?></div>
                                        <div><strong>Email:</strong> <?php echo htmlspecialchars($client['email']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // Toggle client details
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-details-therapist').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const detailsDiv = document.getElementById(targetId);
                    if (detailsDiv.style.display === 'none' || detailsDiv.style.display === '') {
                        detailsDiv.style.display = 'block';
                        this.innerHTML = '<i class="fas fa-eye-slash me-1"></i> Hide Details';
                    } else {
                        detailsDiv.style.display = 'none';
                        this.innerHTML = '<i class="fas fa-eye me-1"></i> Details';
                    }
                });
            });
        });
    </script>
</body>
</html>