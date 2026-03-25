# Email Setup & Troubleshooting Guide

## Current Issues Found

### 1. Invalid Email Address in config.php
**Problem:** Email has double @ symbol
```php
// ❌ WRONG
define('SMTP_USERNAME', 'jasonrajaratnam@email@gmail.com');
define('SMTP_FROM_EMAIL', 'jasonrajaratnam@email@gmail.com');

// ✅ CORRECT
define('SMTP_USERNAME', 'jasonrajaratnam@gmail.com');
define('SMTP_FROM_EMAIL', 'jasonrajaratnam@gmail.com');
```

### 2. Malformed HTML in test-email.php
Line 72 has syntax error - will be fixed in the corrected version.

---

## Step-by-Step Gmail Setup

### Step 1: Enable 2-Factor Authentication
1. Go to: https://myaccount.google.com/security
2. Find "2-Step Verification"
3. Click "Get Started" and follow the prompts
4. Verify with your phone

### Step 2: Generate App Password
1. Still in Google Account → Security
2. Look for "App passwords" (only visible after 2FA is enabled)
3. Click "App passwords"
4. Select:
   - **App:** Mail
   - **Device:** Other (custom name)
5. Type: "Audio Guide System"
6. Click "Generate"
7. **COPY THE 16-CHARACTER PASSWORD** (shown as: xxxx xxxx xxxx xxxx)

### Step 3: Update config.php

Edit `config.php` and replace with your actual details:

```php
// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'youremail@gmail.com');  // Your ACTUAL Gmail address
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx');   // The 16-char App Password from Step 2
define('SMTP_FROM_EMAIL', 'youremail@gmail.com'); // Same as SMTP_USERNAME
define('SMTP_FROM_NAME', 'Audio Guide Tours');
```

