<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit();
}

// Database connection
include('dbconnect.php');

$page_title = 'My Messages';
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

// Get received messages
$received_messages = [];
$received_sql = "
    SELECT 
        m.message_id,
        m.content as message,
        m.created_at as sent_at,
        m.is_read,
        m.sender_id,
        u.username as sender_name,
        'received' as message_type
    FROM messages m
    JOIN users u ON m.sender_id = u.user_id
    WHERE m.receiver_id = ?
    ORDER BY m.created_at DESC
";

$received_stmt = $conn->prepare($received_sql);
$received_stmt->bind_param("i", $_SESSION['user_id']);
$received_stmt->execute();
$received_result = $received_stmt->get_result();
while ($row = $received_result->fetch_assoc()) {
    $received_messages[] = $row;
}
$received_stmt->close();

// Get sent messages
$sent_messages = [];
$sent_sql = "
    SELECT 
        m.message_id,
        m.content as message,
        m.created_at as sent_at,
        m.receiver_id,
        u.username as receiver_name,
        'sent' as message_type
    FROM messages m
    JOIN users u ON m.receiver_id = u.user_id
    WHERE m.sender_id = ?
    ORDER BY m.created_at DESC
";

$sent_stmt = $conn->prepare($sent_sql);
$sent_stmt->bind_param("i", $_SESSION['user_id']);
$sent_stmt->execute();
$sent_result = $sent_stmt->get_result();
while ($row = $sent_result->fetch_assoc()) {
    $sent_messages[] = $row;
}
$sent_stmt->close();

// Combine and sort all messages by date
$all_messages = array_merge($received_messages, $sent_messages);
usort($all_messages, function($a, $b) {
    return strtotime($b['sent_at']) - strtotime($a['sent_at']);
});

// Mark received messages as read when page loads
if (!empty($received_messages)) {
    $update_stmt = $conn->prepare("UPDATE messages SET is_read = TRUE WHERE receiver_id = ?");
    $update_stmt->bind_param("i", $_SESSION['user_id']);
    $update_stmt->execute();
    $update_stmt->close();
}

// Get unread count (should be 0 after update)
$unread_count = 0;
$count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = FALSE");
$count_stmt->bind_param("i", $_SESSION['user_id']);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$unread_count = $count_result->fetch_assoc()['count'];
$count_stmt->close();

// Handle reply message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $reply_content = trim($_POST['reply_content']);
    $receiver_id = intval($_POST['receiver_id']);
    if (empty($reply_content)) {
        $error = 'Reply message cannot be empty.';
    } else {
        include('C:/xampp/htdocs/green2/dbconnect.php');
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $_SESSION['user_id'], $receiver_id, $reply_content);
        if ($stmt->execute()) {
            $success = 'Reply sent successfully!';
        } else {
            $error = 'Error sending reply: ' . $conn->error;
        }
        $stmt->close();
