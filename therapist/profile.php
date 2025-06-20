<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Therapist Profile';
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialization = trim($_POST['specialization']);
    $qualifications = trim($_POST['qualifications']);
    $bio = trim($_POST['bio']);
    $therapist_id = $_SESSION['user_id'];
    
    // Handle password change
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $error = 'Passwords do not match!';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET pwd = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed_password, $therapist_id);
            if ($stmt->execute()) {
                $success = 'Password updated successfully!';
            } else {
                $error = 'Error updating password: ' . $conn->error;
            }
            $stmt->close();
        }
    }
    
    // Handle profile picture upload
    $profile_pic = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profiles/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = 'profile_' . $therapist_id . '_' . time() . '.' . $file_ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                $profile_pic = 'uploads/profiles/' . $new_filename;
            } else {
                $error = 'Failed to upload profile picture';
            }
        } else {
            $error = 'Invalid file type. Only JPG, JPEG, PNG, GIF allowed.';
        }
    }
    
    if (empty($error)) {
        // Check if therapist record exists
        $check_stmt = $conn->prepare("SELECT therapist_id FROM therapists WHERE user_id = ?");
        $check_stmt->bind_param("i", $therapist_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_stmt->close();
        
        if ($check_result->num_rows > 0) {
            // Update existing therapist record
            if ($profile_pic) {
                $stmt = $conn->prepare("UPDATE therapists SET specialization = ?, qualifications = ?, bio = ?, profile_picture = ? WHERE user_id = ?");
                $stmt->bind_param("ssssi", $specialization, $qualifications, $bio, $profile_pic, $therapist_id);
            } else {
                $stmt = $conn->prepare("UPDATE therapists SET specialization = ?, qualifications = ?, bio = ? WHERE user_id = ?");
                $stmt->bind_param("sssi", $specialization, $qualifications, $bio, $therapist_id);
            }
        } else {
            // Insert new therapist record
            if ($profile_pic) {
                $stmt = $conn->prepare("INSERT INTO therapists (user_id, specialization, qualifications, bio, profile_picture) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $therapist_id, $specialization, $qualifications, $bio, $profile_pic);
            } else {
                $stmt = $conn->prepare("INSERT INTO therapists (user_id, specialization, qualifications, bio) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $therapist_id, $specialization, $qualifications, $bio);
            }
        }
        
        if ($stmt->execute()) {
            $success = 'Profile updated successfully!';
            // Update session with new profile picture if changed
            if ($profile_pic) {
                $_SESSION['profile_picture'] = $profile_pic;
            }
        } else {
            $error = 'Error updating profile: ' . $conn->error;
        }
        $stmt->close();
    }
}

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
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="therapist_dashboard_custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Remove all previous embedded styles, as they are now in the CSS file */
    </style>
</head>
<body>
    <div class="therapist-flex">
        <!-- Sidebar -->
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <div class="therapist-sidebar-custom">
            <div class="text-center mb-4">
                <div class="profile-image-container mb-3">
                <?php if (!empty($therapist['profile_picture'])): ?>
                        <img src="../<?php echo htmlspecialchars($therapist['profile_picture']); ?>" class="profile-picture">
                <?php else: ?>
                        <div class="profile-picture bg-secondary d-flex align-items-center justify-content-center">
                        <i class="fas fa-user fa-3x text-white"></i>
                    </div>
                <?php endif; ?>
                    <div class="profile-image-overlay"><i class="fas fa-camera fa-2x"></i></div>
                </div>
                <h5><?php echo htmlspecialchars($therapist['username'] ?? $_SESSION['username']); ?></h5>
                <small><?php echo htmlspecialchars($therapist['email'] ?? $_SESSION['email']); ?></small>
                <?php if (!empty($therapist['specialization'])): ?>
                    <div class="mt-2 card-badge-therapist card-badge-primary"><?php echo htmlspecialchars($therapist['specialization']); ?></div>
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
                <h1 style="font-size:1.4rem;">My Profile</h1>
                <a href="dashboard.php" class="btn-therapist btn-therapist-secondary" style="font-size:0.98rem;padding:0.4rem 1rem;"><i class="fas fa-arrow-left me-2"></i>Back</a>
            </div>
            <?php if ($error): ?>
                <div class="alert-therapist alert-danger-therapist" style="font-size:0.98rem;padding:10px 14px;"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-therapist alert-info-therapist" style="font-size:0.98rem;padding:10px 14px;"><?php echo $success; ?></div>
            <?php endif; ?>
            <div class="profile-page-flex">
                <!-- Profile Picture Container -->
                <div class="card-therapist profile-card-left">
                    <div class="card-body-therapist text-center">
                        <div class="profile-image-container mb-3" style="width:120px;height:120px;cursor:pointer;margin:0 auto;display:flex;align-items:center;justify-content:center;border-radius:50%;overflow:hidden;position:relative;background:#f4f6f8;">
                            <?php if (!empty($therapist['profile_picture'])): ?>
                                <img src="../<?php echo htmlspecialchars($therapist['profile_picture']); ?>" class="profile-picture" id="profilePreview" alt="Profile Picture" style="width:120px;height:120px;border-radius:50%;object-fit:cover;display:block;">
                            <?php else: ?>
                                <img src="../assets/images/default-profile.jpg" class="profile-picture" id="profilePreview" alt="Profile Picture" style="width:120px;height:120px;border-radius:50%;object-fit:cover;display:block;">
                            <?php endif; ?>
                            <div class="profile-image-overlay" style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:50%;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s;background:rgba(0,0,0,0.25);color:#fff;">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>
                        <h4 style="font-size:1.08rem;margin-bottom:4px;margin-top:10px;"> <?php echo htmlspecialchars($therapist['username'] ?? 'Your Name'); ?></h4>
                        <p class="text-muted" style="font-size:0.98rem;margin-bottom:4px;">Therapist</p>
                        <?php if (!empty($therapist['specialization'])): ?>
                            <div class="card-badge-therapist card-badge-primary" style="margin-top: 4px; display: inline-block;font-size:0.92rem;padding:2px 8px;">Specialization: <?php echo htmlspecialchars($therapist['specialization']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Profile Information Container -->
                <div class="card-therapist profile-card-right">
                    <div class="card-body-therapist">
                        <h5 class="mb-3" style="font-size:1.08rem;font-weight:600;">Profile Information</h5>
                        <form method="post" enctype="multipart/form-data" class="profile-form-custom">
                            <div class="form-group-custom" style="margin-bottom:10px;">
                                <label for="profile_picture" class="form-label" style="font-size:0.98rem;">Profile Picture</label>
                                <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*" style="font-size:0.98rem;padding:4px 8px;">
                                    </div>
                            <div class="form-group-custom" style="margin-bottom:10px;">
                                <label for="specialization" class="form-label" style="font-size:0.98rem;">Specialization</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" value="<?php echo htmlspecialchars($therapist['specialization'] ?? ''); ?>" style="font-size:0.98rem;padding:4px 8px;">
                                </div>
                            <div class="form-group-custom" style="margin-bottom:10px;">
                                <label for="qualifications" class="form-label" style="font-size:0.98rem;">Qualifications</label>
                                <input type="text" class="form-control" id="qualifications" name="qualifications" value="<?php echo htmlspecialchars($therapist['qualifications'] ?? ''); ?>" style="font-size:0.98rem;padding:4px 8px;">
                            </div>
                            <div class="form-group-custom" style="margin-bottom:10px;">
                                <label for="bio" class="form-label" style="font-size:0.98rem;">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3" style="font-size:0.98rem;padding:4px 8px;"><?php echo htmlspecialchars($therapist['bio'] ?? ''); ?></textarea>
                                </div>
                            <div class="form-group-custom" style="margin-bottom:10px;">
                                <label for="new_password" class="form-label" style="font-size:0.98rem;">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank to keep current password" style="font-size:0.98rem;padding:4px 8px;">
                                </div>
                            <div class="form-group-custom" style="margin-bottom:10px;">
                                <label for="confirm_password" class="form-label" style="font-size:0.98rem;">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" style="font-size:0.98rem;padding:4px 8px;">
                                </div>
                            <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                                <button type="submit" class="btn-therapist" style="font-size:0.98rem;padding:0.4rem 1.1rem;"><i class="fas fa-save me-2"></i>Save</button>
                            </div>
                        </form>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Click on profile image to trigger file input
        document.querySelector('.profile-image-container').addEventListener('click', function() {
            document.getElementById('profile_picture').click();
        });
        // Image preview for profile picture upload
        document.getElementById('profile_picture').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('profilePreview');
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = function(e) {
                        preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        // Show overlay on hover
        const picContainer = document.querySelector('.profile-image-container');
        const overlay = picContainer.querySelector('.profile-image-overlay');
        picContainer.addEventListener('mouseenter', () => { overlay.style.opacity = 1; });
        picContainer.addEventListener('mouseleave', () => { overlay.style.opacity = 0; });
    </script>
</body>
</html>