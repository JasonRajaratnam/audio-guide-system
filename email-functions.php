<?php
require_once 'config.php';

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

/**
 * Send email using PHPMailer
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string $textBody Plain text email body (optional)
 * @return array Result with success status and message
 */
function sendEmail($to, $subject, $htmlBody, $textBody = '')
{
  $mail = new PHPMailer(true);

  try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    // Uncomment these lines for debugging email issues
    // $mail->SMTPDebug = 2;
    // $mail->Debugoutput = 'html';

    // Recipients
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($to);
    $mail->addReplyTo(COMPANY_EMAIL, COMPANY_NAME);

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $htmlBody;
    $mail->AltBody = !empty($textBody) ? $textBody : strip_tags($htmlBody);

    $mail->send();
    return ['success' => true, 'message' => 'Email sent successfully'];
  } catch (Exception $e) {
    return ['success' => false, 'message' => "Email error: {$mail->ErrorInfo}"];
  }
}

/**
 * Generate HTML email template for audio guide link
 * @param array $linkData Link information
 * @return string HTML email content
 */
function generateAudioGuideEmail($linkData)
{
  $destination = htmlspecialchars($linkData['destination']);
  $customerEmail = htmlspecialchars($linkData['customer_email']);
  $linkUrl = htmlspecialchars($linkData['url']);
  $expiresDate = date('F j, Y \a\t g:i A', $linkData['expires_at']);
  $timeRemaining = getTimeRemaining($linkData['expires_at']);

  $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Audio Guide is Ready!</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">🎧 Your Audio Guide is Ready!</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="font-size: 16px; color: #333; margin: 0 0 20px;">Dear Traveler,</p>
                            
                            <p style="font-size: 16px; color: #333; margin: 0 0 20px; line-height: 1.6;">
                                Your personalized audio guide for <strong>{$destination}</strong> is now available! 
                                Get ready to explore with our expertly crafted audio tour.
                            </p>
                            
                            <!-- Tour Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9fa; border-radius: 8px; margin: 30px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 10px; font-size: 14px; color: #666;">
                                            <strong style="color: #333;">📍 Destination:</strong> {$destination}
                                        </p>
                                        <p style="margin: 0 0 10px; font-size: 14px; color: #666;">
                                            <strong style="color: #333;">⏰ Access Valid Until:</strong> {$expiresDate}
                                        </p>
                                        <p style="margin: 0; font-size: 14px; color: #666;">
                                            <strong style="color: #333;">⌛ Time Remaining:</strong> {$timeRemaining}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Access Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{$linkUrl}" 
                                           style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                                            🎧 Access Your Audio Guide
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Instructions -->
                            <div style="background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 30px 0;">
                                <h3 style="margin: 0 0 10px; font-size: 16px; color: #1976d2;">📱 How to Use:</h3>
                                <ol style="margin: 0; padding-left: 20px; color: #555; font-size: 14px; line-height: 1.8;">
                                    <li>Click the button above to access your audio guide</li>
                                    <li>Play the audio directly in your browser, or</li>
                                    <li>Download it for offline listening</li>
                                    <li>Enjoy your tour at your own pace!</li>
                                </ol>
                            </div>
                            
                            <!-- Important Notice -->
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 30px 0;">
                                <h3 style="margin: 0 0 10px; font-size: 16px; color: #856404;">⚠️ Important:</h3>
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                                    This link will expire in <strong>{$timeRemaining}</strong>. 
                                    We recommend downloading the audio file for offline access during your tour.
                                </p>
                            </div>
                            
                            <!-- Support -->
                            <p style="font-size: 14px; color: #666; margin: 30px 0 0; line-height: 1.6;">
                                Need help? Contact us at <a href="mailto:{COMPANY_EMAIL}" style="color: #667eea;">{COMPANY_EMAIL}</a> 
                                or call {COMPANY_PHONE}
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #dee2e6;">
                            <p style="margin: 0 0 10px; font-size: 14px; color: #666;">
                                Happy exploring! 🌍
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #999;">
                                © 2025 {COMPANY_NAME}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

  return $html;
}

/**
 * Send audio guide link email
 * @param array $linkData Link information
 * @return array Result with success status and message
 */
function sendAudioGuideEmail($linkData)
{
  $to = $linkData['customer_email'];
  $subject = "🎧 Your Audio Guide for {$linkData['destination']} is Ready!";
  $htmlBody = generateAudioGuideEmail($linkData);

  return sendEmail($to, $subject, $htmlBody);
}

/**
 * Test email configuration
 * @param string $testEmail Email to send test to
 * @return array Result with success status and message
 */
function sendTestEmail($testEmail)
{
  $subject = "Test Email - Audio Guide System";
  $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2 style="color: #667eea;">✅ Email Configuration Test</h2>
    <p>If you're reading this, your email configuration is working correctly!</p>
    <p><strong>Server:</strong> {SMTP_HOST}</p>
    <p><strong>From:</strong> {SMTP_FROM_EMAIL}</p>
    <p><strong>Time:</strong> {date('Y-m-d H:i:s')}</p>
</body>
</html>
HTML;

  return sendEmail($testEmail, $subject, $htmlBody);
}
