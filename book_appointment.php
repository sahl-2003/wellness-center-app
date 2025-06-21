<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.html");
    exit();
}

include('dbconnect.php');

if (!isset($conn)) {
    die("Database connection failed. Please contact the administrator.");
}

$page_title = 'Book Appointment';
$error = '';
$success = '';

$therapist_id = isset($_GET['therapist_id']) ? intval($_GET['therapist_id']) : 0;

// Get all therapists
$therapists = [];
$sql = "SELECT user_id, username FROM users WHERE role = 'therapist' ORDER BY username";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $therapists[] = $row;
    }
}

// Get all services
$services = [];
$sql = "SELECT service_id, name, duration, price FROM services WHERE is_active = 1 ORDER BY name";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

// AJAX endpoint for available slots
if (isset($_GET['fetch_slots'])) {
    header('Content-Type: application/json');
    $therapist_id = intval($_GET['therapist_id']);
    $date = $_GET['date'];
    $day_of_week = date('l', strtotime($date));
    $slots = [];
    if ($therapist_id && $date) {
        // Get all available slots for the therapist on that day
        $stmt = $conn->prepare("SELECT start_time, end_time FROM therapist_availability WHERE therapist_id = ? AND day_of_week = ?");
        $stmt->bind_param("is", $therapist_id, $day_of_week);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $slots[] = $row;
        }
        $stmt->close();
        
        // Remove slots that are already booked
        $booked = [];
        $stmt = $conn->prepare("SELECT start_time, end_time FROM appointments WHERE therapist_id = ? AND appointment_date = ?");
        $stmt->bind_param("is", $therapist_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $booked[] = $row;
        }
        $stmt->close();
        
        // Filter out booked slots
        $available = [];
        foreach ($slots as $slot) {
            $is_booked = false;
            foreach ($booked as $b) {
                if ($slot['start_time'] == $b['start_time'] && $slot['end_time'] == $b['end_time']) {
                    $is_booked = true;
                    break;
                }
            }
            if (!$is_booked) $available[] = $slot;
        }
        echo json_encode($available);
        exit;
    }
    echo json_encode([]);
    exit;
}

