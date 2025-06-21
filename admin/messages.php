<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Contact Messages';

// Handle marking as read/unread and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_read'])) {
        $message_id = $_POST['message_id'];
        $stmt = $conn->prepare("UPDATE contact_messages SET is_read = NOT is_read WHERE id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_message'])) {
        $message_id = $_POST['message_id'];
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['send_reply'])) {
        $message_id = $_POST['message_id'];
        $reply_text = $_POST['reply_text'];
        $admin_id = $_SESSION['user_id'];
        
        // Insert reply into database
        $stmt = $conn->prepare("INSERT INTO message_replies (message_id, admin_id, reply_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $message_id, $admin_id, $reply_text);
        $stmt->execute();
        $stmt->close();
        
        // Mark original message as read
        $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $stmt->close();
        
        // Redirect to refresh the page
        header("Location: messages.php?success=1");
        exit();
    }
}

// Get all contact messages with reply count
$messages = [];
$sql = "SELECT cm.*, 
        (SELECT COUNT(*) FROM message_replies WHERE message_id = cm.id) as reply_count,
        (SELECT MAX(created_at) FROM message_replies WHERE message_id = cm.id) as last_reply
        FROM contact_messages cm 
        ORDER BY cm.created_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

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
        .reply-form {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .reply-textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }
        .reply-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        .message-row {
            transition: background-color 0.3s;
        }
        .message-row:hover {
            background-color: #f8f9fa;
        }
        .reply-indicator {
            color: #007bff;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .admin-btn-info {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .admin-btn-info:hover {
            background-color: #138496;
            color: white;
            text-decoration: none;
        }
        .admin-btn-group .admin-btn, 
        .admin-btn-group .admin-btn-info {
            width: 140px;
            box-sizing: border-box;
            padding: 8px 12px;
            font-size: 0.9rem;
            vertical-align: middle;
        }
        .admin-btn-group form {
            display: inline-block;
            vertical-align: middle;
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
                <h1>Contact Messages</h1>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> Reply sent successfully!
                </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5>Inbox</h5>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($messages)): ?>
                        <p>There are no messages.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>From</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Received</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $msg): ?>
                                    <tr class="message-row <?php echo $msg['is_read'] ? '' : 'font-weight-bold'; ?>" id="message-<?php echo $msg['id']; ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($msg['name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($msg['email']); ?></small><br>
                                            <small><?php echo htmlspecialchars($msg['phone']); ?></small>
                                            <?php if ($msg['reply_count'] > 0): ?>
                                                <div class="reply-indicator">
                                                    <i class="fas fa-reply"></i> <?php echo $msg['reply_count']; ?> reply<?php echo $msg['reply_count'] > 1 ? 'ies' : ''; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                        <td style="max-width: 300px; white-space: pre-wrap;"><?php echo htmlspecialchars($msg['message']); ?></td>
                                        <td><?php echo date('M j, Y H:i', strtotime($msg['created_at'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $msg['is_read'] ? 'bg-secondary' : 'bg-success'; ?>">
                                                <?php echo $msg['is_read'] ? 'Read' : 'Unread'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="admin-btn-group" style="flex-direction: row; gap: 8px;">
                                                <button type="button" class="admin-btn admin-btn-primary" onclick="toggleReply(<?php echo $msg['id']; ?>)">
                                                    <i class="fas fa-reply"></i> Reply
                                                </button>
                                                <?php if ($msg['reply_count'] > 0): ?>
                                                    <a href="view_replies.php?message_id=<?php echo $msg['id']; ?>" class="admin-btn admin-btn-info" style="text-decoration: none;">
                                                        <i class="fas fa-eye"></i> View Replies
                                                    </a>
                                                <?php endif; ?>
                                                <form method="post">
                                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                    <button type="submit" name="toggle_read" class="admin-btn admin-btn-secondary">
                                                        <i class="fas fa-check-circle"></i> Mark as <?php echo $msg['is_read'] ? 'Unread' : 'Read'; ?>
                                                    </button>
                                                </form>
                                                <form method="post" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                    <button type="submit" name="delete_message" class="admin-btn admin-btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6">
                                            <div id="reply-form-<?php echo $msg['id']; ?>" class="reply-form">
                                                <form method="post">
                                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                    <div class="mb-3">
                                                        <label for="reply-text-<?php echo $msg['id']; ?>" class="form-label"><strong>Reply to <?php echo htmlspecialchars($msg['name']); ?>:</strong></label>
                                                        <textarea 
                                                            id="reply-text-<?php echo $msg['id']; ?>" 
                                                            name="reply_text" 
                                                            class="reply-textarea" 
                                                            placeholder="Type your reply here..." 
                                                            required></textarea>
                                                    </div>
                                                    <div class="reply-buttons">
                                                        <button type="submit" name="send_reply" class="admin-btn admin-btn-primary">
                                                            <i class="fas fa-paper-plane"></i> Send Reply
                                                        </button>
                                                        <button type="button" class="admin-btn admin-btn-secondary" onclick="toggleReply(<?php echo $msg['id']; ?>)">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleReply(messageId) {
            const replyForm = document.getElementById(`reply-form-${messageId}`);
            const replyTextarea = document.getElementById(`reply-text-${messageId}`);
            
            if (replyForm.style.display === 'block') {
                replyForm.style.display = 'none';
                replyTextarea.value = '';
            } else {
                // Hide all other reply forms first
                const allReplyForms = document.querySelectorAll('.reply-form');
                allReplyForms.forEach(form => {
                    form.style.display = 'none';
                });
                
                // Clear all textareas
                const allTextareas = document.querySelectorAll('.reply-textarea');
                allTextareas.forEach(textarea => {
                    textarea.value = '';
                });
                
                // Show the selected reply form
                replyForm.style.display = 'block';
                replyTextarea.focus();
            }
        }
    </script>
</body>
</html> 