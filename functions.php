<?php
/**
 * Audio Guide System - Core Functions
 * Contains all database operations and utility functions
 */

/**
 * Read database from JSON file
 * @return array Database contents
 */
function readDatabase() {
    if (!file_exists(DB_PATH)) {
        return ['links' => []];
    }
    
    $json = file_get_contents(DB_PATH);
    $data = json_decode($json, true);
    
    return $data ?: ['links' => []];
}

/**
 * Write database to JSON file
 * @param array $data Data to write
 * @return bool Success status
 */
function writeDatabase($data) {
    $json = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents(DB_PATH, $json) !== false;
}

/**
 * Check if admin is logged in
 * Redirects to login page if not
 */
function requireAdmin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Validate a token and return link data
 * @param string $token Token to validate
 * @return array Link data or error
 */
function validateToken($token) {
    if (empty($token)) {
        return [
            'error' => 'invalid',
            'message' => 'No access token provided.'
        ];
    }
    
    $db = readDatabase();
    $now = time();
    
    // Find the link
    foreach ($db['links'] as $link) {
        if ($link['token'] === $token) {
            // Check if expired
            if ($now > $link['expires_at']) {
                return [
                    'error' => 'expired',
                    'message' => 'This link has expired.'
                ];
            }
            
            // Valid link
            return $link;
        }
    }
    
    // Token not found
    return [
        'error' => 'invalid',
        'message' => 'Invalid access token.'
    ];
}

/**
 * Check if an audio file exists
 * @param string $filename Audio filename
 * @return bool File exists
 */
function audioFileExists($filename) {
    $filePath = AUDIO_PATH . $filename;
    return file_exists($filePath);
}

/**
 * Create a new access link
 * @param string $destination Tour destination
 * @param string $audioFile Audio filename
 * @param string $customerEmail Customer email
 * @return array|bool Link data on success, false on failure
 */
function createAccessLink($destination, $audioFile, $customerEmail) {
    // Generate unique token
    $token = md5(uniqid($customerEmail . time(), true));
    
    // Calculate expiry time
    $createdAt = time();
    $expiresAt = $createdAt + (LINK_EXPIRY_HOURS * 3600);
    
    // Create link data
    $linkData = [
        'token' => $token,
        'destination' => $destination,
        'audio_file' => $audioFile,
        'customer_email' => $customerEmail,
        'created_at' => $createdAt,
        'expires_at' => $expiresAt,
        'created_date' => date('Y-m-d H:i:s', $createdAt),
        'expires_date' => date('Y-m-d H:i:s', $expiresAt),
        'status' => 'active'
    ];
    
    // Read database
    $db = readDatabase();
    
    // Add new link
    $db['links'][] = $linkData;
    
    // Write database
    if (writeDatabase($db)) {
        // Add URL to return data
        $linkData['url'] = BASE_URL . '/index.php?token=' . $token;
        return $linkData;
    }
    
    return false;
}

/**
 * Get time remaining for a timestamp
 * @param int $timestamp Expiry timestamp
 * @return string Formatted time remaining
 */
function getTimeRemaining($timestamp) {
    $now = time();
    $diff = $timestamp - $now;
    
    if ($diff <= 0) {
        return 'Expired';
    }
    
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    
    if ($hours > 0) {
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ', ' . 
               $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    } else {
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    }
}

/**
 * Get dashboard statistics
 * @return array Statistics data
 */
function getDashboardStats() {
    $db = readDatabase();
    $links = $db['links'];
    $now = time();
    
    $stats = [
        'total' => count($links),
        'active' => 0,
        'expired' => 0,
        'recent' => []
    ];
    
    foreach ($links as $link) {
        if ($now <= $link['expires_at']) {
            $stats['active']++;
        } else {
            $stats['expired']++;
        }
        
        // Get last 5 links
        if (count($stats['recent']) < 5) {
            $stats['recent'][] = $link;
        }
    }
    
    // Sort recent links by creation date (newest first)
    usort($stats['recent'], function($a, $b) {
        return $b['created_at'] - $a['created_at'];
    });
    
    return $stats;
}

/**
 * Get all links with optional filtering
 * @param string $filter Filter type: 'all', 'active', 'expired'
 * @param string $search Search term
 * @return array Filtered links
 */
function getFilteredLinks($filter = 'all', $search = '') {
    $db = readDatabase();
    $links = $db['links'];
    $now = time();
    $results = [];
    
    foreach ($links as $link) {
        // Apply status filter
        if ($filter === 'active' && $now > $link['expires_at']) {
            continue;
        }
        if ($filter === 'expired' && $now <= $link['expires_at']) {
            continue;
        }
        
        // Apply search filter
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $matchEmail = strpos(strtolower($link['customer_email']), $searchLower) !== false;
            $matchDestination = strpos(strtolower($link['destination']), $searchLower) !== false;
            $matchToken = strpos(strtolower($link['token']), $searchLower) !== false;
            
            if (!$matchEmail && !$matchDestination && !$matchToken) {
                continue;
            }
        }
        
        // Add status indicator
        $link['is_active'] = ($now <= $link['expires_at']);
        $results[] = $link;
    }
    
    // Sort by creation date (newest first)
    usort($results, function($a, $b) {
        return $b['created_at'] - $a['created_at'];
    });
    
    return $results;
}

/**
 * Delete a link by token
 * @param string $token Token to delete
 * @return bool Success status
 */
function deleteLink($token) {
    $db = readDatabase();
    $newLinks = [];
    $found = false;
    
    foreach ($db['links'] as $link) {
        if ($link['token'] !== $token) {
            $newLinks[] = $link;
        } else {
            $found = true;
        }
    }
    
    if ($found) {
        $db['links'] = $newLinks;
        return writeDatabase($db);
    }
    
    return false;
}

/**
 * Get a specific link by token
 * @param string $token Token to find
 * @return array|null Link data or null if not found
 */
function getLinkByToken($token) {
    $db = readDatabase();
    
    foreach ($db['links'] as $link) {
        if ($link['token'] === $token) {
            return $link;
        }
    }
    
    return null;
}

/**
 * Update a link's data
 * @param string $token Token to update
 * @param array $newData New data to merge
 * @return bool Success status
 */
function updateLink($token, $newData) {
    $db = readDatabase();
    $found = false;
    
    foreach ($db['links'] as $index => $link) {
        if ($link['token'] === $token) {
            $db['links'][$index] = array_merge($link, $newData);
            $found = true;
            break;
        }
    }
    
    if ($found) {
        return writeDatabase($db);
    }
    
    return false;
}
/**
 * Create access link with email option
 * @param string $destination Tour destination
 * @param string $audioFile Audio filename
 * @param string $customerEmail Customer email
 * @param bool $sendEmail Whether to send email immediately
 * @param string $scheduledDate Optional scheduled date (Y-m-d H:i:s format)
 * @return array Link data or false
 */
function createAccessLinkWithEmail($destination, $audioFile, $customerEmail, $sendEmail = false, $scheduledDate = null) {
    // Create the link first
    $linkData = createAccessLink($destination, $audioFile, $customerEmail);
    
    if (!$linkData) {
        return false;
    }
    
    // Add scheduling info if provided
    if ($scheduledDate) {
        $linkData['scheduled_send'] = $scheduledDate;
        $linkData['email_sent'] = false;
    }
    
    // Send email if requested
    if ($sendEmail && !$scheduledDate) {
        require_once 'email-functions.php';
        $emailResult = sendAudioGuideEmail($linkData);
        $linkData['email_sent'] = $emailResult['success'];
        $linkData['email_message'] = $emailResult['message'];
    }
    
    return $linkData;
}