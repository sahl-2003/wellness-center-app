<?php
session_start();

// Check if user is logged in and is a therapist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: ../login.html");
    exit();
}

// Database connection
$db_host = "localhost";
$db_user = "root"; // Default XAMPP username
$db_pass = "";     // Default XAMPP password
$db_name = "greenlife"; // Your database name

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$page_title = 'Therapist Dashboard';
$error = '';
$success = '';

// Get therapist profile
$profile = null;
$stmt = $conn->prepare("
    SELECT u.*, t.* 
    FROM users u
    LEFT JOIN therapists t ON u.user_id = t.user_id
    WHERE u.user_id = ?
");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    $stmt->close();
} else {
    $error = "Database error: " . $conn->error;
}

// Only proceed if no error occurred
if (empty($error)) {
    // Get upcoming appointments (using therapist_id instead of client_id)
    $upcoming_appointments = [];
    $stmt = $conn->prepare("
        SELECT 
            a.id as appointment_id,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.status,
            a.notes,
            s.name as service_name,
            u.username as client_name
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        JOIN users u ON a.user_id = u.user_id
        WHERE a.therapist_id = ? AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date, a.start_time
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $upcoming_appointments[] = $row;
        }
        $stmt->close();
    } else {
        $error = "Database error: " . $conn->error;
    }

    // Get client count (only clients with at least one non-cancelled appointment)
    $client_count = 0;
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as count FROM appointments WHERE therapist_id = ? AND status != 'cancelled'");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $client_count = $result->fetch_assoc()['count'];
        $stmt->close();
    } else {
        $error = "Database error: " . $conn->error;
    }

    // Get upcoming appointments count (not cancelled or declined)
    $upcoming_count = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE therapist_id = ? AND appointment_date >= CURDATE() AND status NOT IN ('cancelled', 'declined')");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $upcoming_count = $result->fetch_assoc()['count'];
        $stmt->close();
    } else {
        $error = "Database error: " . $conn->error;
    }

    // Get unread messages count
    $unread_count = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = FALSE");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $unread_count = $result->fetch_assoc()['count'];
        $stmt->close();
    } else {
        $error = "Database error: " . $conn->error;
    }
}

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
    <style>
        .stats-card-link {
            text-decoration: none;
            color: inherit;
        }
        .stats-card-link .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="therapist-flex">
        <!-- Sidebar -->
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <div class="therapist-sidebar-custom">
            <div class="text-center mb-4">
                <div class="profile-image-container mb-3">
                    <?php if (!empty($profile['profile_picture'])): ?>
                        <img src="../<?php echo htmlspecialchars($profile['profile_picture']); ?>" class="profile-picture">
                    <?php else: ?>
                        <div class="profile-picture bg-secondary d-flex align-items-center justify-content-center">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    <?php endif; ?>
                    <div class="profile-image-overlay"><i class="fas fa-camera fa-2x"></i></div>
                </div>
                <h5><?php echo htmlspecialchars($profile['username'] ?? $_SESSION['username']); ?></h5>
                <small><?php echo htmlspecialchars($profile['email'] ?? $_SESSION['email']); ?></small>
                <?php if (!empty($profile['specialization'])): ?>
                    <div class="mt-2 card-badge-therapist card-badge-primary"><?php echo htmlspecialchars($profile['specialization']); ?></div>
                <?php endif; ?>
            </div>
            <ul class="sidebar-nav">
                <li><a class="sidebar-link <?php if($current_page == 'dashboard.php') echo 'active'; ?>" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                <li><a class="sidebar-link <?php if($current_page == 'profile.php') echo 'active'; ?>" href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                <li><a class="sidebar-link <?php if($current_page == 'clients.php') echo 'active'; ?>" href="clients.php"><i class="fas fa-users me-2"></i>My Clients</a></li>
                <li><a class="sidebar-link <?php if($current_page == 'appointments.php') echo 'active'; ?>" href="appointments.php"><i class="fas fa-calendar-check me-2"></i>Appointments</a></li>
                <li><a class="sidebar-link <?php if($current_page == 'schedule.php') echo 'active'; ?>" href="schedule.php"><i class="fas fa-calendar-alt me-2"></i>Schedule</a></li>
                <li class="position-relative"><a class="sidebar-link <?php if($current_page == 'messages.php') echo 'active'; ?>" href="messages.php"><i class="fas fa-envelope me-2"></i>Messages<?php if (isset($unread_count) && $unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="mt-3"><a class="sidebar-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="therapist-main">
            <div class="therapist-header">
                <h1>Therapist Dashboard</h1>
                <div class="toolbar">
                    <a href="schedule.php" class="btn-therapist"><i class="fas fa-calendar-plus me-2"></i>Manage Schedule</a>
                </div>
            </div>
            <?php if ($error): ?>
                <div class="alert-therapist alert-danger-therapist"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <!-- Welcome Card -->
            <div class="card-therapist mb-4">
                <div class="card-body-therapist">
                    <h4 style="margin-bottom: 10px;"><strong>Welcome, <?php echo htmlspecialchars($profile['username'] ?? $_SESSION['username']); ?>!</strong></h4>
                <p>This is your professional dashboard where you can manage your therapy sessions.</p>
                </div>
            </div>
            <!-- Stats Grid -->
            <div class="dashboard-stats-grid">
                <a href="clients.php" class="stats-card-link">
                    <div class="card-therapist stats-card">
                        <div class="card-body-therapist text-center">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h3 style="margin: 10px 0 0 0;"><?php echo $client_count ?? 0; ?></h3>
                            <div class="card-subtitle-therapist">Active Clients</div>
                        </div>
                    </div>
                </a>
                <a href="appointments.php" class="stats-card-link">
                    <div class="card-therapist stats-card">
                        <div class="card-body-therapist text-center">
                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                            <h3 style="margin: 10px 0 0 0;"><?php echo $upcoming_count ?? 0; ?></h3>
                            <div class="card-subtitle-therapist">Upcoming Appointments</div>
                        </div>
                    </div>
                </a>
                <a href="messages.php" class="stats-card-link">
                    <div class="card-therapist stats-card">
                        <div class="card-body-therapist text-center">
                            <i class="fas fa-envelope fa-2x mb-2"></i>
                            <h3 style="margin: 10px 0 0 0;"><?php echo $unread_count ?? 0; ?></h3>
                            <div class="card-subtitle-therapist">Unread Messages</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Upcoming Appointments Card -->
            <div class="card-therapist mb-4">
                <div class="card-header-therapist"><strong>Upcoming Appointments</strong></div>
                <div class="card-body-therapist">
                    <div style="overflow-x:auto;">
                        <table class="therapist-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Client</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($upcoming_appointments as $i => $appt): ?>
                                    <?php if (strtolower($appt['status']) === 'cancelled' || strtolower($appt['status']) === 'declined') continue; ?>
                                    <tr>
                                        <td><?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?></td>
                                        <td><?php echo date('g:i A', strtotime($appt['start_time'])) . ' - ' . date('g:i A', strtotime($appt['end_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($appt['client_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appt['service_name']); ?></td>
                                        <td><span class="card-badge-therapist card-badge-<?php echo strtolower($appt['status']); ?>"><?php echo htmlspecialchars($appt['status']); ?></span></td>
                                        <td><button class="btn-therapist-secondary btn-therapist btn-sm-therapist toggle-details" data-target="details-<?php echo $i; ?>"><i class="fas fa-eye me-1"></i> Details</button></td>
                                    </tr>
                                    <tr id="details-<?php echo $i; ?>" style="display:none;background:#f8f9fa;">
                                        <td colspan="6" style="padding:18px 24px;">
                                            <div style="font-size:1.01rem;">
                                                <strong>Client:</strong> <?php echo htmlspecialchars($appt['client_name']); ?><br>
                                                <strong>Service:</strong> <?php echo htmlspecialchars($appt['service_name']); ?><br>
                                                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?><br>
                                                <strong>Time:</strong> <?php echo date('g:i A', strtotime($appt['start_time'])) . ' - ' . date('g:i A', strtotime($appt['end_time'])); ?><br>
                                                <strong>Status:</strong> <?php echo htmlspecialchars($appt['status']); ?><br>
                                                <?php if (!empty($appt['notes'])): ?>
                                                    <strong>Notes:</strong> <?php echo htmlspecialchars($appt['notes']); ?><br>
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
            <!-- Actions Flex Row -->
            <div class="dashboard-actions-flex">
                <!-- Manage Schedule Card -->
                <div class="card-therapist mb-4">
                    <div class="card-header-therapist"><i class="fas fa-calendar-alt"></i> Manage Schedule</div>
                    <div class="card-body-therapist">
                            <p>Set your availability and working hours.</p>
                        <a href="schedule.php" class="btn-therapist btn-therapist-secondary">Go to Schedule</a>
                    </div>
                </div>
                <!-- View All Appointments Card -->
                <div class="card-therapist mb-4">
                    <div class="card-header-therapist"><i class="fas fa-calendar"></i> View All Appointments</div>
                    <div class="card-body-therapist">
                            <p>See all your upcoming and past appointments.</p>
                        <a href="appointments.php" class="btn-therapist btn-therapist-secondary">View Appointments</a>
                    </div>
                </div>
                <!-- Messages Card -->
                <div class="card-therapist mb-4">
                    <div class="card-header-therapist"><i class="fas fa-envelope"></i> Messages</div>
                    <div class="card-body-therapist">
                            <p>See all your messages.</p>
                        <a href="messages.php" class="btn-therapist btn-therapist-secondary">Go to Messages</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.toggle-details').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const detailsRow = document.getElementById(targetId);
                if (detailsRow.style.display === 'none' || detailsRow.style.display === '') {
                    detailsRow.style.display = 'table-row';
                    this.innerHTML = '<i class="fas fa-eye-slash me-1"></i> Hide Details';
                } else {
                    detailsRow.style.display = 'none';
                    this.innerHTML = '<i class="fas fa-eye me-1"></i> Details';
                }
            });
        });
    });
    </script>
</body>
</html>