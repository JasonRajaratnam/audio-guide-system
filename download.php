<?php
require_once 'config.php';
require_once 'functions.php';

// Get parameters
$token = $_GET['token'] ?? '';
$action = $_GET['action'] ?? 'stream'; // 'stream' or 'download'

// Validate token
$linkData = validateToken($token);

// Check for errors
if (isset($linkData['error'])) {
  http_response_code(403);
  die('Access denied: ' . $linkData['message']);
}

// Get audio file path
$audioFile = $linkData['audio_file'];
$filePath = AUDIO_PATH . $audioFile;

// Check if file exists
if (!file_exists($filePath)) {
  http_response_code(404);
  die('Audio file not found.');
}

// Get file info
$fileSize = filesize($filePath);
$fileName = basename($audioFile);
$mimeType = 'audio/mpeg';

// Clear any previous output
if (ob_get_level()) {
  ob_end_clean();
}

// Set headers based on action
if ($action === 'download') {
  // Force download
  header('Content-Description: File Transfer');
  header('Content-Type: ' . $mimeType);
  header('Content-Disposition: attachment; filename="' . $fileName . '"');
  header('Content-Transfer-Encoding: binary');
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  header('Content-Length: ' . $fileSize);
} else {
  // Stream (play in browser)
  header('Content-Type: ' . $mimeType);
  header('Content-Length: ' . $fileSize);
  header('Accept-Ranges: bytes');
  header('Cache-Control: no-cache');

  // Handle range requests (for seeking in audio)
  if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    $range = str_replace('bytes=', '', $range);
    $range = explode('-', $range);
    $start = intval($range[0]);
    $end = isset($range[1]) && $range[1] !== '' ? intval($range[1]) : $fileSize - 1;

    header('HTTP/1.1 206 Partial Content');
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
    header('Content-Length: ' . ($end - $start + 1));

    // Open file and seek to start position
    $fp = fopen($filePath, 'rb');
    fseek($fp, $start);

    // Output the requested range
    $buffer = 8192;
    $bytesLeft = $end - $start + 1;
    while ($bytesLeft > 0 && !feof($fp)) {
      $bytesToRead = min($buffer, $bytesLeft);
      echo fread($fp, $bytesToRead);
      $bytesLeft -= $bytesToRead;
      flush();
    }

    fclose($fp);
    exit;
  }
}

// Output the file
readfile($filePath);
exit;
