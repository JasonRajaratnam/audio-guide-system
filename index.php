<?php
require_once 'config.php';
require_once 'functions.php';

// Get token from URL
$token = $_GET['token'] ?? '';

// Validate token
$linkData = validateToken($token);

// Check if there's an error
if (isset($linkData['error'])) {
  $errorType = $linkData['error'];
  $errorMessage = $linkData['message'];

  // Show error page
?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Audio Guide</title>
    <link rel="stylesheet" href="assets/css/style.css">
  </head>

  <body>
    <div class="container error-container">
      <div class="error-icon">⛔</div>
      <h1>Access Denied</h1>
      <p><?php echo htmlspecialchars($errorMessage); ?></p>
      <p style="margin-top: 20px; color: #666;">
        <?php if ($errorType === 'expired'): ?>
          This link was only valid for 24 hours. Please contact support for assistance.
        <?php else: ?>
          Please check your link and try again.
        <?php endif; ?>
      </p>
    </div>
  </body>

  </html>
<?php
  exit;
}

// If we reach here, token is valid!
$destination = $linkData['destination'];
$audioFile = $linkData['audio_file'];
$timeRemaining = getTimeRemaining($linkData['expires_at']);
$customerEmail = $linkData['customer_email'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($destination); ?> - Audio Guide</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <div class="container">
    <h1>🎧 Audio Guide</h1>
    <p class="subtitle">Welcome to your personalized tour</p>

    <div class="time-remaining">
      ⏰ Access expires in: <strong><?php echo $timeRemaining; ?></strong>
    </div>

    <div class="audio-player">
      <div class="audio-player-header">
        <div class="destination-name"><?php echo htmlspecialchars($destination); ?></div>
        <div class="audio-filename"><?php echo htmlspecialchars($audioFile); ?></div>
      </div>

      <div class="waveform">
        <div class="waveform-progress" id="waveformProgress"></div>
      </div>

      <div class="progress-bar" id="progressBar">
        <div class="progress-fill" id="progressFill"></div>
      </div>

      <div class="time-display">
        <span id="currentTime">0:00</span>
        <span id="duration">0:00</span>
      </div>

      <div class="controls">
        <button class="control-btn" id="rewindBtn" title="Rewind 10 seconds">⏪</button>
        <button class="control-btn play-btn" id="playBtn" title="Play/Pause">▶</button>
        <button class="control-btn" id="forwardBtn" title="Forward 10 seconds">⏩</button>
      </div>

      <div class="volume-control">
        <span class="volume-icon" id="volumeIcon">🔊</span>
        <div class="volume-slider" id="volumeSlider">
          <div class="volume-fill" id="volumeFill"></div>
        </div>
      </div>
    </div>

    <div class="download-section">
      <button class="download-btn" onclick="downloadAudio()">
        <span>⬇️</span>
        <span>Download for Offline Listening</span>
      </button>

      <div class="download-info">
        <strong>💡 Offline Listening:</strong><br>
        Download this audio guide to listen without internet connection.
        The file will remain accessible on your device even after this link expires,
        but you won't be able to download it again after expiry.
      </div>
    </div>
  </div>

  <script src="assets/js/player.js"></script>
</body>

</html>