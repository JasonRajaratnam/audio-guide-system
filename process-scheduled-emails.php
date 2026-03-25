<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'email-functions.php';

// This script should be run via cron job every hour
// Example cron: 0 * * * * /usr/bin/php /path/to/process-scheduled-emails.php

$db = readDatabase();
$now = time();
$processed = 0;

foreach ($db['links'] as &$link) {
  // Check if this link has a scheduled send time
  if (isset($link['scheduled_send']) && !isset($link['email_sent_at'])) {
    $scheduledTime = strtotime($link['scheduled_send']);

    // If it's time to send (or past due)
    if ($now >= $scheduledTime) {
      $result = sendAudioGuideEmail($link);

      if ($result['success']) {
        $link['email_sent_at'] = date('Y-m-d H:i:s');
        $link['email_status'] = 'sent';
        $processed++;
      } else {
        $link['email_status'] = 'failed';
        $link['email_error'] = $result['message'];
      }
    }
  }
}

// Save updated database
writeDatabase($db);

// Log results
$logMessage = date('Y-m-d H:i:s') . " - Processed {$processed} scheduled emails\n";
file_put_contents('logs/email-scheduler.log', $logMessage, FILE_APPEND);

echo "Processed {$processed} scheduled emails.\n";
