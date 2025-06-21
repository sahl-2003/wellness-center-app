<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Message Replies';

if (!isset($_GET['message_id'])) {
    header("Location: messages.php");
    exit();
}

$message_id = $_GET['message_id'];

// Get the original message
$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();
$original_message = $result->fetch_assoc();
$stmt->close();

if (!$original_message) {
    header("Location: messages.php");
    exit();
}

// Get all replies for this message
$replies = [];
$stmt = $conn->prepare("SELECT mr.*, u.username as admin_name FROM message_replies mr 
                       JOIN users u ON mr.admin_id = u.user_id 
                       WHERE mr.message_id = ? 
                       ORDER BY mr.created_at ASC");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $replies[] = $row;
}
$stmt->close();

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
        .message-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .reply-item {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .reply-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .reply-author {
            font-weight: bold;
            color: #007bff;
        }
        .reply-date {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .reply-content {
            white-space: pre-wrap;
            line-height: 1.5;
        }
        .back-button {
            margin-bottom: 20px;
        }
        .no-replies {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
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
                <li><a class="admin-nav-link" href="appointments.php"><i class="fas fa-calendar-check"></i>Appointments</a></li>
                <li><a class="admin-nav-link active" href="messages.php"><i class="fas fa-envelope"></i>Messages</a></li>
                <li class="mt-3"><a class="admin-nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1>Message Replies</h1>
            </div>
            
            <div class="back-button">
                <a href="messages.php" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Messages
                </a>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5>Original Message</h5>
                </div>
                <div class="admin-card-body">
                    <div class="message-details">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>From:</strong> <?php echo htmlspecialchars($original_message['name']); ?><br>
                                <strong>Email:</strong> <?php echo htmlspecialchars($original_message['email']); ?><br>
                                <strong>Phone:</strong> <?php echo htmlspecialchars($original_message['phone']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Subject:</strong> <?php echo htmlspecialchars($original_message['subject']); ?><br>
                                <strong>Received:</strong> <?php echo date('M j, Y H:i', strtotime($original_message['created_at'])); ?><br>
                                <strong>Status:</strong> 
                                <span class="badge <?php echo $original_message['is_read'] ? 'bg-secondary' : 'bg-success'; ?>">
                                    <?php echo $original_message['is_read'] ? 'Read' : 'Unread'; ?>
                                </span>
                            </div>
                        </div>
                        <hr>
                        <div class="mt-3">
                            <strong>Message:</strong><br>
                            <div style="white-space: pre-wrap; margin-top: 10px; padding: 15px; background-color: white; border-radius: 4px;">
                                <?php echo htmlspecialchars($original_message['message']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5>Replies (<?php echo count($replies); ?>)</h5>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($replies)): ?>
                        <div class="no-replies">
                            <i class="fas fa-comment-slash fa-3x mb-3"></i>
                            <p>No replies yet for this message.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($replies as $reply): ?>
                            <div class="reply-item">
                                <div class="reply-header">
                                    <div class="reply-author">
                                        <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($reply['admin_name']); ?>
                                    </div>
                                    <div class="reply-date">
                                        <?php echo date('M j, Y H:i', strtotime($reply['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="reply-content">
                                    <?php echo htmlspecialchars($reply['reply_text']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 