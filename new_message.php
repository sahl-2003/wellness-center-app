<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit();
}

// Database connection
include('C:/xampp/htdocs/green2/dbconnect.php');

$page_title = 'New Message';
$error = '';
$success = '';

// Get client profile for sidebar
$profile = null;
$profile_stmt = $conn->prepare("SELECT * FROM client_profiles WHERE user_id = ?");
$profile_stmt->bind_param("i", $_SESSION['user_id']);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$profile = $profile_result->fetch_assoc();
$profile_stmt->close();

// Get therapists for recipient dropdown
$therapists = [];
$therapist_stmt = $conn->prepare("
    SELECT u.user_id, u.username 
    FROM users u
    JOIN therapists t ON u.user_id = t.user_id
    WHERE u.role = 'therapist'
    ORDER BY u.username
");
$therapist_stmt->execute();
$therapist_result = $therapist_stmt->get_result();
while ($row = $therapist_result->fetch_assoc()) {
    $therapists[] = $row;
}
$therapist_stmt->close();

// Get unread messages count for sidebar
$unread_count = 0;
$msg_stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = FALSE");
$msg_stmt->bind_param("i", $_SESSION['user_id']);
$msg_stmt->execute();
$msg_result = $msg_stmt->get_result();
$unread_count = $msg_result->fetch_assoc()['count'];
$msg_stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_id = $_POST['recipient_id'] ?? '';
    $content = trim($_POST['content'] ?? '');
    
    // Validate inputs
    if (empty($recipient_id)) {
        $error = 'Please select a recipient';
    } elseif (empty($content)) {
        $error = 'Message content is required';
    } else {
        // Insert message into database
        $insert_stmt = $conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        if ($insert_stmt) {
            $insert_stmt->bind_param("iis", $_SESSION['user_id'], $recipient_id, $content);
            
            if ($insert_stmt->execute()) {
                $success = 'Message sent successfully!';
                // Clear form field
                $content = '';
            } else {
                $error = 'Failed to send message: ' . $conn->error;
            }
            $insert_stmt->close();
        } else {
            $error = 'Database error: ' . $conn->error;
        }
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
    <link rel="stylesheet" href="client_dashboard_custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <img src="assets/images/default-profile.jpg" class="profile-picture" alt="Profile Picture">
                    <?php endif; ?>
                </div>
                <h5><?php echo htmlspecialchars($profile['full_name'] ?? $_SESSION['username']); ?></h5>
                <small>Client Account</small>
            </div>
            <ul class="sidebar-nav">
                <li><a class="sidebar-link" href="client_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                <li><a class="sidebar-link" href="client_profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                <li><a class="sidebar-link" href="client_therapists.php"><i class="fas fa-user-md me-2"></i>Therapists</a></li>
                <li><a class="sidebar-link" href="client_appointments.php"><i class="fas fa-calendar-check me-2"></i>Appointments</a></li>
                <li class="position-relative"><a class="sidebar-link active" href="client_messages.php"><i class="fas fa-envelope me-2"></i>Messages<?php if ($unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="spacing-top"><a class="sidebar-link text-primary" href="index.php"><i class="fas fa-arrow-left me-2"></i>Go Back</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="dashboard-main">
            <div class="dashboard-header">
                <h1>New Message</h1>
                <a href="client_messages.php" class="btn-custom btn-custom-secondary" style="max-width:200px;"><i class="fas fa-arrow-left me-2"></i>Back to Messages</a>
            </div>
            <?php if ($error): ?>
                <div class="alert-custom alert-danger-custom"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-custom alert-info-custom"><?php echo $success; ?></div>
            <?php endif; ?>
            <div class="card-custom" style="max-width:600px;margin:0 auto;">
                <div class="card-header-custom">Send a New Message</div>
                <div class="card-body-custom">
                    <form method="post" action="new_message.php">
                        <div style="margin-bottom:18px;">
                            <label for="recipient_id" style="display:block;font-weight:500;margin-bottom:6px;">To:</label>
                            <select id="recipient_id" name="recipient_id" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                                <option value="">Select Therapist</option>
                                <?php foreach ($therapists as $therapist): ?>
                                    <option value="<?php echo $therapist['user_id']; ?>" <?php if (isset($recipient_id) && $recipient_id == $therapist['user_id']) echo 'selected'; ?>><?php echo htmlspecialchars($therapist['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="content" style="display:block;font-weight:500;margin-bottom:6px;">Message</label>
                            <textarea id="content" name="content" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;min-height:100px;"><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                        </div>
                        <div style="margin-top:24px;">
                            <button type="submit" class="btn-custom" style="width:100%;max-width:220px;"><i class="fas fa-paper-plane me-2"></i>Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>