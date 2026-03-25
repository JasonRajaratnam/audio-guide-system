# Audio Guide System - Quick Start Guide

## 🚀 Installation Steps

### 1. Upload Files
Extract the `audio-guide-system-FIXED.zip` to your web server:
```bash
unzip audio-guide-system-FIXED.zip
```

### 2. Set Permissions
Make the database directory writable:
```bash
chmod 755 audio-guide-system/database/
chmod 666 audio-guide-system/database/links.json
```

### 3. Configure Settings
Edit `config.php` and update:
```php
define('SITE_URL', 'http://your-domain.com/audio-guide-system');
define('BASE_URL', 'http://your-domain.com/audio-guide-system');
```

### 4. Change Admin Password
Edit `config.php` (around line 16-23):
```php
if (!defined('ADMIN_USERNAME')) {
    define('ADMIN_USERNAME', 'your_username');
}

if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', 'your_secure_password');
}
```

⚠️ **IMPORTANT:** Only change credentials in `config.php`, NOT in `login.php`!

## 🔐 Default Login Credentials
- **URL:** `http://your-domain.com/audio-guide-system/login.php`
- **Username:** admin
- **Password:** admin123

⚠️ **Change these immediately after first login!**

## 📁 Directory Structure
```
audio-guide-system/
├── assets/          # CSS and JS files
├── audio-files/     # Place your audio files here
├── database/        # JSON database (must be writable)
├── *.php           # Application files
└── FIXES_APPLIED.md # Detailed fix documentation
```

## 🎵 Adding Audio Files
1. Upload MP3, WAV, OGG, or M4A files to `audio-files/` directory
2. Files should be under 50MB each
3. They will automatically appear in the link generation dropdown

## 📝 Creating Audio Guide Links

1. **Login** to the admin panel
2. Go to **"Create New Link"**
3. Fill in:
   - Tour Destination (e.g., "Colombo City Tour")
   - Select Audio File from dropdown
   - Customer Email
4. Click **"Generate Link"**
5. Copy the generated link and send to customer

## 🔗 Link Features
- Links expire after 24 hours (configurable)
- Customers can:
  - Stream audio directly in browser
  - Download for offline listening
  - Access beautiful audio player interface
- Admin can:
  - View all links and their status
  - Filter by active/expired
  - Search by email, destination, or token
  - Delete links

## ⚙️ System Requirements
- PHP 7.4 or higher
- Web server (Apache/Nginx)
- Write permissions on database directory
- Modern web browser for admin panel

## 🐛 Troubleshooting

**Can't login?**
- Clear browser cookies
- Check credentials in login.php
- Verify session support is enabled

**Links not working?**
- Check BASE_URL in config.php
- Verify audio files exist in audio-files/
- Check database file permissions

**Audio won't play?**
- Verify audio file exists
- Check file format (MP3 recommended)
- Ensure download.php has no errors

## 📊 Features Overview

### Admin Dashboard
- Total links count
- Active vs expired statistics
- Recent links display
- Quick action buttons

### Link Management
- View all generated links
- Filter by status
- Search functionality
- Delete unwanted links
- See creation and expiry dates

### Audio Player (Customer View)
- Beautiful waveform visualization
- Play/pause controls
- Skip forward/backward (10 seconds)
- Progress bar with seek
- Volume control
- Time remaining display
- Download button for offline access

## 🔒 Security Notes
1. Change default admin credentials immediately
2. Use HTTPS in production
3. Set proper file permissions (never 777)
4. Keep PHP updated
5. Regularly backup database/links.json

## 📈 Next Steps
1. ✅ Complete installation
2. ✅ Test login
3. ✅ Upload audio files
4. ✅ Create test link
5. ✅ Test customer experience
6. 🔐 Change admin password
7. 🌐 Configure domain settings
8. 📧 Consider adding email delivery

## 💡 Tips
- Use descriptive destination names
- Keep audio files optimized (not too large)
- Regularly clean up expired links
- Test links before sending to customers
- Monitor disk space for audio files

## 📞 Need Help?
Refer to `FIXES_APPLIED.md` for detailed technical information about all fixes applied to the system.

---

**System is ready to use! Happy streaming! 🎧**
