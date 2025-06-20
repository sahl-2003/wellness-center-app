<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.html");
    exit();
}

// Use absolute path to ensure the file is found
include('C:/xampp/htdocs/green2/dbconnect.php');

$page_title = 'My Profile';
$error = '';
$success = '';

// Check if connection was successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get current profile with JOIN
$profile = null;
$stmt = $conn->prepare("
    SELECT u.*, cp.* 
    FROM users u
    LEFT JOIN client_profiles cp ON u.user_id = cp.user_id
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $profile_picture = $profile['profile_picture'] ?? '';
    
    // Handle file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'C:/xampp/htdocs/green2/uploads/profiles/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                $error = 'Failed to create upload directory';
            }
        }
        
        $file_name = basename($_FILES['profile_picture']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = 'client_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
            $target_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
               // Store relative path from site root
    $profile_picture = '/green2/uploads/profiles/' . $new_file_name; 
            
            
               // Delete old profile picture if it exists
                if (!empty($profile['profile_picture'])) {
                    $old_picture_path = 'C:/xampp/htdocs/green2/' . $profile['profile_picture'];
                    if (file_exists($old_picture_path)) {
                        @unlink($old_picture_path);
                    }
                }
               
            } else {
                $error = 'Failed to save uploaded file';
                error_log("File upload failed. Target path: $target_path");
            }
        } else {
            $error = 'Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.';
        }
    }
    
    if (empty($error)) {
        // Check if profile exists
        $check_stmt = $conn->prepare("SELECT * FROM client_profiles WHERE user_id = ?");
        $check_stmt->bind_param("i", $_SESSION['user_id']);
        $check_stmt->execute();
        $profile_exists = $check_stmt->get_result()->num_rows > 0;
        $check_stmt->close();
        
        if ($profile_exists) {
            // Update existing profile
            $stmt = $conn->prepare("
                UPDATE client_profiles 
                SET full_name = ?, date_of_birth = ?, gender = ?, phone = ?, address = ?, profile_picture = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param("ssssssi", $full_name, $date_of_birth, $gender, $phone, $address, $profile_picture, $_SESSION['user_id']);
        } else {
            // Insert new profile
            $stmt = $conn->prepare("
                INSERT INTO client_profiles 
                (user_id, full_name, date_of_birth, gender, phone, address, profile_picture)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issssss", $_SESSION['user_id'], $full_name, $date_of_birth, $gender, $phone, $address, $profile_picture);
        }
        
        if ($stmt->execute()) {
            $success = 'Profile saved successfully!';
            // Refresh profile data
            $stmt = $conn->prepare("
                SELECT u.*, cp.* 
                FROM users u
                LEFT JOIN client_profiles cp ON u.user_id = cp.user_id
                WHERE u.user_id = ?
            ");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $profile = $result->fetch_assoc();
            $stmt->close();
            
            // Debug output
            error_log("Profile picture path after save: " . $profile['profile_picture']);
        } else {
            $error = 'Error saving profile: ' . $conn->error;
            error_log("Database error: " . $conn->error);
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
                <li><a class="sidebar-link active" href="client_profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                <li><a class="sidebar-link" href="client_therapists.php"><i class="fas fa-user-md me-2"></i>Therapists</a></li>
                <li><a class="sidebar-link" href="client_appointments.php"><i class="fas fa-calendar-check me-2"></i>Appointments</a></li>
                <li><a class="sidebar-link" href="client_messages.php"><i class="fas fa-envelope me-2"></i>Messages</a></li>
                <li class="mt-3"><a class="sidebar-link text-primary" href="index.php"><i class="fas fa-arrow-left me-2"></i>Go Back</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="dashboard-main">
            <div class="dashboard-header">
                <h1>My Profile</h1>
                <a href="client_dashboard.php" class="btn-custom btn-custom-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
            </div>
            <?php if ($error): ?>
                <div class="alert-custom alert-danger-custom"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-custom alert-info-custom"><?php echo $success; ?></div>
            <?php endif; ?>
            <div class="row" style="display: flex; flex-wrap: wrap; gap: 16px;">
                <div style="flex: 1 1 30%; min-width: 260px;">
                    <div class="card-custom h-100">
                        <div class="card-body-custom text-center">
                            <div class="profile-image-container mb-3">
                                <?php if (!empty($profile['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($profile['profile_picture']); ?>" class="profile-picture" id="currentProfileImage" alt="Profile Picture">
                                <?php else: ?>
                                    <img src="assets/images/default-profile.jpg" class="profile-picture" id="currentProfileImage" alt="Profile Picture">
                                <?php endif; ?>
                                <div class="profile-image-overlay"><i class="fas fa-camera fa-2x"></i></div>
                            </div>
                            <h4><?php echo htmlspecialchars($profile['full_name'] ?? 'Your Name'); ?></h4>
                            <p class="text-muted">Client</p>
                        </div>
                    </div>
                </div>
                <div style="flex: 1 1 65%; min-width: 320px;">
                    <div class="card-custom">
                        <div class="card-header-custom"><h5 class="mb-0">Profile Information</h5></div>
                        <div class="card-body-custom">
                            <form method="post" enctype="multipart/form-data" class="profile-form-custom">
                                <div class="form-group-custom">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                                    <img id="imagePreview" src="#" alt="Image Preview" class="mt-2" style="max-width: 200px; display: none;">
                                </div>
                                <div class="form-group-custom">
                                    <label for="full_name" class="form-label">Full Name <span style="color: #e74c3c;">*</span></label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-row-custom">
                                    <div class="form-group-custom" style="flex:1;">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group-custom" style="flex:1;">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select class="form-select" id="gender" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" <?php echo (isset($profile['gender']) && $profile['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo (isset($profile['gender']) && $profile['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="other" <?php echo (isset($profile['gender']) && $profile['gender'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                            <option value="prefer_not_to_say" <?php echo (isset($profile['gender']) && $profile['gender'] === 'prefer_not_to_say') ? 'selected' : ''; ?>>Prefer not to say</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-custom">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-group-custom">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                                </div>
                                <div style="display: flex; justify-content: flex-end; margin-top: 18px;">
                                    <button type="submit" class="btn-custom"><i class="fas fa-save me-2"></i>Save Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Image preview for profile picture upload
        document.getElementById('profile_picture').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            const currentImage = document.getElementById('currentProfileImage');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    currentImage.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        // Click on profile image to trigger file input
        document.querySelector('.profile-image-container').addEventListener('click', function() {
            document.getElementById('profile_picture').click();
        });
    </script>
</body>
</html>