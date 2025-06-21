<?php
session_start();

// Check if user is logged in and is a therapist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Schedule Management';
$error = '';
$success = '';

// Create therapist_availability table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS therapist_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    therapist_id INT NOT NULL,
    day_of_week VARCHAR(10) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (therapist_id) REFERENCES users(user_id),
    CONSTRAINT no_overlap_slots UNIQUE (therapist_id, day_of_week, start_time, end_time)
)";

if (!$conn->query($create_table_sql)) {
    die("Error creating therapist availability table: " . $conn->error);
}

// Handle form submission for adding/updating availability
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $day_of_week = isset($_POST['day_of_week']) ? $_POST['day_of_week'] : null;
        $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : null;
        $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : null;
        
        switch ($action) {
            case 'add_availability':
                if ($day_of_week && $start_time && $end_time) {
                    // Validate time range
                    if ($start_time >= $end_time) {
                        $error = "End time must be after start time";
                        break;
                    }
                    
                    // Check for overlapping availability
                    $stmt = $conn->prepare("SELECT id FROM therapist_availability 
                                          WHERE therapist_id = ? AND day_of_week = ? 
                                          AND ((start_time <= ? AND end_time >= ?) OR 
                                               (start_time >= ? AND start_time <= ?))");
                    $stmt->bind_param("isssss", $_SESSION['user_id'], $day_of_week, 
                                     $end_time, $start_time, $start_time, $end_time);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $error = "You already have availability that overlaps with this time slot.";
                    } else {
                        $stmt = $conn->prepare("INSERT INTO therapist_availability 
                                              (therapist_id, day_of_week, start_time, end_time) 
                                              VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("isss", $_SESSION['user_id'], $day_of_week, $start_time, $end_time);
                        if ($stmt->execute()) {
                            $success = "Availability added successfully!";
                        } else {
                            $error = "Error adding availability: " . $conn->error;
                        }
                    }
                    $stmt->close();
                }
                break;
                
            case 'remove_availability':
                $availability_id = isset($_POST['availability_id']) ? intval($_POST['availability_id']) : null;
                if ($availability_id) {
                    $stmt = $conn->prepare("DELETE FROM therapist_availability 
                                          WHERE id = ? AND therapist_id = ?");
                    $stmt->bind_param("ii", $availability_id, $_SESSION['user_id']);
                    if ($stmt->execute()) {
                        $success = "Availability removed successfully!";
                    } else {
                        $error = "Error removing availability: " . $conn->error;
                    }
                    $stmt->close();
                }
                break;
        }
    }
}

// Get therapist's current availability
$availability = [];
$stmt = $conn->prepare("SELECT id, day_of_week, start_time, end_time 
                       FROM therapist_availability 
                       WHERE therapist_id = ? 
                       ORDER BY 
                           CASE day_of_week
                               WHEN 'Monday' THEN 1
                               WHEN 'Tuesday' THEN 2
                               WHEN 'Wednesday' THEN 3
                               WHEN 'Thursday' THEN 4
                               WHEN 'Friday' THEN 5
                               WHEN 'Saturday' THEN 6
                               WHEN 'Sunday' THEN 7
                           END, start_time");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $availability[] = $row;
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
                <li><a class="sidebar-link <?php if($current_page == 'messages.php') echo 'active'; ?>" href="messages.php"><i class="fas fa-envelope"></i>Messages<?php if ($unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="mt-3"><a class="sidebar-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="therapist-main">
            <div class="therapist-header" style="margin-bottom: 24px;">
                <h1 style="font-size: 2rem; color: #333;">Schedule Management</h1>
            </div>
            <?php if ($error): ?>
                <div class="alert-therapist alert-danger-therapist"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-therapist alert-info-therapist"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <div class="card-therapist" style="margin-bottom: 24px;">
                <div class="card-header-therapist"><i class="fas fa-calendar-plus"></i> Set Your Availability</div>
                <div class="card-body-therapist">
                    <form method="post" id="availabilityForm" class="profile-form-custom">
                        <input type="hidden" name="action" value="add_availability">
                        <div class="form-row-custom">
                            <div class="form-group-custom" style="flex:1;min-width:120px;">
                                <label for="day_of_week" class="form-label">Day of Week</label>
                                <select class="form-control" id="day_of_week" name="day_of_week" required>
                                    <option value="">Select Day</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>
                            <div class="form-group-custom" style="flex:1;min-width:120px;">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required min="08:00" max="20:00" step="900">
                            </div>
                            <div class="form-group-custom" style="flex:1;min-width:120px;">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required min="08:00" max="21:00" step="900">
                            </div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;margin-top:12px;">
                            <button type="submit" class="btn-therapist"><i class="fas fa-plus me-2"></i>Add Availability</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-therapist">
                <div class="card-header-therapist"><i class="fas fa-calendar-alt"></i> Your Current Availability <span style="font-size:0.95rem;color:#888;font-weight:400;margin-left:8px;">(24-hour format)</span></div>
                <div class="card-body-therapist">
                    <?php if (empty($availability)): ?>
                        <div class="alert-therapist alert-info-therapist">You haven't set any availability yet. Add your available time slots above.</div>
                    <?php else: ?>
                        <?php 
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        foreach ($days as $day): 
                            $day_availability = array_filter($availability, function($slot) use ($day) {
                                return $slot['day_of_week'] === $day;
                            });
                        ?>
                        <div style="margin-bottom: 24px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                                <span style="font-weight:600;font-size:1.08rem;"><?php echo $day; ?></span>
                                <span class="card-badge-therapist card-badge-primary"><?php echo count($day_availability); ?> slot(s)</span>
                            </div>
                            <?php if (empty($day_availability)): ?>
                                <div style="color:#6c757d;font-style:italic;padding:8px 0;">No availability set for this day</div>
                            <?php else: ?>
                                <?php foreach ($day_availability as $slot): ?>
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 15px;background:#f8f9fa;border-radius:6px;margin-bottom:8px;">
                                    <span style="font-family:monospace;font-size:1.08rem;">
                                        <?php echo date('H:i', strtotime($slot['start_time'])); ?> - 
                                        <?php echo date('H:i', strtotime($slot['end_time'])); ?>
                                    </span>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="remove_availability">
                                        <input type="hidden" name="availability_id" value="<?php echo $slot['id']; ?>">
                                        <button type="submit" class="btn-therapist btn-therapist-danger btn-sm-therapist"><i class="fas fa-trash-alt"></i> Remove</button>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Form validation
        document.getElementById('availabilityForm').addEventListener('submit', function(e) {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            if (startTime && endTime) {
                const start = new Date(`2000-01-01T${startTime}`);
                const end = new Date(`2000-01-01T${endTime}`);
                if (start >= end) {
                    alert('End time must be after start time');
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>