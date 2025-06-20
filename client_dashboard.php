<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.html");
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

$page_title = 'Client Dashboard';
$error = '';
$success = '';

// Get client profile with JOIN to client_profiles
$profile = null;
$stmt = $conn->prepare("
    SELECT u.*, cp.* 
    FROM users u
    LEFT JOIN client_profiles cp ON u.user_id = cp.user_id
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
    // Get upcoming appointments (changed client_id to user_id)
    $upcoming_appointments = [];
    $subtotal = 0;
    $stmt = $conn->prepare("
        SELECT 
            a.id as appointment_id,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.status,
            a.notes,
            s.name as service_name,
            s.price as service_price,
            u.username as therapist_name
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        JOIN users u ON a.therapist_id = u.user_id
        WHERE a.user_id = ? AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date, a.start_time
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $upcoming_appointments[] = $row;
            if ($row['status'] !== 'cancelled' && $row['status'] !== 'declined') {
                $subtotal += $row['service_price'];
            }
        }
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

// Handle appointment cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $stmt = $conn->prepare("SELECT status FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        if ($appointment['status'] === 'pending' || $appointment['status'] === 'confirmed') {
            $update_stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
            $update_stmt->bind_param("i", $appointment_id);
            if ($update_stmt->execute()) {
                $success = 'Appointment cancelled successfully!';
            } else {
                $error = 'Error cancelling appointment: ' . $conn->error;
            }
            $update_stmt->close();
        } else {
            $error = 'Cannot cancel an appointment that is already cancelled or completed';
        }
    } else {
        $error = 'Appointment not found or you do not have permission to cancel it';
    }
    $stmt->close();
   
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="client_dashboard_custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Remove all previous embedded styles, as they are now in the CSS file */
    </style>
