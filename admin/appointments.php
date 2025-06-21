<?php
session_start();
include('../dbconnect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Appointment Overview";

// Fetching data for filters
$therapists = $conn->query("SELECT user_id, username FROM users WHERE role = 'therapist'");
$statuses = ['scheduled', 'completed', 'cancelled', 'pending'];

// Filtering logic
$sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, s.price, u_client.username as client_name, u_therapist.username as therapist_name, s.name as service_name 
        FROM appointments a
        JOIN users u_client ON a.user_id = u_client.user_id
        JOIN users u_therapist ON a.therapist_id = u_therapist.user_id
        JOIN services s ON a.service_id = s.service_id
        WHERE 1";

$filters = [];
$types = '';
if (!empty($_GET['therapist'])) {
    $therapist_id = $_GET['therapist'];
    $sql .= " AND a.therapist_id = ?";
    $types .= 'i';
    $filters[] = $therapist_id;
}
if (!empty($_GET['status'])) {
    $status = $_GET['status'];
    $sql .= " AND a.status = ?";
    $types .= 's';
    $filters[] = $status;
}
if (!empty($_GET['start_date'])) {
    $start_date = $_GET['start_date'];
    $sql .= " AND a.appointment_date >= ?";
    $types .= 's';
    $filters[] = $start_date;
}
if (!empty($_GET['end_date'])) {
    $end_date = $_GET['end_date'];
    $sql .= " AND a.appointment_date <= ?";
    $types .= 's';
    $filters[] = $end_date;
}

$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $conn->prepare($sql);
if (!empty($filters)) {
    $stmt->bind_param($types, ...$filters);
}
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);

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
    <link rel="stylesheet" href="admin_dashboard_custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filter-form .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        .filter-form .form-group {
            flex: 1;
            min-width: 150px;
        }
        .filter-form label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .filter-form .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filter-buttons {
             margin-top: 10px;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 12px;
            color: white;
            font-size: 0.8rem;
        }
        .bg-scheduled { background-color: #007bff; }
        .bg-completed { background-color: #28a745; }
        .bg-cancelled { background-color: #dc3545; }
        .bg-pending { background-color: #ffc107; color: #212529 !important; }

    </style>
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
                <li><a class="admin-nav-link" href="services.php"><i class="fas fa-concierge-bell"></i>Services</a></li>
                <li><a class="admin-nav-link active" href="appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a></li>
                <li><a class="admin-nav-link" href="messages.php"><i class="fas fa-envelope"></i>Messages<?php if ($unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="mt-3"><a class="admin-nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1><?php echo $page_title; ?></h1>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h5>Filter Appointments</h5>
                </div>
                <div class="admin-card-body">
                    <form method="get" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="therapist">Therapist</label>
                                <select id="therapist" name="therapist" class="form-control">
                                    <option value="">All Therapists</option>
                                    <?php mysqli_data_seek($therapists, 0); // Reset pointer ?>
                                    <?php while($row = $therapists->fetch_assoc()): ?>
                                        <option value="<?php echo $row['user_id']; ?>" <?php echo (($_GET['therapist'] ?? '') == $row['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['username']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <?php foreach($statuses as $s): ?>
                                        <option value="<?php echo $s; ?>" <?php echo (($_GET['status'] ?? '') == $s) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($s); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group filter-buttons">
                                <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                                <a href="appointments.php" class="admin-btn admin-btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h5>All Appointments (<?php echo count($appointments); ?>)</h5>
                </div>
                <div class="admin-card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Therapist</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($appointments)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No appointments found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($appointments as $appt): ?>
                                        <tr>
                                            <td><?php echo $appt['id']; ?></td>
                                            <td><?php echo htmlspecialchars($appt['client_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appt['therapist_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appt['service_name']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($appt['appointment_date'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($appt['appointment_time'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo strtolower(htmlspecialchars($appt['status'])); ?>">
                                                    <?php echo ucfirst(htmlspecialchars($appt['status'])); ?>
                                                </span>
                                            </td>
                                            <td>RS <?php echo number_format($appt['price'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 