// AJAX endpoint for available days
if (isset($_GET['fetch_days'])) {
    header('Content-Type: application/json');
    $therapist_id = intval($_GET['therapist_id']);
    $days = [];
    if ($therapist_id) {
        $stmt = $conn->prepare("SELECT DISTINCT day_of_week FROM therapist_availability WHERE therapist_id = ?");
        $stmt->bind_param("i", $therapist_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $days[] = $row['day_of_week'];
        }
        $stmt->close();
        echo json_encode($days);
        exit;
    }
    echo json_encode([]);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $therapist_id = intval($_POST['therapist_id']);
    $service_id = intval($_POST['service_id']);
    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    $notes = trim($_POST['notes']);
    
    if (empty($therapist_id) || empty($service_id) || empty($appointment_date) || empty($start_time)) {
        $error = 'Please fill all required fields';
    } else {
        // Get service duration
        $stmt = $conn->prepare("SELECT duration FROM services WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $service = $result->fetch_assoc();
        $stmt->close();
        
        if ($service) {
            $duration = $service['duration'];
            $end_time = date('H:i:s', strtotime($start_time) + ($duration * 60));
            
            // Check for existing appointments at the same time
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM appointments 
                WHERE therapist_id = ? 
                AND appointment_date = ? 
                AND (
                    (start_time <= ? AND end_time > ?) OR
                    (start_time < ? AND end_time >= ?) OR
                    (start_time >= ? AND start_time < ?)
                )
            ");
            $stmt->bind_param("isssssss", 
                $therapist_id, 
                $appointment_date, 
                $start_time, 
                $start_time,
                $end_time, 
                $end_time,
                $start_time, 
                $end_time
            );
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row['count'] > 0) {
                $error = 'This time slot is already booked for the selected therapist. Please choose a different time or therapist.';
            } else {
                // Book appointment
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO appointments 
                        (user_id, therapist_id, service_id, appointment_date, start_time, end_time, appointment_time, notes, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
                    ");
                    $stmt->bind_param("iiisssss", $_SESSION['user_id'], $therapist_id, $service_id, 
                                     $appointment_date, $start_time, $end_time, $start_time, $notes);
                    
                    if ($stmt->execute()) {
                        $success = 'Appointment booked successfully! It is now pending approval.';
                        header("Refresh: 2; url=client_appointments.php");
                    }
                    $stmt->close();
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062) { // Duplicate entry error
                        $error = 'This time slot is already booked. Please choose another time.';
                    } else {
                        $error = 'Error booking appointment: ' . $e->getMessage();
                    }
                }
            }
        } else {
            $error = 'Invalid service selected';
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
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="booking.css">
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <style>
        .return-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        .booking-hero {
            background: url('image/s1.avif') no-repeat center center !important;
            background-size: contain !important;
            min-height: 50vh !important;
            max-width: 900px !important;
            margin: 60px auto 0 auto !important;
            padding: 60px 40px !important;
            text-align: center;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px !important;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08) !important;
        }
        .booking-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 24px;
        }
        .booking-hero-content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <a href="client_appointments.php" class="btn btn-primary return-button">
        <i class="fas fa-arrow-left"></i> Return to Appointments
    </a>
    <section class="booking-hero">
        <div class="container">
            <div class="booking-hero-content">
                <h1>Book Your Wellness Session</h1>
                <p>Select your preferred service, therapist, and available time slot</p>
            </div>
        </div>
    </section>
    <div class="booking-steps">
        <div class="container">
            <ul class="step-indicator">
                <li class="step active"><span class="step-number">1</span><span class="step-text">Service</span></li>
                <li class="step"><span class="step-number">2</span><span class="step-text">Therapist</span></li>
                <li class="step"><span class="step-number">3</span><span class="step-text">Date & Time</span></li>
                <li class="step"><span class="step-number">4</span><span class="step-text">Confirm</span></li>
            </ul>
                </div>
                </div>
    <main class="booking-main">
        <div class="container">
            <div class="booking-container">
                <div class="booking-form-container">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php elseif (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    <form class="booking-form" id="appointmentForm" method="POST" action="book_appointment.php">
                        <!-- Step 1: Service Selection -->
                        <div class="form-step active" data-step="1">
                            <h2>Select Your Service</h2>
                            <div class="service-options">
                                <?php foreach ($services as $service): ?>
                                <div class="service-option">
                                    <input type="radio" name="service_id" id="service_<?php echo $service['service_id']; ?>" value="<?php echo $service['service_id']; ?>" <?php if (empty($_POST['service_id']) && $service === reset($services)) echo 'checked'; if (!empty($_POST['service_id']) && $_POST['service_id'] == $service['service_id']) echo 'checked'; ?>>
                                    <label for="service_<?php echo $service['service_id']; ?>">
                                        <div class="service-icon">
                                            <i class="fas fa-spa"></i>
                                        </div>
                                        <div class="service-info">
                                            <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                                            <p>Duration: <?php echo htmlspecialchars($service['duration']); ?> mins</p>
                                            <div class="service-meta">
                                                <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($service['duration']); ?> mins</span>
                                                <span><i class="fas fa-rupee-sign"></i> ₹<?php echo htmlspecialchars($service['price']); ?></span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                        <?php endforeach; ?>
                            </div>
                            <div class="step-actions">
                                <button type="button" class="btn btn-primary next-step">Continue <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                        <!-- Step 2: Therapist Selection -->
                        <div class="form-step" data-step="2">
                            <h2>Select Your Therapist</h2>
                            <div class="therapist-options">
                                <?php foreach ($therapists as $therapist): ?>
                                <div class="therapist-option">
                                    <input type="radio" name="therapist_id" id="therapist_<?php echo $therapist['user_id']; ?>" value="<?php echo $therapist['user_id']; ?>" <?php if (!empty($_POST['therapist_id']) && $_POST['therapist_id'] == $therapist['user_id']) echo 'checked'; ?>>
                                    <label for="therapist_<?php echo $therapist['user_id']; ?>">
                                        <div class="therapist-info">
                                            <h3><?php echo htmlspecialchars($therapist['username']); ?></h3>
                                        </div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="step-actions">
                                <button type="button" class="btn btn-secondary prev-step"><i class="fas fa-arrow-left"></i> Back</button>
                                <button type="button" class="btn btn-primary next-step">Continue <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                        <!-- Step 3: Date & Time Selection -->
                        <div class="form-step" data-step="3">
                            <h2>Select Date & Time</h2>
                            <div class="form-group">
                                <label for="appointment_date">Date</label>
                                <input type="text" id="appointment_date" name="appointment_date" class="form-control" autocomplete="off" required>
                                </div>
                            <div class="form-group">
                                <label for="start_time">Available Time Slots</label>
                                <select id="start_time" name="start_time" class="form-control" required>
                                    <option value="">Select a time slot</option>
                                </select>
                                </div>
                            <div class="step-actions">
                                <button type="button" class="btn btn-secondary prev-step"><i class="fas fa-arrow-left"></i> Back</button>
                                <button type="button" class="btn btn-primary next-step">Continue <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                        <!-- Step 4: Confirm -->
                        <div class="form-step" data-step="4">
                            <h2>Confirm Your Appointment</h2>
                            <div class="form-group">
                                <label for="notes">Notes (optional)</label>
                                <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                            </div>
                            <div class="step-actions">
                                <button type="button" class="btn btn-secondary prev-step"><i class="fas fa-arrow-left"></i> Back</button>
                                <button type="submit" class="btn btn-success">Book Appointment</button>
                            </div>
                            </div>
                        </form>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Multi-step form navigation
        const steps = document.querySelectorAll('.form-step');
        const nextBtns = document.querySelectorAll('.next-step');
        const prevBtns = document.querySelectorAll('.prev-step');
        let currentStep = 0;
        function showStep(step) {
            steps.forEach((s, i) => {
                s.classList.toggle('active', i === step);
            });
            // Update step indicator
            document.querySelectorAll('.step-indicator .step').forEach((el, i) => {
                el.classList.toggle('active', i === step);
            });
        }
        nextBtns.forEach(btn => btn.addEventListener('click', () => {
            if (currentStep < steps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        }));
        prevBtns.forEach(btn => btn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        }));
        showStep(currentStep);
        // Date picker and slot AJAX
        let selectedTherapist = null;
        let availableDays = [];
        function fetchAvailableDays(therapistId) {
            $.get('book_appointment.php?fetch_days=1&therapist_id=' + therapistId, function(days) {
                availableDays = days;
                flatpickr('#appointment_date', {
                    dateFormat: 'Y-m-d',
                    minDate: 'today',
                    disable: [function(date) {
                        const day = date.toLocaleDateString('en-US', { weekday: 'long' });
                        return !availableDays.includes(day);
                    }]
                });
            }, 'json');
        }
        function fetchAvailableSlots(therapistId, date) {
            $.get('book_appointment.php?fetch_slots=1&therapist_id=' + therapistId + '&date=' + date, function(slots) {
                const slotSelect = document.getElementById('start_time');
                slotSelect.innerHTML = '<option value="">Select a time slot</option>';
                slots.forEach(slot => {
                    const opt = document.createElement('option');
                    opt.value = slot.start_time;
                    opt.textContent = slot.start_time + ' - ' + slot.end_time;
                    slotSelect.appendChild(opt);
                });
            }, 'json');
        }
        document.querySelectorAll('input[name="therapist_id"]').forEach(input => {
            input.addEventListener('change', function() {
                selectedTherapist = this.value;
                fetchAvailableDays(selectedTherapist);
            });
        });
        document.getElementById('appointment_date').addEventListener('change', function() {
            if (selectedTherapist && this.value) {
                fetchAvailableSlots(selectedTherapist, this.value);
            }
        });
        // Initialize with first therapist if available
        if (document.querySelector('input[name="therapist_id"]:checked')) {
            selectedTherapist = document.querySelector('input[name="therapist_id"]:checked').value;
            fetchAvailableDays(selectedTherapist);
        }
    </script>
</body>
</html>