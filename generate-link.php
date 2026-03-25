<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'email-functions.php';

// Check if admin is logged in
requireAdmin();

$error = '';
$success = '';
$generatedLink = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination = trim($_POST['destination'] ?? '');
    $audioFile = trim($_POST['audio_file'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $sendEmail = isset($_POST['send_email']);
    $scheduleSend = isset($_POST['schedule_send']);
    $tourDateTime = trim($_POST['tour_datetime'] ?? '');
    
    // Validate inputs
    if (empty($destination)) {
        $error = 'Please enter a destination.';
    } elseif (empty($audioFile)) {
        $error = 'Please enter an audio filename.';
    } elseif (empty($customerEmail)) {
        $error = 'Please enter customer email.';
    } elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!audioFileExists($audioFile)) {
        $error = 'Audio file not found: ' . $audioFile;
    } elseif ($scheduleSend && empty($tourDateTime)) {
        $error = 'Please enter tour date/time for scheduled sending.';
    } else {
        // Calculate scheduled send time if needed (24 hours before tour)
        $scheduledDate = null;
        if ($scheduleSend && $tourDateTime) {
            $tourTimestamp = strtotime($tourDateTime);
            $sendTimestamp = $tourTimestamp - (24 * 3600); // 24 hours before
            $scheduledDate = date('Y-m-d H:i:s', $sendTimestamp);
        }
        
        // Create the link
        $result = createAccessLinkWithEmail($destination, $audioFile, $customerEmail, $sendEmail, $scheduledDate);
        
        if ($result) {
            $success = 'Link generated successfully!';
            $generatedLink = $result;
            
            if ($sendEmail && !$scheduleSend) {
                if ($result['email_sent']) {
                    $success .= ' Email sent to customer.';
                } else {
                    $error = 'Link created but email failed: ' . $result['email_message'];
                }
            } elseif ($scheduleSend) {
                $success .= ' Email scheduled for ' . $scheduledDate;
            }
        } else {
            $error = 'Failed to create link. Check permissions.';
        }
    }
}

// Get list of audio files
$audioFiles = [];
if (is_dir(AUDIO_PATH)) {
    $files = scandir(AUDIO_PATH);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'mp3') {
            $audioFiles[] = $file;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Link - Audio Guide System</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .form-section h3 {
            margin: 0 0 15px;
            color: #333;
            font-size: 1.1rem;
        }
        
        .checkbox-group {
            margin: 15px 0;
        }
        
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }
        
        .datetime-input {
            display: none;
            margin-top: 10px;
        }
        
        .datetime-input.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h2>🎧 Audio Guide</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav class="nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="manage-links.php" class="nav-item">
                    <span class="icon">🔗</span>
                    <span>Manage Links</span>
                </a>
                <a href="generate-link.php" class="nav-item active">
                    <span class="icon">➕</span>
                    <span>Create New Link</span>
                </a>
                <a href="test-email.php" class="nav-item">
                    <span class="icon">✉️</span>
                    <span>Test Email</span>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <span class="icon">🚪</span>
                    <span>Logout</span>
                </a>
            </nav>
            
            <div class="user-info">
                <p>Logged in as:</p>
                <p><strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></p>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="page-header">
                <h1>Generate New Link</h1>
                <p>Create a secure audio guide link for your customer</p>
            </header>
            
            <div class="section">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3>📋 Basic Information</h3>
                        
                        <div class="form-group">
                            <label for="customer_email">Customer Email *</label>
                            <input type="email" id="customer_email" name="customer_email" 
                                   placeholder="customer@example.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="destination">Tour Destination *</label>
                            <input type="text" id="destination" name="destination" 
                                   placeholder="e.g., Colombo City Tour" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="audio_file">Audio File *</label>
                            <?php if (count($audioFiles) > 0): ?>
                                <select id="audio_file" name="audio_file" required>
                                    <option value="">-- Select Audio File --</option>
                                    <?php foreach ($audioFiles as $file): ?>
                                        <option value="<?php echo htmlspecialchars($file); ?>">
                                            <?php echo htmlspecialchars($file); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" id="audio_file" name="audio_file" 
                                       placeholder="e.g., colombo-tour.mp3" required>
                                <p class="hint" style="color: orange;">⚠️ No audio files found in audio-files/ folder</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Email Options -->
                    <div class="form-section">
                        <h3>✉️ Email Options</h3>
                        
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" id="send_email" name="send_email" checked>
                                <span>Send email to customer immediately</span>
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" id="schedule_send" name="schedule_send">
                                <span>Schedule email (send 24 hours before tour)</span>
                            </label>
                        </div>
                        
                        <div class="datetime-input" id="datetime_container">
                            <label for="tour_datetime">Tour Date & Time *</label>
                            <input type="datetime-local" id="tour_datetime" name="tour_datetime">
                            <p class="hint">Email will be sent 24 hours before this time</p>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 16px;">
                        🔗 Generate Link
                    </button>
                </form>
                
                <?php if ($generatedLink): ?>
                    <div class="form-section" style="background: #e8f5e9; border: 2px solid #4caf50; margin-top: 30px;">
                        <h3 style="color: #2e7d32;">✅ Link Generated Successfully!</h3>
                        
                        <div style="margin: 15px 0;">
                            <strong>Customer:</strong> <?php echo htmlspecialchars($generatedLink['customer_email']); ?>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Destination:</strong> <?php echo htmlspecialchars($generatedLink['destination']); ?>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Expires:</strong> <?php echo $generatedLink['expires_date']; ?>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Access Link:</strong><br>
                            <input type="text" value="<?php echo htmlspecialchars($generatedLink['url']); ?>" 
                                   readonly style="margin-top: 5px;" id="generated_link">
                            <button type="button" class="btn btn-success btn-sm" 
                                    onclick="copyGeneratedLink()" style="margin-top: 10px;">
                                📋 Copy Link
                            </button>
                        </div>
                        
                        <?php if (isset($generatedLink['email_sent'])): ?>
                            <div style="margin: 15px 0; padding: 10px; background: #fff; border-radius: 5px;">
                                <?php if ($generatedLink['email_sent']): ?>
                                    <span style="color: #2e7d32;">✅ Email sent successfully!</span>
                                <?php else: ?>
                                    <span style="color: #c62828;">❌ Email sending failed</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // Toggle datetime input based on schedule checkbox
        const scheduleSendCheckbox = document.getElementById('schedule_send');
        const sendEmailCheckbox = document.getElementById('send_email');
        const datetimeContainer = document.getElementById('datetime_container');
        const datetimeInput = document.getElementById('tour_datetime');
        
        scheduleSendCheckbox.addEventListener('change', function() {
            if (this.checked) {
                datetimeContainer.classList.add('show');
                datetimeInput.required = true;
                sendEmailCheckbox.checked = false;
                sendEmailCheckbox.disabled = true;
            } else {
                datetimeContainer.classList.remove('show');
                datetimeInput.required = false;
                sendEmailCheckbox.disabled = false;
            }
        });
        
        function copyGeneratedLink() {
            const linkInput = document.getElementById('generated_link');
            linkInput.select();
            document.execCommand('copy');
            alert('Link copied to clipboard!');
        }
    </script>
</body>
</html>