</head>
<body>
    <div class="dashboard-flex">
        <!-- Sidebar -->
        <div class="dashboard-sidebar-custom">
            <div class="text-center mb-4">
                <div class="mb-3">
                <?php if (!empty($profile['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($profile['profile_picture']); ?>" class="profile-picture" id="currentProfileImage" alt="Profile Picture">
                <?php else: ?>
                    <img src="assets/images/default-profile.jpg" class="profile-picture" id="currentProfileImage" alt="Profile Picture">
                <?php endif; ?>
                </div>
                <h5><?php echo htmlspecialchars($profile['full_name'] ?? $_SESSION['username']); ?></h5>
                <small>Client Account</small>
            </div>
            <ul class="sidebar-nav">
                <li><a class="sidebar-link active" href="client_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                <li><a class="sidebar-link" href="client_profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                <li><a class="sidebar-link" href="client_therapists.php"><i class="fas fa-user-md me-2"></i>Therapists</a></li>
                <li><a class="sidebar-link" href="client_appointments.php"><i class="fas fa-calendar-check me-2"></i>Appointments</a></li>
                <li class="position-relative">
                    <a class="sidebar-link" href="client_messages.php">
                        <i class="fas fa-envelope me-2"></i>Messages
                        <?php if ($unread_count > 0): ?>
                            <span class="sidebar-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="mt-3">
                    <a class="sidebar-link text-primary" href="index.php"><i class="fas fa-arrow-left me-2"></i>Go Back</a>
                </li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="dashboard-main">
            <div class="dashboard-header">
                <h1>Client Dashboard</h1>
                <div class="toolbar">
                    <a href="book_appointment.php" class="btn-custom"><i class="fas fa-plus me-2"></i>Book Appointment</a>
                </div>
            </div>
            <?php if ($error): ?>
                <div class="alert-custom alert-danger-custom"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <!-- Welcome Section -->
            <div class="alert-custom alert-info-custom">
                <h4>Welcome, <?php echo htmlspecialchars($profile['full_name'] ?? $_SESSION['username']); ?>!</h4>
                <p>This is your personal dashboard where you can manage your therapy journey.</p>
            </div>
            <!-- Profile Completion Alert -->
            <?php if (!$profile || empty($profile['full_name'])): ?>
                <div class="alert-custom alert-warning-custom">
                    <h5>Complete Your Profile</h5>
                    <p>Please complete your profile to get the best therapy experience.</p>
                    <a href="client_profile.php" class="btn-custom btn-custom-warning">Complete Profile</a>
                </div>
            <?php endif; ?>
            <!-- Upcoming Appointments -->
            <div class="card-custom mb-4">
                <div class="card-header-custom">
                    <h5 class="mb-0">Upcoming Appointments</h5>
                </div>
                <div class="card-body-custom">
                    <?php if (empty($upcoming_appointments)): ?>
                        <div class="alert-custom alert-info-custom">You don't have any upcoming appointments.</div>
                    <?php else: ?>
                        <div class="row" style="display: flex; flex-wrap: wrap; gap: 16px;">
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                            <div style="flex: 1 1 45%; min-width: 320px;">
                                <div class="card-custom appointment-card">
                                    <div class="card-body-custom">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <h5 class="card-title-custom"><?php echo htmlspecialchars($appointment['service_name']); ?></h5>
                                            <span class="card-badge card-badge-<?php 
                                                switch($appointment['status']) {
                                                    case 'confirmed': echo 'success'; break;
                                                    case 'pending': echo 'warning'; break;
                                                    case 'cancelled': echo 'danger'; break;
                                                    case 'completed': echo 'info'; break;
                                                    default: echo 'primary';
                                                }
                                            ?>"><?php echo ucfirst($appointment['status']); ?></span>
                                        </div>
                                        <h6 class="card-subtitle-custom">
                                            With <?php echo htmlspecialchars($appointment['therapist_name']); ?>
                                        </h6>
                                        <p style="margin-bottom: 12px;">
                                            <i class="fas fa-calendar-day me-2"></i>
                                            <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?><br>
                                            <i class="fas fa-clock me-2"></i>
                                            <?php echo date('g:i A', strtotime($appointment['start_time'])); ?> - 
                                            <?php echo date('g:i A', strtotime($appointment['end_time'])); ?>
                                        </p>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <button class="toggle-details-custom" data-target="details-<?php echo $appointment['appointment_id']; ?>">
                                                <i class="fas fa-eye me-1"></i> Details
                                            </button>
                                            <?php if ($appointment['status'] === 'pending' || $appointment['status'] === 'confirmed'): ?>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                    <button type="submit" name="cancel_appointment" class="btn-custom btn-custom-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                        <i class="fas fa-times me-1"></i> Cancel
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        <div class="appointment-details mt-3" id="details-<?php echo $appointment['appointment_id']; ?>" style="display:none;">
                                            <h6>Service:</h6>
                                            <p><?php echo htmlspecialchars($appointment['service_name']); ?></p>
                                            <h6>Therapist:</h6>
                                            <p><?php echo htmlspecialchars($appointment['therapist_name']); ?></p>
                                            <?php if (!empty($appointment['notes'])): ?>
                                                <h6>Notes:</h6>
                                                <p><?php echo htmlspecialchars($appointment['notes']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($upcoming_appointments)): ?>
                            <div class="alert-custom alert-info-custom text-end mt-4">
                                <strong>Subtotal for upcoming appointments:</strong> $<?php echo number_format($subtotal, 2); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Quick Actions -->
            <div class="row" style="display: flex; flex-wrap: wrap; gap: 16px;">
                <div style="flex: 1 1 30%; min-width: 260px;">
                    <div class="card-custom h-100">
                        <div class="card-body-custom text-center">
                            <i class="fas fa-user-md fa-3x mb-3 text-primary"></i>
                            <h5>Find a Therapist</h5>
                            <p>Browse our qualified therapists and find the right one for you.</p>
                            <a href="client_therapists.php" class="btn-custom">Browse Therapists</a>
                        </div>
                    </div>
                </div>
                <div style="flex: 1 1 30%; min-width: 260px;">
                    <div class="card-custom h-100">
                        <div class="card-body-custom text-center">
                            <i class="fas fa-calendar-plus fa-3x mb-3 text-success"></i>
                            <h5>Book Appointment</h5>
                            <p>Schedule a new therapy session with your preferred therapist.</p>
                            <a href="book_appointment.php" class="btn-custom btn-custom-secondary">Book Now</a>
                        </div>
                    </div>
                </div>
                <div style="flex: 1 1 30%; min-width: 260px;">
                    <div class="card-custom h-100">
                        <div class="card-body-custom text-center">
                            <i class="fas fa-envelope fa-3x mb-3 text-info"></i>
                            <h5>Messages</h5>
                            <p>Communicate with your therapist and get support.</p>
                            <a href="client_messages.php" class="btn-custom btn-custom-secondary">View Messages</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.querySelectorAll('.toggle-details-custom').forEach(button => {
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
    </script>
</body>
</html>