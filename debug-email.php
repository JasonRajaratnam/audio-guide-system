<?php
/**
 * Simple Email Debug Script
 * This will show you EXACTLY what's wrong with your email configuration
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'email-functions.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .config { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .config p { margin: 5px 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Configuration Debug Tool</h1>
        
        <div class="config">
            <h3>Current Configuration</h3>
            <p><strong>SMTP Host:</strong> <?php echo defined('SMTP_HOST') ? SMTP_HOST : '❌ NOT DEFINED'; ?></p>
            <p><strong>SMTP Port:</strong> <?php echo defined('SMTP_PORT') ? SMTP_PORT : '❌ NOT DEFINED'; ?></p>
            <p><strong>SMTP Username:</strong> <?php echo defined('SMTP_USERNAME') ? SMTP_USERNAME : '❌ NOT DEFINED'; ?></p>
            <p><strong>SMTP Password:</strong> <?php echo defined('SMTP_PASSWORD') ? (SMTP_PASSWORD ? '✅ SET (hidden)' : '❌ EMPTY') : '❌ NOT DEFINED'; ?></p>
            <p><strong>From Email:</strong> <?php echo defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : '❌ NOT DEFINED'; ?></p>
            <p><strong>From Name:</strong> <?php echo defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : '❌ NOT DEFINED'; ?></p>
        </div>

        <?php
        // Check configuration
        $errors = [];
        
        if (!defined('SMTP_USERNAME') || empty(SMTP_USERNAME) || SMTP_USERNAME == 'your-email@gmail.com') {
            $errors[] = "SMTP_USERNAME not configured in config.php";
        }
        
        if (!defined('SMTP_PASSWORD') || empty(SMTP_PASSWORD) || SMTP_PASSWORD == 'xxxx xxxx xxxx xxxx') {
            $errors[] = "SMTP_PASSWORD not configured in config.php";
        }
        
        if (defined('SMTP_USERNAME') && strpos(SMTP_USERNAME, '@@') !== false) {
            $errors[] = "SMTP_USERNAME has double @ symbol - this is invalid!";
        }
        
        if (defined('SMTP_FROM_EMAIL') && strpos(SMTP_FROM_EMAIL, '@@') !== false) {
            $errors[] = "SMTP_FROM_EMAIL has double @ symbol - this is invalid!";
        }
        
        if (defined('SMTP_USERNAME') && defined('SMTP_FROM_EMAIL') && SMTP_USERNAME !== SMTP_FROM_EMAIL) {
            $errors[] = "SMTP_USERNAME and SMTP_FROM_EMAIL should be the same for Gmail";
        }
        
        if (!empty($errors)) {
            echo '<div class="error">';
            echo '<h3>❌ Configuration Errors Found:</h3><ul>';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
            echo '<p><strong>Fix these in config.php before testing!</strong></p>';
            echo '</div>';
        } else {
            echo '<div class="success">✅ Configuration looks good! Ready to test.</div>';
            
            // Test email sending
            if (isset($_POST['send_test'])) {
                $testEmail = $_POST['test_email'] ?? '';
                
                if (empty($testEmail)) {
                    echo '<div class="error">❌ Please enter a test email address</div>';
                } elseif (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                    echo '<div class="error">❌ Invalid email address format</div>';
                } else {
                    echo '<div class="warning">';
                    echo '<h3>🔄 Sending test email to: ' . htmlspecialchars($testEmail) . '</h3>';
                    echo '<p>Please wait...</p>';
                    echo '</div>';
                    
                    // Enable debug output
                    ob_start();
                    $result = sendTestEmail($testEmail);
                    $debugOutput = ob_get_clean();
                    
                    if ($result['success']) {
                        echo '<div class="success">';
                        echo '<h3>✅ Email Sent Successfully!</h3>';
                        echo '<p>' . htmlspecialchars($result['message']) . '</p>';
                        echo '<p><strong>Check your inbox (and spam folder) at:</strong> ' . htmlspecialchars($testEmail) . '</p>';
                        echo '</div>';
                    } else {
                        echo '<div class="error">';
                        echo '<h3>❌ Email Sending Failed</h3>';
                        echo '<p><strong>Error:</strong> ' . htmlspecialchars($result['message']) . '</p>';
                        echo '</div>';
                        
                        // Show common solutions
                        echo '<div class="warning">';
                        echo '<h3>🔧 Common Solutions:</h3>';
                        echo '<ul>';
                        echo '<li><strong>Invalid credentials:</strong> Make sure you are using Gmail App Password (16 characters), NOT your regular password</li>';
                        echo '<li><strong>2FA not enabled:</strong> You must enable 2-Factor Authentication to use App Passwords</li>';
                        echo '<li><strong>Wrong email:</strong> SMTP_USERNAME and SMTP_FROM_EMAIL must be the same</li>';
                        echo '<li><strong>Firewall:</strong> Port 587 might be blocked by firewall</li>';
                        echo '<li><strong>Less secure apps:</strong> If not using App Password, enable "Less secure app access" in Gmail</li>';
                        echo '</ul>';
                        echo '</div>';
                    }
                    
                    if (!empty($debugOutput)) {
                        echo '<details><summary><strong>Click to see debug output</strong></summary>';
                        echo '<pre>' . htmlspecialchars($debugOutput) . '</pre>';
                        echo '</details>';
                    }
                }
            }
            
            // Show test form
            ?>
            <form method="POST" style="margin: 30px 0;">
                <h3>Send Test Email</h3>
                <p>
                    <label for="test_email"><strong>Your Email Address:</strong></label><br>
                    <input type="email" id="test_email" name="test_email" 
                           placeholder="your-email@example.com" required
                           style="width: 100%; padding: 10px; margin: 10px 0; border: 2px solid #ddd; border-radius: 5px; font-size: 16px;">
                </p>
                <button type="submit" name="send_test" class="btn">📧 Send Test Email</button>
            </form>
            <?php
        }
        ?>
        
        <div style="margin-top: 40px; padding: 20px; background: #e7f3ff; border-left: 4px solid #2196f3; border-radius: 5px;">
            <h3 style="margin-top: 0; color: #1976d2;">📚 Gmail App Password Setup Guide</h3>
            <ol style="line-height: 1.8;">
                <li>Go to <a href="https://myaccount.google.com/security" target="_blank">Google Account Security</a></li>
                <li>Enable "2-Step Verification" if not already enabled</li>
                <li>Find "App passwords" (only visible after 2FA is enabled)</li>
                <li>Select "Mail" → "Other (custom name)" → Type "Audio Guide"</li>
                <li>Click "Generate" and copy the 16-character password</li>
                <li>Paste it in config.php as SMTP_PASSWORD</li>
            </ol>
            <p><strong>Need more help?</strong> Check EMAIL_TROUBLESHOOTING.md for detailed solutions.</p>
        </div>
        
        <p style="margin-top: 30px; text-align: center;">
            <a href="dashboard.php" class="btn">← Back to Dashboard</a>
        </p>
    </div>
</body>
</html>
