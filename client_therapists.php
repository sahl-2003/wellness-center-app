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

$page_title = 'Our Therapists';
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

// Get all therapists with their info
$therapists = [];
$sql = "
    SELECT 
        t.therapist_id,
        u.username,
        u.user_id,
        u.email,
        t.specialization as specialties,
        t.qualifications,
        t.bio,
        t.profile_picture as profile_pic
    FROM therapists t
    JOIN users u ON t.user_id = u.user_id
    WHERE u.role = 'therapist'
";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $therapists[] = $row;
    }
} else {
    $error = "Error loading therapists: " . $conn->error;
}

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
    <link rel="stylesheet" href="client_dashboard_custom.css ">
    <link rel="stylesheet" href="common.css ">
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
                <li><a class="sidebar-link active" href="client_therapists.php"><i class="fas fa-user-md me-2"></i>Therapists</a></li>
                <li><a class="sidebar-link" href="client_appointments.php"><i class="fas fa-calendar-check me-2"></i>Appointments</a></li>
                <li class="position-relative"><a class="sidebar-link" href="client_messages.php"><i class="fas fa-envelope me-2"></i>Messages<?php if ($unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="mt-3"><a class="sidebar-link text-primary" href="index.php"><i class="fas fa-arrow-left me-2"></i>Go Back</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="dashboard-main">
            <div class="dashboard-header">
                <h1>Our Therapists</h1>
                <div class="toolbar">
                    <div class="input-group" style="display: flex; gap: 8px;">
                        <input type="text" class="form-control" placeholder="Search therapists..." id="therapistSearch" style="flex: 1;">
                        <button class="btn-custom btn-custom-secondary" type="button"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
            <?php if ($error): ?>
                <div class="alert-custom alert-danger-custom"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="row" id="therapistContainer" style="display: flex; flex-wrap: wrap; gap: 16px;">
                <?php foreach ($therapists as $therapist): ?>
                <div style="flex: 1 1 30%; min-width: 260px;" class="therapist-item">
                    <div class="card-custom h-100 therapist-card">
                        <div class="card-body-custom text-center">
                            <!-- Profile Picture -->
                            <div class="mb-3">
                                <?php
                                $pic = $therapist['profile_pic'];
                                if (!empty($pic)) {
                                    $pic = ltrim($pic, '/');
                                    if (strpos($pic, 'uploads/profiles/') === 0) {
                                        $pic = './' . $pic;
                                    } else if (strpos($pic, './uploads/profiles/') !== 0) {
                                        $pic = './uploads/profiles/' . basename($pic);
                                    }
                                    $imgPath = htmlspecialchars($pic);
                                } else {
                                    $imgPath = '../assets/images/default-profile.jpg';
                                }
                                ?>
                                <img src="<?php echo $imgPath; ?>" class="therapist-img" alt="Therapist Profile Picture">
                            </div>
                            <h5 class="card-title-custom"><?php echo htmlspecialchars($therapist['username']); ?></h5>
                            <p class="text-muted mb-2">Licensed Therapist</p>
                            <?php if (!empty($therapist['specialties'])): ?>
                                <div class="mb-3">
                                    <?php 
                                    $specialties = explode(', ', $therapist['specialties']);
                                    foreach ($specialties as $specialty): 
                                    ?>
                                    <span class="card-badge card-badge-primary specialty-badge"><?php echo htmlspecialchars(trim($specialty)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 12px;">
                                <button class="toggle-details-custom btn-custom btn-custom-secondary" data-target="details-<?php echo $therapist['therapist_id']; ?>"><i class="fas fa-eye me-1"></i> Details</button>
                                <a href="book_appointment.php?therapist_id=<?php echo $therapist['therapist_id']; ?>" class="btn-custom btn-custom-success"><i class="fas fa-calendar-plus me-1"></i> Book Now</a>
                            </div>
                            <div class="therapist-details mt-3" id="details-<?php echo $therapist['therapist_id']; ?>" style="display:none;">
                                <h6>Name: <?php echo htmlspecialchars($therapist['username']); ?></h6>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($therapist['email']); ?></p>
                                <?php if (!empty($therapist['specialties'])): ?>
                                    <p><strong>Specialties:</strong> <?php echo htmlspecialchars($therapist['specialties']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($therapist['qualifications'])): ?>
                                    <p><strong>Qualifications:</strong> <?php echo htmlspecialchars($therapist['qualifications']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($therapist['bio'])): ?>
                                    <p><strong>Bio:</strong> <?php echo htmlspecialchars($therapist['bio']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
        // Therapist search functionality
        document.getElementById('therapistSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const therapistItems = document.querySelectorAll('.therapist-item');
            therapistItems.forEach(item => {
                const therapistName = item.querySelector('.card-title-custom').textContent.toLowerCase();
                const specialties = item.querySelectorAll('.specialty-badge');
                let found = therapistName.includes(searchTerm);
                if (!found) {
                    specialties.forEach(badge => {
                        if (badge.textContent.toLowerCase().includes(searchTerm)) {
                            found = true;
                        }
                    });
                }
                item.style.display = found ? 'block' : 'none';
            });
        });
        document.querySelectorAll('.toggle-details-custom').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = document.getElementById(this.getAttribute('data-target'));
                if (target.style.display === 'none' || target.style.display === '') {
                    target.style.display = 'block';
                } else {
                    target.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>