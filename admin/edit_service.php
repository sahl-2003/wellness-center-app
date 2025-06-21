<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Edit Service';
$error = '';
$success = '';

// Get service ID from URL
$service_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch service data
$service = null;
if ($service_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM services WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = intval($_POST['service_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $duration = intval($_POST['duration']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $current_image = trim($_POST['current_image']);
    $new_image_path = $current_image;
    
    // Validate inputs
    if (empty($name) || empty($duration) || empty($price)) {
        $error = 'Please fill all required fields';
    } elseif ($duration <= 0) {
        $error = 'Duration must be greater than 0';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than 0';
    } else {
        // Handle image upload if new image was provided
        if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/services/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = basename($_FILES['service_image']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $new_file_name = uniqid('service_', true) . '.' . $file_ext;
                $target_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($_FILES['service_image']['tmp_name'], $target_path)) {
                    // Delete old image if it exists
                    if (!empty($current_image)) {
                        $old_image_path = '../' . $current_image;
                        if (file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                    $new_image_path = 'uploads/services/' . $new_file_name;
                } else {
                    $error = 'Failed to upload image';
                }
            } else {
                $error = 'Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.';
            }
        }
        
        if (empty($error)) {
            // Update service data
            $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, duration = ?, price = ?, category = ?, is_active = ?, image_path = ? WHERE service_id = ?");
            $stmt->bind_param("ssidsssi", $name, $description, $duration, $price, $category, $is_active, $new_image_path, $service_id);
            
            if ($stmt->execute()) {
                $success = 'Service updated successfully!';
                // Refresh service data
                $stmt = $conn->prepare("SELECT * FROM services WHERE service_id = ?");
                $stmt->bind_param("i", $service_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $service = $result->fetch_assoc();
                $stmt->close();
            } else {
                $error = 'Error updating service: ' . $conn->error;
            }
        }
    }
}

$conn->close();

// Redirect if no service found
if (!$service && $service_id > 0) {
    header("Location: services.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="admin_dashboard_custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-flex">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <img src="../image/c2.jpg" alt="Admin Profile" class="admin-profile-pic">
                </div>
                <h5><?php echo htmlspecialchars($_SESSION['username']); ?></h5>
                <small><?php echo htmlspecialchars($_SESSION['email']); ?></small>
                <div class="mt-2 admin-badge">Administrator</div>
            </div>
            <ul class="admin-nav">
                <li><a class="admin-nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li><a class="admin-nav-link" href="users.php"><i class="fas fa-users"></i>Manage Users</a></li>
                <li><a class="admin-nav-link" href="therapists.php"><i class="fas fa-user-md"></i>Therapists</a></li>
                <li><a class="admin-nav-link active" href="services.php"><i class="fas fa-concierge-bell"></i>Services</a></li>
                <li><a class="admin-nav-link" href="appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a></li>
                <li><a class="admin-nav-link" href="messages.php"><i class="fas fa-envelope"></i>Messages</a></li>
                <li class="mt-3"><a class="admin-nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1>Edit Service</h1>
                <a href="services.php" class="admin-btn admin-btn-secondary" style="max-width:200px;">
                    <i class="fas fa-arrow-left me-2"></i>Back to Services
                </a>
            </div>
            <div class="admin-card" style="max-width:600px;margin:0 auto;">
                <div class="admin-card-header">Edit Service Details</div>
                <div class="admin-card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="margin-bottom:18px;background:#ffeaea;color:#c0392b;padding:12px 18px;border-radius:8px;">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success" style="margin-bottom:18px;background:#eaffea;color:#218838;padding:12px 18px;border-radius:8px;">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="service_id" value="<?php echo $service['service_id']; ?>">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($service['image_path']); ?>">
                        <div style="margin-bottom:18px;">
                            <label for="name" style="display:block;font-weight:500;margin-bottom:6px;">Service Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($service['name']); ?>" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="description" style="display:block;font-weight:500;margin-bottom:6px;">Description</label>
                            <textarea id="description" name="description" required style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;min-height:80px;"><?php echo htmlspecialchars($service['description']); ?></textarea>
                        </div>
                        <div style="margin-bottom:18px;display:flex;gap:12px;">
                            <div style="flex:1;">
                                <label for="duration" style="display:block;font-weight:500;margin-bottom:6px;">Duration (min)</label>
                                <input type="number" id="duration" name="duration" value="<?php echo htmlspecialchars($service['duration']); ?>" required min="1" style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                            </div>
                            <div style="flex:1;">
                                <label for="price" style="display:block;font-weight:500;margin-bottom:6px;">Price ($)</label>
                                <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($service['price']); ?>" required min="1" step="0.01" style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                            </div>
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="category" style="display:block;font-weight:500;margin-bottom:6px;">Category</label>
                            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($service['category']); ?>" style="width:100%;padding:10px 12px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom:18px;display:flex;align-items:center;gap:10px;">
                            <input type="checkbox" id="is_active" name="is_active" <?php echo $service['is_active'] ? 'checked' : ''; ?> style="width:18px;height:18px;">
                            <label for="is_active" style="font-weight:500;">Active</label>
                        </div>
                        <div style="margin-bottom:18px;">
                            <label for="service_image" style="display:block;font-weight:500;margin-bottom:6px;">Service Image</label>
                            <?php if (!empty($service['image_path'])): ?>
                                <div style="margin-bottom:10px;">
                                    <img src="../<?php echo htmlspecialchars($service['image_path']); ?>" alt="Current Image" style="max-width:120px;border-radius:8px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" id="service_image" name="service_image" accept="image/*" style="width:100%;">
                        </div>
                        <div style="margin-top:24px;">
                            <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;max-width:220px;">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>