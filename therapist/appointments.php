<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Appointment Management';
$error = '';
$success = '';

// Handle appointment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['appointment_id'])) {
        $appointment_id = intval($_POST['appointment_id']);
        $action = $_POST['action'];
        
        switch ($action) {
            case 'accept':
                $stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ? AND therapist_id = ?");
                $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    $success = 'Appointment confirmed successfully!';
                } else {
                    $error = 'Error confirming appointment: ' . $conn->error;
                }
                $stmt->close();
                break;
                
            case 'decline':
                // First check if the appointment exists and belongs to this therapist
                $check_stmt = $conn->prepare("SELECT id, status FROM appointments WHERE id = ? AND therapist_id = ?");
                $check_stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $appointment = $check_result->fetch_assoc();
                $check_stmt->close();
                
                if ($appointment) {
                    // Update the appointment status
                    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND therapist_id = ?");
                $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
                if ($stmt->execute()) {
                        $affected_rows = $stmt->affected_rows;
                        if ($affected_rows > 0) {
                            $success = 'Appointment cancelled successfully!';
                        } else {
                            $error = 'No changes made. Appointment may already be cancelled.';
                        }
                    } else {
                        $error = 'Error cancelling appointment: ' . $conn->error;
                    }
                    $stmt->close();
                } else {
                    $error = 'Appointment not found or you do not have permission to cancel it.';
                }
                break;
                
            case 'reschedule':
                $new_date = $_POST['new_date'];
                $new_start_time = $_POST['new_start_time'];
                $new_end_time = $_POST['new_end_time'];
                $datetime = $new_date . ' ' . $new_start_time;
                $stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, start_time = ?, end_time = ?, status = 'rescheduled' WHERE id = ? AND therapist_id = ?");
                $stmt->bind_param("sssii", $datetime, $new_start_time, $new_end_time, $appointment_id, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    $success = 'Appointment rescheduled successfully!';
                } else {
                    $error = 'Error rescheduling appointment: ' . $conn->error;
                }
                $stmt->close();
                break;
        }
    }
}

