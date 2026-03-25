<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'email-functions.php';

// Check if admin is logged in
requireAdmin();

$testResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $testEmail = trim($_POST['test_email'] ?? '');

  if (empty($testEmail)) {
    $testResult = ['success' => false, 'message' => 'Please enter an email address'];
  } elseif (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    $testResult = ['success' => false, 'message' => 'Invalid email address'];
  } else {
    $testResult = sendTestEmail($testEmail);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test Email - Audio Guide System</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
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
        <a href="generate-link.php" class="nav-item">
          <span class="icon">➕</span>
          <span>Create New Link</span>
        </a>
        <a href="test-email.php" class="nav-item active">
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
        <h1>Test Email Configuration</h1>
        <p>Send a test email to verify your SMTP settings</p>
      </header>
      
      <div class="section">
            <!-- Current Configuration -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3 style="margin: 0 0 15px;">⚙️ Current Email Configuration</h3>
                <p><strong>SMTP Host:</strong> <?php echo SMTP_HOST; ?></p>
                <p><strong>SMTP Port:</strong> <?php echo SMTP_PORT; ?></p>
                <p><strong>From Email:</strong> <?php echo SMTP_FROM_EMAIL; ?></p>
                <p><strong>From Name:</strong> <?php echo SMTP_FROM_NAME; ?></p>
            </div>
            
            <?php if ($testResult): ?>
                <div class="alert <?php echo $testResult['success'] ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo htmlspecialchars($testResult['message']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Test Form -->
            <form method="POST">
                <div class="form-group">
                    <label for="test_email">Test Email Address *</label>
                    <input type="email" id="test_email" name="test_email" 
                           placeholder="your-email@example.com" required>
                    <p style="font-size: 14px; color: #666; margin-top: 5px;">
                        Enter your email address to receive a test email
                    </p>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                    📧 Send Test Email
                </button>
            </form>
            
            <!-- Troubleshooting -->
            <div style="margin-top: 40px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
                <h3 style="margin: 0 0 15px; color: #856404;">🔧 Troubleshooting Email Issues</h3>
                <ul style="color: #856404; line-height: 1.8;">
                    <li><strong>Gmail Users:</strong> Make sure you're using an App Password, not your regular password</li>
                    <li><strong>2-Factor Authentication:</strong> Must be enabled to use App Passwords</li>
                    <li><strong>Less Secure Apps:</strong> App Passwords bypass this setting</li>
                    <li><strong>Firewall:</strong> Ensure port 587 is not blocked</li>
                    <li><strong>Config File:</strong> Check config.php for correct credentials</li>
                </ul>
                
                <p style="margin-top: 15px; color: #856404;">
                    <strong>Still not working?</strong> Check your email provider's SMTP documentation or try an alternative service like SendGrid or Mailgun.
                </p>
            </div>
        </div>
    </main>
</div>