$conn->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="client_dashboard_custom.css">
    <link rel="stylesheet" href="common.css">
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
                <li><a class="sidebar-link" href="client_therapists.php"><i class="fas fa-user-md me-2"></i>Therapists</a></li>
                <li><a class="sidebar-link" href="client_appointments.php"><i class="fas fa-calendar-check me-2"></i>Appointments</a></li>
                <li class="position-relative"><a class="sidebar-link active" href="client_messages.php"><i class="fas fa-envelope me-2"></i>Messages<?php if ($unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="spacing-top"><a class="sidebar-link text-primary" href="index.php"><i class="fas fa-arrow-left me-2"></i>Go Back</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="dashboard-main">
            <div class="dashboard-header">
                <h1>My Messages</h1>
                <a href="new_message.php" class="btn-custom"><i class="fas fa-plus me-2"></i>New Message</a>
            </div>
            <?php if ($error): ?>
                <div class="alert-custom alert-danger-custom"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="message-tabs-custom mb-4">
                <button class="btn-custom btn-custom-secondary message-tab-btn active" data-tab="all">All Messages</button>
                <button class="btn-custom btn-custom-secondary message-tab-btn" data-tab="inbox">Inbox</button>
                <button class="btn-custom btn-custom-secondary message-tab-btn" data-tab="sent">Sent</button>
            </div>
            <div class="message-content-custom" id="tab-all">
                    <?php if (empty($all_messages)): ?>
                    <div class="alert-custom alert-info-custom">You don't have any messages yet.</div>
                    <?php else: ?>
                        <?php foreach ($all_messages as $message): ?>
                    <div class="card-custom message-card mb-3 <?php echo ($message['message_type'] === 'received' && !$message['is_read']) ? 'unread' : ''; ?> <?php echo ($message['message_type'] === 'sent') ? 'sent-message' : ''; ?>">
                        <div class="card-body-custom">
                            <div style="display: flex; justify-content: space-between;">
                                <h5 class="card-title-custom">Message</h5>
                                    <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($message['sent_at'])); ?></small>
                                </div>
                            <h6 class="card-subtitle-custom" style="margin-bottom: 8px; color: #888;">
                                    <?php if ($message['message_type'] === 'received'): ?>
                                        From: <?php echo htmlspecialchars($message['sender_name']); ?>
                                    <?php else: ?>
                                        To: <?php echo htmlspecialchars($message['receiver_name']); ?>
                                    <?php endif; ?>
                                </h6>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                <?php if ($message['message_type'] === 'received'): ?>
                                <button class="btn-custom btn-custom-secondary reply-btn" data-receiver-id="<?php echo $message['sender_id']; ?>" data-receiver-name="<?php echo htmlspecialchars($message['sender_name']); ?>"> <i class="fas fa-reply me-1"></i> Reply</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <div class="message-content-custom" id="tab-inbox" style="display:none;">
                    <?php if (empty($received_messages)): ?>
                    <div class="alert-custom alert-info-custom">Your inbox is empty.</div>
                    <?php else: ?>
                        <?php foreach ($received_messages as $message): ?>
                    <div class="card-custom message-card mb-3 <?php echo !$message['is_read'] ? 'unread' : ''; ?>">
                        <div class="card-body-custom">
                            <div style="display: flex; justify-content: space-between;">
                                <h5 class="card-title-custom">Message</h5>
                                    <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($message['sent_at'])); ?></small>
                                </div>
                            <h6 class="card-subtitle-custom" style="margin-bottom: 8px; color: #888;">From: <?php echo htmlspecialchars($message['sender_name']); ?></h6>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            <button class="btn-custom btn-custom-secondary reply-btn" data-receiver-id="<?php echo $message['sender_id']; ?>" data-receiver-name="<?php echo htmlspecialchars($message['sender_name']); ?>"> <i class="fas fa-reply me-1"></i> Reply</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <div class="message-content-custom" id="tab-sent" style="display:none;">
                    <?php if (empty($sent_messages)): ?>
                    <div class="alert-custom alert-info-custom">You haven't sent any messages yet.</div>
                    <?php else: ?>
                        <?php foreach ($sent_messages as $message): ?>
                    <div class="card-custom message-card mb-3 sent-message">
                        <div class="card-body-custom">
                            <div style="display: flex; justify-content: space-between;">
                                <h5 class="card-title-custom">Message</h5>
                                    <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($message['sent_at'])); ?></small>
                                </div>
                            <h6 class="card-subtitle-custom" style="margin-bottom: 8px; color: #888;">To: <?php echo htmlspecialchars($message['receiver_name']); ?></h6>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
            </div>
            <!-- Reply Modal (custom) -->
            <div id="replyModalCustom" class="modal-custom" style="display:none;">
                <div class="modal-custom-content">
                    <form method="post">
                        <div class="modal-custom-header">
                            <h5>Reply to <span id="replyToName"></span></h5>
                            <span class="modal-custom-close" id="closeReplyModal">&times;</span>
                        </div>
                        <div class="modal-custom-body">
                            <input type="hidden" name="receiver_id" id="replyReceiverId">
                            <div class="form-group-custom">
                                <label for="reply_content" class="form-label">Your Message</label>
                                <textarea class="form-control" name="reply_content" id="reply_content" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="modal-custom-footer">
                            <button type="button" class="btn-custom btn-custom-secondary" id="cancelReplyModal">Cancel</button>
                            <button type="submit" name="reply_message" class="btn-custom">Send Reply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Tabs functionality
        document.querySelectorAll('.message-tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.message-tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const tab = this.getAttribute('data-tab');
                document.querySelectorAll('.message-content-custom').forEach(c => c.style.display = 'none');
                document.getElementById('tab-' + tab).style.display = '';
            });
        });
        // Reply modal functionality
        document.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('replyModalCustom').style.display = 'block';
                document.getElementById('replyReceiverId').value = this.getAttribute('data-receiver-id');
                document.getElementById('replyToName').textContent = this.getAttribute('data-receiver-name');
                document.getElementById('reply_content').value = '';
                });
            });
        document.getElementById('closeReplyModal').onclick = function() {
            document.getElementById('replyModalCustom').style.display = 'none';
        };
        document.getElementById('cancelReplyModal').onclick = function() {
            document.getElementById('replyModalCustom').style.display = 'none';
        };
        window.onclick = function(event) {
            if (event.target == document.getElementById('replyModalCustom')) {
                document.getElementById('replyModalCustom').style.display = 'none';
                }
        };
    </script>
    <style>
    /* Custom modal styles */
    .modal-custom {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        overflow: auto;
        background: rgba(0,0,0,0.4);
        align-items: center;
        justify-content: center;
    }
    .modal-custom-content {
        background: #fff;
        margin: 5% auto;
        border-radius: 8px;
        padding: 0;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.18);
        animation: fadeIn 0.2s;
    }
    .modal-custom-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
    }
    .modal-custom-body {
        padding: 20px;
    }
    .modal-custom-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 20px;
        border-top: 1px solid #eee;
        background: #f8f9fa;
        border-radius: 0 0 8px 8px;
    }
    .modal-custom-close {
        font-size: 1.5rem;
        cursor: pointer;
        color: #888;
        margin-left: 12px;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    </style>
</body>
</html>