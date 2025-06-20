<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit();
}

// Database connection
include('C:/xampp/htdocs/green2/dbconnect.php');

$page_title = 'My Appointments';
$error = '';
$success = '';

// Handle appointment cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointment_id = intval($_POST['appointment_id']);
    
    // Verify the appointment belongs to the current client
    $stmt = $conn->prepare("SELECT status FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        
        // Only allow cancellation if status is pending or confirmed
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

// Get client profile for sidebar
$profile = null;
$profile_stmt = $conn->prepare("SELECT * FROM client_profiles WHERE user_id = ?");
$profile_stmt->bind_param("i", $_SESSION['user_id']);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$profile = $profile_result->fetch_assoc();
$profile_stmt->close();

// Get client appointments
$appointments = [];
$sql = "
    SELECT 
        a.id as appointment_id,
        a.appointment_date,
        a.start_time,
        a.end_time,
        a.status,
        a.notes,
        s.name as service_name,
        s.price as service_price,
        s.description as service_description,
        u.username as therapist_name,
        u.email as therapist_email,
        t.specialization as therapist_specialization
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN users u ON a.therapist_id = u.user_id
    LEFT JOIN therapists t ON u.user_id = t.user_id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC, a.start_time DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();

// Get unread messages count for sidebar
$unread_count = 0;
$msg_stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = FALSE");
$msg_stmt->bind_param("i", $_SESSION['user_id']);
$msg_stmt->execute();
$msg_result = $msg_stmt->get_result();
$unread_count = $msg_result->fetch_assoc()['count'];
$msg_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
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
                <div class="profile-image-container mb-3">
                    <?php if ($profile && !empty($profile['profile_picture'])): ?>
                        <img src="../<?php echo htmlspecialchars($profile['profile_picture']); ?>" class="profile-picture" alt="Profile Picture">
                    <?php else: ?>
                        <img src="../assets/images/default-profile.jpg" class="profile-picture" alt="Profile Picture">
                    <?php endif; ?>
                    <div class="profile-image-overlay"><i class="fas fa-camera fa-2x"></i></div>
                </div>
                <h5><?php echo htmlspecialchars($profile['full_name'] ?? $_SESSION['username']); ?></h5>
                <small>Client Account</small>
            </div>
            <ul class="sidebar-nav">
                <li><a class="sidebar-link" href="client_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                <li><a class="sidebar-link" href="client_profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                <li><a class="sidebar-link" href="client_therapists.php"><i class="fas fa-user-md me-2"></i>Therapists</a></li>
                <li><a class="sidebar-link active" href="client_appointments.php"><i class="fas fa-calendar-check me-2"></i>Appointments</a></li>
                <li class="position-relative"><a class="sidebar-link" href="client_messages.php"><i class="fas fa-envelope me-2"></i>Messages<?php if ($unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="mt-3"><a class="sidebar-link text-primary" href="index.php"><i class="fas fa-arrow-left me-2"></i>Go Back</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="dashboard-main">
            <div class="dashboard-header">
                <h1>My Appointments</h1>
                <a href="book_appointment.php" class="btn-custom"><i class="fas fa-plus me-2"></i>New Appointment</a>
            </div>
            <?php if ($error): ?>
                <div class="alert-custom alert-danger-custom"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-custom alert-info-custom"><?php echo $success; ?></div>
            <?php endif; ?>
            <div class="row" style="display: flex; flex-wrap: wrap; gap: 16px;">
                <?php if (empty($appointments)): ?>
                    <div style="flex: 1 1 100%;">
                        <div class="alert-custom alert-info-custom">You don't have any appointments yet.</div>
                    </div>
                <?php else: ?>
                    <?php 
                    $subtotal = 0;
                    foreach ($appointments as $appointment): 
                        if ($appointment['status'] !== 'cancelled' && $appointment['status'] !== 'declined') {
                            $subtotal += $appointment['service_price'];
                        }
                    ?>
                    <div style="flex: 1 1 45%; min-width: 320px;">
                        <div class="card-custom appointment-card mb-4">
                            <div class="card-body-custom">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <h5 class="card-title-custom"><?php echo htmlspecialchars($appointment['service_name']); ?></h5>
                                        <h6 class="card-subtitle-custom">
                                            With <?php echo htmlspecialchars($appointment['therapist_name']); ?>
                                        </h6>
                                    </div>
                                    <span class="card-badge card-badge-<?php 
                                        switch($appointment['status']) {
                                            case 'confirmed': echo 'success'; break;
                                            case 'pending': echo 'warning'; break;
                                            case 'cancelled': echo 'danger'; break;
                                            case 'completed': echo 'info'; break;
                                            default: echo 'primary';
                                        }
                                    ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <p class="card-text mb-1"><i class="fas fa-calendar-day me-2"></i><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                                    <p class="card-text mb-1"><i class="fas fa-clock me-2"></i><?php echo date('g:i A', strtotime($appointment['start_time'])); ?> - <?php echo date('g:i A', strtotime($appointment['end_time'])); ?></p>
                                </div>
                                <div class="appointment-details" id="details-<?php echo $appointment['appointment_id']; ?>" style="display: none;">
                                    <h6>Service Details:</h6>
                                    <p><?php echo htmlspecialchars($appointment['service_description']); ?></p>
                                    <p><strong>Amount:</strong> $<?php echo number_format($appointment['service_price'], 2); ?></p>
                                    <h6 class="mt-3">Therapist Information:</h6>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($appointment['therapist_name']); ?><br><strong>Email:</strong> <?php echo htmlspecialchars($appointment['therapist_email']); ?><br><strong>Specialization:</strong> <?php echo htmlspecialchars($appointment['therapist_specialization'] ?? 'Not specified'); ?></p>
                                    <?php if (!empty($appointment['notes'])): ?>
                                        <h6 class="mt-3">Additional Notes:</h6>
                                        <p><?php echo htmlspecialchars($appointment['notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="appointment-actions" style="display: flex; gap: 10px; margin-top: 15px;">
                                    <button class="btn-custom btn-custom-secondary toggle-details-custom" data-target="details-<?php echo $appointment['appointment_id']; ?>"><i class="fas fa-eye me-1"></i> Details</button>
                                    <?php if ($appointment['status'] === 'pending' || $appointment['status'] === 'confirmed'): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                            <button type="submit" name="cancel_appointment" class="btn-custom btn-custom-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')"><i class="fas fa-times me-1"></i> Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($appointment['status'] === 'pending'): ?>
                                        <a href="reschedule_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn-custom btn-custom-warning"><i class="fas fa-calendar-alt me-1"></i> Reschedule</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="alert-custom alert-info-custom text-end mt-4"><strong>Subtotal for all appointments:</strong> $<?php echo number_format($subtotal, 2); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // Toggle appointment details visibility
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