**IMPORTANT:**
- Use the SAME email for SMTP_USERNAME and SMTP_FROM_EMAIL
- Remove any extra @ symbols
- Use the App Password, NOT your regular Gmail password
- Keep the spaces in the App Password (they're fine)

---

## Common Email Errors & Solutions

### Error: "SMTP connect() failed"
**Causes:**
- Wrong SMTP host or port
- Firewall blocking port 587
- Server doesn't allow outbound SMTP

**Solutions:**
```php
// Try SSL instead of TLS
define('SMTP_PORT', 465);
// Then update email-functions.php line 31:
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Change from STARTTLS
```

### Error: "Invalid credentials" or "Authentication failed"
**Causes:**
- Using regular password instead of App Password
- Wrong email address
- App Password not generated correctly

**Solutions:**
1. Delete old App Password and generate a new one
2. Double-check email address has no typos
3. Make sure 2FA is enabled
4. Try removing spaces from App Password:
   ```php
   define('SMTP_PASSWORD', 'xxxxxxxxxxxxxxxx'); // No spaces
   ```

### Error: "Could not instantiate mail function"
**Causes:**
- PHP mail() function disabled
- Missing PHPMailer files

**Solutions:**
1. Ensure phpmailer/ folder exists and contains:
   - PHPMailer.php
   - SMTP.php
   - Exception.php
2. Check file permissions: `chmod 644 phpmailer/*.php`

### Error: "From address does not match authenticated user"
**Causes:**
- SMTP_FROM_EMAIL different from SMTP_USERNAME

**Solution:**
```php
// Make them the same!
define('SMTP_USERNAME', 'youremail@gmail.com');
define('SMTP_FROM_EMAIL', 'youremail@gmail.com');
```

---

## Testing Your Email Setup

### Method 1: Use test-email.php (Recommended)
1. Login to admin panel
2. Go to: `http://localhost/audio-guide-system/test-email.php`
3. Enter your email address
4. Click "Send Test Email"
5. Check your inbox (and spam folder!)

### Method 2: Manual PHP Test
Create a file `test-simple-email.php`:

```php
<?php
require_once 'config.php';
require_once 'email-functions.php';

$result = sendTestEmail('your-email@gmail.com'); // Use YOUR email
echo '<pre>';
print_r($result);
echo '</pre>';
```

Run it: `http://localhost/audio-guide-system/test-simple-email.php`

---

## Alternative Email Providers

### Using SendGrid (Free tier: 100 emails/day)

1. Sign up: https://sendgrid.com/
2. Create API key
3. Update config.php:

```php
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'apikey'); // Literally the word "apikey"
define('SMTP_PASSWORD', 'SG.xxxxxxxxxxxxxxxxxxxxx'); // Your API key
define('SMTP_FROM_EMAIL', 'youremail@yourdomain.com');
```

### Using Mailgun (Free tier: 10,000 emails/month)

1. Sign up: https://mailgun.com/
2. Verify domain
3. Get SMTP credentials
4. Update config.php:

```php
define('SMTP_HOST', 'smtp.mailgun.org');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'postmaster@mg.yourdomain.com');
define('SMTP_PASSWORD', 'your-mailgun-password');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
```

### Using Office 365/Outlook

```php
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'yourname@outlook.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_FROM_EMAIL', 'yourname@outlook.com');
```

---

## Debugging Tips

### Enable Debug Output

Edit `email-functions.php`, add after line 26:

```php
$mail->SMTPDebug = 2; // Enable verbose debug output
$mail->Debugoutput = 'html'; // Output format
```

This will show detailed SMTP conversation.

### Check PHP Error Log

**Windows (XAMPP):**
```
C:\xampp\apache\logs\error.log
```

**Linux:**
```
/var/log/apache2/error.log
```

Look for lines containing "PHPMailer" or "SMTP"

### Test SMTP Connection

Create `test-smtp-connection.php`:

```php
<?php
$host = 'smtp.gmail.com';
$port = 587;

$connection = @fsockopen($host, $port, $errno, $errstr, 30);

if ($connection) {
    echo "✅ Successfully connected to $host:$port<br>";
    echo "SMTP server is reachable!";
    fclose($connection);
} else {
    echo "❌ Failed to connect to $host:$port<br>";
    echo "Error: $errstr ($errno)<br>";
    echo "Possible causes:<br>";
    echo "- Firewall blocking port $port<br>";
    echo "- Server doesn't allow outbound connections<br>";
    echo "- SMTP host is incorrect<br>";
}
```

---

## Security Best Practices

1. **Never commit config.php to Git:**
   ```bash
   # Add to .gitignore
   echo "config.php" >> .gitignore
   ```

2. **Use environment variables (advanced):**
   ```php
   define('SMTP_PASSWORD', getenv('EMAIL_APP_PASSWORD'));
   ```

3. **Restrict file permissions:**
   ```bash
   chmod 600 config.php  # Owner read/write only
   ```

4. **Use a dedicated email account:**
   - Don't use your personal email
   - Create a separate Gmail account for the application

---

## Quick Checklist

Before asking for help, verify:

- [ ] Email address has NO double @ symbols
- [ ] Using Gmail App Password (16 characters)
- [ ] 2-Factor Authentication is enabled on Gmail
- [ ] SMTP_USERNAME and SMTP_FROM_EMAIL are the same
- [ ] Port 587 is not blocked
- [ ] PHPMailer files are present
- [ ] No PHP errors in error log
- [ ] Test email sent to spam folder (check there!)

---

## What to Do If Still Failing

1. **Check the exact error message** from test-email.php
2. **Enable debug mode** in email-functions.php
3. **Test SMTP connection** using the script above
4. **Try alternative port:**
   - Port 465 with SSL instead of 587 with TLS
5. **Contact your hosting provider:**
   - Some hosts block outbound SMTP
   - May need to use specific SMTP relay

---

## Sample Working Configuration (Gmail)

```php
// ✅ This should work if you follow the steps
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'audioguidetours@gmail.com');
define('SMTP_PASSWORD', 'abcd efgh ijkl mnop'); // 16-char App Password
define('SMTP_FROM_EMAIL', 'audioguidetours@gmail.com');
define('SMTP_FROM_NAME', 'Audio Guide Tours');
```

Replace with YOUR actual email and App Password!

---

## Still Need Help?

Include this information when asking for support:

1. Error message from test-email.php (exact text)
2. Your email provider (Gmail, Outlook, etc.)
3. Whether 2FA is enabled
4. Whether you generated an App Password
5. SMTP debug output (with password removed!)
6. PHP version: `<?php echo phpversion(); ?>`

---

**Most common issue:** Using regular password instead of App Password! 🔑
