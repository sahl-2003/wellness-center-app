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

// Handle appointment rescheduling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reschedule_appointment'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $new_date = $_POST['new_date'];
    $new_start_time = $_POST['new_start_time'];
    $new_end_time = $_POST['new_end_time'];
    
    // Verify the appointment belongs to the current client and is pending
    $stmt = $conn->prepare("SELECT status, service_id, therapist_id FROM appointments WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        
        // Check for conflicts with other appointments
        $conflict_stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM appointments 
            WHERE therapist_id = ? 
            AND appointment_date = ? 
            AND status != 'cancelled'
            AND id != ?
            AND (
                (start_time <= ? AND end_time > ?) OR
                (start_time < ? AND end_time >= ?) OR
                (start_time >= ? AND start_time < ?)
            )
        ");
        $conflict_stmt->bind_param("isissssss", 
            $appointment['therapist_id'], 
            $new_date, 
            $appointment_id,
            $new_start_time, 
            $new_start_time,
            $new_end_time, 
            $new_end_time,
            $new_start_time, 
            $new_end_time
        );
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();
        $conflict_count = $conflict_result->fetch_assoc()['count'];
        $conflict_stmt->close();
        
        if ($conflict_count > 0) {
            $error = 'This time slot is already booked. Please choose a different time.';
        } else {
            // Update the appointment
            $update_stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, start_time = ?, end_time = ?, status = 'pending' WHERE id = ? AND user_id = ?");
            $update_stmt->bind_param("sssii", $new_date, $new_start_time, $new_end_time, $appointment_id, $_SESSION['user_id']);
            
            if ($update_stmt->execute()) {
                $success = 'Appointment rescheduled successfully! It is now pending approval.';
            } else {
                $error = 'Error rescheduling appointment: ' . $conn->error;
            }
            $update_stmt->close();
        }
    } else {
        $error = 'Appointment not found or cannot be rescheduled.';
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
    <link rel="stylesheet" href="common.css">
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
                    <?php if ($profile && !empty($profile['profile_picture'])): ?>
                        <img src="../<?php echo htmlspecialchars($profile['profile_picture']); ?>" class="profile-picture" alt="Profile Picture">
                    <?php else: ?>
                        <img src="../assets/images/default-profile.jpg" class="profile-picture" alt="Profile Picture">
                    <?php endif; ?>
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
                                    <p><strong>Amount:</strong> RS <?php echo number_format($appointment['service_price'], 2); ?></p>
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
                                        <button class="btn-custom btn-custom-warning reschedule-btn" 
                                            data-appointment-id="<?php echo $appointment['appointment_id']; ?>"
                                            data-current-date="<?php echo htmlspecialchars(date('Y-m-d', strtotime($appointment['appointment_date']))); ?>"
                                            data-current-start="<?php echo htmlspecialchars(date('H:i', strtotime($appointment['start_time']))); ?>"
                                            data-current-end="<?php echo htmlspecialchars(date('H:i', strtotime($appointment['end_time']))); ?>">
                                            <i class="fas fa-calendar-alt me-1"></i> Reschedule
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($appointment['status'] === 'cancelled'): ?>
                                        <a href="book_appointment.php?rebook=<?php echo $appointment['appointment_id']; ?>" class="btn-custom btn-custom-success"><i class="fas fa-calendar-plus me-1"></i> Rebook</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="alert-custom alert-info-custom text-end mt-4"><strong>Subtotal for all appointments:</strong> RS <?php echo number_format($subtotal, 2); ?></div>
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

    <!-- Reschedule Modal Popup -->
    <div id="reschedule-modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:1000;"></div>
    <div id="reschedule-modal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,0.18);padding:32px 28px;z-index:1001;min-width:320px;max-width:95vw;">
        <button id="close-reschedule-modal" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:1.5rem;color:#888;cursor:pointer;">&times;</button>
        <h2 style="margin-top:0;font-size:1.3rem;margin-bottom:18px;color:#333;">Reschedule Appointment</h2>
        <form method="post" style="display:flex;flex-direction:column;gap:14px;min-width:220px;">
            <input type="hidden" name="appointment_id" id="reschedule-appointment-id" value="">
            <input type="hidden" name="reschedule_appointment" value="1">
            <label style="font-weight:500;">New Date
                <input type="date" name="new_date" required style="margin-top:4px;padding:7px 10px;border-radius:6px;border:1px solid #ccc;width:100%;">
            </label>
            <label style="font-weight:500;">New Start Time
                <input type="time" name="new_start_time" required style="margin-top:4px;padding:7px 10px;border-radius:6px;border:1px solid #ccc;width:100%;">
            </label>
            <label style="font-weight:500;">New End Time
                <input type="time" name="new_end_time" required style="margin-top:4px;padding:7px 10px;border-radius:6px;border:1px solid #ccc;width:100%;">
            </label>
            <button type="submit" class="btn-custom btn-custom-warning" style="margin-top:10px;">Submit</button>
        </form>
    </div>

    <script>
        // Reschedule modal logic
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('reschedule-modal');
            const overlay = document.getElementById('reschedule-modal-overlay');
            let currentAppointmentId = null;

            document.querySelectorAll('.reschedule-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentAppointmentId = this.getAttribute('data-appointment-id');
                    document.getElementById('reschedule-appointment-id').value = currentAppointmentId;
                    // Set current values
                    document.querySelector('#reschedule-modal input[name="new_date"]').value = this.getAttribute('data-current-date');
                    document.querySelector('#reschedule-modal input[name="new_start_time"]').value = this.getAttribute('data-current-start');
                    document.querySelector('#reschedule-modal input[name="new_end_time"]').value = this.getAttribute('data-current-end');
                    modal.style.display = 'block';
                    overlay.style.display = 'block';
                });
            });

            document.getElementById('close-reschedule-modal').onclick = function() {
                modal.style.display = 'none';
                overlay.style.display = 'none';
            };

            overlay.onclick = function() {
                modal.style.display = 'none';
                overlay.style.display = 'none';
            };
        });
    </script>
</body>
</html>