// Get therapist's appointments (updated to use user_id instead of client_id)
$appointments = [];
$stmt = $conn->prepare("SELECT a.id, a.user_id as client_id, a.therapist_id, a.appointment_date, a.start_time, a.end_time, a.status, a.notes, u.username AS client_name, u.email AS client_email, s.name AS service_name, cp.profile_picture AS client_profile_picture FROM appointments a JOIN users u ON a.user_id = u.user_id JOIN services s ON a.service_id = s.service_id LEFT JOIN client_profiles cp ON u.user_id = cp.user_id WHERE a.therapist_id = ? ORDER BY a.appointment_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
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
                <li class="mt-3"><a class="sidebar-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="therapist-main">
            <div class="therapist-header" style="margin-bottom: 24px;">
                <h1 style="font-size: 2rem; color: #333;">Appointment Management</h1>
            </div>
            <?php if ($error): ?>
                <div class="alert-therapist alert-danger-therapist"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-therapist alert-info-therapist"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="dashboard-stats-grid" style="flex-wrap: wrap; gap: 24px;">
                    <?php if (empty($appointments)): ?>
                    <div class="card-therapist" style="width:100%;"><div class="card-body-therapist text-center">No appointments found</div></div>
                    <?php else: ?>
                            <?php foreach ($appointments as $appointment): 
                        $appt_date = date('M j, Y', strtotime($appointment['appointment_date']));
                        $appt_time = date('g:i A', strtotime($appointment['start_time']));
                        $appt_end_time = isset($appointment['end_time']) ? date('g:i A', strtotime($appointment['end_time'])) : '';
                                $status_class = 'status-' . strtolower($appointment['status']);
                    ?>
                    <div class="card-therapist <?php echo $status_class; ?>" style="min-width:300px;max-width:420px;flex:1 1 320px;">
                        <div class="card-body-therapist">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div class="profile-image-container" style="width:48px;height:48px;min-width:48px;">
                                        <img src="<?php 
                                            $pic = $appointment['client_profile_picture'];
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
                                        ?>" class="profile-picture" alt="Client Avatar" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
                                    </div>
                                                <div>
                                        <h4 style="margin:0;font-size:1.08rem;font-weight:600;line-height:1.2;"><?php echo htmlspecialchars($appointment['client_name']); ?></h4>
                                        <div style="color:#888;font-size:0.97rem;"> <?php echo htmlspecialchars($appointment['client_email']); ?></div>
                                                </div>
                                            </div>
                                <span class="card-badge-therapist <?php 
                                    echo $appointment['status'] === 'pending' ? 'card-badge-warning' : 
                                         ($appointment['status'] === 'confirmed' ? 'card-badge-success' : 
                                         ($appointment['status'] === 'cancelled' ? 'card-badge-danger' : 'card-badge-info')); 
                                ?>">
                                                    <?php echo htmlspecialchars(ucfirst($appointment['status'])); ?>
                                                </span>
                                            </div>
                            <div style="margin-bottom:12px;">
                                <div><strong>Date:</strong> <?php echo $appt_date; ?></div>
                                <div><strong>Time:</strong> <?php echo $appt_time; ?><?php if ($appt_end_time) echo ' - ' . $appt_end_time; ?></div>
                                            <?php if (!empty($appointment['notes'])): ?>
                                    <div><strong>Notes:</strong> <?php echo htmlspecialchars($appointment['notes']); ?></div>
                                            <?php endif; ?>
                                        </div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px;">
                                <?php if ($appointment['status'] === 'cancelled'): ?>
                                    <button class="btn-therapist btn-therapist-danger" disabled>
                                        <i class="fas fa-times me-1"></i> Cancelled
                                    </button>
                                    <button class="btn-therapist btn-therapist-success" disabled><i class="fas fa-check me-1"></i> Confirm</button>
                                    <button class="btn-therapist btn-therapist-info" disabled><i class="fas fa-clock me-1"></i> Reschedule</button>
                                    <button class="btn-therapist btn-therapist-info" disabled><i class="fas fa-eye me-1"></i> Details</button>
                                <?php elseif (!in_array($appointment['status'], ['confirmed', 'cancelled', 'completed'])): ?>
                                    <form method="post" style="display:inline;">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                    <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn-therapist btn-therapist-success">
                                            <i class="fas fa-check me-1"></i> Confirm
                                                    </button>
                                                </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <input type="hidden" name="action" value="decline">
                                        <button type="submit" class="btn-therapist btn-therapist-danger">
                                            <i class="fas fa-times me-1"></i> Cancel
                                                </button>
                                    </form>
                                    <button class="btn-therapist btn-therapist-info reschedule-btn" 
                                        data-appointment-id="<?php echo $appointment['id']; ?>"
                                        data-current-date="<?php echo htmlspecialchars(date('Y-m-d', strtotime($appointment['appointment_date']))); ?>"
                                        data-current-start="<?php echo htmlspecialchars(date('H:i', strtotime($appointment['start_time']))); ?>"
                                        data-current-end="<?php echo htmlspecialchars(date('H:i', strtotime($appointment['end_time']))); ?>">
                                                    <i class="fas fa-clock me-1"></i> Reschedule
                                                </button>
                                    <button class="btn-therapist btn-therapist-info toggle-details-therapist" data-target="details-<?php echo $appointment['id']; ?>">
                                        <i class="fas fa-eye me-1"></i> Details
                                                    </button>
                                <?php elseif ($appointment['status'] === 'confirmed' || $appointment['status'] === 'rescheduled'): ?>
                                    <button class="btn-therapist btn-therapist-success" disabled>
                                        <i class="fas fa-check me-1"></i> <?php echo ucfirst($appointment['status']); ?>
                                                    </button>
                                    <button class="btn-therapist btn-therapist-info reschedule-btn" 
                                        data-appointment-id="<?php echo $appointment['id']; ?>"
                                        data-current-date="<?php echo htmlspecialchars(date('Y-m-d', strtotime($appointment['appointment_date']))); ?>"
                                        data-current-start="<?php echo htmlspecialchars(date('H:i', strtotime($appointment['start_time']))); ?>"
                                        data-current-end="<?php echo htmlspecialchars(date('H:i', strtotime($appointment['end_time']))); ?>">
                                                        <i class="fas fa-clock me-1"></i> Reschedule
                                                    </button>
                                    <button class="btn-therapist btn-therapist-info toggle-details-therapist" data-target="details-<?php echo $appointment['id']; ?>">
                                        <i class="fas fa-eye me-1"></i> Details
                                    </button>
                                            <?php endif; ?>
                                        </div>
                            <div class="appointment-details mt-2" id="details-<?php echo $appointment['id']; ?>" style="display:none;">
                                <div><strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_name']); ?></div>
                                <div><strong>Client:</strong> <?php echo htmlspecialchars($appointment['client_name']); ?></div>
                                <?php if (!empty($appointment['notes'])): ?>
                                    <div><strong>Notes:</strong> <?php echo htmlspecialchars($appointment['notes']); ?></div>
                                <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                    <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // Toggle appointment details
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

            // Reschedule modal logic
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

    <!-- Reschedule Modal Popup -->
    <div id="reschedule-modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:1000;"></div>
    <div id="reschedule-modal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,0.18);padding:32px 28px;z-index:1001;min-width:320px;max-width:95vw;">
        <button id="close-reschedule-modal" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:1.5rem;color:#888;cursor:pointer;">&times;</button>
        <h2 style="margin-top:0;font-size:1.3rem;margin-bottom:18px;color:#333;">Reschedule Appointment</h2>
        <form method="post" style="display:flex;flex-direction:column;gap:14px;min-width:220px;">
            <input type="hidden" name="appointment_id" id="reschedule-appointment-id" value="">
            <input type="hidden" name="action" value="reschedule">
            <label style="font-weight:500;">New Date
                <input type="date" name="new_date" required style="margin-top:4px;padding:7px 10px;border-radius:6px;border:1px solid #ccc;width:100%;">
            </label>
            <label style="font-weight:500;">New Start Time
                <input type="time" name="new_start_time" required style="margin-top:4px;padding:7px 10px;border-radius:6px;border:1px solid #ccc;width:100%;">
            </label>
            <label style="font-weight:500;">New End Time
                <input type="time" name="new_end_time" required style="margin-top:4px;padding:7px 10px;border-radius:6px;border:1px solid #ccc;width:100%;">
            </label>
            <button type="submit" class="btn-therapist btn-therapist-info" style="margin-top:10px;">Submit</button>
        </form>
    </div>
</body>
</html>