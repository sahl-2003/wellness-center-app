<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: ../login.html");
    exit();
}

include('../dbconnect.php');

$page_title = 'Messages';
$error = '';
$success = '';

// Handle sending new messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $content = trim($_POST['message_content']);
    
    if (empty($content)) {
        $error = 'Message content cannot be empty';
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $_SESSION['user_id'], $receiver_id, $content);
        
        if ($stmt->execute()) {
            $success = 'Message sent successfully!';
        } else {
            $error = 'Error sending message: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Handle marking messages as read when conversation is opened
if (isset($_GET['conversation']) && is_numeric($_GET['conversation'])) {
    $other_user_id = intval($_GET['conversation']);
    
    // Mark all messages from this user as read
    $stmt = $conn->prepare("UPDATE messages SET is_read = TRUE 
                           WHERE receiver_id = ? AND sender_id = ? AND is_read = FALSE");
    $stmt->bind_param("ii", $_SESSION['user_id'], $other_user_id);
    $stmt->execute();
    $stmt->close();

    // Update session unread count
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM messages WHERE receiver_id = ? AND is_read = FALSE");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $_SESSION['unread_count'] = $result->fetch_assoc()['count'];
    $stmt->close();
}

// Get conversation with a specific user if selected
$current_conversation = null;
$conversation_messages = [];
if (isset($_GET['conversation']) && is_numeric($_GET['conversation'])) {
    $other_user_id = intval($_GET['conversation']);
    
    // Get the other user's details
    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $other_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_conversation = $result->fetch_assoc();
    $stmt->close();
    
    // Get all messages between current user and the other user
    $stmt = $conn->prepare("SELECT m.*, u.username AS sender_name 
                           FROM messages m
                           JOIN users u ON m.sender_id = u.user_id
                           WHERE (m.sender_id = ? AND m.receiver_id = ?)
                           OR (m.sender_id = ? AND m.receiver_id = ?)
                           ORDER BY m.created_at ASC");
    $stmt->bind_param("iiii", $_SESSION['user_id'], $other_user_id, $other_user_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $conversation_messages[] = $row;
    }
    $stmt->close();
}

// Get all conversations (users who have messaged or been messaged by the therapist)
$conversations = [];
$stmt = $conn->prepare("SELECT u.user_id, u.username, 
                       (SELECT content FROM messages 
                        WHERE (sender_id = u.user_id AND receiver_id = ?) 
                        OR (sender_id = ? AND receiver_id = u.user_id) 
                        ORDER BY created_at DESC LIMIT 1) AS last_message,
                       (SELECT COUNT(*) FROM messages 
                        WHERE receiver_id = ? AND sender_id = u.user_id AND is_read = FALSE) AS unread_count
                       FROM users u
                       WHERE u.user_id IN (
                           SELECT DISTINCT sender_id FROM messages WHERE receiver_id = ?
                           UNION
                           SELECT DISTINCT receiver_id FROM messages WHERE sender_id = ?
                       )
                       ORDER BY (SELECT MAX(created_at) FROM messages 
                                WHERE (sender_id = u.user_id AND receiver_id = ?) 
                                OR (sender_id = ? AND receiver_id = u.user_id)) DESC");
$stmt->bind_param("iiiiiii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $conversations[] = $row;
}
$stmt->close();

// Get unread messages count for the badge
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

// Get all clients who have appointments with this therapist
$clients = [];
$stmt = $conn->prepare("SELECT DISTINCT u.user_id, u.username FROM users u JOIN appointments a ON u.user_id = a.user_id WHERE a.therapist_id = ? AND a.status != 'cancelled'");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $clients[] = $row;
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
    <link rel="stylesheet" href="therapist_dashboard_custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .conversation-list {
            max-height: 70vh;
            overflow-y: auto;
        }
        .conversation-item {
            cursor: pointer;
            transition: background-color 0.2s;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .conversation-item:hover {
            background-color: #f8f9fa;
        }
        .conversation-item.active {
            background-color: #e9f7fe;
        }
        .message-container {
            max-height: 60vh;
            overflow-y: auto;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            margin-bottom: 10px;
            position: relative;
        }
        .message-sent {
            background-color: #d4edda;
            margin-left: auto;
            border-bottom-right-radius: 0;
        }
        .message-received {
            background-color: #f8f9fa;
            margin-right: auto;
            border-bottom-left-radius: 0;
            border: 1px solid #e9ecef;
        }
        .message-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 5px;
            text-align: right;
        }
        .unread-badge {
            font-size: 0.7rem;
            margin-left: 5px;
        }
        .last-message-preview {
            font-size: 0.85rem;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
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
                <li><a class="sidebar-link <?php if($current_page == 'messages.php') echo 'active'; ?>" href="messages.php"><i class="fas fa-envelope"></i>Messages<?php if (isset($unread_count) && $unread_count > 0): ?><span class="sidebar-badge"><?php echo $unread_count; ?></span><?php endif; ?></a></li>
                <li class="mt-3"><a class="sidebar-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="therapist-main">
            <div class="therapist-header" style="margin-bottom: 24px;">
                <h1 style="font-size: 2rem; color: #333;">Messages</h1>
            </div>
            <?php if ($error): ?>
                <div class="alert-therapist alert-danger-therapist"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-therapist alert-info-therapist"><?php echo $success; ?></div>
            <?php endif; ?>
            <div style="display:flex;gap:32px;flex-wrap:wrap;align-items:flex-start;">
                <!-- Conversation List -->
                <div style="flex:1 1 320px;max-width:340px;min-width:260px;">
                    <div class="card-therapist" style="margin-bottom:18px;">
                        <div class="card-header-therapist"><i class="fas fa-user-friends"></i> Select Client to Chat</div>
                        <div class="card-body-therapist">
                            <form method="get" id="clientSelectForm">
                                <div class="form-group-custom">
                                    <select class="form-control" name="conversation" id="clientSelect" onchange="document.getElementById('clientSelectForm').submit();">
                                        <option value="">-- Select Client --</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?php echo $client['user_id']; ?>" <?php if (isset($_GET['conversation']) && $_GET['conversation'] == $client['user_id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($client['username']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-therapist">
                        <div class="card-header-therapist"><i class="fas fa-comments"></i> Messages</div>
                        <div class="card-body-therapist">
                            <div class="conversation-list">
                                <?php if (empty($conversations)): ?>
                                    <div class="alert-therapist alert-info-therapist">No conversations yet</div>
                                <?php else: ?>
                                    <?php foreach ($conversations as $conversation): ?>
                                        <div class="conversation-item <?php echo ($current_conversation && $current_conversation['user_id'] == $conversation['user_id']) ? 'active' : ''; ?>" 
                                             onclick="window.location.href='messages.php?conversation=<?php echo $conversation['user_id']; ?>'">
                                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                                <span style="font-weight:600;"><?php echo htmlspecialchars($conversation['username']); ?></span>
                                                <?php if ($conversation['unread_count'] > 0): ?>
                                                    <span class="card-badge-therapist card-badge-danger unread-badge"><?php echo $conversation['unread_count']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="last-message-preview"><?php echo htmlspecialchars($conversation['last_message']); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Message Area -->
                <div style="flex:2 1 480px;min-width:320px;max-width:900px;">
                    <?php if ($current_conversation): ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                            <h3 style="font-size:1.2rem;font-weight:600;margin:0;">Conversation with <?php echo htmlspecialchars($current_conversation['username']); ?></h3>
                            <a href="clients.php?client_id=<?php echo $current_conversation['user_id']; ?>" class="btn-therapist btn-sm-therapist btn-therapist-secondary">
                                <i class="fas fa-user me-1"></i> View Profile
                            </a>
                        </div>
                        <div class="message-container mb-3">
                            <?php if (empty($conversation_messages)): ?>
                                <div class="text-center text-muted py-4">No messages yet</div>
                            <?php else: ?>
                                <?php foreach ($conversation_messages as $message): ?>
                                    <div class="message-bubble <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'message-sent' : 'message-received'; ?>">
                                        <div><?php echo htmlspecialchars($message['content']); ?></div>
                                        <div class="message-time">
                                            <?php echo date('M j, g:i a', strtotime($message['created_at'])); ?>
                                            <?php if ($message['sender_id'] == $_SESSION['user_id']): ?>
                                                <?php if ($message['is_read']): ?>
                                                    <i class="fas fa-check-double text-primary" title="Read"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-check text-muted" title="Sent"></i>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <form method="post" style="display:flex;gap:12px;align-items:flex-end;">
                            <input type="hidden" name="receiver_id" value="<?php echo $current_conversation['user_id']; ?>">
                            <textarea name="message_content" class="form-control" rows="2" placeholder="Type your message..." style="flex:1;resize:vertical;min-height:38px;"></textarea>
                            <button type="submit" name="send_message" class="btn-therapist btn-therapist-primary" style="min-width:120px;">
                                <i class="fas fa-paper-plane me-1"></i> Send
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="card-therapist">
                            <div class="card-body-therapist text-center" style="color:#888;">Select a client to start a conversation.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>